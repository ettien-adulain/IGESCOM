<?php

namespace App\Repositories;

use Core\Database\Connection;
use App\Utils\Logger;
use PDO;
use Exception;

/**
 * DocumentRepository V31.0 - Sûreté des Données et Ingénierie Financière
 * Cible : Gestion transactionnelle des Proformas, Factures et Suivi Logistique.
 * Architecture certifiée pour YAOCOM'S GROUPE.
 */
class DocumentRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    // ============================================================
    // 1. MOTEUR D'ENREGISTREMENT (CRÉATION ET LIGNES)
    // ============================================================

    /**
     * MODULE 2, 3 & 4 : Insertion de l'en-tête de document
     * Gère la cascade financière complète conforme Sage 100.
     */
    public function insertDocument(array $data): int
    {
        try {
            $sql = "INSERT INTO documents (
                numero_officiel, type_doc, id_client, id_auteur, id_agence,
                methode_expedition, conditions_paiement, date_livraison, 
                total_ht_brut, remise_globale, escompte, frais_port, 
                total_tva, net_a_payer, statut, entreprise_capital
            ) VALUES (
                :ref, :type, :client, :auteur, :agence,
                :met, :cond, :dat, :ht_b, :rem, :esc, :port, 
                :tva, :net, :stat, :cap
            )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'ref'    => $data['numero_officiel'],
                'type'   => $data['type_doc'] ?? 'PROFORMA',
                'client' => (int)$data['id_client'],
                'auteur' => (int)$data['id_auteur'],
                'agence' => (int)$data['id_agence'],
                'met'    => $data['log_methode'] ?? 'DÉPART MAGASIN',
                'cond'   => $data['log_paiement'] ?? 'À LA RÉCEPTION',
                'dat'    => $data['log_date'] ?? null,
                'ht_b'   => (float)$data['total_ht_brut'],
                'rem'    => (float)($data['remise_globale'] ?? 0),
                'esc'    => (float)($data['escompte'] ?? 0),
                'port'   => (float)($data['frais_port'] ?? 0),
                'tva'    => (float)$data['total_tva'],
                'net'    => (float)$data['net_a_payer'],
                'stat'   => $data['statut'] ?? 'ATTENTE_VAL_CLIENT',
                'cap'    => '10.000.000 FCFA'
            ]);

            $docId = (int)$this->db->lastInsertId();
            
            if ($docId <= 0) throw new Exception("Échec de récupération de l'ID document.");
            
            return $docId;

        } catch (Exception $e) {
            Logger::log("SQL_DOC_HEADER_ERROR", $e->getMessage());
            throw new Exception("Erreur de persistance financière (Entête) : " . $e->getMessage());
        }
    }

    /**
     * MODULE 3 : Insertion des lignes d'articles
     * Assure l'intégrité entre les quantités et les prix unitaires.
     */
    public function insertItems(int $docId, array $items): void
    {
        try {
            $sql = "INSERT INTO document_items (
                id_document, id_article, designation, quantite, 
                prix_unitaire_applique, total_ligne_ht
            ) VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($items as $item) {
                $stmt->execute([
                    $docId,
                    (!empty($item['id']) && $item['id'] != 0 ? (int)$item['id'] : null),
                    strip_tags($item['designation']),
                    (int)$item['qty'],
                    (float)$item['pu'],
                    ((float)$item['qty'] * (float)$item['pu'])
                ]);
            }
        } catch (Exception $e) {
            Logger::log("SQL_DOC_ITEMS_ERROR", $e->getMessage());
            throw new Exception("Erreur de persistance financière (Lignes) : " . $e->getMessage());
        }
    }

    // ============================================================
    // 2. MOTEUR DE LECTURE & RÉGISTRE (REPORTING)
    // ============================================================

    /**
     * RÉGISTRE GLOBAL : Récupère tous les documents pour la liste de suivi
     * Jointures optimisées pour éviter le problème N+1.
     */
    public function getAllDocuments(int $agenceId): array
    {
        try {
            $sql = "SELECT d.*, 
                           c.nom_prenom as client_nom, 
                           c.id_unique_client as client_code,
                           u.nom_complet as auteur_nom
                    FROM documents d
                    JOIN clients c ON d.id_client = c.id
                    JOIN utilisateurs u ON d.id_auteur = u.id
                    WHERE d.id_agence = :ag 
                    ORDER BY d.date_creation DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['ag' => $agenceId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            Logger::log("SQL_REGISTRY_ERROR", $e->getMessage());
            return [];
        }
    }

    /**
     * RÉCUPÉRATION COMPLÈTE : Pour le moteur PDF (DocumentService)
     */
    public function findWithDetails(int $id): ?array
    {
        try {
            $sql = "SELECT d.*, c.nom_prenom as client_nom, c.id_unique_client, c.adresse_complete as client_adr,
                           c.telephone as client_tel, c.email as client_email,
                           u.nom_complet as auteur_nom, a.nom as agence_nom, a.adresse as agence_adr
                    FROM documents d
                    JOIN clients c ON d.id_client = c.id
                    JOIN agences a ON d.id_agence = a.id
                    JOIN utilisateurs u ON d.id_auteur = u.id
                    WHERE d.id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            $doc = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($doc) {
                // Récupération des lignes rattachées
                $stItems = $this->db->prepare("SELECT * FROM document_items WHERE id_document = ?");
                $stItems->execute([$id]);
                $doc['items'] = $stItems->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return $doc ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    // ============================================================
    // 3. MOTEUR LOGISTIQUE (MODULE 5)
    // ============================================================

    /**
     * FILTRAGE LOGISTIQUE : Sépare les flux pour le Magasinier
     */
    public function getDocumentsByLogistics(int $agenceId, string $status): array
    {
        // Si 'LIVRE', on cherche les factures closes. Si 'EN_ATTENTE', on cherche les accords client.
        $sqlStatus = ($status === 'LIVRE') ? "='FACTURE_VALIDEE'" : "='CLIENT_OK'";
        
        $sql = "SELECT d.id, d.numero_officiel, d.date_creation, d.net_a_payer, d.pdf_path,
                       c.nom_prenom as client_nom, c.id_unique_client as client_code
                FROM documents d
                JOIN clients c ON d.id_client = c.id
                WHERE d.id_agence = :ag AND d.statut $sqlStatus
                ORDER BY d.date_creation ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ag' => $agenceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * COMPTEURS DASHBOARD
     */
    public function countByLogisticsStatus(int $agenceId, string $status): int
    {
        $sqlStatus = ($status === 'LIVRE') ? "='FACTURE_VALIDEE'" : "='CLIENT_OK'";
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM documents WHERE id_agence = ? AND statut $sqlStatus");
        $stmt->execute([$agenceId]);
        return (int)$stmt->fetchColumn();
    }

    // ============================================================
    // 4. WORKFLOW & ARCHIVAGE (SAE)
    // ============================================================

    /**
     * MODULE 4.2 : Passage en Facture (Transformation immuable)
     */
    public function convertToInvoice(int $id, string $newRef): bool
    {
        $sql = "UPDATE documents SET 
                statut = 'FACTURE_VALIDEE', 
                type_doc = 'FACTURE', 
                numero_officiel = :ref, 
                date_emission = CURRENT_TIMESTAMP 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['ref' => $newRef, 'id' => $id]);
    }

    /**
     * MISE À JOUR DU CHEMIN SAE : Scelle le lien vers le PDF physique
     */
    public function updatePdfPath(int $id, string $path): bool
    {
        $stmt = $this->db->prepare("UPDATE documents SET pdf_path = ? WHERE id = ?");
        return $stmt->execute([$path, $id]);
    }

    /**
     * MODULE 6 : ANALYSE CHIFFRE D'AFFAIRES
     */
    public function getTurnover(int $agenceId, string $period = 'month'): float
    {
        $sql = "SELECT SUM(net_a_payer) FROM documents WHERE id_agence = ? AND statut = 'FACTURE_VALIDEE'";
        
        if ($period === 'day') $sql .= " AND DATE(date_creation) = CURDATE()";
        elseif ($period === 'month') $sql .= " AND MONTH(date_creation) = MONTH(CURRENT_DATE()) AND YEAR(date_creation) = YEAR(CURRENT_DATE())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$agenceId]);
        return (float)($stmt->fetchColumn() ?: 0.0);
    }
}