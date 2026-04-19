<?php

namespace App\Repositories;

use Core\Database\Connection;
use App\Models\Article;
use App\Utils\Logger;
use PDO;
use Exception;

/**
 * ArticleRepository V5.5 - Moteur de Persistance ATIC
 * Architecture Haute Performance pour YAOCOM'S GROUPE
 * 
 * Ce repository gère :
 * 1. Le catalogue ATIC (300+ produits)
 * 2. Le moteur de recherche instantané (Autocomplete)
 * 3. La logique de stock multi-agences
 */
class ArticleRepository
{
    private $db;

    /** @var bool|null Cache par requête : colonne articles.actif présente */
    private ?bool $hasActifColumn = null;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    private function articlesHasActifColumn(): bool
    {
        if ($this->hasActifColumn !== null) {
            return $this->hasActifColumn;
        }
        try {
            $q = $this->db->query("SHOW COLUMNS FROM `articles` LIKE 'actif'");
            $this->hasActifColumn = (bool) $q->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->hasActifColumn = false;
        }
        return $this->hasActifColumn;
    }

    /**
     * Filtre articles actifs à la caisse / ventes / API (pas pour l’écran catalogue).
     */
    private function sqlActiveArticleClause(string $alias = 'a'): string
    {
        if (!$this->articlesHasActifColumn()) {
            return '';
        }
        return " AND IFNULL({$alias}.actif, 1) = 1";
    }

    /**
     * STATISTIQUES : Compte le nombre total d'articles.
     * Utilisé par le panneau /management.
     */
    public function countAll(): int
    {
        try {
            $res = $this->db->query("SELECT COUNT(*) FROM articles")->fetchColumn();
            return (int)($res ?? 0);
        } catch (Exception $e) {
            Logger::log("ARTICLE_COUNTALL_ERROR", $e->getMessage());
            return 0;
        }
    }

    /**
     * ============================================================
     * MODULE 6 : GESTION DU CATALOGUE (ATIC)
     * ============================================================
     */

    /**
     * RÉCUPÉRATION GLOBALE DU CATALOGUE (back-office)
     * Inclut les articles désactivés pour la caisse : ils restent visibles ici avec indicateur UI.
     * Pour la caisse / devis / API, utiliser searchLive() ou searchAtic() qui filtrent sur actif.
     */
    public function getAllATIC(): array
    {
        try {
            // Jointure pour récupérer le libellé catégorie et le stock global
            $sql = "SELECT a.*, c.libelle as categorie_nom,
                    (a.prix_vente_revient - a.prix_achat) as benefice_reel
                    FROM articles a
                    LEFT JOIN categories c ON a.id_categorie = c.id
                    ORDER BY a.designation ASC";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            Logger::log("CATALOG_FETCH_ERROR", $e->getMessage());
            return [];
        }
    }

    /**
     * RÉCUPÉRATION PAR ID (Fiche Technique)
     */
    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM articles WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $id]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * MODULE 6.1 : INSERTION ATIC AVEC MARGE 30%
     */
    public function insertAtic(array $data): int
    {
        try {
            $sql = "INSERT INTO articles (
                        reference_atic, designation, description, fiche_technique, 
                        type_article, prix_achat, marge_pourcentage, photo, id_categorie
                    ) VALUES (:ref, :des, :rem, :fiche, :type, :achat, :marge, :photo, :cat)";

            // description en BDD souvent NOT NULL : jamais envoyer NULL (formulaire n'a pas toujours le champ "description")
            $desc = trim((string)($data['description'] ?? ''));
            if ($desc === '') {
                $desc = trim((string)($data['fiche_technique'] ?? ''));
            }
            if ($desc === '') {
                $desc = trim((string)($data['designation'] ?? ''));
            }
            $fiche = trim((string)($data['fiche_technique'] ?? ''));
            if ($fiche === '') {
                $fiche = $desc;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'ref'   => $data['reference_atic'],
                'des'   => strtoupper($data['designation']),
                'rem'   => $desc,
                'fiche' => $fiche,
                'type'  => $data['type_article'],
                'achat' => (float)$data['prix_achat'],
                'marge' => 30.00, // Forcé par le cahier des charges
                'photo' => $data['photo'] ?? 'atic/default.png',
                'cat'   => $data['id_categorie'] ?? null
            ]);

            return (int)$this->db->lastInsertId();
        } catch (Exception $e) {
            Logger::log("DB_INSERT_ATIC_FAIL", $e->getMessage());
            throw new Exception("Erreur de création article : " . $e->getMessage());
        }
    }

    /**
     * Mise à jour catalogue ATIC (fiche article).
     * Ne pas mettre à jour prix_vente_revient / benefice_unitaire si ce sont des colonnes GENERATED en MySQL.
     */
    public function updateAtic(int $id, array $data): void
    {
        $desc = trim((string)($data['description'] ?? ''));
        if ($desc === '') {
            $desc = trim((string)($data['fiche_technique'] ?? ''));
        }
        if ($desc === '') {
            $desc = trim((string)($data['designation'] ?? ''));
        }
        $fiche = trim((string)($data['fiche_technique'] ?? ''));
        if ($fiche === '') {
            $fiche = $desc;
        }

        try {
            $sql = "UPDATE articles SET
                designation = :des,
                description = :rem,
                fiche_technique = :fiche,
                type_article = :type,
                prix_achat = :achat,
                marge_pourcentage = :marge,
                stock_alerte = :alerte,
                photo = :photo,
                id_categorie = :cat
                WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'des'   => strtoupper((string)$data['designation']),
                'rem'   => $desc,
                'fiche' => $fiche,
                'type'  => $data['type_article'],
                'achat' => (float)$data['prix_achat'],
                'marge' => 30.00,
                'alerte'=> (int)($data['stock_alerte'] ?? 5),
                'photo' => $data['photo'] ?? 'atic/default_item.png',
                'cat'   => (int)($data['id_categorie'] ?? 1),
                'id'    => $id,
            ]);
        } catch (Exception $e) {
            Logger::log("DB_UPDATE_ATIC_FAIL", $e->getMessage());
            throw new Exception("Erreur de mise à jour article : " . $e->getMessage());
        }
    }

    /**
     * Active ou désactive un article (nécessite la colonne actif — voir database/migrations).
     */
    public function setArticleActif(int $id, bool $active): bool
    {
        if (!$this->articlesHasActifColumn()) {
            return false;
        }
        try {
            $st = $this->db->prepare("UPDATE articles SET actif = ? WHERE id = ?");
            return $st->execute([(int) $active, $id]);
        } catch (Exception $e) {
            Logger::log("ARTICLE_SET_ACTIF_FAIL", $e->getMessage());
            return false;
        }
    }

    /**
     * Alias pour l’API AJAX (facturation / devis).
     */
    public function searchAtic(string $term, ?string $type = null): array
    {
        return $this->searchLive($term, $type);
    }

    /**
     * ============================================================
     * MODULE 2.2 : MOTEUR DE RECHERCHE INTELLIGENT (AJAX)
     * ============================================================
     */

    /**
     * RECHERCHE LIVE (Autocomplete dès la 1ère lettre)
     * Cible : caisse, facturation et devis — articles désactivés exclus.
     * 
     * @param string $term Le texte saisi par l'agent
     * @param string|null $type Optionnel : Filtrer par INFO, BIO, etc.
     */
    public function searchLive(string $term, ?string $type = null): array
    {
        try {
            $sql = "SELECT id, reference_atic, designation, prix_vente_revient, stock_actuel, type_article
                    FROM articles 
                    WHERE (designation LIKE :t OR reference_atic LIKE :t)";
            
            $params = ['t' => "%$term%"];

            if ($this->articlesHasActifColumn()) {
                $sql .= " AND IFNULL(actif, 1) = 1";
            }

            if ($type) {
                $sql .= " AND type_article = :type";
                $params['type'] = $type;
            }

            $sql .= " LIMIT 15"; // Performance : on ne remonte pas tout

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * ============================================================
     * MODULE 5 : GESTION DES STOCKS & LOGISTIQUE
     * ============================================================
     */

    /**
     * RÉCUPÉRATION STOCK PAR AGENCE
     * Intelligence : Calcule la marge de sécurité (Stock - Alerte)
     */
    public function getStockByAgency(int $agenceId): array
    {
        $sql = "SELECT a.id, a.reference_atic, a.designation, a.stock_alerte,
                       IFNULL(s.quantite, 0) as qte_agence
                FROM articles a
                LEFT JOIN stocks s ON a.id = s.id_article AND s.id_agence = :ag
                ORDER BY a.designation ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ag' => $agenceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * MISE À JOUR PHYSIQUE DU STOCK (Mouvement de stock)
     * Utilisé par le POS et le Magasinier.
     */
    public function updateStockLevel(int $articleId, int $agenceId, int $quantity, string $type = 'SORTIE'): bool
    {
        try {
            $this->db->beginTransaction();

            // 1. Calculer le changement
            $change = ($type === 'ENTREE') ? $quantity : -$quantity;

            // 2. Mise à jour de la table stocks (Liaison Agence)
            $sql = "INSERT INTO stocks (id_article, id_agence, quantite) 
                    VALUES (:art, :ag, :qty) 
                    ON DUPLICATE KEY UPDATE quantite = quantite + :diff";
            
            $this->db->prepare($sql)->execute([
                'art'  => $articleId,
                'ag'   => $agenceId,
                'qty'  => ($change > 0) ? $change : 0, // Si entrée, on part de la qte, si sortie on part de 0
                'diff' => $change
            ]);

            // 3. Mise à jour du stock global (Table articles)
            $this->db->prepare("UPDATE articles SET stock_actuel = stock_actuel + ? WHERE id = ?")
                     ->execute([$change, $articleId]);

            // 4. Archivage du mouvement (Module 5)
            $this->db->prepare("INSERT INTO mouvements_stock (id_article, id_agence, id_utilisateur, type_mouvement, quantite, motif) 
                                VALUES (?, ?, ?, ?, ?, ?)")
                     ->execute([
                         $articleId, $agenceId, $_SESSION['user_id'], $type, $quantity, "Opération Commerciale / Inventaire"
                     ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::log("STOCK_CRITICAL_ERROR", $e->getMessage());
            return false;
        }
    }

    /**
     * RÉCUPÈRE LES ARTICLES EN ALERTE RUPTURE
     * Cible : Badge rouge Sidebar et Dashboard
     */
    public function getLowStockItems(int $agenceId): array
    {
        try {
            $sql = "SELECT a.designation, s.quantite, a.stock_alerte 
                    FROM articles a
                    JOIN stocks s ON a.id = s.id_article
                    WHERE s.id_agence = :ag AND s.quantite <= a.stock_alerte";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['ag' => $agenceId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            Logger::log("LOW_STOCK_QUERY_SKIP", $e->getMessage());
            return [];
        }
    }

    /**
     * Alias rétro-compatible : certaines parties du code appellent getLowStock().
     */
    public function getLowStock(int $agenceId): array
    {
        return $this->getLowStockItems($agenceId);
    }

    /**
     * ============================================================
     * MODULE 6 : BUSINESS INTELLIGENCE (ANALYTIQUE)
     * ============================================================
     */

    /**
     * Valeur totale du stock au prix d'achat
     */
    public function getTotalInventoryValue(): float
    {
        $sql = "SELECT SUM(prix_achat * stock_actuel) FROM articles";
        return (float)$this->db->query($sql)->fetchColumn();
    }

    /**
     * Top 10 des articles les plus rentables
     */
    public function getTopProfitable(): array
    {
        $sql = "SELECT designation, benefice_unitaire FROM articles 
                ORDER BY benefice_unitaire DESC LIMIT 10";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}