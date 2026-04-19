<?php
namespace App\Services;

use Core\Database\Connection;
use App\Utils\Logger;

class NotificationService {

    /**
     * MODULE 5 : Crée une alerte de relance pour le commercial
     */
    public static function notifyBlockage(int $docId, string $reason, int $authorId) {
        $db = Connection::getInstance();

        // 1. On identifie le commercial qui a créé la facture
        $stmt = $db->prepare("SELECT id_auteur, numero_officiel FROM documents WHERE id = ?");
        $stmt->execute([$docId]);
        $doc = $stmt->fetch();

        if ($doc) {
            $commercialId = $doc['id_auteur'];
            $ref = $doc['numero_officiel'];

            // 2. Création de la notification interne
            $msg = "🚨 RELANCE URGENTE : La commande $ref a été BLOQUÉE au magasin. Motif : $reason";
            
            $sql = "INSERT INTO notifications (id_utilisateur, type, message, doc_id) 
                    VALUES (?, 'URGENT', ?, ?)";
            $db->prepare($sql)->execute([$commercialId, $msg, $docId]);

            // 3. Archivage pour l'Audit Trail (Module 5)
            Logger::log("LOGISTICS_ALERT", "Relance générée pour $ref. Raison: $reason");
        }
    }

    /**
     * Récupère les alertes non lues pour le Header
     */
    public static function getUnreadCount(int $userId): int {
        $db = Connection::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE id_utilisateur = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
}