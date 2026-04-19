<?php
/**
 * WINTECH ERP V2.5 - CONFIGURATION BDD PROFESSIONNELLE
 * Système de détection automatique d'environnement (Local vs Production)
 */

// 1. DÉTECTION DE L'ENVIRONNEMENT
// En CLI (bin/console), REMOTE_ADDR n'existe pas : on force le mode local.
$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? null;
$isCli = (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg');
$is_local = $isCli || in_array($remoteAddr, ['127.0.0.1', '::1'], true);

// 2. CONFIGURATION DES ACCÈS
if ($is_local) {
    // --- PARAMÈTRES XAMPP (LOCAL) ---
    $host     = 'localhost';
    $dbname   = 'wintech_erp_v2'; // Nom de votre base locale
    $user     = 'root';
    $pass     = '';
} else {
    // --- PARAMÈTRES SERVEUR WEB (PRODUCTION) ---
    $host     = 'localhost'; // Standard pour PlanetHoster N0C
    $dbname   = 'dywytkyvna_gescom';
    $user     = 'dywytkyvna_aboua'; // Vérifiez votre nom d'utilisateur BDD sur le panel N0C
    $pass     = 'Yaocoms*2021';
}

return [
    'driver'   => 'mysql',
    'host'     => $host,
    'database' => $dbname,
    'username' => $user,
    'password' => $pass,
    'charset'  => 'utf8mb4',
    'collation'=> 'utf8mb4_unicode_ci',
    
    // 3. OPTIONS PDO HAUTE PERFORMANCE (NIVEAU SENIOR)
    'options'  => [
        // Lance une exception en cas d'erreur (Sécurité)
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // Retourne les données sous forme de tableau associatif (Performance)
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Désactive la simulation de requêtes préparées (Anti-Injection SQL)
        PDO::ATTR_EMULATE_PREPARES   => false,
        // Force l'encodage et le fuseau horaire à chaque connexion
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone = '+00:00'"
    ],
];