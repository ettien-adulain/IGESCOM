<?php
namespace App\Repositories;

use Core\Database\Connection;
use PDO;
use Exception;

class HRRepository {
    private $db;

    public function __construct() {
        $this->db = Connection::getInstance();
    }

    /**
     * MODULE 6 : Calcul de la masse salariale TTC par agence
     * Innovation : Utilise COALESCE pour éviter les erreurs de calcul sur valeurs NULL
     */
    public function getMonthlyPayroll(int $agenceId): float {
        $sql = "SELECT SUM(
                    COALESCE(salaire_base, 0) + 
                    COALESCE(avantages, 0) + 
                    COALESCE(charges_patronales, 0)
                ) as masse_totale 
                FROM employes WHERE id_agence = :ag";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ag' => $agenceId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($res['masse_totale'] ?? 0.0);
    }

    /**
     * Récupère tous les employés d'une agence avec calcul du coût unitaire
     */
    public function getAllEmployees(int $agenceId): array {
        $sql = "SELECT *, 
                (salaire_base + avantages + charges_patronales) as cout_total_entreprise
                FROM employes WHERE id_agence = ? ORDER BY nom ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$agenceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}