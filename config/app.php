<?php
/**
 * WINTECH ERP V2.5 - GLOBAL SETTINGS
 * Source de vérité pour le DocumentService et le CalculationService
 */
return [
    'app_name' => 'IGESCOM GIA ERP',
    'version'  => '2.5.0-Sovereign',
    
    // IDENTITÉ LÉGALE (Module 3.1)
    'company' => [
        'name'      => "YAO COM'S GROUP",
        'legal'     => "SARL au capital de 1 000 000 FCFA",
        'address'   => "Abidjan Cocody Cité des Arts",
        'bp'        => "08 BP 2523 ABIDJAN 08",
        'phone'     => "+225 07 07 89 22 16 46",
        'email'     => "info.yaocoms@gmail.com",
        'rccm'      => "CI-ABJ-2023-B-11590",
        'bank'      => "SIB N° 00805 5000014527",
        'logo'      => "assets/img/logo.png"
    ],

    // RÈGLES FISCALES (Module 8.2)
    'finance' => [
        'tva_rate'     => 0.18, // 18% Standard CI
        'currency'     => 'FCFA',
        'atic_margin'  => 0.30  // 30% Fixe
    ],

    'debug' => true,
    'timezone' => 'Africa/Abidjan'
];