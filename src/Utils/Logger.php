<?php
namespace App\Utils;

use Core\Database\Connection;

class Logger {
    /**
     * Enregistre une action utilisateur dans le journal d'audit (Base de données)
     * et dans un fichier de secours (Storage)
     */
    public static function log(string $action, $details = null): void {
        $db = Connection::getInstance();
        $userId = $_SESSION['user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $detailsJson = is_array($details) ? json_encode($details) : json_encode(['info' => $details]);

        try {
            // 1. Archivage SQL (Journal d'Audit)
            $sql = "INSERT INTO journal_audit (id_utilisateur, action, details, ip_address) VALUES (?, ?, ?, ?)";
            $db->prepare($sql)->execute([$userId, $action, $detailsJson, $ip]);

            // 2. Archivage physique (Secours Sécurité)
            $logLine = sprintf("[%s] [USER:%s] [IP:%s] ACTION: %s | DETAILS: %s\n", 
                date('Y-m-d H:i:s'), $userId ?? 'SYSTEM', $ip, $action, $detailsJson);
            file_put_contents(dirname(__DIR__, 2) . '/storage/logs/security.log', $logLine, FILE_APPEND);
            
        } catch (\Exception $e) {
            error_log("Logging failed: " . $e->getMessage());
        }
    }
}