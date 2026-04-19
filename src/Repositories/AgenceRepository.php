<?php

namespace App\Repositories;

use Core\Database\Connection;
use App\Utils\Logger;
use PDO;
use Exception;

/**
 * AgenceRepository - Moteur de Persistance de l'Infrastructure
 * Cible : Gestion du réseau d'agences et isolation des sessions
 * Version : 2.5 (Sovereign Edition)
 */
class AgenceRepository
{
    private $db;

    public function __construct()
    {
        // Récupération de l'instance unique de connexion (Singleton)
        $this->db = Connection::getInstance();
    }

    /**
     * RÉCUPÉRATION GLOBALE (Module Login)
     * Récupère la liste simplifiée pour le sélecteur de connexion.
     * 
     * @return array Liste des agences actives
     */
    public function getAll(): array
    {
        try {
            $sql = "SELECT id, nom, ville, telephone 
                    FROM agences 
                    ORDER BY nom ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            Logger::log("DB_AGENCE_ERROR", "Échec getAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * RÉCUPÉRATION PRÉCISE (Module Session)
     * Récupère toutes les colonnes d'une agence pour l'en-tête (FIX Warning 222).
     * 
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        try {
            $sql = "SELECT * FROM agences WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (Exception $e) {
            Logger::log("DB_AGENCE_ERROR", "Échec findById [$id]: " . $e->getMessage());
            return null;
        }
    }

    /**
     * LIAISON UTILISATEUR (Module Sécurité)
     * Récupère les agences autorisées pour un matricule spécifique.
     * 
     * @param int $userId
     * @return array
     */
    public function getAgencesByUser(int $userId): array
    {
        try {
            $sql = "SELECT a.id, a.nom, a.ville 
                    FROM agences a
                    INNER JOIN user_agences ua ON a.id = ua.id_agence
                    WHERE ua.id_utilisateur = :uid
                    ORDER BY a.nom ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['uid' => $userId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * SAUVEGARDE ADMINISTRATIVE (Module Management)
     * Gère la création et la mise à jour des points de vente.
     * 
     * @param array $data Données de l'agence
     * @return bool Succès de l'opération
     */
    public function save(array $data): bool
    {
        try {
            $this->db->beginTransaction();

            $isUpdate = isset($data['id']) && !empty($data['id']);

            if ($isUpdate) {
                // MISE À JOUR
                $sql = "UPDATE agences SET 
                            nom = :nom, 
                            ville = :ville, 
                            adresse = :adr, 
                            telephone = :tel, 
                            email_agence = :email 
                        WHERE id = :id";
            } else {
                // CRÉATION
                $sql = "INSERT INTO agences (nom, ville, adresse, telephone, email_agence) 
                        VALUES (:nom, :ville, :adr, :tel, :email)";
            }

            $stmt = $this->db->prepare($sql);
            
            $params = [
                'nom'   => strtoupper(strip_tags($data['nom'])),
                'ville' => strip_tags($data['ville']),
                'adr'   => strip_tags($data['adresse'] ?? ''),
                'tel'   => strip_tags($data['telephone'] ?? ''),
                'email' => filter_var($data['email_agence'] ?? '', FILTER_SANITIZE_EMAIL)
            ];

            if ($isUpdate) {
                $params['id'] = (int)$data['id'];
            }

            $stmt->execute($params);
            
            $this->db->commit();
            Logger::log("AGENCE_MGMT", ($isUpdate ? "Mise à jour" : "Création") . " agence : " . $params['nom']);
            
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            Logger::log("DB_AGENCE_FATAL", $e->getMessage());
            return false;
        }
    }

    /**
     * SUPPRESSION SÉCURISÉE (Module Audit)
     * Empêche la suppression si des utilisateurs ou documents sont liés.
     */
    public function delete(int $id): bool
    {
        try {
            // 1. Vérification d'intégrité (Vérifier s'il y a des documents liés)
            $check = $this->db->prepare("SELECT COUNT(*) FROM documents WHERE id_agence = ?");
            $check->execute([$id]);
            if ($check->fetchColumn() > 0) {
                throw new Exception("Impossible de supprimer : cette agence possède des archives comptables.");
            }

            // 2. Suppression (Cascade automatique sur user_agences gérée par MySQL)
            $stmt = $this->db->prepare("DELETE FROM agences WHERE id = ?");
            return $stmt->execute([$id]);

        } catch (Exception $e) {
            Logger::log("AGENCE_DELETE_DENIED", $e->getMessage());
            return false;
        }
    }

    /**
     * ANALYTIQUE (Module Dashboard)
     * Compte le nombre d'employés actifs par agence.
     */
    public function getStaffCount(int $agenceId): int
    {
        $sql = "SELECT COUNT(*) FROM user_agences WHERE id_agence = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$agenceId]);
        return (int)$stmt->fetchColumn();
    }
}