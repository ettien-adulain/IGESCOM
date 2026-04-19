<?php
/**
 * =============================================================================
 * WINTECH ERP V2.5 - PANNEAU DE GESTION ADMINISTRATIVE "SOVEREIGN"
 * =============================================================================
 * @package     WinTech GIA Edition
 * @author      Senior Native PHP Architect
 * @version     10.0.3 (Production Ready)
 * 
 * DESCRIPTION :
 * Interface opérationnelle à 12 modules. Gère l'accès aux fonctions métier
 * critiques : Stocks, Ventes, Finance et RH.
 * =============================================================================
 */

// Sécurité : uniquement dans le contrôleur (DashboardController::management).
// Les tuiles renvoient vers des modules qui appliquent leur propre middleware.

// Badges (injectés par le contrôleur via operational_stats)
$badge_stock   = $operational_stats['articles_epuises'] ?? 0;
$badge_facture = $operational_stats['factures_attente'] ?? 0;
$user_role     = $_SESSION['user_role'] ?? 'AGENT';
?>

<div class="management-container animate__animated animate__fadeIn">

    <!-- -----------------------------------------------------------------------
       SECTION 1 : BANNIÈRE D'ENTÊTE TECHNIQUE (Fidèle Image 1)
    ------------------------------------------------------------------------ -->
    <div class="hub-card info-banner-gray mb-4 shadow-pro border-0">
        <div class="p-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="header-icon-box me-3">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <h2 class="fw-800 text-white m-0 text-uppercase letter-spacing-1">
                        Panneau de Gestion Administrative
                    </h2>
                    <p class="text-white-50 m-0 fw-500">
                        Pilotage centralisé des stocks, commandes et factures en temps réel.
                    </p>
                </div>
            </div>
            <!-- Indicateur de rôle visuel -->
            <div class="role-badge-pill">
                <span class="dot"></span> PRIVILÈGE : <?= $user_role ?>
            </div>
        </div>
    </div>

    <!-- -----------------------------------------------------------------------
       SECTION 2 : LA GRILLE DES 12 TUILES ÉLITE (Architecture 4x3)
    ------------------------------------------------------------------------ -->
    <div class="tile-grid">
        
        <!-- MODULE 1 : ARTICLES -->
        <a href="<?= $base_url ?>/catalog" class="tile-card shadow-pro">
            <div class="tile-inner">
                <div class="tile-icon"><i class="fas fa-boxes"></i></div>
                <span class="tile-label">ARTICLE</span>
            </div>
        </a>

        <!-- MODULE 2 : FOURNISSEUR -->
        <a href="<?= $base_url ?>/fournisseurs" class="tile-card shadow-pro">
            <div class="tile-inner">
                <div class="tile-icon"><i class="fas fa-truck-loading"></i></div>
                <span class="tile-label">FOURNISSEUR</span>
            </div>
        </a>

        <!-- MODULE 3 : COMMANDE (DOCUMENTS) -->
        <a href="<?= $base_url ?>/sales/list" class="tile-card shadow-pro">
            <div class="tile-inner">
                <div class="tile-icon"><i class="fas fa-file-signature"></i></div>
                <span class="tile-label">COMMANDE</span>
            </div>
        </a>

        <!-- MODULE 4 : STOCKS -->
        <a href="<?= $base_url ?>/stocks" class="tile-card shadow-pro">
            <?php if($badge_stock > 0): ?>
                <div class="tile-badge animate__animated animate__pulse animate__infinite"><?= $badge_stock ?></div>
            <?php endif; ?>
            <div class="tile-inner">
                <div class="tile-icon"><i class="fas fa-warehouse"></i></div>
                <span class="tile-label">STOCKS</span>
            </div>
        </a>

        <!-- MODULE 5 : CLIENT -->
        <a href="<?= $base_url ?>/clients" class="tile-card shadow-pro">
            <div class="tile-inner">
                <div class="tile-icon"><i class="fas fa-user-tie"></i></div>
                <span class="tile-label">CLIENT</span>
            </div>
        </a>

        <!-- MODULE 6 : VENTE DIRECTE -->
        <a href="<?= $base_url ?>/ventes/directe" class="tile-card shadow-pro">
            <div class="tile-inner">
                <div class="tile-icon"><i class="fas fa-cash-register"></i></div>
                <span class="tile-label">VENTE DIRECTE</span>
            </div>
        </a>

        <!-- MODULE 7 : COMMANDE CLIENTS -->
        <a href="<?= $base_url ?>/sales/orders" class="tile-card shadow-pro">
            <div class="tile-inner">
                <div class="tile-icon"><i class="fas fa-book-open"></i></div>
                <span class="tile-label">COMMANDE CLIENTS</span>
            </div>
        </a>

        <!-- MODULE 8 : CHIFFRE D'AFFAIRE -->
        <a href="<?= $base_url ?>/compta/analytics" class="tile-card shadow-pro">
            <div class="tile-inner">
                <div class="tile-icon"><i class="fas fa-chart-line"></i></div>
                <span class="tile-label">CHIFFRE D'AFFAIRE</span>
            </div>
        </a>

        <!-- MODULE 9 : FACTURE -->
        <a href="<?= $base_url ?>/invoices" class="tile-card shadow-pro">
            <?php if($badge_facture > 0): ?>
                <div class="tile-badge"><?= $badge_facture ?></div>
            <?php endif; ?>
            <div class="tile-inner">
                <div class="tile-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <span class="tile-label">FACTURE</span>
            </div>
        </a>

        <!-- MODULE 10 : SERVICES -->
        <a href="<?= $base_url ?>/services" class="tile-card shadow-pro">
            <div class="tile-inner">
                <div class="tile-icon"><i class="fas fa-tools"></i></div>
                <span class="tile-label">SERVICES</span>
            </div>
        </a>

        <!-- MODULE 11 : AGENCE -->
        <a href="<?= $base_url ?>/agences" class="tile-card shadow-pro">
            <div class="tile-inner">
                <div class="tile-icon"><i class="fas fa-city"></i></div>
                <span class="tile-label">AGENCE</span>
            </div>
        </a>

        <!-- MODULE 12 : SALAIRE -->
        <a href="<?= $base_url ?>/rh/salaires" class="tile-card shadow-pro">
            <div class="tile-inner">
                <div class="tile-icon"><i class="fas fa-users-cog"></i></div>
                <span class="tile-label">SALAIRE</span>
            </div>
        </a>

    </div>

    <!-- -----------------------------------------------------------------------
       SECTION 3 : ACTIONS DE RETOUR & PIED DE PAGE
    ------------------------------------------------------------------------ -->
    <div class="mt-5 d-flex justify-content-between align-items-center">
        <a href="<?= $base_url ?>/dashboard" class="btn btn-dark-ycs px-5 py-2 rounded-pill fw-800 shadow-lg">
            <i class="fas fa-arrow-left me-2"></i> RETOUR AU HUB D'ACCUEIL
        </a>
        <div class="text-muted small italic">
            WinTech Management Engine v10.0 &copy; <?= date('Y') ?>
        </div>
    </div>
</div>

<!-- ---------------------------------------------------------------------------
     STYLE DÉDIÉ : MANAGEMENT INTERFACE (OVERRIDE CSS)
---------------------------------------------------------------------------- -->
<style>
    /* Spécificités pour la bannière grise */
    .info-banner-gray { background-color: #546e7a; border-radius: 25px; }
    .header-icon-box { 
        width: 50px; height: 50px; background: rgba(255,255,255,0.1); 
        border-radius: 12px; display: flex; align-items: center; 
        justify-content: center; font-size: 1.5rem; color: white;
    }

    /* Le Badge de Rôle */
    .role-badge-pill {
        background: rgba(0,0,0,0.2); color: white; padding: 8px 20px; 
        border-radius: 50px; font-size: 0.65rem; font-weight: 800; 
        letter-spacing: 1px; display: flex; align-items: center; gap: 8px;
    }
    .role-badge-pill .dot { width: 8px; height: 8px; background: #10b981; border-radius: 50%; }

    /* Badges de tuiles */
    .tile-badge {
        position: absolute; top: 15px; right: 15px; 
        background: var(--primary-red); color: white; 
        padding: 5px 12px; border-radius: 10px; 
        font-weight: 900; font-size: 0.75rem; z-index: 5;
        box-shadow: 0 4px 10px rgba(225, 29, 72, 0.4);
    }

    /* Grille et Tuiles */
    .tile-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }

    .tile-card {
        background: #607d8b; /* Gris-bleu profond (Image 1) */
        height: 175px; border-radius: 35px; border: none;
        display: flex; flex-direction: column; align-items: center; 
        justify-content: center; position: relative;
    }

    .tile-inner { text-align: center; transition: var(--transition); }
    .tile-label { 
        display: block; color: white; font-weight: 800; 
        font-size: 0.85rem; margin-top: 15px; letter-spacing: 1px;
    }
    .tile-icon { font-size: 3.2rem; color: white; }

    /* Hover effects */
    .tile-card:hover { background-color: #455a64; transform: translateY(-8px); }
    .tile-card:hover .tile-icon { transform: scale(1.1); }
</style>