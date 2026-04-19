<?php
namespace App\Repositories;

use Core\Database\Connection;
use App\Utils\Logger;
use PDO;
use Exception;

/**
 * FournisseurRepository V43.0 - Sovereign Data Access
 * Gère l'intégralité du cycle de vie des partenaires YAOCOM'S.
 */
class FournisseurRepository {
    private $db;

    public function __construct() {
        $this->db = Connection::getInstance();
    }

    /**
     * MODULE 6.2 : Liste exhaustive des fournisseurs
     */
    public function getAll(): array {
        try {
            $sql = "SELECT * FROM fournisseurs ORDER BY nom ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            Logger::log("SQL_ERROR", "FournisseurRepo@getAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * INTELLIGENCE : Génération du Compte Tiers (Standard Sage 100)
     * Format : 401-[INITIALE][ANNÉE][COMPTEUR]
     */
    public function generateNextAccountCode(string $name = ''): string {
        $prefix = "401-";
        $initiale = !empty($name) ? strtoupper(substr($name, 0, 1)) : "F";
        $year = date('y');
        
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM fournisseurs WHERE code_fournisseur LIKE ?");
        $stmt->execute([$prefix . $initiale . "%"]);
        $count = (int)$stmt->fetchColumn() + 1;

        return $prefix . $initiale . $year . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * MODULE 1 (PDF) : Insertion complète et robuste
     * Gère les 6 étapes d'enrôlement en une seule transaction via le Controller
     */
    public function insertFullSupplier(array $data): int {
        try {
            $sql = "INSERT INTO fournisseurs (
                code_fournisseur, nom, abbreviation, contact, email, 
                localisation, type_article, whatsapp_number, logo_file,
                bank_name, account_number, iban, swift_code, sensitivity_level
            ) VALUES (
                :code, :nom, :abbr, :tel, :email, 
                :loc, :type, :wa, :logo,
                :bnk, :acc, :iban, :swift, :sens
            )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'code'  => $data['supplier_account_code'],
                'nom'   => strtoupper(strip_tags(trim($data['supplier_name']))),
                'abbr'  => strtoupper(strip_tags(trim($data['supplier_abbreviation'] ?? ''))),
                'tel'   => strip_tags($data['phone_number']),
                'email' => filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL) ?: null,
                'loc'   => strip_tags($data['address'] . ' - ' . $data['city']),
                'type'  => $data['product_category'],
                'wa'    => $data['whatsapp_number'] ?? null,
                'logo'  => $data['logo_path'] ?? 'atic/default_supplier.png',
                'bnk'   => strip_tags($data['bank_name'] ?? ''),
                'acc'   => strip_tags($data['account_number'] ?? ''),
                'iban'  => strip_tags($data['iban'] ?? ''),
                'swift' => strip_tags($data['swift_code'] ?? ''),
                'sens'  => $data['sensitivity_level'] ?? 'FAIBLE'
            ]);

            return (int)$this->db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception("Erreur Repository : " . $e->getMessage());
        }
    }

    /**
     * Recherche précise pour fiche technique ou modification
     */
    public function findById(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM fournisseurs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}