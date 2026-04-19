<?php

namespace App\Controllers\Sales;

use App\Controllers\Controller;
use App\Repositories\DocumentRepository;
use App\Repositories\ClientRepository;
use App\Repositories\ArticleRepository;
use App\Services\DocumentService;
use App\Services\CalculationService;
use App\Utils\Logger;
use Exception;

/**
 * SalesController V26.0 - Moteur d'Ingénierie Commerciale Élite
 * Cible : Orchestration Sage 100, Sécurité Financière et Archivage SAE.
 * Conçu pour YAOCOM'S GROUPE.
 */
class SalesController extends Controller
{
    private DocumentRepository $docRepo;
    private ClientRepository $clientRepo;
    private ArticleRepository $artRepo;

    public function __construct()
    {
        parent::__construct();
        // Sécurité : Seuls les rôles habilités accèdent au cycle de vente
        $this->middleware(['COMMERCIAL', 'ADMIN', 'SUPERADMIN', 'COMPTABLE']);
        
        $this->docRepo = new DocumentRepository();
        $this->clientRepo = new ClientRepository();
        $this->artRepo = new ArticleRepository();
    }

    /**
     * TUILE : [COMMANDE] -> NOUVELLE COTATION
     * Affiche le formulaire de saisie intelligent conforme Sage 100
     */
    public function newQuote()
    {
        try {
            $agenceId = $_SESSION['agence_id'] ?? 1;
            
            // On pré-charge les clients pour l'auto-complétion instantanée
            $clients = $this->clientRepo->getAll($agenceId);

            $this->render('sales/proforma_form', [
                'title'        => 'Nouvelle Cotation | WinTech GIA',
                'page_title'   => 'Édition de Proforma',
                'active'       => 'management',
                'clients_list' => $clients ?: [],
                'temp_ref'     => 'PF-' . date('Ymd-Hi') . '-' . $_SESSION['user_id']
            ]);
        } catch (Exception $e) {
            Logger::log("UI_SALES_ERROR", $e->getMessage());
            $this->redirect('/management?error=chargement_formulaire_impossible');
        }
    }

    /**
     * ACTION : SAUVEGARDE TRANSACTIONNELLE (SaveFullQuote)
     * Cible : Moteur de calcul cascade et génération PDF sans blocage.
     */
    public function saveQuote()
    {
        // --- HYGIÈNE JSON ABSOLUE ---
        // On désactive l'affichage des erreurs pour ne pas polluer le flux JSON
        error_reporting(0);
        while (ob_get_level()) ob_end_clean();
        ob_start();

        $data = $this->request->getJson();

        try {
            if (empty($data['id_client']) || empty($data['items'])) {
                throw new Exception("L'en-tête client ou les lignes d'articles sont manquants.");
            }

            $this->db->beginTransaction();

            // 1. GÉNÉRATION DE LA RÉFÉRENCE UNIQUE (Module 2.2)
            // Format : REF-[INITIAL]-YYYYMMDD-HHMM-[USER_ID]
            $firstArtName = $data['items'][0]['designation'] ?? 'X';
            $initiale = strtoupper(substr(trim($firstArtName), 0, 1));
            $refUnique = "PF-" . $initiale . "-" . date('Ymd-Hi') . "-" . $_SESSION['user_id'];

            // 2. SÉCURITÉ DES PRIX : Recalcul souverain côté serveur
            // On ne fait jamais confiance aux calculs provenant du navigateur
            $totalHtBrut = 0;
            foreach ($data['items'] as $item) {
                $itemPu = (float)$item['pu'];
                $itemQty = (int)$item['qty'];
                $totalHtBrut += ($itemPu * $itemQty);
            }

            // 3. CASCADE FINANCIÈRE SAGE 100 (Recalculée via CalculationService)
            $remisePct = (float)($data['remise_globale'] ?? 0);
            $escomptePct = (float)($data['escompte'] ?? 0);
            $portVal = (float)($data['frais_port'] ?? 0);

            $valRemise = $totalHtBrut * ($remisePct / 100);
            $htApresRemise = $totalHtBrut - $valRemise;

            $valEscompte = $htApresRemise * ($escomptePct / 100);
            $htNetFinancier = $htApresRemise - $valEscompte;

            $htTaxable = $htNetFinancier + $portVal;
            $tvaMontant = $htTaxable * 0.18; // Taux CI 18%
            $netAPayer = $htTaxable + $tvaMontant;

            // 4. PERSISTANCE EN-TÊTE
            $docId = $this->docRepo->insertDocument([
                'numero_officiel'      => $refUnique,
                'type_doc'             => 'PROFORMA',
                'id_client'            => (int)$data['id_client'],
                'id_auteur'            => $_SESSION['user_id'],
                'id_agence'            => $_SESSION['agence_id'],
                'log_methode'          => $data['log_methode'] ?? 'DÉPART MAGASIN',
                'log_date'             => $data['log_date'] ?? null,
                'total_ht_brut'        => $totalHtBrut,
                'remise_globale'       => $remisePct,
                'escompte'             => $escomptePct,
                'frais_port'           => $portVal,
                'total_tva'            => $tvaMontant,
                'net_a_payer'          => $netAPayer,
                'statut'               => 'ATTENTE_VAL_CLIENT'
            ]);

            // 5. PERSISTANCE LIGNES (Picking List)
            $this->docRepo->insertItems($docId, $data['items']);

            // 6. GÉNÉRATION PDF & ARCHIVAGE SAE (Module 5)
            $docService = new DocumentService();
            $pdfPath = $docService->generate($docId, 'PROFORMA');
            
            // Mise à jour du chemin physique pour le suivi
            $this->docRepo->updatePdfPath($docId, $pdfPath);

            // 7. AUDIT TRAIL
            Logger::log("CREATE_PROFORMA", "Document $refUnique généré pour client ID: {$data['id_client']}");

            $this->db->commit();

            // 8. RÉPONSE PURE JSON (Sans aucun texte parasite)
            ob_clean();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status'  => 'success',
                'pdf_url' => $pdfPath,
                'message' => 'Proforma générée et archivée.'
            ]);
            exit;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            ob_clean();
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode([
                'status'  => 'error', 
                'message' => 'Échec de la validation financière : ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * TUILE : [FACTURE] -> RÉGISTRE DES PIÈCES
     * Affiche l'historique complet pour pilotage et contrôle
     */
    public function list()
    {
        $this->middleware();
        try {
            $agenceId = $_SESSION['agence_id'] ?? 1;
            $documents = $this->docRepo->getAllDocuments($agenceId);

            $this->render('sales/proforma_list', [
                'title'      => 'Registre des Pièces | WinTech',
                'page_title' => 'Registre Commercial',
                'active'     => 'management',
                'proformas'  => $documents ?: []
            ]);
        } catch (Exception $e) {
            Logger::log("LIST_ERROR", "Echec registre: " . $e->getMessage());
            $this->redirect('/management?error=acces_registre_impossible');
        }
    }

    /**
     * ACTION SAGE : [CLIENT OK]
     * Valide une proforma pour la transformer en facture officielle immuable
     */
    public function validateClientOk($id)
    {
        $this->middleware(['COMMERCIAL', 'ADMIN', 'SUPERADMIN', 'COMPTABLE']);
        
        try {
            $this->db->beginTransaction();

            $doc = $this->docRepo->getById((int)$id);
            if (!$doc || $doc['statut'] !== 'ATTENTE_VAL_CLIENT') {
                throw new Exception("Le document est déjà validé ou inexistant.");
            }

            // 1. Mutation en Facture Officielle
            $newRef = "FAC-" . date('Ymd') . "-" . str_pad($id, 5, '0', STR_PAD_LEFT);
            
            $sql = "UPDATE documents SET 
                    statut = 'CLIENT_OK', 
                    type_doc = 'FACTURE', 
                    numero_officiel = :ref, 
                    date_emission = CURRENT_TIMESTAMP 
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['ref' => $newRef, 'id' => $id]);

            // 2. Régénération du PDF en version "FACTURE" (Titre & Mentions)
            $docService = new DocumentService();
            $pdfPath = $docService->generate((int)$id, 'FACTURE');
            $this->docRepo->updatePdfPath((int)$id, $pdfPath);

            // 3. Archivage Logistique
            $this->db->prepare("INSERT INTO tracking_commandes (id_document, statut_prepa) VALUES (?, 'EN_ATTENTE')")
                     ->execute([$id]);

            Logger::log("INVOICE_MUTATION", "Proforma $id mutée en Facture $newRef");

            $this->db->commit();
            $this->redirect('/sales/list?success=La facture officielle a été générée et envoyée en logistique.');

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            Logger::log("VALIDATION_FAIL", $e->getMessage());
            $this->redirect('/sales/list?error=Erreur de validation workflow.');
        }
    }

    /**
     * API AJAX : Recherche dynamique d'articles (Module 2.2)
     */
    public function ajaxSearchArticle()
    {
        $this->middleware();
        $term = $this->request->input('q');
        $type = $this->request->input('type', 'INFO');

        $articles = $this->artRepo->searchAtic($term, $type);
        return $this->response->json($articles);
    }
}