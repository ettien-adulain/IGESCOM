<?php

namespace App\Controllers\Dashboard;

use App\Controllers\Controller;
use App\Repositories\DocumentRepository;
use App\Repositories\ArticleRepository;
use App\Repositories\ClientRepository;
use App\Repositories\HRRepository;
use App\Services\AIService;
use App\Utils\Logger;
use App\Utils\Formatter;
use Exception;

/**
 * DashboardController - Architecture Senior V6.5
 * Gère l'intelligence métier et l'agrégation de données pour YAOCOM'S GROUPE.
 * Système robuste, évolutif et conforme aux standards de production N0C.
 */
class DashboardController extends Controller
{
    /**
     * Définition explicite des propriétés typées (Supprime les erreurs IDE)
     */
    private DocumentRepository $docRepo;
    private ArticleRepository $artRepo;
    private ClientRepository $clientRepo;
    private HRRepository $hrRepo;
    private AIService $aiService;

    /**
     * Constructeur Professionnel
     */
    public function __construct()
    {
        // 1. Initialisation des outils HTTP et BDD du parent
        parent::__construct();
        
        // 2. Sécurité : Verrouillage session obligatoire
        $this->middleware();

        // 3. Injection des Repositories et Services (Élimine le soulignement rouge)
        $this->docRepo    = new DocumentRepository();
        $this->artRepo    = new ArticleRepository();
        $this->clientRepo = new ClientRepository();
        $this->hrRepo     = new HRRepository();
        $this->aiService  = new AIService();
    }

    /**
     * HUB D'ACCUEIL : Vision Stratégique et Psychologique
     * Cible : Design Image 2 (Bannière verte, Objectifs, Conseil du jour)
     */
    public function index(): void
    {
        try {
            $userId   = (int)$_SESSION['user_id'];
            $agenceId = (int)$_SESSION['agence_id'];

            // A. Calcul de performance en temps réel (Module 7)
            $caReel = $this->docRepo->getTurnover($agenceId, 'month');
            
            // Récupération de l'objectif mensuel via SQL (Table user_objectifs)
            $stmt = $this->db->prepare("
                SELECT ca_mois_cible 
                FROM user_objectifs 
                WHERE id_utilisateur = ? AND mois = ? AND annee = ?
            ");
            $stmt->execute([$userId, date('m'), date('Y')]);
            $cible = (float)($stmt->fetchColumn() ?: 5000000.00);

            // B. Préparation du Ticker (Flux d'activités récentes)
            $recentActions = $this->db->query("
                SELECT action 
                FROM journal_activite 
                ORDER BY date_log DESC LIMIT 3
            ")->fetchAll();

            $this->render('dashboard/index', [
                'title'      => 'Accueil Hub | IGESCOM GIA',
                'page_title' => 'Tableau de Bord Stratégique',
                'active'     => 'dashboard',
                'performance' => [
                    'ca_actuel'      => $caReel,
                    'ca_objectif'    => $cible,
                    'taux_atteinte'  => ($cible > 0) ? round(($caReel / $cible) * 100, 1) : 0,
                    'statut_couleur' => ($caReel >= $cible) ? 'text-success' : 'text-danger'
                ],
                'ticker' => $recentActions,
                'tip'    => $this->getBusinessIntelligenceTip()
            ]);

        } catch (Exception $e) {
            Logger::log("CRITICAL_HUB_ERROR", $e->getMessage());
            
            // Fallback fail-safe pour éviter les boucles de redirection
            $this->render('dashboard/index', [
                'page_title' => 'Hub (Mode Dégradé)',
                'active'     => 'dashboard',
                'performance' => ['taux_atteinte' => 0, 'ca_actuel' => 0, 'ca_objectif' => 0],
                'error_msg'  => "Synchronisation interrompue : " . $e->getMessage(),
                'tip'        => "Vérifiez vos tables SQL 'user_objectifs' et 'journal_activite'."
            ]);
        }
    }

    /**
     * PANNEAU DE MANAGEMENT : Gestion Opérationnelle
     * Cible : Image 1 (Grille de 12 Tuiles Gris-Bleu)
     */
    public function management(): void
    {
        // Hub affiché dans la sidebar pour tout utilisateur connecté ;
        // chaque tuile mène vers un module qui applique son propre contrôle de rôle.
        $this->middleware();

        try {
            $agenceId = (int)($_SESSION['agence_id'] ?? 0);

            $lowStock = [];
            try {
                $lowStock = $this->artRepo->getLowStock($agenceId);
            } catch (\Throwable $e) {
                Logger::log("MGMT_LOWSTOCK_DEFER", $e->getMessage());
            }

            $proformasPending = 0;
            try {
                $stPending = $this->db->prepare("
                    SELECT COUNT(*) FROM documents 
                    WHERE statut = 'ATTENTE_VAL_CLIENT' AND id_agence = ?
                ");
                $stPending->execute([$agenceId]);
                $proformasPending = (int)$stPending->fetchColumn();
            } catch (\Throwable $e) {
                Logger::log("MGMT_PROFORMA_COUNT_DEFER", $e->getMessage());
            }

            $caJour = 0.0;
            try {
                $caJour = $this->docRepo->getTurnover($agenceId, 'day');
            } catch (\Throwable $e) {
                Logger::log("MGMT_TURNOVER_DEFER", $e->getMessage());
            }

            // Agrégation dynamique des compteurs pour les badges de tuiles
            $stats = [
                'articles' => $this->artRepo->countAll(),
                'clients'  => $this->clientRepo->countAll(),
                'alerte_stock' => count($lowStock),
                'ca_jour'  => $caJour,
                'proformas_pending' => $proformasPending
            ];

            $this->render('dashboard/management', [
                'title'      => 'Gestion Administrative | WinTech',
                'page_title' => 'Panneau de Management',
                'active'     => 'management',
                'stats'      => $stats,
                'operational_stats' => [
                    'articles_epuises'  => count($lowStock),
                    'factures_attente'  => $proformasPending,
                ],
            ]);

        } catch (\Throwable $e) {
            Logger::log("MGMT_CONTROLLER_ERROR", $e->getMessage());
            $this->redirect('/dashboard?error=management_unreachable');
        }
    }

    /**
     * API : FLUX EN TEMPS RÉEL (Module 6.2)
     * Pour mise à jour du CA et des alertes sans recharger la page
     */
    public function apiLiveStats(): void
    {
        $agenceId = (int)$_SESSION['agence_id'];

        $this->json([
            'ca_live'      => Formatter::fcfa($this->docRepo->getTurnover($agenceId, 'day')),
            'stock_alerts' => count($this->artRepo->getLowStock($agenceId)),
            'server_time'  => Formatter::timeAMPM(),
            'status'       => 'SECURE_SYNC_OK'
        ]);
    }

    /**
     * GIA ASSISTANT : Intelligence Artificielle (Module 8)
     */
    public function askAI(): void
    {
        $input = $this->request->getJson();
        $query = $input['message'] ?? '';
        
        // Appel au Service IA (Cerveau GIA) corrigé
        $response = $this->aiService->analyze($query);

        $this->json(['reply' => $response]);
    }

    /**
     * LOGIQUE INTELLIGENTE : Conseil métier dynamique
     */
    private function getBusinessIntelligenceTip(): string
    {
        $tips = [
            "La satisfaction client passe par une facturation normalisée (DGI-Ready).",
            "Analysez vos marges sur les articles ATIC pour optimiser votre rentabilité.",
            "Un client bien identifié (Format Sage) est un client fidélisé.",
            "Vérifiez l'équilibre de votre journal avant chaque clôture de caisse.",
            "Le GIA Assistant peut vous aider à identifier les ruptures de stock critiques."
        ];
        return $tips[date('j') % count($tips)];
    }
}