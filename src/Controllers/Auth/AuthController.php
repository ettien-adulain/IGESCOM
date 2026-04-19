<?php
namespace App\Controllers\Auth;

use App\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Repositories\AgenceRepository;
use App\Utils\Logger;
use Exception;

/**
 * AuthController - Gardien du Noyau WinTech V2.5
 */
class AuthController extends Controller {

    private UserRepository $userRepo;
    private AgenceRepository $agenceRepo;

    public function __construct() {
        // Note : On n'appelle pas middleware() ici car cette page doit être accessible à tous
        $this->request = \Core\Container::get('request');
        $this->response = \Core\Container::get('response');
        $this->db = \Core\Database\Connection::getInstance();
        
        $this->userRepo = new UserRepository();
        $this->agenceRepo = new AgenceRepository();
    }

    /**
     * AFFICHE LE PORTAIL DE CONNEXION (SANS LAYOUTS APPLI)
     */
    public function login() {
        // Redirection si déjà en session
        if (isset($_SESSION['user_id'])) {
            $this->response->redirect('/dashboard');
        }

        try {
            // Récupération des agences pour le sélecteur
            $agences = $this->agenceRepo->getAll();

            // INTELLIGENCE : On définit les variables pour la vue
            $title = "Connexion | WinTech ERP";
            
            // Calcul du base_url manuellement pour éviter les conflits
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
            $base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/public') . '/public';

            // --- RENDER ISOLÉ ---
            // On inclut directement la vue sans passer par $this->render() 
            // pour ne pas charger la sidebar et le header de l'application.
            require_once dirname(__DIR__, 3) . '/views/auth/login.php';
            
        } catch (Exception $e) {
            Logger::log("LOGIN_VIEW_ERROR", $e->getMessage());
            die("Erreur critique d'accès au portail d'authentification.");
        }
    }

    /**
     * TRAITEMENT SÉCURISÉ DES IDENTIFIANTS
     */
    public function authenticate() {
        $matricule = $this->request->input('matricule');
        $password  = $this->request->input('password');
        $id_agence = (int)$this->request->input('id_agence');

        if (empty($matricule) || empty($password) || $id_agence <= 0) {
            $this->response->redirect('/login?error=champs_vides');
        }

        try {
            // 1. Recherche de l'utilisateur actif
            $user = $this->userRepo->findByMatricule($matricule);

            if ($user && password_verify($password, $user['password'])) {
                
                // 2. Vérification d'accès à l'agence (Module Multi-Agences)
                if (!$this->userRepo->hasAccessToAgence($user['id'], $id_agence)) {
                    Logger::log("SECURITY_ALERT", "Tentative d'intrusion: $matricule sur Agence ID $id_agence");
                    $this->response->redirect('/login?error=agence_interdite');
                }

                // 3. Récupération des infos agence pour la session
                $agence = $this->agenceRepo->findById($id_agence);

                // 4. Initialisation de la Session Elite
                $_SESSION['user_id']        = $user['id'];
                $_SESSION['user_nom']       = $user['nom_complet'];
                $_SESSION['user_role']      = $user['role'];
                $_SESSION['user_matricule'] = $user['matricule'];
                $_SESSION['agence_id']      = $id_agence;
                $_SESSION['agence_nom']     = $agence['nom'] ?? 'SIÈGE WINTECH';
                
                // 5. Audit Trail (Module 5)
                Logger::log("AUTH_SUCCESS", "Connexion réussie : " . $user['nom_complet'] . " sur " . $_SESSION['agence_nom']);

                // Redirection vers le Hub
                $this->response->redirect('/dashboard');

            } else {
                // Échec : Logs et redirection
                Logger::log("AUTH_FAILED", "Échec de connexion pour le matricule: $matricule");
                $this->response->redirect('/login?error=auth_failed');
            }

        } catch (Exception $e) {
            Logger::log("AUTH_CRITICAL", $e->getMessage());
            $this->response->redirect('/login?error=server_error');
        }
    }

    /**
     * DÉCONNEXION ET PURGE DE SESSION
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            Logger::log("AUTH_LOGOUT", "Utilisateur " . $_SESSION['user_nom'] . " s'est déconnecté.");
        }

        // Destruction propre de la session
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        $this->response->redirect('/login?msg=session_close');
    }

    /**
     * RÉINITIALISATION MDP (Module Évolutif)
     */
    public function resetPassword() {
        // Logique de changement de mot de passe à la première connexion
    }
}