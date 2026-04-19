<?php

namespace App\Models;

/**
 * MODÈLE CLIENT V36.0 - ÉDITION VISIONNAIRE
 * Pilote central de la gestion des tiers (Module 1.b & 1.c)
 * 
 * Cette version anticipe :
 * 1. Le lettrage comptable (Sage 100)
 * 2. L'analyse de risque par l'IA GIA
 * 3. La traçabilité SAE (Archivage Électronique)
 */
class Client
{
    // --- PROPRIÉTÉS D'IDENTITÉ ---
    public ?int $id = null;
    public string $id_unique_client = '';
    public string $nom_prenom = '';
    public string $type_client = 'PARTICULIER'; // ENUM('PARTICULIER','PROFESSIONNEL')
    public ?string $logo_path = 'default_client.png';
    public ?string $nom_magasin = null;
    public ?string $localisation_magasin = null;

    // --- COORDONNÉES ET LOCALISATION ---
    public string $telephone = '';
    public ?string $email = null;
    public ?string $adresse_complete = null;

    // --- INTELLIGENCE FINANCIÈRE (SAGE 100 LOGIC) ---
    public float $solvabilite_max = 0.00;
    public float $encours_actuel = 0.00;
    public int $is_blocked = 0; // 0: Actif, 1: Bloqué (Risque financier)
    public string $categorie_tarifaire = 'DETAIL'; // GROS ou DETAIL

    // --- MÉTADONNÉES ET SYSTÈME ---
    public ?int $id_agence = null;
    public string $created_at = '';
    public ?string $updated_at = null;

    /**
     * CONSTRUCTEUR ÉVOLUTIF
     * Hydrate l'objet et sécurise les types de données pour éviter les Warnings.
     */
    public function __construct(array $data = [])
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->id_unique_client = $data['id_unique_client'] ?? 'CLI-TEMP';
        $this->nom_prenom = $data['nom_prenom'] ?? 'Client Anonyme';
        $this->type_client = $data['type_client'] ?? 'PARTICULIER';
        $this->logo_path = $data['logo_path'] ?? 'default_client.png';
        $this->nom_magasin = $data['nom_magasin'] ?? null;
        $this->localisation_magasin = $data['localisation_magasin'] ?? null;

        $this->telephone = $data['telephone'] ?? '';
        $this->email = $data['email'] ?? null;
        $this->adresse_complete = $data['adresse_complete'] ?? null;

        $this->solvabilite_max = isset($data['solvabilite_max']) ? (float)$data['solvabilite_max'] : 0.00;
        $this->encours_actuel = isset($data['encours_actuel']) ? (float)$data['encours_actuel'] : 0.00;
        $this->is_blocked = isset($data['is_blocked']) ? (int)$data['is_blocked'] : 0;
        $this->categorie_tarifaire = $data['categorie_tarifaire'] ?? 'DETAIL';

        $this->id_agence = isset($data['id_agence']) ? (int)$data['id_agence'] : null;
        $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
    }

    /**
     * RÉSOLUTION DE BUG D'AFFICHAGE : getLabel()
     * Retourne le nom le plus pertinent pour l'interface.
     */
    public function getLabel(): string
    {
        if ($this->type_client === 'PROFESSIONNEL' && !empty($this->nom_magasin)) {
            return strtoupper((string)$this->nom_magasin);
        }
        return strtoupper((string)$this->nom_prenom);
    }

    /**
     * Segment relatif après /uploads/ (usage historique).
     * @deprecated Préférer getLogoSrc() pour éviter les 404 (assets vs uploads).
     */
    public function getAvatar(): string
    {
        $path = $this->logo_path ?? '';
        if ($this->hasCustomLogo($path)) {
            return 'clients/' . basename($path);
        }
        return '';
    }

    /**
     * URL complète du logo : un seul chargement valide, évite boucles onerror.
     */
    public function getLogoSrc(string $baseUrl): string
    {
        $baseUrl = rtrim($baseUrl, '/');
        $placeholder = $baseUrl . '/assets/img/static/default_user.png';
        $path = $this->logo_path ?? '';
        if (!$this->hasCustomLogo($path)) {
            return $placeholder;
        }
        return $baseUrl . '/uploads/clients/' . basename($path);
    }

    private function hasCustomLogo(string $path): bool
    {
        $path = trim($path);
        if ($path === '') {
            return false;
        }
        foreach (['default.png', 'default_client.png', 'default_user.png'] as $def) {
            if (strcasecmp($path, $def) === 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * ANALYSE DE SOLVABILITÉ (Sage 100 Logic)
     * Calcule le crédit restant avant blocage.
     */
    public function getCreditDisponible(): float
    {
        if ($this->solvabilite_max <= 0) return 0.00;
        return max(0, $this->solvabilite_max - $this->encours_actuel);
    }

    /**
     * DÉTECTION DE RISQUE (Alerte Rouge)
     * Utilisé pour colorer les badges dans la vue index.php
     */
    public function getRiskLevel(): string
    {
        if ($this->is_blocked === 1) return 'CRITIQUE';
        if ($this->solvabilite_max > 0 && $this->encours_actuel >= ($this->solvabilite_max * 0.8)) {
            return 'WARNING';
        }
        return 'SAIN';
    }

    /**
     * INTERFACE GIA ASSISTANT
     * Formate les données pour que l'IA puisse présenter le client proprement.
     */
    public function getAIPresentation(): string
    {
        $status = ($this->is_blocked) ? "bloqué" : "actif";
        $type = ($this->isPro()) ? "Compte Professionnel ({$this->nom_magasin})" : "Particulier";
        return "Le client {$this->nom_prenom} est un {$type}, actuellement {$status}. Encours : " . number_format($this->encours_actuel, 0, '.', ' ') . " FCFA.";
    }

    /**
     * VÉRIFICATEURS DE TYPE
     */
    public function isPro(): bool
    {
        return $this->type_client === 'PROFESSIONNEL';
    }

    /**
     * ANTICIPATION COMPTABILITÉ (Module 6)
     * Prépare le compte collectif pour le Grand Livre.
     */
    public function getCompteCollectif(): string
    {
        // On anticipe la structure Syscohada
        return ($this->type_client === 'PROFESSIONNEL') ? '411100' : '411200';
    }

    /**
     * HELPER FORMATAGE FINANCIER
     */
    public function formatAmount(float $amount): string
    {
        return number_format($amount, 0, '.', ' ') . ' FCFA';
    }
}