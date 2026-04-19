<?php
namespace App\Repositories;

use Core\Database\Connection;
use PDO;

class LogisticsRepository {
    private $db;

    public function __construct() {
        $this->db = Connection::getInstance();
    }

    /**
     * Récupère les commandes validées par le client, prêtes pour le picking
     */
    public function getOrdersToPrepare(int $agenceId) {
        $sql = "SELECT d.id, d.numero_officiel, d.date_creation, c.nom_prenom as client, 
                       c.localisation_magasin as zone, tl.statut_prepa
                FROM documents d
                JOIN clients c ON d.id_client = c.id
                LEFT JOIN tracking_logistique tl ON d.id = tl.id_document
                WHERE d.id_agence = :ag 
                AND d.statut = 'CLIENT_OK' 
                AND (tl.statut_prepa IS NULL OR tl.statut_prepa != 'LIVRE')
                ORDER BY d.date_creation ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ag' => $agenceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}