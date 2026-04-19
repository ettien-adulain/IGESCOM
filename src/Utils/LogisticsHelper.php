<?php
namespace App\Utils;

class LogisticsHelper {
    /**
     * MODULE 5 : Détermine les frais de port par défaut selon la ville
     */
    public static function estimateShipping(string $ville): float {
        $v = strtoupper(trim($ville));
        $zones = [
            'PLATEAU' => 0,
            'COCODY'  => 2500,
            'YAMOUSSOUKRO' => 15000,
            'SAN-PEDRO' => 25000
        ];

        return $zones[$v] ?? 5000; // 5000 F par défaut
    }
}