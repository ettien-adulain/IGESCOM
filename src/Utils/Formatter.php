<?php
namespace App\Utils;

/**
 * =============================================================================
 * WINTECH ERP V2.5 - FORMATTER ÉLITE
 * =============================================================================
 * Gère la présentation des données financières et temporelles.
 */
class Formatter {

    /**
     * Formatage monétaire FCFA
     * @param float $amount
     * @return string
     */
    public static function fcfa($amount): string {
        return number_format($amount, 0, '.', ' ') . ' FCFA';
    }

    /**
     * Date formatée en Français long (ex: Mardi 21 Janvier 2026)
     */
    public static function dateFR($date): string {
        if (!$date) return "Date inconnue";
        
        $timestamp = strtotime($date);
        $jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        $mois = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        
        return $jours[date('w', $timestamp)] . ' ' . date('d', $timestamp) . ' ' . $mois[date('n', $timestamp)] . ' ' . date('Y', $timestamp);
    }

    /**
     * Heure format AM/PM pour l'en-tête (Fidèle aux images)
     */
    public static function timeAMPM($time = null): string {
        $timestamp = $time ? strtotime($time) : time();
        return date('h:i:s A', $timestamp);
    }

    /**
     * Générateur de badges de statut (Logiciel Sage 100 style)
     */
    public static function statusBadge(string $status): string {
        $status = strtoupper($status);
        $colors = [
            'LIVRÉE'   => 'bg-success',
            'EN ATTENTE' => 'bg-warning text-dark',
            'ANNULÉ'   => 'bg-danger',
            'PROFORMA' => 'bg-info text-white'
        ];
        $class = $colors[$status] ?? 'bg-secondary';
        return "<span class='badge $class px-3 rounded-pill' style='font-size: 0.65rem;'>$status</span>";
    }
}