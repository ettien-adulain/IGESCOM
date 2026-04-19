<?php
namespace Core;

/**
 * MOTEUR DE CONFIGURATION WINTECH
 */
class Config {
    private static array $settings = [];

    public static function load(): void {
        // Chargement des fichiers de config (app.php, database.php, routes.php)
        $configPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
        
        $files = glob($configPath . '*.php');
        
        if (empty($files)) {
            die("Erreur critique : Aucun fichier de configuration trouvé dans $configPath");
        }

        foreach ($files as $file) {
            $key = basename($file, '.php');
            self::$settings[$key] = require $file;
        }
    }

    public static function get(string $key, $default = null) {
        $parts = explode('.', $key);
        $data = self::$settings;

        foreach ($parts as $part) {
            if (!isset($data[$part])) return $default;
            $data = $data[$part];
        }

        return $data;
    }
}