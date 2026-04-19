<?php
/**
 * WINTECH ERP V2.5 - PSR-4 COMPLIANT AUTOLOADER
 */
spl_autoload_register(function ($class) {
    // Configuration des racines de Namespace
    $map = [
        'Core\\' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR,
        'App\\'  => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR
    ];

    foreach ($map as $prefix => $base_dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) continue;

        $relative_class = substr($class, $len);
        
        // Normalisation du chemin (Remplace \ par / ou \ selon Windows/Linux)
        $file = $base_dir . str_replace('\\', DIRECTORY_SEPARATOR, $relative_class) . '.php';

        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
});