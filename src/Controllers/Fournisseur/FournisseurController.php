<?php
namespace App\Controllers\Fournisseur;

use App\Controllers\Controller;
use App\Repositories\FournisseurRepository;
use App\Utils\Uploader;
use App\Utils\Logger;
use Exception;

/**
 * FournisseurController V43.0 - Pilotage des Partenaires
 */
class FournisseurController extends Controller {

    private FournisseurRepository $repo;

    public function __construct() {
        parent::__construct();
        // Sécurité : Seuls les cadres et administratifs gèrent les tiers
        $this->middleware(['ADMIN', 'SUPERADMIN', 'COMPTABLE']);
        $this->repo = new FournisseurRepository();
    }

    /**
     * ACTION [index] : Registre des Fournisseurs
     */
    public function index() {
        try {
            $fournisseurs = $this->repo->getAll();

            $this->render('fournisseurs/index', [
                'page_title'   => 'Répertoire Fournisseurs',
                'active'       => 'fournisseurs',
                'fournisseurs' => $fournisseurs
            ]);
        } catch (Exception $e) {
            Logger::log("VIEW_ERROR", $e->getMessage());
            $this->redirect('/management?error=chargement_impossible');
        }
    }

    /**
     * ACTION [create] : Formulaire d'enrôlement multi-onglets
     */
    public function create() {
        // Pré-génération d'un code temporaire pour l'UI
        $tempCode = $this->repo->generateNextAccountCode();

        $this->render('fournisseurs/create', [
            'page_title' => 'Nouvel Enrôlement',
            'active'     => 'fournisseurs',
            'next_code'  => $tempCode
        ]);
    }

    /**
     * ACTION [save] : Moteur d'enregistrement transactionnel
     */
    public function save() {
        // 1. Capture et validation
        $data = $this->request->all();
        $logo = $_FILES['logo_file'] ?? null;

        if (empty($data['supplier_name']) || empty($data['phone_number'])) {
            $this->redirect('/fournisseurs/create?error=donnees_incompletes');
        }

        try {
            $this->db->beginTransaction();

            // 2. Intelligence : On regénère le code final basé sur le nom réel
            $data['supplier_account_code'] = $this->repo->generateNextAccountCode($data['supplier_name']);

            // 3. Gestion du Logo (Module 2 du PDF)
            if ($logo && $logo['error'] === UPLOAD_ERR_OK) {
                $data['logo_path'] = Uploader::upload($logo, 'fournisseurs');
            }

            // 4. Enregistrement via le Repository
            $this->repo->insertFullSupplier($data);

            // 5. Audit Trail (Module 5)
            Logger::log("CREATE_SUPPLIER", "Fournisseur enrôlé : " . $data['supplier_name']);

            $this->db->commit();
            $this->redirect('/fournisseurs?success=Enrôlement effectué avec succès.');

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            Logger::log("CRITICAL_ERROR", "Échec enrôlement fournisseur: " . $e->getMessage());
            $this->redirect('/fournisseurs/create?error=erreur_serveur_contactez_admin');
        }
    }

    /**
     * API AJAX : Pour l'auto-complétion dans le module Achat
     */
    public function ajaxSearch() {
        $term = $this->request->input('q');
        if (!$term) return $this->json([]);
        
        $sql = "SELECT id, nom as label, code_fournisseur as sub FROM fournisseurs WHERE nom LIKE ? LIMIT 10";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["%$term%"]);
        return $this->json($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}