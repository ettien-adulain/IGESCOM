<?php

namespace Core\Http;

use Core\Config;

/**
 * Router Intelligent - Moteur de Navigation WinTech V2.5
 * 
 * Ce composant assure la liaison entre les URLs virtuelles et les 
 * contrôleurs physiques, tout en gérant les paramètres dynamiques.
 * 
 * @author Architecte Logiciel Senior - WinTech
 */
class Router
{
    /** @var array Liste des routes chargées depuis la configuration */
    protected array $routes = [];

    /** @var array Paramètres extraits de l'URL ({id}, {slug}, etc.) */
    protected array $params = [];

    /**
     * Initialise le routeur en chargeant la cartographie des routes.
     */
    public function __construct()
    {
        $this->routes = require dirname(__DIR__, 2) . '/config/routes.php';
    }

    /**
     * Analyse l'URL entrante et exécute l'action correspondante.
     * Gère intelligemment les installations XAMPP en sous-dossiers.
     * 
     * @param string $url L'URL brute passée par le point d'entrée unique
     */
    public function dispatch(string $url)
    {
        // 1. Nettoyage de l'URL (Retrait de la Query String et des slashes superflus)
        $url = $this->removeQueryStringVariables($url);
        $url = $this->normalizeUrl($url);

        // 2. Parcourir la table de routage pour trouver une correspondance
        foreach ($this->routes as $routePath => $params) {
            
            // On normalise la route de configuration pour la comparaison
            $normalizedRoute = '/' . trim($routePath, '/');

            // Conversion des paramètres dynamiques {id} en expressions régulières (Regex)
            // {id} devient (?P<id>[a-zA-Z0-9-]+)
            $pattern = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-zA-Z0-9-]+)', $normalizedRoute);
            $pattern = '#^' . $pattern . '$#i';

            if (preg_match($pattern, $url, $matches)) {
                // Correspondance trouvée ! Extraction des arguments nommés
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $this->params[$key] = $match;
                    }
                }

                // 3. Construction dynamique du contrôleur
                // Format attendu dans routes.php : 'SousDossier\NomDuControleur'
                $controllerName = "App\\Controllers\\" . $params['controller'] . "Controller";
                $action = $params['action'];

                // 4. Vérification de robustesse (Existence de la classe et de la méthode)
                if (class_exists($controllerName)) {
                    $controllerInstance = new $controllerName();

                    if (method_exists($controllerInstance, $action)) {
                        // Exécution de l'action avec injection des paramètres extraits
                        return call_user_func_array([$controllerInstance, $action], $this->params);
                    }
                    
                    $this->abort(500, "L'action [ $action ] est manquante dans le contrôleur [ $controllerName ].");
                }
                
                $this->abort(500, "Le contrôleur [ $controllerName ] est introuvable. Vérifiez le fichier physique et le Namespace.");
            }
        }

        // 5. Aucune route ne correspond à l'URL demandée
        $this->abort(404, "La ressource [ $url ] n'existe pas ou a été déplacée.");
    }

    /**
     * Normalise l'URL pour la rendre compatible avec XAMPP et les domaines réels.
     */
    protected function normalizeUrl(string $url): string
    {
        // Retrait du chemin physique du script (C'est ici que XAMPP est géré)
        $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        
        if ($scriptPath !== '/') {
            $url = str_replace($scriptPath, '', $url);
        }

        return '/' . trim($url, '/');
    }

    /**
     * Supprime les variables de type ?id=1&name=test de l'URL pour le matching.
     */
    protected function removeQueryStringVariables(string $url): string
    {
        if ($url != '') {
            $parts = explode('&', $url, 2);
            if (strpos($parts[0], '=') === false) {
                $url = $parts[0];
            } else {
                $url = '';
            }
        }
        return $url;
    }

    /**
     * Déclenche une erreur HTTP stylisée et arrête le script.
     * 
     * @param int $code Code erreur (404, 500)
     * @param string $message Message d'erreur détaillé (utile pour le debug)
     */
    private function abort(int $code, string $message = "")
    {
        http_response_code($code);
        
        // Calcul du base_url pour que les assets (CSS/Images) de la page d'erreur chargent bien
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . $scriptDir;

        // On passe le message à la vue d'erreur
        $error_msg = $message;

        $viewPath = dirname(__DIR__, 2) . "/views/errors/{$code}.php";
        
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            // Fallback si la vue d'erreur elle-même est absente
            die("<h1>Erreur $code</h1><p>$message</p>");
        }
        exit;
    }
}