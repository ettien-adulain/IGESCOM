<?php

namespace App\Services;

use Exception;

/**
 * LOGICIEL : WinTech ERP V2.5 / IGESCOM
 * SERVICE  : CalculationService (Moteur de Calcul Souverain)
 * PROJET   : YAOCOM'S GROUPE
 * 
 * DESCRIPTION : 
 * Ce service implémente la rigueur mathématique de Sage 100 Commercial.
 * Il décompose chaque étape fiscale pour garantir une conformité totale
 * avec la Direction Générale des Impôts (DGI) de Côte d'Ivoire.
 */
class CalculationService
{
    // --- CONSTANTES FISCALES ET MÉTIER (Standard CI) ---
    private const TVA_RATE        = 0.18;    // 18% (TVA Standard)
    private const MARGIN_RATE     = 0.30;    // 30% (Marge ATIC imposée)
    private const AIRSI_RATE      = 0.02;    // 2% (Acompte Impôt / Optionnel)
    private const ROUND_PRECISION = 2;       // Précision pour les calculs
    private const CURRENCY        = "FCFA";

    /**
     * MODULE 6.1 : CALCULATEUR DE PRIX ATIC
     * Transforme un prix d'achat en prix de revient avec marge de 30%.
     * 
     * @param float $prixAchat
     * @return array [pu_ht, benefice]
     */
    public static function computeAticSellingPrice(float $prixAchat): array
    {
        if ($prixAchat < 0) {
            throw new Exception("Le prix d'achat ne peut être négatif.");
        }

        $prixVente = $prixAchat * (1 + self::MARGIN_RATE);
        $benefice  = $prixVente - $prixAchat;

        return [
            'prix_vente_ht' => self::financialRound($prixVente),
            'benefice_ht'   => self::financialRound($benefice),
            'marge_taux'    => (self::MARGIN_RATE * 100) . "%"
        ];
    }

    /**
     * MODULE 2.4 : CALCULATEUR DE LIGNE DE DOCUMENT
     * Calcule le montant d'une ligne d'article avant taxes.
     */
    public static function computeLineItem(float $pu_ht, int $qty, float $remise_pct = 0): array
    {
        $brut_ht = $pu_ht * $qty;
        $val_remise = $brut_ht * ($remise_pct / 100);
        $net_ht = $brut_ht - $val_remise;

        return [
            'brut_ht'    => self::financialRound($brut_ht),
            'remise_v'   => self::financialRound($val_remise),
            'net_ht'     => self::financialRound($net_ht),
            'remise_p'   => $remise_pct
        ];
    }

    /**
     * MODULE GLOBAL : MOTEUR DE FACTURATION "SAGE 100 STYLE"
     * Calcule l'intégralité du document en respectant la hiérarchie financière.
     * 
     * @param array $items        Tableau d'objets [qty, pu_ht, remise_p]
     * @param float $remise_glob  Taux de remise commerciale globale
     * @param float $escompte_pct Taux d'escompte financier
     * @param float $frais_port   Montant net des frais de transport
     * @return array
     */
    public static function computeFullInvoice(
        array $items, 
        float $remise_glob = 0, 
        float $escompte_pct = 0, 
        float $frais_port = 0
    ): array {
        
        $total_ht_brut = 0;
        $processed_items = [];

        // 1. Calcul cumulatif des lignes
        foreach ($items as $item) {
            $calc = self::computeLineItem(
                (float)$item['pu'], 
                (int)$item['qty'], 
                (float)($item['remise'] ?? 0)
            );
            $total_ht_brut += $calc['net_ht'];
            $processed_items[] = $calc;
        }

        // 2. Cascade Sage : Remise Globale
        $val_remise_globale = $total_ht_brut * ($remise_glob / 100);
        $net_commercial     = $total_ht_brut - $val_remise_globale;

        // 3. Cascade Sage : Escompte Financier
        $val_escompte   = $net_commercial * ($escompte_pct / 100);
        $net_financier  = $net_commercial - $val_escompte;

        // 4. Base Taxable (Net Financier + Port)
        // Note : En Sage, le port est ajouté avant le calcul de la TVA
        $base_tva = $net_financier + $frais_port;

        // 5. Calcul de la TVA (18%)
        $montant_tva = $base_tva * self::TVA_RATE;

        // 6. Total TTC (Net à Payer)
        $total_ttc = $base_tva + $montant_tva;

        // 7. Décomposition pour le résumé DGI (Image FNE)
        return [
            'details' => [
                'ht_brut'          => self::financialRound($total_ht_brut),
                'remise_globale_v' => self::financialRound($val_remise_globale),
                'remise_globale_p' => $remise_glob,
                'net_commercial'   => self::financialRound($net_commercial),
                'escompte_v'       => self::financialRound($val_escompte),
                'escompte_p'       => $escompte_pct,
                'net_financier'    => self::financialRound($net_financier),
                'port'             => self::financialRound($frais_port),
                'base_tva'         => self::financialRound($base_tva)
            ],
            'fiscalite' => [
                'tva_taux'     => (self::TVA_RATE * 100),
                'tva_montant'  => self::financialRound($montant_tva),
                'taxe_label'   => "TVA normal - TVA sur HT 18,00% - A"
            ],
            'net_a_payer' => self::monetaryRound($total_ttc),
            'net_a_payer_formatted' => self::formatPrice($total_ttc) . " " . self::CURRENCY
        ];
    }

    /**
     * MÉTHODE DE SÉCURITÉ : Arrondi Financier (4 décimales)
     */
    private static function financialRound(float $value): float
    {
        return round($value, 4);
    }

    /**
     * MÉTHODE DE SÉCURITÉ : Arrondi Monétaire (Entier pour FCFA)
     */
    private static function monetaryRound(float $value): float
    {
        // En Côte d'Ivoire, pour le FCFA, on arrondit généralement à l'unité
        return round($value, 0);
    }

    /**
     * FORMATTEUR ÉLITE : 1264960 -> 1 264 960
     */
    public static function formatPrice($price): string
    {
        return number_format((float)$price, 0, '.', ' ');
    }

    /**
     * MODULE IA : Analyse de marge pour GIA Assistant
     */
    public static function analyzeProfitability(float $ca, float $charges): array
    {
        $profit = $ca - $charges;
        $percent = ($ca > 0) ? ($profit / $ca) * 100 : 0;

        return [
            'statut' => ($percent > 20) ? 'EXCELLENT' : (($percent > 10) ? 'STABLE' : 'CRITIQUE'),
            'marge_nette' => round($percent, 2) . "%",
            'gain_reel' => self::formatPrice($profit)
        ];
    }
}