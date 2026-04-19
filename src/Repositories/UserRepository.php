<?php

namespace App\Repositories;

use Core\Database\Connection;
use App\Utils\Logger;
use PDO;
use Exception;

/**
 * UserRepository - Gestionnaire de Persistance des Utilisateurs
 * Sécurité, Profilage et Contrôle d'accès Multi-Agences
 * Version : 2.5 (Elite Edition)
 */
class UserRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    /**
     * AUTHENTIFICATION : Trouve un utilisateur par son matricule
     * @param string $matricule
     * @return array|null
     */
    public function findByMatricule(string $matricule): ?array
    {
        try {
            $sql = "SELECT * FROM utilisateurs WHERE matricule = :mat AND active = 1 LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['mat' => $matricule]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ?: null;
        } catch (Exception $e) {
            Logger::log("USER_REPO_ERROR", "Erreur findByMatricule: " . $e->getMessage());
            return null;
        }
    }

    /**
     * RÉCUPÉRATION PAR ID : Pour le profil et les vérifications de droits
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT id, matricule, nom_complet, email, telephone, annee_naissance, 
                       role, photo_path, first_login, active, created_at 
                FROM utilisateurs WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * SÉCURITÉ CRITIQUE : Vérification Multi-Agences
     * @param int $userId
     * @param int $agenceId
     * @return bool
     */
    public function hasAccessToAgence(int $userId, int $agenceId): bool
    {
        try {
            // 1. On récupère d'abord le rôle de l'utilisateur
            $user = $this->findById($userId);
            if (!$user) return false;

            // 2. INTELLIGENCE : Le SUPERADMIN a un accès universel (Droit divin)
            if ($user['role'] === 'SUPERADMIN') {
                return true;
            }

            // 3. Pour les autres, on vérifie la table de liaison user_agences
            $sql = "SELECT COUNT(*) FROM user_agences 
                    WHERE id_utilisateur = :uid AND id_agence = :aid";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['uid' => $userId, 'aid' => $agenceId]);
            
            return (int)$stmt->fetchColumn() > 0;

        } catch (Exception $e) {
            Logger::log("SECURITY_ALERT", "Erreur vérification agence: " . $e->getMessage());
            return false;
        }
    }

    /**
     * MISE À JOUR PROFIL : Intègre Photo, Email et Année de naissance
     */
    public function updateProfile(int $id, array $data): bool
    {
        try {
            $sql = "UPDATE utilisateurs SET 
                        nom_complet = :nom, 
                        email = :email, 
                        telephone = :tel, 
                        annee_naissance = :annee";
            
            $params = [
                'nom'   => strip_tags($data['nom_complet']),
                'email' => filter_var($data['email'], FILTER_SANITIZE_EMAIL),
                'tel'   => strip_tags($data['telephone'] ?? ''),
                'annee' => (int)($data['annee_naissance'] ?? 0),
                'id'    => $id
            ];

            // Gestion dynamique de la photo
            if (!empty($data['photo_path'])) {
                $sql .= ", photo_path = :photo";
                $params['photo'] = $data['photo_path'];
            }

            $sql .= " WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);

        } catch (Exception $e) {
            Logger::log("PROFILE_ERROR", $e->getMessage());
            return false;
        }
    }

    /**
     * SÉCURITÉ : Mise à jour du mot de passe (Hashage Bcrypt)
     */
    public function changePassword(int $userId, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $sql = "UPDATE utilisateurs SET password = :pass, first_login = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['pass' => $hash, 'id' => $userId]);
    }

    /**
     * ADMINISTRATION : Création d'un nouvel agent
     */
    public function save(array $data): int
    {
        $sql = "INSERT INTO utilisateurs (matricule, nom_complet, email, password, role, photo_path) 
                VALUES (:mat, :nom, :email, :pass, :role, :photo)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'mat'   => $data['matricule'],
            'nom'   => $data['nom_complet'],
            'email' => $data['email'],
            'pass'  => password_hash($data['password'], PASSWORD_BCRYPT),
            'role'  => $data['role'],
            'photo' => $data['photo_path'] ?? 'default.png'
        ]);
        
        return (int)$this->db->lastInsertId();
    }

    /**
     * RÉCUPÉRATION GLOBALE : Pour la gestion RH
     */
    public function getAll(): array
    {
        $sql = "SELECT id, matricule, nom_complet, role, active, created_at 
                FROM utilisateurs ORDER BY nom_complet ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}