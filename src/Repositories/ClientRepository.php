<?php

namespace App\Repositories;

use Core\Database\Connection;
use App\Utils\Logger;
use PDO;
use Exception;

/**
 * ClientRepository - Architecture de Persistance Élite
 * Gère l'intégrité, la recherche et les statistiques de la base clients.
 * Version : 5.2 (Sovereign Edition)
 */
class ClientRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    /**
     * MODULE STATISTIQUES : Résout l'erreur 500
     * Compte le nombre total de clients dans la base
     */
    public function countAll(): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM clients";
            $res = $this->db->query($sql)->fetchColumn();
            return (int)($res ?? 0);
        } catch (Exception $e) {
            Logger::log("DB_ERROR_COUNT", $e->getMessage());
            return 0;
        }
    }

    /**
     * MODULE 1.B : Récupération exhaustive des clients
     * Supporte le filtrage multi-agences et le tri alphabétique
     */
    public function getAll(int $agenceId = null): array
    {
        try {
            $sql = "SELECT c.*, 
                    (SELECT COUNT(*) FROM documents WHERE id_client = c.id) as nb_factures
                    FROM clients c";
            
            $params = [];
            // Si l'utilisateur n'est pas SUPERADMIN, on filtre par agence
            if ($agenceId !== null && $_SESSION['user_role'] !== 'SUPERADMIN') {
                $sql .= " WHERE c.id_agence = :ag";
                $params['ag'] = $agenceId;
            }

            $sql .= " ORDER BY c.nom_prenom ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            Logger::log("CLIENT_REPO_GETALL", $e->getMessage());
            return [];
        }
    }

    /**
     * MODULE 2.1 : Recherche intelligente (Autocomplete)
     * Recherche par Nom, Téléphone, ID Unique ou Enseigne Magasin
     */
    public function search(string $term): array
    {
        try {
            $sql = "SELECT id, id_unique_client, nom_prenom, telephone, email, type_client, nom_magasin 
                    FROM clients 
                    WHERE (nom_prenom LIKE :t OR telephone LIKE :t OR id_unique_client LIKE :t OR nom_magasin LIKE :t)
                    AND is_blocked = 0 
                    LIMIT 15";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['t' => "%$term%"]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * MODULE 1.B : Enregistrement et Mise à jour (Moteur Robuste)
     * Gère la validation des doublons et l'intégrité des données
     */
    public function save(array $data): array
    {
        try {
            $this->db->beginTransaction();

            // 1. Vérification Anti-Doublon (Téléphone)
            $check = $this->db->prepare("SELECT id FROM clients WHERE telephone = ? AND id != ?");
            $check->execute([$data['telephone'], $data['id'] ?? 0]);
            if ($check->fetch()) {
                throw new Exception("Ce numéro de téléphone est déjà utilisé par un autre compte.");
            }

            $isUpdate = isset($data['id']) && !empty($data['id']);

            if ($isUpdate) {
                // LOGIQUE UPDATE
                $sql = "UPDATE clients SET 
                            nom_prenom = :nom, email = :email, telephone = :tel, 
                            adresse_complete = :adr, type_client = :type, 
                            nom_magasin = :mag, localisation_magasin = :loc,
                            solvabilite_max = :solv, logo_path = :logo
                        WHERE id = :id";
            } else {
                // LOGIQUE INSERT
                $sql = "INSERT INTO clients (
                            id_unique_client, nom_prenom, email, telephone, 
                            adresse_complete, type_client, nom_magasin, 
                            localisation_magasin, solvabilite_max, logo_path, id_agence
                        ) VALUES (
                            :uid, :nom, :email, :tel, :adr, :type, :mag, :loc, :solv, :logo, :ag
                        )";
                // Génération ID Unique CLI-XXXX
                $data['uid'] = 'CLI-' . strtoupper(substr(uniqid(), -5));
            }

            $stmt = $this->db->prepare($sql);
            $params = [
                'nom'   => strtoupper(strip_tags($data['nom_prenom'])),
                'email' => filter_var($data['email'], FILTER_SANITIZE_EMAIL),
                'tel'   => preg_replace('/[^0-9+]/', '', $data['telephone']),
                'adr'   => strip_tags($data['adresse_complete'] ?? ''),
                'type'  => $data['type_client'] ?? 'PARTICULIER',
                'mag'   => ($data['type_client'] === 'PROFESSIONNEL') ? strtoupper(strip_tags($data['nom_magasin'])) : null,
                'loc'   => strip_tags($data['localisation_magasin'] ?? ''),
                'solv'  => (float)($data['solvabilite_max'] ?? 0),
                'logo'  => $data['logo_path'] ?? 'default_client.png'
            ];

            if ($isUpdate) {
                $params['id'] = $data['id'];
            } else {
                $params['uid'] = $data['uid'];
                $params['ag'] = $_SESSION['agence_id'] ?? 1;
            }

            $stmt->execute($params);
            $finalId = $isUpdate ? (int)$data['id'] : (int)$this->db->lastInsertId();

            $this->db->commit();
            return ['status' => 'success', 'id' => $finalId];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * RÉCUPÉRATION PAR ID (Cible : Fiche détail et modifications)
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM clients WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * MODULE 1.C : Suivi de solvabilité (Modèle Sage 100)
     * Calcule le cumul des factures non payées pour définir le risque
     */
    public function updateEncours(int $clientId): float
    {
        $sql = "SELECT SUM(net_a_payer) as total FROM documents 
                WHERE id_client = :id AND statut != 'FACTURE_VALIDEE'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $clientId]);
        $encours = (float)($stmt->fetchColumn() ?? 0);

        $this->db->prepare("UPDATE clients SET encours_actuel = ? WHERE id = ?")
                 ->execute([$encours, $clientId]);

        return $encours;
    }

    /**
     * MODULE 6 : Statistiques RH/Commerciaux
     * Compte les acquisitions de nouveaux clients sur le mois en cours
     */
    public function countNewThisMonth(): int
    {
        $sql = "SELECT COUNT(*) FROM clients 
                WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                AND YEAR(created_at) = YEAR(CURRENT_DATE())";
        return (int)$this->db->query($sql)->fetchColumn();
    }

    /**
     * BLOCAGE SÉCURISÉ (Module 5)
     */
    public function toggleStatus(int $id, int $blockStatus): bool
    {
        $stmt = $this->db->prepare("UPDATE clients SET is_blocked = ? WHERE id = ?");
        return $stmt->execute([$blockStatus, $id]);
    }

    /**
     * SUPPRESSION AVEC CONTRÔLE D'INTÉGRITÉ
     */
    public function delete(int $id): bool
    {
        // Empêcher la suppression si le client possède des factures
        $check = $this->db->prepare("SELECT COUNT(*) FROM documents WHERE id_client = ?");
        $check->execute([$id]);
        if ((int)$check->fetchColumn() > 0) {
            throw new Exception("Ce client possède un historique financier. Désactivation recommandée au lieu de suppression.");
        }

        $stmt = $this->db->prepare("DELETE FROM clients WHERE id = ?");
        return $stmt->execute([$id]);
    }
}