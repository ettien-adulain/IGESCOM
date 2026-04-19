<?php
/**
 * =============================================================================
 * WINTECH ERP V2.5 - MASTER SIDEBAR "SOVEREIGN ELITE"
 * =============================================================================
 * @package     WinTech GIA Edition
 * @author      Senior Native PHP Architect
 * @version     10.0.1 (Production Ready)
 * 
 * DESCRIPTION :
 * Gère la navigation principale, les alertes de stock critiques,
 * l'identité visuelle IGESCOM / YCS et le profil utilisateur bas de page.
 * =============================================================================
 */

// 1. LOGIQUE DE PRÉ-CHARGEMENT
$current_route = $active ?? 'dashboard';
$stock_alert_count = $stats['alertes_stock'] ?? 405; // Dynamique via Repository

// 2. DEFINITION DES MODULES (STRUCTURE DE GRILLE)
$menu_modules = [
    'PILOTAGE' => [
        ['slug' => 'dashboard', 'label' => 'ACCUEIL', 'icon' => 'fas fa-home', 'url' => '/dashboard'],
        ['slug' => 'management', 'label' => 'MANAGEMENT', 'icon' => 'fas fa-th-large', 'url' => '/management'],
    ],
    'OPÉRATIONS' => [
        ['slug' => 'catalog', 'label' => 'ARTICLES ATIC', 'icon' => 'fas fa-boxes', 'url' => '/catalog'],
        ['slug' => 'clients', 'label' => 'CLIENTS', 'icon' => 'fas fa-users', 'url' => '/clients'],
        ['slug' => 'fournisseurs', 'label' => 'FOURNISSEUR', 'icon' => 'fas fa-truck', 'url' => '/fournisseurs'],
        ['slug' => 'sales', 'label' => 'COMMANDE', 'icon' => 'fas fa-file-invoice-dollar', 'url' => '/sales/list'],
    ],
    'ADMINISTRATION' => [
        ['slug' => 'agencies', 'label' => 'AGENCE', 'icon' => 'fas fa-city', 'url' => '/agences'],
        ['slug' => 'services', 'label' => 'SERVICE', 'icon' => 'fas fa-tools', 'url' => '/services'],
    ]
];
?>

<nav id="sidebar" class="gia-sidebar-main shadow-lg">
    
    <!-- SECTION 1 : ALERTE HAUTE DENSITÉ (Fidèle Image 2) -->
    <div class="sidebar-emergency-bar animate__animated animate__fadeInDown">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <span><?= $stock_alert_count ?> ARTICLE(S) ÉPUISÉ(S)</span>
    </div>

    <!-- SECTION 2 : BRANDING DUAL LOGO -->
    <div class="sidebar-branding-zone">
        <!-- Logo YCS Groupe -->
        <div class="brand-ycs mb-4">
            <img src="<?= $base_url ?>/assets/img/static/logo-ycs.png" alt="YCS Group" class="img-fluid logo-full">
        </div>
        
        <!-- Logo IGESCOM (Demande spécifique : Avant les boutons) -->
        <div class="brand-igescom animate__animated animate__pulse animate__infinite">
            <div class="igescom-box">
                <span class="iges-i">i</span>GESCOM
            </div>
            <small class="text-muted fw-bold" style="font-size: 0.55rem; letter-spacing: 2px;">GESTION COMMERCIALE GIA</small>
        </div>
    </div>

    <!-- SECTION 3 : MENU EN GRILLE (WHITE STYLE) -->
    <div class="sidebar-navigation-area">
        <?php foreach ($menu_modules as $section => $items): ?>
            <div class="nav-section-container">
                <div class="nav-grid-label"><?= $section ?></div>
                <ul class="nav flex-column mb-3">
                    <?php foreach ($items as $item): ?>
                        <li class="nav-item">
                            <a href="<?= $base_url . $item['url'] ?>" 
                               class="nav-link-elite <?= ($current_route == $item['slug']) ? 'active' : '' ?>">
                                <div class="nav-icon-square">
                                    <i class="<?= $item['icon'] ?>"></i>
                                </div>
                                <span class="nav-text-label"><?= $item['label'] ?></span>
                                <?php if ($current_route == $item['slug']): ?>
                                    <div class="active-indicator-green"></div>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- SECTION 4 : USER PROFILE FOOTER (Fidèle Image 2) -->
    <div class="sidebar-profile-footer">
        <div class="d-flex align-items-center mb-3">
            <!-- Avatar Initials -->
            <div class="profile-avatar-initials">
                <?= strtoupper(substr($_SESSION['user_nom'] ?? 'A', 0, 1) . substr($_SESSION['user_nom'] ?? 'D', -1)) ?>
            </div>
            <div class="profile-details ms-2 overflow-hidden">
                <div class="u-full-name text-truncate"><?= $_SESSION['user_nom'] ?? 'ACHI Deklerk' ?></div>
                <div class="u-role-tag d-flex align-items-center">
                    <span class="status-pulse-green me-1"></span>
                    <?= $_SESSION['user_role'] ?? 'Administrateur' ?>
                </div>
            </div>
        </div>
        
        <!-- Bouton Déconnexion (Direct) -->
        <a href="<?= $base_url ?>/logout" class="btn-logout-elite">
            <i class="fas fa-power-off me-2"></i> <span>Quitter la session</span>
        </a>
    </div>
</nav>

<!-- ---------------------------------------------------------------------------
     CSS SCOPÉ : ARCHITECTURE SIDEBAR V10
---------------------------------------------------------------------------- -->
<style>
.gia-sidebar-main {
    width: 250px; height: 100vh; background: #ffffff;
    position: fixed; top: 0; left: 0; z-index: 1060;
    display: flex; flex-direction: column;
    border-right: 1px solid #e2e8f0; transition: var(--transition);
}

/* Alerte Rouge */
.sidebar-emergency-bar {
    background: #e11d48; color: white; padding: 12px;
    font-weight: 900; font-size: 0.65rem; text-align: center;
    letter-spacing: 0.5px; box-shadow: 0 4px 10px rgba(225, 29, 72, 0.2);
}

/* Branding IGESCOM */
.sidebar-branding-zone { padding: 25px 20px; text-align: center; border-bottom: 1px solid #f1f5f9; }
.igescom-box {
    font-weight: 900; font-size: 1.4rem; color: #0f172a;
    letter-spacing: -1px; margin-bottom: 0;
}
.iges-i { color: #e11d48; border-bottom: 3px solid #e6db00; }

/* Navigation Grid */
.sidebar-navigation-area { flex-grow: 1; overflow-y: auto; padding-top: 10px; }
.nav-grid-label {
    color: #94a3b8; font-size: 0.6rem; font-weight: 800;
    padding: 15px 25px 8px; letter-spacing: 1.2px;
}

/* Menu Items (White Style) */
.nav-link-elite {
    display: flex; align-items: center; padding: 12px 25px;
    color: #475569; font-weight: 700; font-size: 0.72rem;
    text-decoration: none; position: relative; transition: 0.2s;
}

.nav-icon-square {
    width: 32px; height: 32px; background: #f8fafc;
    border-radius: 8px; display: flex; align-items: center;
    justify-content: center; margin-right: 15px;
    color: #64748b; font-size: 1rem; border: 1px solid #f1f5f9;
}

.nav-link-elite:hover { background: #f8fafc; color: #0f172a; }

/* État Actif */
.nav-link-elite.active { background: #f1f5f9; color: #0f172a; }
.nav-link-elite.active .nav-icon-square {
    background: #e11d48; color: white; border-color: #e11d48;
    box-shadow: 0 4px 10px rgba(225, 29, 72, 0.3);
}

/* Liseré Vert (Image 2) */
.active-indicator-green {
    position: absolute; right: 0; top: 0; bottom: 0;
    width: 4px; background: #00695c;
}

/* Footer Profile */
.sidebar-profile-footer {
    padding: 20px; background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

.profile-avatar-initials {
    width: 38px; height: 38px; background: #00695c;
    color: white; border-radius: 10px; display: flex;
    align-items: center; justify-content: center;
    font-weight: 800; font-size: 0.85rem; border: 2px solid white;
}

.u-full-name { font-weight: 800; font-size: 0.75rem; color: #1e293b; line-height: 1.1; }
.u-role-tag { font-size: 0.65rem; color: #64748b; font-weight: 600; }

.status-pulse-green {
    width: 7px; height: 7px; background: #10b981;
    border-radius: 50%; display: inline-block;
    box-shadow: 0 0 0 rgba(16, 185, 129, 0.4);
    animation: status-pulse 2s infinite;
}

@keyframes status-pulse {
    0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
    100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.btn-logout-elite {
    display: block; width: 100%; padding: 10px;
    background: #fff1f2; color: #e11d48;
    text-align: center; border-radius: 8px;
    font-size: 0.7rem; font-weight: 800;
    text-decoration: none; transition: 0.3s;
    border: 1px solid #fecaca;
}
.btn-logout-elite:hover { background: #e11d48; color: white; border-color: #e11d48; }

/* Rabat / Collapse Logic */
#sidebar.collapsed { width: 75px; }
#sidebar.collapsed .sidebar-emergency-bar span,
#sidebar.collapsed .sidebar-logo-area .logo-full,
#sidebar.collapsed .brand-igescom,
#sidebar.collapsed .nav-grid-label,
#sidebar.collapsed .nav-text-label,
#sidebar.collapsed .active-indicator-green,
#sidebar.collapsed .profile-details,
#sidebar.collapsed .btn-logout-elite span { display: none; }
#sidebar.collapsed .nav-link-elite { justify-content: center; padding: 15px 0; }
#sidebar.collapsed .nav-icon-square { margin-right: 0; }
#sidebar.collapsed .sidebar-profile-footer { display: flex; justify-content: center; }
</style>