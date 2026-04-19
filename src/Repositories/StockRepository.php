<?php
namespace App\Repositories;

use Core\Database\Connection;
use PDO;
use Exception;

class StockRepository {
    private $db;

    public function __construct() {
        $this->db = Connection::getInstance();
    }

    /**
     * MODULE 5 : Liste du stock réel d'une agence avec alertes
     */
    public function getAgencyInventory(int $agenceId): array {
        $sql = "SELECT a.id, a.reference_atic, a.designation, a.type_article, a.stock_alerte,
                       IFNULL(s.quantite_reelle, 0) as quantite,
                       (IFNULL(s.quantite_reelle, 0) - a.stock_alerte) as marge_securite
                FROM articles a
                LEFT JOIN stocks s ON a.id = s.id_article AND s.id_agence = :ag
                ORDER BY a.designation ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ag' => $agenceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * MODULE 4 : Enregistrement d'un mouvement de stock (Entrée/Sortie)
     */
    public function logMovement(int $artId, int $agId, int $qty, string $type, string $motif): bool {
        try {
            $this->db->beginTransaction();

            // 1. Mise à jour de la table stocks (Upsert)
            $sqlStock = "INSERT INTO stocks (id_article, id_agence, quantite_reelle) 
                         VALUES (:art, :ag, :qty) 
                         ON DUPLICATE KEY UPDATE quantite_reelle = quantite_reelle + :diff";
            
            $diff = ($type === 'ENTREE') ? $qty : -$qty;
            $this->db->prepare($sqlStock)->execute([
                'art' => $artId, 'ag' => $agId, 'qty' => $qty, 'diff' => $diff
            ]);

            // 2. Archivage du mouvement
            $sqlLog = "INSERT INTO mouvements_stock (id_article, id_agence, id_utilisateur, type_mouvement, quantite, motif) 
                       VALUES (?, ?, ?, ?, ?, ?)";
            $this->db->prepare($sqlLog)->execute([
                $artId, $agId, $_SESSION['user_id'], $type, $qty, $motif
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}