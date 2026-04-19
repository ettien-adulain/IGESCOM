<?php
namespace App\Services;

use Core\Config;
use Core\Database\Connection;
use App\Utils\Logger;
use Exception;

/**
 * DgiApiService V5.0 - Expert Implementation
 * Gère l'authentification OAuth2, la signature FNE et la normalisation fiscale.
 */
class DgiApiService {

    private $db;
    private string $baseUrl;
    private string $apiKey;
    private string $token = '';

    // Codes Taxes DGI Côte d'Ivoire (Standard FNE)
    private const TAX_TVA   = "TVA";   // 18% Normal
    private const TAX_TVAB  = "TVAB";  // 9% Réduit
    private const TAX_TVAC  = "TVAC";  // 0% Exonéré
    private const TAX_AIRSI = "AIRSI"; // 2% Acompte Impôt (Secteur Informel)

    public function __construct() {
        $this->db = Connection::getInstance();
        
        // Intelligence : Basculement automatique selon environnement
        $isLocal = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);
        $this->baseUrl = $isLocal ? "http://54.247.95.108/ws" : Config::get('dgi.prod_url');
        $this->apiKey  = Config::get('dgi.api_key', 'kAF01gEM40r1Uz5WLJn5lxAnGMwVjCME');
    }

    /**
     * 1. AUTHENTIFICATION (JWT Bearer Token)
     */
    private function authenticate(): bool {
        if (!empty($this->token)) return true;

        try {
            $ch = curl_init($this->baseUrl . "/auth/login");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['apiKey' => $this->apiKey]));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $data = json_decode($response, true);
                $this->token = $data['token'];
                return true;
            }
            Logger::log("DGI_AUTH_ERROR", "Code: $httpCode | Res: $response");
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 2. SIGNATURE FISCALE (Normalisation de Facture)
     * Module 3.2 du Cahier des charges
     */
    public function signInvoice(int $docId): array {
        if (!$this->authenticate()) {
            return ['status' => 'error', 'message' => 'Liaison DGI impossible (Auth).'];
        }

        // Récupération des données via DocumentRepository
        $docRepo = new \App\Repositories\DocumentRepository();
        $d = $docRepo->findWithDetails($docId);

        if (!$d) throw new Exception("Données document introuvables.");

        // Construction du Payload conforme API DGI (Module 3.2 & 3.3)
        $payload = [
            "invoiceType"        => "sale",
            "paymentMethod"      => $this->mapPayment($d['conditions_paiement']),
            "template"           => ($d['type_client'] === 'PROFESSIONNEL') ? "B2B" : "B2C",
            "clientNcc"          => $d['ncc_fiscal'] ?? "",
            "clientCompanyName"  => $d['client_nom'],
            "clientPhone"        => (int)preg_replace('/[^0-9]/', '', $d['client_tel']),
            "clientEmail"        => $d['client_email'],
            "pointOfSale"        => (string)$d['id_agence'],
            "establishment"      => $d['agence_nom'],
            "commercialMessage"  => "Merci de votre confiance.",
            "items"              => $this->formatItems($d['items']),
            "foreignCurrency"    => "",
            "foreignCurrencyRate"=> 0
        ];

        try {
            $ch = curl_init($this->baseUrl . "/external/invoices/sign");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

            $result = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $res = json_decode($result, true);

            if ($status === 200 || $status === 201) {
                // SUCCÈS : On scelle la certification en base (Module 5)
                $this->updateFiscalData($docId, $res);
                return ['status' => 'success', 'data' => $res];
            }

            Logger::log("DGI_SIGN_ERROR", $res);
            return ['status' => 'error', 'message' => $res['message'] ?? 'Erreur DGI.'];

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Crash API DGI.'];
        }
    }

    /**
     * 3. FORMATAGE DES ARTICLES (Mapping Intelligent)
     */
    private function formatItems(array $items): array {
        $out = [];
        foreach ($items as $it) {
            $out[] = [
                "reference"   => $it['reference_atic'] ?? "REF-".$it['id'],
                "description" => $it['designation'],
                "quantity"    => (float)$it['quantite'],
                "amount"      => (float)$it['prix_unitaire_applique'],
                "taxes"       => [self::TAX_TVA], // TVA 18% par défaut
                "customTaxes" => $this->checkAirsi($it)
            ];
        }
        return $out;
    }

    /**
     * 4. DÉTECTION AIRSI (2%)
     */
    private function checkAirsi($it): array {
        $taxes = [];
        if (isset($it['apply_airsi']) && $it['apply_airsi']) {
            $taxes[] = ["name" => self::TAX_AIRSI, "amount" => 2];
        }
        return $taxes;
    }

    /**
     * 5. MISE À JOUR FISCALE (Module 3.2 & 5)
     */
    private function updateFiscalData(int $docId, array $res) {
        $sql = "UPDATE documents SET 
                statut = 'FACTURE_VALIDEE',
                numero_fiscal = :ref,
                token_fiscal = :tok,
                sticker_balance = :bal,
                date_certification = CURRENT_TIMESTAMP
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'ref' => $res['reference'],
            'tok' => $res['token'],
            'bal' => $res['balance_sticker'],
            'id'  => $docId
        ]);
        
        Logger::log("FISCAL_CERT", "Doc $docId normalisé : " . $res['reference']);
    }

    private function mapPayment($cond): string {
        if (stripos($cond, 'momo') !== false) return 'mobile-money';
        if (stripos($cond, 'virement') !== false) return 'transfer';
        return 'cash';
    }
}