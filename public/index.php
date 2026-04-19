<?php
/**
 * WINTECH ERP V2.5 - SOVEREIGN BOOTSTRAPPER
 * Propriété de YAOCOM'S GROUPE - Édition GIA
 * ---------------------------------------------------------
 * Rôle : Initialisation, Sécurisation, Nettoyage et Routage.
 */

// 1. DÉFINITION DES CONSTANTES DE STRUCTURE
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__DIR__));
define('CORE', ROOT . DS . 'core');
define('APP', ROOT . DS . 'src');

// 2. HYGIÈNE DE SORTIE (Élimine le "bruit" visuel et les conflits de rendu)
// On lance un tampon de sortie pour pouvoir nettoyer l'écran en cas d'erreur
if (ob_get_level()) ob_end_clean();
ob_start();

// 3. GESTION RIGOUREUSE DES ERREURS
// En développement : On affiche tout. En production : On logue tout en silence.
ini_set('display_errors', 1); // Passer à 0 sur PlanetHoster
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 4. CONFIGURATION DE LA SESSION (Standards Bancaires)
// Empêche le vol de session et les conflits de cookies
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 5. LOCALISATION ET ENCODAGE
date_default_timezone_set('Africa/Abidjan');
mb_internal_encoding('UTF-8');
header('Content-Type: text/html; charset=utf-8');

// 6. CHARGEMENT DE L'AUTOLOADER ÉLITE
$autoload = CORE . DS . 'autoload_custom.php';
if (!file_exists($autoload)) {
    header('HTTP/1.1 500 Internal Server Error');
    die("<div style='font-family:sans-serif;padding:50px;text-align:center;'>
            <h2 style='color:#e11d48;'>ERREUR D'INFRASTRUCTURE</h2>
            <p>Le moteur de chargement [core/autoload_custom.php] est introuvable.</p>
         </div>");
}
require_once $autoload;

// 7. GESTIONNAIRE D'EXCEPTIONS GLOBAL (Audit Trail)
set_exception_handler(function($e) {
    // On nettoie tout ce qui a été affiché par erreur avant le crash
    if (ob_get_level()) ob_clean();
    
    error_log("[FATAL ERROR] " . $e->getMessage() . " in " . $e->getFile());
    
    http_response_code(500);
    $viewPath = ROOT . DS . 'views' . DS . 'errors' . DS . '500.php';
    
    if (file_exists($viewPath)) {
        include $viewPath;
    } else {
        echo "<div style='padding:40px; background:#0f172a; color:white; font-family:sans-serif; border-radius:15px; margin:50px auto; max-width:600px; border-top:5px solid #e11d48;'>
                <h2 style='color:#e11d48;'>🚨 ERREUR SYSTÈME CRITIQUE</h2>
                <p>Une anomalie a été détectée dans le noyau. L'action a été enregistrée.</p>
                <code style='color:#94a3b8; font-size:0.8rem;'>" . $e->getMessage() . "</code>
              </div>";
    }
    exit;
});

// 8. INJECTION DES DÉPENDANCES (Container)
use Core\Config;
use Core\Container;
use Core\Http\Request;
use Core\Http\Response;
use Core\Http\Router;

try {
    // Chargement de la configuration (TVA, Marges, BDD)
    Config::load();

    // Enregistrement des instances (Singletons) dans le Container
    Container::set('request',  function() { return new Request(); });
    Container::set('response', function() { return new Response(); });
    Container::set('db',       function() { return \Core\Database\Connection::getInstance(); });

    // 9. RÉSOLUTION DE L'URL ET SÉCURITÉ DE REDIRECTION
    // On récupère l'URL transmise par le .htaccess
    $url = $_GET['url'] ?? '/';

    // 10. LOGIQUE DE REDIRECTION AUTOMATIQUE (L'intelligence du point d'entrée)
    // Si l'utilisateur est à la racine, on décide de son sort selon sa session
    if ($url === '/' || $url === '') {
        if (!isset($_SESSION['user_id'])) {
            (new Response())->redirect('/login');
        } else {
            (new Response())->redirect('/dashboard');
        }
    }

    // 11. DÉMARRAGE DU ROUTER
    $router = new Router();
    
    // Déclenchement de l'aiguillage
    $router->dispatch($url);

    // 12. ENVOI DU RÉSULTAT FINAL
    // Si tout s'est bien passé, on libère le tampon
    ob_end_flush();

} catch (\Exception $e) {
    // Capturé par le set_exception_handler défini plus haut
    throw $e;
}