<?php
namespace App\Utils;

/**
 * BankHelper - Validation des instruments financiers
 */
class BankHelper {

    /**
     * Valide le RIB (Clé de contrôle UEMOA)
     * Formule : (89 x CodeBanque + 15 x CodeGuichet + 3 x NumCompte + Clé) % 97 = 0
     */
    public static function validateRIB(string $b, string $g, string $c, string $k): bool {
        // Nettoyage
        $c_numeric = strtr(strtoupper($c), 
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 
            '12345678912345678923456789'
        );

        $val = (89 * (int)$b) + (15 * (int)$g) + (3 * (int)$c_numeric) + (int)$k;
        return ($val % 97 === 0);
    }

    /**
     * Formate un IBAN brut pour l'affichage (Groupes de 4)
     */
    public static function formatIban(string $iban): string {
        return trim(chunk_split(str_replace(' ', '', $iban), 4, ' '));
    }
}