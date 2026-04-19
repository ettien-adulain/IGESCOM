<?php
namespace App\Controllers\ATIC;

use App\Controllers\Controller;
use App\Repositories\ArticleRepository;
use App\Services\CalculationService;
use App\Utils\Logger;
use App\Utils\Uploader;
use Exception;

/**
 * ArticleController V6.0 - Moteur du Catalogue ATIC
 * Cible : Gestion du référentiel, calculs de rentabilité et moteur de recherche.
 * Design : Elite V3 - Standard de Production.
 */
class ArticleController extends Controller {

    private ArticleRepository $articleRepo;

    public function __construct() {
        parent::__construct();
        // Sécurité : Seuls les profils autorisés accèdent au catalogue
        $this->middleware(['ADMIN', 'SUPERADMIN', 'COMMERCIAL', 'VENDEUR', 'MAGASINIER']);
        $this->articleRepo = new ArticleRepository();
    }

    /**
     * TUILE : [ARTICLE] - INDEX DU CATALOGUE
     * Affiche la grille complète des produits avec KPIs de stock
     */
    public function index() {
        try {
            $agenceId = $_SESSION['agence_id'] ?? 1;
            
            // Récupération de tous les articles avec calcul de bénéfice SQL
            $articles = $this->articleRepo->getAllATIC();

            // Statistiques pour le bandeau du haut
            $stats = [
                'total' => count($articles),
                'info'  => count(array_filter($articles, fn($a) => $a['type_article'] === 'INFO')),
                'bio'   => count(array_filter($articles, fn($a) => $a['type_article'] === 'BIOTIQUE')),
                'alerte' => count(array_filter($articles, fn($a) => $a['stock_actuel'] <= $a['stock_alerte']))
            ];

            $this->render('catalog/index', [
                'title'      => 'Catalogue ATIC | WinTech ERP',
                'page_title' => 'Référentiel des Articles',
                'active'     => 'catalog',
                'articles'   => $articles,
                'stats'      => $stats
            ]);

        } catch (Exception $e) {
            Logger::log("CATALOG_UI_ERROR", $e->getMessage());
            $this->redirect('/management?error=chargement_catalogue_echec');
        }
    }

    /**
     * ACTION : Formulaire d'ajout (ATIC 6.1)
     */
    public function create() {
        $this->middleware(['ADMIN', 'SUPERADMIN']);
        
        // Récupération des catégories pour le sélecteur
        $categories = $this->db->query("SELECT * FROM categories ORDER BY libelle ASC")->fetchAll();

        $this->render('catalog/create', [
            'page_title' => 'Enregistrement ATIC',
            'active'     => 'catalog',
            'categories' => $categories
        ]);
    }

    /**
     * ACTION : Sauvegarde transactionnelle (Logic Sage 100)
     * Inclus : Référence Auto, Marge 30%, Upload Photo
     */
    public function save() {
        $this->middleware(['ADMIN', 'SUPERADMIN']);
        $data = $this->request->all();
        $photo = $_FILES['photo'] ?? null;

        try {
            if (empty($data['designation']) || empty($data['prix_achat'])) {
                throw new Exception("Veuillez renseigner la désignation et le prix d'achat.");
            }

            $this->db->beginTransaction();

            // 1. GÉNÉRATION DE RÉFÉRENCE COMPLEXE (Module 2.2)
            // Format : REF-[INITIALE]-YYYYMMDD-HHMM-[USER_ID]
            $initiale = strtoupper(substr(trim($data['designation']), 0, 1));
            $reference = "REF-" . $initiale . "-" . date('Ymd-Hi') . "-" . $_SESSION['user_id'];

            // 2. GESTION DE LA PHOTO (Module 6.1)
            $photoPath = 'atic/default_item.png';
            if ($photo && $photo['error'] === UPLOAD_ERR_OK) {
                $photoPath = Uploader::upload($photo, 'articles/atic');
            }

            // 3. ENREGISTREMENT VIA REPOSITORY
            $articleData = [
                'reference_atic'    => $reference,
                'designation'       => strtoupper(strip_tags($data['designation'])),
                'type_article'      => $data['type_article'],
                'prix_achat'        => (float)$data['prix_achat'],
                'marge_pourcentage' => 30.00, // Constante imposée par le cahier des charges
                'stock_alerte'      => (int)($data['stock_alerte'] ?? 5),
                'fiche_technique'   => $data['fiche_technique'] ?? '',
                'photo'             => $photoPath,
                'id_categorie'      => (int)($data['id_categorie'] ?? 1)
            ];

            $this->articleRepo->insertAtic($articleData);

            // 4. ARCHIVAGE ÉLECTRONIQUE (Module 5)
            Logger::log("ATIC_CREATE", "Article $reference créé avec une marge de 30%");

            $this->db->commit();
            $this->redirect('/catalog?success=article_ajoute');

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            Logger::log("ATIC_SAVE_ERROR", $e->getMessage());
            $this->redirect('/catalog/create?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * ACTION : Fiche technique (catalog/show/{id})
     */
    public function show($id) {
        try {
            $article = $this->articleRepo->getById((int)$id);
            if (!$article) throw new Exception("Article introuvable.");

            $this->render('catalog/show', [
                'page_title' => 'Fiche Technique',
                'active'     => 'catalog',
                'article'    => $article
            ]);
        } catch (Exception $e) {
            $this->redirect('/catalog?error=fiche_introuvable');
        }
    }

    /**
     * API AJAX : Recherche intelligente (FIX POUR LA FACTURE)
     * Cible : Filtrage instantané à la première lettre.
     */
    public function ajaxSearchArticle() {
        // HYGIÈNE JSON : On vide le tampon pour éviter les erreurs de parsing
        while (ob_get_level()) ob_end_clean();
        
        $term = $this->request->input('q');
        $type = $this->request->input('type', 'INFO'); // Par défaut INFO

        if (!$term || strlen($term) < 1) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }

        try {
            // Recherche dans le Repository (Nom ou Référence)
            $results = $this->articleRepo->searchAtic($term, $type);
            
            // Formatage propre pour le moteur de recherche JS
            $data = [];
            foreach ($results as $row) {
                $data[] = [
                    'id'         => $row['id'],
                    'nom'        => $row['designation'],
                    'reference'  => $row['reference_atic'],
                    'prix_vente' => number_format($row['prix_vente_revient'], 0, '', ''),
                    'stock'      => $row['stock_actuel']
                ];
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($data);
            exit;

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur de recherche.']);
            exit;
        }
    }

    /**
     * ACTION : Suppression sécurisée
     */
    public function delete($id) {
        $this->middleware(['SUPERADMIN', 'ADMIN']);
        try {
            $this->db->prepare("DELETE FROM articles WHERE id = ?")->execute([$id]);
            Logger::log("ATIC_DELETE", "Suppression article ID: $id");
            $this->redirect('/catalog?success=suppression_reussie');
        } catch (Exception $e) {
            $this->redirect('/catalog?error=impossible_supprimer_historique');
        }
    }
}