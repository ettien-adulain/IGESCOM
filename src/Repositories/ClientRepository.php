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

    /** @var array<string, true>|null Cache SHOW COLUMNS clients */
    private ?array $clientsColNames = null;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    private function clientHasColumn(string $name): bool
    {
        if ($this->clientsColNames === null) {
            $this->clientsColNames = [];
            try {
                $q = $this->db->query('SHOW COLUMNS FROM `clients`');
                while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
                    $this->clientsColNames[$row['Field']] = true;
                }
            } catch (Exception $e) {
                Logger::log('CLIENT_COL_CACHE', $e->getMessage());
            }
        }

        return isset($this->clientsColNames[$name]);
    }

    /** Colonne fichier logo en base (les schémas varient : logo_path, logo, photo_logo). */
    private function pickLogoColumn(): ?string
    {
        foreach (['logo_path', 'logo', 'photo_logo'] as $c) {
            if ($this->clientHasColumn($c)) {
                return $c;
            }
        }

        return null;
    }

    /** Plafond / encours max autorisé (solvabilite_max, plafond_credit, etc.). */
    private function pickCreditColumn(): ?string
    {
        foreach (['solvabilite_max', 'plafond_credit', 'encours_max'] as $c) {
            if ($this->clientHasColumn($c)) {
                return $c;
            }
        }

        return null;
    }

    /** Colonne rattachement agence (schémas multi-sites). */
    private function pickAgenceColumn(): ?string
    {
        foreach (['id_agence', 'agence_id', 'id_agence_client', 'idagence', 'idAgence'] as $c) {
            if ($this->clientHasColumn($c)) {
                return $c;
            }
        }

        return null;
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
            $agenceCol = $this->pickAgenceColumn();
            if ($agenceId !== null && ($agenceCol !== null) && ($_SESSION['user_role'] ?? '') !== 'SUPERADMIN') {
                $agSafe = str_replace('`', '``', $agenceCol);
                $sql .= ' WHERE c.`' . $agSafe . '` = :ag';
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
            // Pas de beginTransaction ici : le contrôleur peut déjà envelopper la requête (évite imbrication PDO).

            // 1. Vérification Anti-Doublon (Téléphone)
            $check = $this->db->prepare("SELECT id FROM clients WHERE telephone = ? AND id != ?");
            $check->execute([$data['telephone'], $data['id'] ?? 0]);
            if ($check->fetch()) {
                throw new Exception("Ce numéro de téléphone est déjà utilisé par un autre compte.");
            }

            $isUpdate = isset($data['id']) && !empty($data['id']);

            $creditCol = $this->pickCreditColumn();
            $logoCol = $this->pickLogoColumn();
            $agenceCol = $this->pickAgenceColumn();

            $creditVal = (float) ($data['plafond_credit'] ?? $data['solvabilite_max'] ?? 0);
            $logoVal = $data['logo_path'] ?? 'default_client.png';

            if ($isUpdate) {
                $sets = [
                    'nom_prenom = :nom',
                    'email = :email',
                    'telephone = :tel',
                    'adresse_complete = :adr',
                    'type_client = :type',
                    'nom_magasin = :mag',
                    'localisation_magasin = :loc',
                ];
                if ($creditCol !== null) {
                    $sets[] = '`' . $creditCol . '` = :solv';
                }
                if ($logoCol !== null) {
                    $sets[] = '`' . $logoCol . '` = :logo';
                }
                $sql = 'UPDATE clients SET ' . implode(', ', $sets) . ' WHERE id = :id';
            } else {
                $cols = [
                    'id_unique_client', 'nom_prenom', 'email', 'telephone',
                    'adresse_complete', 'type_client', 'nom_magasin',
                    'localisation_magasin',
                ];
                $ph = [':uid', ':nom', ':email', ':tel', ':adr', ':type', ':mag', ':loc'];
                if ($creditCol !== null) {
                    $cols[] = $creditCol;
                    $ph[] = ':solv';
                }
                if ($logoCol !== null) {
                    $cols[] = $logoCol;
                    $ph[] = ':logo';
                }
                if ($agenceCol !== null) {
                    $cols[] = $agenceCol;
                    $ph[] = ':ag';
                }

                $uid = trim((string) ($data['id_unique_client'] ?? ''));
                $data['uid'] = $uid !== '' ? strtoupper($uid) : ('CLI-' . strtoupper(substr(uniqid(), -5)));

                $sql = 'INSERT INTO clients (`' . implode('`, `', $cols) . '`) VALUES (' . implode(', ', $ph) . ')';
            }

            $stmt = $this->db->prepare($sql);
            $params = [
                'nom'   => strtoupper(strip_tags($data['nom_prenom'])),
                'email' => filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL),
                'tel'   => preg_replace('/[^0-9+]/', '', $data['telephone']),
                'adr'   => strip_tags($data['adresse_complete'] ?? ''),
                'type'  => $data['type_client'] ?? 'PARTICULIER',
                'mag'   => ($data['type_client'] === 'PROFESSIONNEL') ? strtoupper(strip_tags($data['nom_magasin'] ?? '')) : null,
                'loc'   => strip_tags($data['localisation_magasin'] ?? ''),
            ];
            if ($creditCol !== null) {
                $params['solv'] = $creditVal;
            }
            if ($logoCol !== null) {
                $params['logo'] = $logoVal;
            }

            if ($isUpdate) {
                $params['id'] = $data['id'];
            } else {
                $params['uid'] = $data['uid'];
                if ($agenceCol !== null) {
                    $params['ag'] = $_SESSION['agence_id'] ?? 1;
                }
            }

            $stmt->execute($params);
            $finalId = $isUpdate ? (int)$data['id'] : (int)$this->db->lastInsertId();

            return ['status' => 'success', 'id' => $finalId];

        } catch (Exception $e) {
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