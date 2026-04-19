<?php
namespace App\Controllers\Sales; // <--- TRÈS IMPORTANT : Doit correspondre au dossier

use App\Controllers\Controller;
use App\Repositories\ArticleRepository;
use App\Repositories\ClientRepository;
use App\Repositories\DocumentRepository;
use App\Services\CalculationService;
use App\Services\DocumentService;
use App\Utils\Logger;
use Exception;

/**
 * POSController - Module de Vente Directe / Caisse (Module 7)
 * Gère les ventes comptoir sans passer par l'étape Proforma.
 */
class POSController extends Controller {

    private ArticleRepository $artRepo;
    private DocumentRepository $docRepo;

    public function __construct() {
        parent::__construct();
        $this->middleware(['VENDEUR', 'COMMERCIAL', 'ADMIN', 'SUPERADMIN']);
        $this->artRepo = new ArticleRepository();
        $this->docRepo = new DocumentRepository();
    }

    /**
     * Affiche l'interface de Caisse
     */
    public function index() {
        $clientRepo = new ClientRepository();
        
        $this->render('sales/pos', [
            'title'        => 'Caisse Rapide | WinTech',
            'page_title'   => 'Vente Directe Comptoir',
            'active'       => 'management',
            'clients_list' => $clientRepo->getAll() // Pour l'auto-complétion client
        ]);
    }

    /**
     * Traitement de la vente (AJAX)
     * Génère la facture finale et décrémente les stocks immédiatement.
     */
    public function process() {
        $data = $this->request->getJson();

        if (empty($data['items'])) {
            return $this->json(['status' => 'error', 'message' => 'Le panier est vide.'], 400);
        }

        try {
            $this->db->beginTransaction();

            // 1. GÉNÉRATION DE RÉFÉRENCE FACTURE DIRECTE
            $ref = "FAC-DIR-" . date('Ymd-Hi') . "-" . $_SESSION['user_id'];

            // 2. CALCULS SÉCURISÉS CÔTÉ SERVEUR
            $financials = CalculationService::computeTotals($data['items'], $data['remise_globale'] ?? 0);

            // 3. ENREGISTREMENT DU DOCUMENT (Statut final : FACTURE_VALIDEE)
            $docId = $this->docRepo->insertDocument([
                'numero_officiel' => $ref,
                'type_doc'        => 'FACTURE',
                'id_client'       => $data['id_client'] ?? 1, // 1 = Vente Comptoir par défaut
                'id_auteur'       => $_SESSION['user_id'],
                'id_agence'       => $_SESSION['agence_id'],
                'total_ht'        => $financials['ht_net'],
                'total_tva'       => $financials['tva'],
                'net_a_payer'     => $financials['ttc'],
                'statut'          => 'FACTURE_VALIDEE'
            ]);

            // 4. BOUCLE ITEMS : ENREGISTREMENT ET SORTIE DE STOCK
            foreach ($data['items'] as $item) {
                // Insertion ligne
                $this->docRepo->insertItems($docId, [$item]);

                // MISE À JOUR DU STOCK IMMÉDIATE (Module 5)
                $this->artRepo->updateStock(
                    (int)$item['id'], 
                    (int)$item['qty'], 
                    (int)$_SESSION['agence_id'], 
                    'SUB' // Soustraction
                );
            }

            // 5. GÉNÉRATION PDF & ARCHIVAGE SAE
            $docService = new DocumentService();
            $pdfPath = $docService->generate($docId, 'FACTURE');
            $this->docRepo->updatePdfPath($docId, $pdfPath);

            Logger::log("DIRECT_SALE", "Vente $ref encaissée (Total: {$financials['ttc']} F)");

            $this->db->commit();

            return $this->json([
                'status'  => 'success',
                'pdf_url' => $pdfPath,
                'message' => 'Vente terminée avec succès.'
            ]);

        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::log("POS_ERROR", $e->getMessage());
            return $this->json(['status' => 'error', 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }
}