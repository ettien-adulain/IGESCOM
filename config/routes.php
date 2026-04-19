<?php
/**
 * ==============================================================================
 * WINTECH ERP V2.5 - GIA (GESTION INTÉGRÉE AUTOMATISÉE)
 * MASTER ROUTING TABLE - ÉDITION SOUVERAINE
 * ==============================================================================
 * 
 * Architecture : URL_VIRTUELLE => [CONTROLEUR, ACTION, RÔLE_MINIMAL]
 * Standard : Sage 100 Commercial & Conformité DGI (Côte d'Ivoire)
 * 
 * @author Architecte Logiciel Senior
 * @version 27.0.2 (Feb 2026)
 */

return [

    // ============================================================
    // 🛡️ 1. AUTHENTIFICATION & SÉCURITÉ ACCÈS
    // ============================================================
    '/'                         => ['controller' => 'Auth\Auth', 'action' => 'login'],
    '/login'                    => ['controller' => 'Auth\Auth', 'action' => 'login'],
    '/logout'                   => ['controller' => 'Auth\Auth', 'action' => 'logout'],
    '/authenticate'             => ['controller' => 'Auth\Auth', 'action' => 'authenticate'],
    '/profile/force-password'   => ['controller' => 'Auth\Auth', 'action' => 'changePassword'],

    // ============================================================
    // 📊 2. PILOTAGE DÉCISIONNEL (DASHBOARDS)
    // ============================================================
    '/dashboard'                => ['controller' => 'Dashboard\Dashboard', 'action' => 'index', 'role' => 'USER'],      // Hub Stratégique
    '/management'               => ['controller' => 'Dashboard\Dashboard', 'action' => 'management', 'role' => 'ADMIN'], // Grille Opérationnelle

    // ============================================================
    // 🤝 3. CRM & GESTION DES TIERS (SAGE 100 STYLE)
    // ============================================================
    // --- Clients ---
    '/clients'                  => ['controller' => 'Client\Client', 'action' => 'index', 'role' => 'COMMERCIAL'],
    '/clients/create'           => ['controller' => 'Client\Client', 'action' => 'create', 'role' => 'COMMERCIAL'],
    '/clients/save'             => ['controller' => 'Client\Client', 'action' => 'save', 'role' => 'COMMERCIAL'],
    '/clients/edit/{id}'        => ['controller' => 'Client\Client', 'action' => 'edit', 'role' => 'ADMIN'],
    '/clients/history/{id}'     => ['controller' => 'Client\Client', 'action' => 'history', 'role' => 'USER'], // Historique & Zones
    '/clients/map'              => ['controller' => 'Client\Client', 'action' => 'geoloc', 'role' => 'USER'],

    // --- Fournisseurs ---
    '/fournisseurs'             => ['controller' => 'Fournisseur\Fournisseur', 'action' => 'index', 'role' => 'ADMIN'],
    '/fournisseurs/create'      => ['controller' => 'Fournisseur\Fournisseur', 'action' => 'create', 'role' => 'ADMIN'],
    '/fournisseurs/save'        => ['controller' => 'Fournisseur\Fournisseur', 'action' => 'save', 'role' => 'ADMIN'],

    // ============================================================
    // 📦 4. CATALOGUE PRODUITS ATIC (MODULE 6.1)
    // ============================================================
    '/catalog'                  => ['controller' => 'ATIC\Article', 'action' => 'index', 'role' => 'USER'],
    '/catalog/create'           => ['controller' => 'ATIC\Article', 'action' => 'create', 'role' => 'ADMIN'],
    '/catalog/save'             => ['controller' => 'ATIC\Article', 'action' => 'save', 'role' => 'ADMIN'],
    '/catalog/show/{id}'        => ['controller' => 'ATIC\Article', 'action' => 'show', 'role' => 'USER'],
    '/catalog/margin-calc'      => ['controller' => 'ATIC\Article', 'action' => 'calculator', 'role' => 'USER'], // Marge 30%

    // --- Stocks (panneau Management) ---
    '/stocks'                   => ['controller' => 'Inventory\Inventory', 'action' => 'index', 'role' => 'MAGASINIER'],

    // ============================================================
    // 💰 5. MOTEUR COMMERCIAL & FACTURATION (SAGE 100 FLOW)
    // ============================================================
    // --- Cycle Proforma (Écotation) ---
    '/sales/new'                => ['controller' => 'Sales\Sales', 'action' => 'newQuote', 'role' => 'COMMERCIAL'],
    '/sales/save'               => ['controller' => 'Sales\Sales', 'action' => 'saveQuote', 'role' => 'COMMERCIAL'],
    '/sales/list'               => ['controller' => 'Sales\Sales', 'action' => 'list', 'role' => 'COMMERCIAL'],
    // Assurez-vous que ces lignes sont présentes
'/sales/orders' => ['controller' => 'Sales\Sales', 'action' => 'ordersHub'],
'/sales/orders/{status}' => ['controller' => 'Sales\Sales', 'action' => 'ordersByStatus'],
    
    // --- Validation Workflow (Module 4.2) ---
    '/sales/validate/{id}'      => ['controller' => 'Sales\Sales', 'action' => 'validateClientOk', 'role' => 'COMMERCIAL'], // Bouton "CLIENT OK"
    '/sales/edit/{id}'          => ['controller' => 'Sales\Sales', 'action' => 'editQuote', 'role' => 'COMMERCIAL'],
    
    // --- Facturation Définitive (DGI FNE Ready) ---
    '/invoices'                 => ['controller' => 'Sales\Invoice', 'action' => 'index', 'role' => 'COMPTABLE'],
    '/invoices/generate/{id}'   => ['controller' => 'Sales\Invoice', 'action' => 'finalize', 'role' => 'COMPTABLE'], // Transformation en Facture
    '/invoices/archive'         => ['controller' => 'Sales\Invoice', 'action' => 'archive', 'role' => 'ADMIN'],

    // --- Vente Directe / Caisse ---
    '/ventes/directe'           => ['controller' => 'Sales\POS', 'action' => 'index', 'role' => 'VENDEUR'],
    '/pos/process'              => ['controller' => 'Sales\POS', 'action' => 'process', 'role' => 'VENDEUR'],

    // ============================================================
    // 🚚 6. LOGISTIQUE & COORDINATION (MODULE 5)
    // ============================================================
    '/logistics'                => ['controller' => 'Logistics\Tracking', 'action' => 'index', 'role' => 'MAGASINIER'],
    '/logistics/picking/{id}'   => ['controller' => 'Logistics\Tracking', 'action' => 'pickingList', 'role' => 'MAGASINIER'],
    '/logistics/ready/{id}'     => ['controller' => 'Logistics\Tracking', 'action' => 'markAsReady', 'role' => 'MAGASINIER'],
    '/logistics/deliveries'     => ['controller' => 'Logistics\Tracking', 'action' => 'deliveries', 'role' => 'LIVREUR'],

    // ============================================================
    // 🏦 7. COMPTABILITÉ & FINANCES (SYSCOHADA)
    // ============================================================
    '/compta/analytics'         => ['controller' => 'Accounting\Finance', 'action' => 'stats', 'role' => 'COMPTABLE'],
    '/compta/journals'          => ['controller' => 'Accounting\Finance', 'action' => 'journals', 'role' => 'COMPTABLE'],
    '/compta/ledger'            => ['controller' => 'Accounting\Finance', 'action' => 'ledger', 'role' => 'COMPTABLE'],
    '/compta/treasury'          => ['controller' => 'Accounting\Finance', 'action' => 'treasury', 'role' => 'COMPTABLE'],

    // ============================================================
    // 👥 8. RESSOURCES HUMAINES & PERFORMANCE
    // ============================================================
    '/rh/employees'             => ['controller' => 'Accounting\HR', 'action' => 'index', 'role' => 'ADMIN'],
    '/rh/salaires'              => ['controller' => 'Accounting\HR', 'action' => 'payroll', 'role' => 'COMPTABLE'],
    '/objectifs'                => ['controller' => 'Profile\Profile', 'action' => 'objectifs', 'role' => 'USER'],

    // --- Panneau Management (tuiles) ---
    '/services'                 => ['controller' => 'Service\Service', 'action' => 'index', 'role' => 'USER'],
    '/agences'                  => ['controller' => 'Agency\Agency', 'action' => 'index', 'role' => 'ADMIN'],

    // ============================================================
    // 🤖 9. API INTELLIGENCE & INTERFAÇAGE (GIA & DGI)
    // ============================================================
    '/api/clients/search'       => ['controller' => 'Client\Client', 'action' => 'ajaxSearch'],
    '/api/articles/search'      => ['controller' => 'ATIC\Article', 'action' => 'ajaxSearchArticle'],
    '/api/assistant/ask'        => ['controller' => 'Dashboard\Dashboard', 'action' => 'askAI'],
    '/api/dgi/certify'          => ['controller' => 'Sales\Invoice', 'action' => 'dgiInterface'], // Certification FNE

];