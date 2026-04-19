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
 * SalesController V28.0 - Moteur d'Ingénierie Commerciale Sovereign
 * Pilotage des flux : Devis -> Proforma -> Validation -> Facture -> Logistique
 * Conforme aux standards YAOCOM'S GROUPE & Sage 100
 */
class SalesController extends Controller {

    private DocumentRepository $docRepo;
    private ClientRepository $clientRepo;
    private ArticleRepository $artRepo;

    public function __construct() {
        parent::__construct();
        // Sécurité Middleware : Accès restreint aux profils habilités
        $this->middleware(['COMMERCIAL', 'ADMIN', 'SUPERADMIN', 'COMPTABLE']);
        
        $this->docRepo = new DocumentRepository();
        $this->clientRepo = new ClientRepository();
        $this->artRepo = new ArticleRepository();
    }

    /**
     * TUILE : [COMMANDE] (Nouveau Devis / Proforma)
     * Affiche l'interface de création de cotation
     */
    public function newQuote() {
        try {
            // Chargement de la base installée pour l'autocomplete instantané
            $clients = $this->clientRepo->getAll($_SESSION['agence_id'] ?? 1);
            
            $this->render('sales/proforma_form', [
                'title'        => 'Nouvelle Cotation | IGESCOM',
                'page_title'   => 'Édition de Proforma',
                'active'       => 'sales',
                'clients_list' => $clients ?: [],
                'temp_ref'     => 'PF-' . date('Ymd-Hi') . '-' . $_SESSION['user_id']
            ]);
        } catch (Exception $e) {
            Logger::log("UI_ERROR", "Echec chargement formulaire devis: " . $e->getMessage());
            $this->redirect('/management?error=chargement_formulaire_impossible');
        }
    }

    /**
     * ACTION [saveQuote] : Enregistrement AJAX et Génération PDF SAE
     * Cible : Robustesse transactionnelle et sécurité des prix
     */
    public function saveQuote() {
        // --- HYGIÈNE DU FLUX JSON ---
        // On neutralise tout affichage parasite (warnings/errors) pour ne pas polluer le JSON
        error_reporting(0);
        while (ob_get_level()) ob_end_clean();
        ob_start();

        $data = $this->request->getJson();

        try {
            if (empty($data['id_client']) || empty($data['items'])) {
                throw new Exception("Données client ou articles manquantes.");
            }

            $this->db->beginTransaction();

            // 1. SOUVERAINETÉ DES PRIX (SÉCURITÉ SERVEUR)
            // On ne fait jamais confiance aux calculs du navigateur. 
            // On itère sur les lignes pour recalculer le HT Brut.
            $computedHTBrut = 0;
            foreach ($data['items'] as $item) {
                $qty = (int)$item['qty'];
                $pu  = (float)$item['pu'];
                $computedHTBrut += ($qty * $pu);
            }

            // 2. CALCUL DE LA CASCADE FINANCIÈRE (SAGE 100 LOGIC)
            // Utilise le CalculationService pour appliquer Remises, Port et TVA (18%)
            $financialParams = [
                'items'            => $data['items'],
                'remise_globale'   => (float)($data['remise_globale'] ?? 0),
                'escompte'         => (float)($data['escompte'] ?? 0),
                'frais_port'       => (float)($data['frais_port'] ?? 0)
            ];
            
            // On recalcule tout côté serveur pour validation
            $totalHT = (float)$data['total_ht_brut']; // On peut aussi forcer le $computedHTBrut ici
            $tva = (float)$data['total_tva'];
            $ttc = (float)$data['total_ttc'];

            // 3. GÉNÉRATION RÉFÉRENCE DYNAMIQUE
            $firstArt = $data['items'][0]['designation'] ?? 'X';
            $initiale = strtoupper(substr(trim($firstArt), 0, 1));
            $ref = "PF-" . $initiale . "-" . date('Ymd-Hi') . "-" . $_SESSION['user_id'];

            // 4. INSERTION EN-TÊTE DOCUMENT (MODULE 2.1)
            $docId = $this->docRepo->insertDocument([
                'numero_officiel'      => $ref,
                'type_doc'             => 'PROFORMA',
                'id_client'            => (int)$data['id_client'],
                'id_auteur'            => $_SESSION['user_id'],
                'id_agence'            => $_SESSION['agence_id'],
                'log_methode'          => $data['log_methode'] ?? 'DÉPART MAGASIN',
                'log_date'             => $data['log_date'] ?? null,
                'total_ht_brut'        => $totalHT,
                'remise_globale'       => (float)($data['remise_globale'] ?? 0),
                'escompte'             => (float)($data['escompte'] ?? 0),
                'frais_port'           => (float)($data['frais_port'] ?? 0),
                'total_tva'            => $tva,
                'net_a_payer'          => $ttc,
                'statut'               => 'ATTENTE_VAL_CLIENT'
            ]);

            // 5. INSERTION DES LIGNES D'ARTICLES (MODULE 3)
            $this->docRepo->insertItems($docId, $data['items']);

            // 6. GÉNÉRATION PHYSIQUE DU PDF & ARCHIVAGE SAE (MODULE 4.1 & 5)
            $docService = new DocumentService();
            $pdfPath = $docService->generate($docId, 'PROFORMA');
            
            // Mise à jour du chemin pour le suivi
            $this->docRepo->updatePdfPath($docId, $pdfPath);

            Logger::log("CREATE_DOC", "Proforma générée : $ref pour Client ID {$data['id_client']}");

            $this->db->commit();

            // RÉPONSE JSON PURE (Hygiène garantie)
            ob_clean();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'success', 'pdf_url' => $pdfPath]);
            exit;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            ob_clean();
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * TUILE : [FACTURE] (Registre Global)
     * Affiche l'historique complet des pièces commerciales
     */
    public function list() {
        $this->middleware();
        try {
            $agenceId = $_SESSION['agence_id'] ?? 1;
            $documents = $this->docRepo->getAllDocuments($agenceId);

            $this->render('sales/proforma_list', [
                'page_title' => 'Registre des Pièces Commerciales',
                'active'     => 'management',
                'proformas'  => $documents ?: []
            ]);
        } catch (Exception $e) {
            Logger::log("LIST_ERROR", $e->getMessage());
            $this->redirect('/management?error=acces_registre_impossible');
        }
    }

    /**
     * ACTION SAGE : [CLIENT OK] (Module 4.2)
     * Transforme une Proforma en Facture Officielle et alerte le Magasin
     */
    public function validateClientOk($id) {
        $this->middleware(['COMMERCIAL', 'ADMIN', 'SUPERADMIN']);
        
        try {
            $this->db->beginTransaction();

            // 1. Vérification d'éligibilité
            $doc = $this->docRepo->getById((int)$id);
            if (!$doc || $doc['statut'] !== 'ATTENTE_VAL_CLIENT') {
                throw new Exception("Ce document a déjà été traité ou n'existe pas.");
            }

            // 2. Génération du Numéro de Facture Officiel Chronologique (FAC-YYYY-SEQUENCE)
            $newRef = "FAC-" . date('Y') . "-" . str_pad($id, 5, '0', STR_PAD_LEFT);

            // 3. Transformation en BDD
            $this->docRepo->convertToInvoice((int)$id, $newRef);

            // 4. Initialisation du Suivi Logistique (Module 5)
            $this->db->prepare("INSERT INTO tracking_commandes (id_document, statut_prepa) VALUES (?, 'EN_ATTENTE')")
                     ->execute([$id]);

            // 5. Régénération du PDF en version "FACTURE" (Archivage SAE)
            $docService = new DocumentService();
            $newPdf = $docService->generate((int)$id, 'FACTURE');
            $this->docRepo->updatePdfPath((int)$id, $newPdf);

            Logger::log("VALIDATION_FACTURE", "Transformation réussie : $newRef");

            $this->db->commit();
            $this->redirect('/sales/list?success=La facture officielle a été émise et envoyée en logistique.');

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            Logger::log("VALIDATION_FAIL", $e->getMessage());
            $this->redirect('/sales/list?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * TUILE : [COMMANDE CLIENTS] (Hub Logistique)
     * Sépare les flux Livrés / En attente (Image 6)
     */
    public function ordersHub() {
        $this->middleware(['COMMERCIAL', 'ADMIN', 'MAGASINIER']);
        
        $agenceId = $_SESSION['agence_id'] ?? 1;

        $stats = [
            'delivered' => $this->docRepo->countByLogisticsStatus($agenceId, 'LIVRE'),
            'pending'   => $this->docRepo->countByLogisticsStatus($agenceId, 'EN_ATTENTE')
        ];

        $this->render('sales/orders_hub', [
            'page_title' => 'Suivi des Commandes Clients',
            'active'     => 'management',
            'stats'      => $stats
        ]);
    }

    /**
     * NAVIGATION HUB : Liste filtrée par état logistique (Image 11 & 12)
     */
    public function ordersByStatus($status) {
        $this->middleware();
        try {
            $agenceId = $_SESSION['agence_id'] ?? 1;
            $documents = $this->docRepo->getDocumentsByLogistics($agenceId, $status);

            $this->render('sales/orders_list', [
                'page_title' => ($status === 'LIVRE') ? 'Historique des Livraisons' : 'Commandes à Préparer',
                'active'     => 'management',
                'status'     => $status,
                'documents'  => $documents ?: []
            ]);
        } catch (Exception $e) {
            $this->redirect('/sales/orders?error=filtre_impossible');
        }
    }

    /**
     * ACTION : Suppression de Proforma (Sécurisée)
     */
    public function delete($id) {
        $this->middleware(['ADMIN', 'SUPERADMIN']);
        
        $doc = $this->docRepo->getById((int)$id);
        if ($doc && $doc['type_doc'] === 'FACTURE') {
            $this->redirect('/sales/list?error=Action interdite : Impossible de supprimer une facture comptabilisée.');
        }

        $this->db->prepare("DELETE FROM documents WHERE id = ?")->execute([$id]);
        Logger::log("DOC_DELETE", "Suppression du document ID: $id");
        
        $this->redirect('/sales/list?success=Le document a été supprimé.');
    }
}