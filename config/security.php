<?php
return [
    'roles' => [
        'SUPERADMIN' => ['level' => 100, 'access' => ['*']],
        'ADMIN'      => ['level' => 80,  'access' => ['dashboard', 'catalog', 'clients', 'sales', 'logistics']],
        'COMMERCIAL' => ['level' => 50,  'access' => ['dashboard', 'catalog', 'clients', 'sales']],
        'COMPTABLE'  => ['level' => 60,  'access' => ['dashboard', 'sales', 'accounting']],
        'MAGASINIER' => ['level' => 30,  'access' => ['catalog', 'logistics']],
        'LIVREUR'    => ['level' => 20,  'access' => ['logistics']]
    ],
    
    // Modules sensibles (exigent validation double)
    'strict_modules' => ['rh', 'accounting', 'invoice_delete'],

    // Archivage automatique (Module 5)
    'audit_trail' => true
];