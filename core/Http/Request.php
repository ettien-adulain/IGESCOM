<?php

namespace Core\Http;

/**
 * Request - Moteur de Gestion des Protocoles et Flux HTTP
 * Version : 28.0 Elite - Haute Sécurité
 */
class Request
{
    private array $data;
    private array $json;

    public function __construct()
    {
        // Fusion sécurisée des sources de données (GET et POST)
        $this->data = array_merge($_GET, $_POST);
        
        // Pré-chargement des données JSON (pour les appels AJAX/Fetch)
        $rawInput = file_get_contents('php://input');
        $this->json = json_decode($rawInput, true) ?? [];
    }

    /**
     * RÉSOUT L'ERREUR FATALE : Retourne la méthode de la requête (POST, GET, etc.)
     * @return string
     */
    public function getMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Vérifie si la méthode est POST
     */
    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    /**
     * Vérifie si la méthode est GET
     */
    public function isGet(): bool
    {
        return $this->getMethod() === 'GET';
    }

    /**
     * Vérifie si la requête est de type AJAX/XMLHttpRequest
     */
    public function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    /**
     * Récupère l'URI actuelle nettoyée
     */
    public function getUri(): string
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    /**
     * RÉCUPÉRATION SÉCURISÉE (Cœur du module)
     * Filtre les entrées contre les attaques XSS (Module 5)
     */
    public function input(string $key, $default = null)
    {
        $value = $this->data[$key] ?? $this->json[$key] ?? $default;
        return $this->sanitize($value);
    }

    /**
     * Retourne toutes les données de la requête sous forme de tableau nettoyé
     */
    public function all(): array
    {
        $allData = array_merge($this->data, $this->json);
        return $this->sanitizeRecursive($allData);
    }

    /**
     * Récupère spécifiquement les données JSON décodées
     */
    public function getJson(): array
    {
        return $this->sanitizeRecursive($this->json);
    }

    /**
     * Gestion sécurisée des fichiers uploadés (Module 6.1 ATIC)
     */
    public function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    /**
     * LOGIQUE DE SÉCURISATION (Native Anti-XSS)
     */
    private function sanitize($value)
    {
        if (is_string($value)) {
            // Nettoyage des balises et conversion des caractères spéciaux
            return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
        return $value;
    }

    /**
     * Nettoyage récursif pour les tableaux (ex: lignes de factures)
     */
    private function sanitizeRecursive(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->sanitizeRecursive($value);
            } else {
                $data[$key] = $this->sanitize($value);
            }
        }
        return $data;
    }
}