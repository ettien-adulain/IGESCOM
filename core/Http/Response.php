<?php
namespace Core\Http;

class Response {
    public function json($data, int $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function redirect(string $path) {
        // Détecte automatiquement le dossier racine (ex: /wintech_erp/public)
        $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $base = ($script === '/' || $script === '\\') ? '' : $script;
        header("Location: " . $base . '/' . ltrim($path, '/'));
        exit;
    }
}