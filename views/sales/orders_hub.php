<?php
/**
 * WINTECH ERP V2.5 - HUB LOGISTIQUE DES COMMANDES
 * Cible : Coordination Magasinier / Commercial
 * Design : Elite V3 - Power Contrast
 */

// --- ZONE DE PROGRAMMATION DÉFENSIVE ---
// On garantit que $stats existe pour éviter les erreurs "Undefined variable"
$stats = $stats ?? [
    'delivered' => 0,
    'pending'   => 0
];

// Vérification de la session agence pour l'affichage localisé
$nomAgence = $_SESSION['agence_nom'] ?? 'Agence non définie';
?>

<div class="p-4 animate-up">
    <!-- 1. NAVIGATION & FIL D'ARIANE -->
    <nav class="mb-2">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= $base_url ?>/management" class="text-muted text-decoration-none">Management</a></li>
            <li class="breadcrumb-item active text-danger fw-bold">Flux Logistique</li>
        </ol>
    </nav>

    <!-- 2. TITRE DE SECTION ÉLITE -->
    <div class="text-center mb-5">
        <h2 class="fw-bold text-dark text-uppercase mb-0" style="letter-spacing: -1px; font-size: 2.2rem;">
            Suivi des <span class="text-danger">Commandes</span>
        </h2>
        <p class="text-muted small">Pilotage des flux de livraison pour l'unité : <b><?= htmlspecialchars($nomAgence) ?></b></p>
        <div class="d-flex justify-content-center mt-3">
            <hr class="border-danger border-2 opacity-100" style="width: 60px;">
        </div>
    </div>

    <!-- 3. GRILLE DE SÉLECTION (Dual-Pane) -->
    <div class="row g-5 justify-content-center">
        
        <!-- BLOC A : COMMANDES LIVRÉES (DOSSIERS CLOS) -->
        <div class="col-md-5 col-xl-4">
            <div class="hub-card bg-white shadow-lg p-0 border-0 h-100 position-relative transition-hover" style="border-radius: 30px;">
                
                <!-- Badge de comptage intelligent (Haut Droite) -->
                <div class="position-absolute top-0 end-0 m-4">
                    <span class="badge bg-success-subtle text-success border border-success rounded-pill px-3 py-2 fw-800 shadow-sm" style="font-size: 0.75rem;">
                        <i class="fas fa-check-double me-1"></i> 
                        <?= number_format((float)($stats['delivered'] ?? 0), 0) ?> DOSSIERS CLOS
                    </span>
                </div>

                <div class="p-5 text-center">
                    <div class="icon-circle-lg bg-light text-success mx-auto mb-4 shadow-sm">
                        <i class="fas fa-truck-loading fa-3x"></i>
                    </div>
                    
                    <h4 class="fw-bold text-dark text-uppercase mb-3" style="letter-spacing: 1px;">Commandes Livrées</h4>
                    <p class="text-muted small mb-4 px-3" style="line-height: 1.6;">
                        Accédez aux archives complètes des transactions finalisées, dont la réception a été confirmée et le stock décrémenté.
                    </p>
                    
                    <a href="<?= $base_url ?>/sales/orders/LIVRE" class="btn btn-dark w-100 py-3 rounded-pill fw-bold shadow-pro">
                        AFFICHAGE HISTORIQUE <i class="fas fa-chevron-right ms-2 small"></i>
                    </a>
                </div>
                
                <!-- Décoration fine bas de carte -->
                <div class="bg-success" style="height: 6px; width: 40%; margin: 0 auto; border-radius: 10px 10px 0 0;"></div>
            </div>
        </div>

        <!-- BLOC B : COMMANDES EN ATTENTE (ENCOURS LOGISTIQUE) -->
        <div class="col-md-5 col-xl-4">
            <div class="hub-card bg-white shadow-lg p-0 border-0 h-100 position-relative transition-hover" style="border-radius: 30px;">
                
                <!-- Badge de comptage intelligent (Haut Droite) -->
                <div class="position-absolute top-0 end-0 m-4">
                    <span class="badge bg-danger-subtle text-danger border border-danger rounded-pill px-3 py-2 fw-800 shadow-sm" style="font-size: 0.75rem;">
                        <i class="fas fa-clock me-1"></i> 
                        <?= number_format((float)($stats['pending'] ?? 0), 0) ?> EN ATTENTE
                    </span>
                </div>

                <div class="p-5 text-center">
                    <div class="icon-circle-lg bg-light text-danger mx-auto mb-4 shadow-sm">
                        <i class="fas fa-shipping-fast fa-3x"></i>
                    </div>
                    
                    <h4 class="fw-bold text-dark text-uppercase mb-3" style="letter-spacing: 1px;">Flux en Attente</h4>
                    <p class="text-muted small mb-4 px-3" style="line-height: 1.6;">
                        Pilotage des commandes validées par le client ("OK CLIENT") nécessitant une préparation magasin ou une expédition.
                    </p>
                    
                    <a href="<?= $base_url ?>/sales/orders/EN_ATTENTE" class="btn btn-danger w-100 py-3 rounded-pill fw-bold shadow-pro">
                        GÉRER LES ENCOURS <i class="fas fa-chevron-right ms-2 small"></i>
                    </a>
                </div>

                <!-- Décoration fine bas de carte -->
                <div class="bg-danger" style="height: 6px; width: 40%; margin: 0 auto; border-radius: 10px 10px 0 0;"></div>
            </div>
        </div>

    </div>

    <!-- 4. PIED DE PAGE & RETOUR -->
    <div class="mt-5 text-center">
        <a href="<?= $base_url ?>/management" class="btn btn-link text-muted fw-bold text-decoration-none small transition-hover">
            <i class="fas fa-arrow-left me-2"></i> RETOUR AU PANNEAU DE GESTION ADMINISTRATIVE
        </a>
    </div>
</div>

<style>
/* --- STYLES EXCLUSIFS ELITE V3 --- */

.icon-circle-lg {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e2e8f0;
}

.bg-success-subtle { background-color: #f0fdf4; color: #166534 !important; }
.bg-danger-subtle { background-color: #fff1f2; color: #991b1b !important; }

.shadow-pro {
    box-shadow: 0 10px 25px -5px rgba(225, 29, 72, 0.2);
}

.transition-hover {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.transition-hover:hover {
    transform: translateY(-12px);
    box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.15) !important;
}

.fw-800 { font-weight: 800; }

.hub-card {
    border: 1px solid rgba(0,0,0,0.02) !important;
}

/* Animation au chargement */
.animate-up {
    animation: fadeInUp 0.6s ease-out forwards;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
    .col-md-5 { margin-bottom: 20px; }
}
</style>

