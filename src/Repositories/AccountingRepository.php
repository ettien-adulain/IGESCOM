<?php
namespace App\Repositories;

use Core\Database\Connection;
use PDO;
use Exception;

/**
 * AccountingRepository - Moteur de Calcul SYSCOHADA
 */
class AccountingRepository {
    private $db;

    public function __construct() {
        $this->db = Connection::getInstance();
    }

    /**
     * FIX ERREUR LIGNE 34 : Calcule le solde de tous les comptes de classe 5 (Trésorerie)
     */
    public function getGlobalCashBalance(int $agenceId): float {
        try {
            $sql = "SELECT SUM(l.debit - l.credit) as solde 
                    FROM compta_lignes l
                    JOIN compta_ecritures e ON l.id_ecriture = e.id
                    WHERE e.id_agence = :ag AND l.compte_numero LIKE '5%'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['ag' => $agenceId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return (float)($res['solde'] ?? 0.0);
        } catch (Exception $e) {
            return 0.0;
        }
    }

    /**
     * JOURNAL : Liste des écritures avec jointures complètes
     */
    public function getJournal(int $agenceId, int $limit = 50): array {
        $sql = "SELECT e.date_operation, e.numero_piece, e.libelle_operation, 
                       l.compte_numero, l.debit, l.credit, l.lettrage
                FROM compta_ecritures e
                JOIN compta_lignes l ON e.id = l.id_ecriture
                WHERE e.id_agence = :ag
                ORDER BY e.date_operation DESC, e.id DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ag', $agenceId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * BALANCE : Récupère le total Débit et Crédit pour vérifier l'équilibre
     */
    public function getJournalTotals(int $agenceId): array {
        $sql = "SELECT SUM(l.debit) as total_debit, SUM(l.credit) as total_credit
                FROM compta_lignes l
                JOIN compta_ecritures e ON l.id_ecriture = e.id
                WHERE e.id_agence = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$agenceId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_debit' => 0, 'total_credit' => 0];
    }

    /**
     * TRÉSORERIE : Détail par compte (Banque vs Caisse)
     */
    public function getTreasuryBalances(int $agenceId): array {
        $sql = "SELECT c.numero, c.libelle, SUM(l.debit - l.credit) as solde
                FROM compta_comptes c
                LEFT JOIN compta_lignes l ON c.numero = l.compte_numero
                LEFT JOIN compta_ecritures e ON l.id_ecriture = e.id
                WHERE c.classe = 5 AND (e.id_agence = :ag OR e.id_agence IS NULL)
                GROUP BY c.numero, c.libelle";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ag' => $agenceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * TRANSACTION : Sauvegarde d'une écriture complète (Entête + Lignes)
     */
    public function saveEntry(array $header, array $lines): bool {
        try {
            $this->db->beginTransaction();

            $sqlH = "INSERT INTO compta_ecritures (id_journal, id_auteur, id_agence, date_operation, numero_piece, libelle_operation) 
                     VALUES (?, ?, ?, ?, ?, ?)";
            $this->db->prepare($sqlH)->execute([
                $header['journal_id'], $_SESSION['user_id'], $_SESSION['agence_id'], 
                $header['date'], $header['piece'], $header['libelle']
            ]);
            
            $ecritureId = $this->db->lastInsertId();

            $sqlL = "INSERT INTO compta_lignes (id_ecriture, compte_numero, debit, credit) VALUES (?, ?, ?, ?)";
            $stmtL = $this->db->prepare($sqlL);
            
            foreach ($lines as $line) {
                $stmtL->execute([$ecritureId, $line['compte'], $line['debit'], $line['credit']]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}