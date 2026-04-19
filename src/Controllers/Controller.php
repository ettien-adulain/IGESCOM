<?php

namespace App\Controllers;

use Core\Container;
use Core\Config;
use App\Utils\Logger;

/**
 * Controller - Le Pilier Central de l'Architecture MVC
 * 
 * Cette classe abstraite fournit tous les outils nécessaires aux contrôleurs 
 * enfants pour piloter l'interface et sécuriser les données.
 * 
 * @version 6.0 "Sovereign Engine"
 * @author Senior AI Architect - YAOCOM'S GROUPE
 */
abstract class Controller
{
    /** @var \Core\Http\Request Instance de capture des flux entrants */
    protected $request;

    /** @var \Core\Http\Response Instance de gestion des sorties et headers */
    protected $response;

    /** @var \PDO Instance de connexion à la base de données Gescom */
    protected $db;

    /** @var array Stockage des données globales partagées avec les vues */
    protected array $viewData = [];

    /**
     * Initialisation du moteur de contrôleur
     */
    public function __construct()
    {
        // Extraction des instances depuis le Container de services
        $this->request  = Container::get('request');
        $this->response = Container::get('response');
        $this->db       = Container::get('db');

        // Sécurité : Empêcher l'exécution si le moteur HTTP est défaillant
        if ($this->request === null || $this->response === null) {
            Logger::log("CORE_FATAL", "Échec d'initialisation du moteur HTTP.");
            die("<h2 style='color:#e11d48; font-family:sans-serif;'>Erreur Système : Moteur HTTP non résolu.</h2>");
        }

        // Chargement des constantes d'application pour les interfaces
        $this->viewData['app_name'] = Config::get('app.app_name', 'WinTech ERP');
        $this->viewData['company']  = Config::get('app.company');
    }

    /**
     * Rendu Intelligent de l'Interface (UX Multi-Couches)
     * 
     * @param string $view Chemin de la vue (ex: 'auth/login' ou 'dashboard/index')
     * @param array $data Données à injecter dans la page
     * @param bool $withLayouts Si FALSE, affiche la page seule (utile pour le Login ou PDF)
     */
    protected function render(string $view, array $data = [], bool $withLayouts = true): void
    {
        // Fusion des données globales et locales
        $data = array_merge($this->viewData, $data);
        extract($data);

        // 1. CALCUL ROBUSTE DE L'URL DE BASE (XAMPP vs PRODUCTION)
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        
        // Nettoyage pour garantir que le base_url pointe toujours vers /public
        $base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . rtrim($scriptDir, '/');

        // 2. DÉTERMINATION DU CHEMIN PHYSIQUE DES VUES
        $viewPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;

        // 3. ASSEMBLAGE DE L'INTERFACE
        try {
            // Démarrage de la temporisation de sortie pour éviter les fuites de texte
            ob_start();

            // Inclusion du Header (si demandé)
            if ($withLayouts === true) {
                $headerFile = $viewPath . 'layouts' . DIRECTORY_SEPARATOR . 'header.php';
                if (file_exists($headerFile)) require_once $headerFile;
            }

            // Inclusion de la vue spécifique
            $targetFile = $viewPath . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $view) . '.php';
            if (file_exists($targetFile)) {
                require_once $targetFile;
            } else {
                throw new \Exception("La vue demandée est introuvable : " . $view);
            }

            // Inclusion du Footer (si demandé)
            if ($withLayouts === true) {
                $footerFile = $viewPath . 'layouts' . DIRECTORY_SEPARATOR . 'footer.php';
                if (file_exists($footerFile)) require_once $footerFile;
            }

            // Envoi du flux vers le navigateur
            echo ob_get_clean();

        } catch (\Exception $e) {
            ob_end_clean();
            Logger::log("VIEW_ERROR", $e->getMessage());
            die("Erreur d'interface : " . $e->getMessage());
        }
    }

    /**
     * Middleware de Sécurité par Rôle (RBAC)
     * 
     * @param array|string $roles Rôle(s) autorisé(s) pour accéder à l'action
     * @return bool
     */
    protected function middleware($roles = []): bool
    {
        // Vérification de la session active
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login?msg=session_expired');
            return false;
        }

        // Vérification des privilèges
        if (!empty($roles)) {
            $rolesArray = is_array($roles) ? $roles : [$roles];
            $userRole = $_SESSION['user_role'] ?? 'VENDEUR';

            // Le SUPERADMIN est l'utilisateur suprême (Bypass total)
            if ($userRole === 'SUPERADMIN') return true;

            if (!in_array($userRole, $rolesArray)) {
                Logger::log("SECURITY_BREACH", "Accès refusé pour {$_SESSION['user_nom']} sur " . $_SERVER['REQUEST_URI']);
                $this->redirect('/dashboard?error=access_denied');
                return false;
            }
        }
        return true;
    }

    /**
     * Redirection Sécurisée (Intelligente)
     * 
     * @param string $path Chemin relatif (ex: '/dashboard')
     */
    protected function redirect(string $path): void
    {
        // On délègue la redirection à l'objet Response du Core
        $this->response->redirect($path);
        exit; // Arrêt impératif du script
    }

    /**
     * Réponse API JSON standardisée
     */
    protected function json(array $data, int $status = 200): void
    {
        $this->response->json($data, $status);
    }
}