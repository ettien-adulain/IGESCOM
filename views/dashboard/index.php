<?php
/**
 * =============================================================================
 * WINTECH ERP V2.5 - HUB D'ACCUEIL STRATÉGIQUE "SOVEREIGN"
 * =============================================================================
 * @package     WinTech GIA Edition
 * @author      Senior Native PHP Architect
 * @version     10.0.2 (Production Ready)
 * 
 * DESCRIPTION :
 * Première interface post-connexion. Focus sur l'expérience utilisateur (UX),
 * la performance commerciale (KPIs) et l'assistance intelligente (GIA).
 * =============================================================================
 */

// 1. LOGIQUE DE SALUTATION TEMPORELLE (Intelligence Native)
$heure = (int)date('H');
$salutation = ($heure >= 6 && $heure < 18) ? "Bonjour" : "Bonsoir";
$prenom = explode(' ', $_SESSION['user_nom'])[0]; // Extraction du premier prénom

// 2. SIMULATION DE DONNÉES (À remplacer par les variables du Controller)
// Note : Le contrôleur doit injecter $stats['objectifs']
$obj_journalier = $stats['objectifs']['journalier'] ?? 65;
$obj_hebdo      = $stats['objectifs']['hebdo'] ?? 42;
$obj_mensuel    = $stats['objectifs']['mensuel'] ?? 18;
?>

<div class="container-fluid px-0 animate__animated animate__fadeIn">

    <!-- -----------------------------------------------------------------------
       SECTION 1 : BANNIÈRE DE BIENVENUE IMMERSIVE (Fidèle Image 2)
    ------------------------------------------------------------------------ -->
    <div class="welcome-banner-gia mb-5 shadow-pro">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center">
                    <!-- Icône de salutation animée -->
                    <div class="welcome-emoji me-4 animate__animated animate__wave animate__delay-1s">
                        <img src="<?= $base_url ?>/assets/img/static/icons/hand_wave.png" width="60" alt="👋" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3342/3342207.png'">
                    </div>
                    <div>
                        <h1 class="fw-800 text-white m-0 text-uppercase letter-spacing-1">
                            <?= $salutation ?>, <span class="text-warning-ycs"><?= $prenom ?></span>
                        </h1>
                        <div class="mt-2 text-white-50 small fw-600">
                            <i class="fas fa-calendar-alt me-2"></i> <?= \App\Utils\Formatter::dateFR(date('Y-m-d')) ?> 
                            <span class="mx-2">|</span>
                            <i class="fas fa-clock me-2"></i> <span id="clock-hub"><?= date('H:i:s') ?></span>
                            <span class="mx-2">|</span>
                            <i class="fas fa-cloud-sun me-2 text-warning"></i> 28°C - Abidjan, CI
                        </div>
                    </div>
                </div>
            </div>
            <!-- BOUTON D'ACCÈS AU MANAGEMENT (CRITIQUE) -->
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <a href="<?= $base_url ?>/management" class="btn btn-warning-ycs fw-800 px-5 py-3 rounded-pill shadow-lg animate__animated animate__pulse animate__infinite">
                    <i class="fas fa-th-large me-2"></i> ACCÉDER AU PANNEAU DE GESTION
                </a>
            </div>
        </div>
        
        <!-- Logo en filigrane (Fidèle Image 2) -->
        <div class="banner-watermark">g</div>
    </div>

    <!-- -----------------------------------------------------------------------
       SECTION 2 : INDICATEURS DE PERFORMANCE (BUSINESS INTELLIGENCE)
    ------------------------------------------------------------------------ -->
    <div class="row g-4 mb-5">
        
        <!-- Carte Objectifs (Noir Matte) -->
        <div class="col-xl-8 col-lg-7">
            <div class="kpi-card-dark p-4 h-100 shadow-pro position-relative overflow-hidden">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-800 text-danger-ycs m-0 text-uppercase small letter-spacing-2">
                        <i class="fas fa-bullseye me-2"></i> Mes Indicateurs de Performance
                    </h6>
                    <a href="<?= $base_url ?>/objectifs" class="btn btn-outline-light btn-xs fw-bold px-3 rounded-pill" style="font-size: 0.65rem;">
                        <i class="fas fa-edit me-1"></i> ENTRE MES OBJECTIFS
                    </a>
                </div>

                <div class="row g-4">
                    <!-- Objectif Journalier -->
                    <div class="col-md-4">
                        <div class="perf-item text-center">
                            <small class="text-white-50 d-block mb-2">Journalier</small>
                            <h3 class="fw-800 text-danger-ycs m-0"><?= $obj_journalier ?>%</h3>
                            <div class="progress-bar-gia mt-2">
                                <div class="progress-fill-red" style="width: <?= $obj_journalier ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Objectif Hebdomadaire -->
                    <div class="col-md-4 border-start border-secondary border-opacity-25">
                        <div class="perf-item text-center">
                            <small class="text-white-50 d-block mb-2">Hebdomadaire</small>
                            <h3 class="fw-800 text-white m-0"><?= $obj_hebdo ?>%</h3>
                            <div class="progress-bar-gia mt-2">
                                <div class="progress-fill-red" style="width: <?= $obj_hebdo ?>%; background: #64748b;"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Objectif Mensuel -->
                    <div class="col-md-4 border-start border-secondary border-opacity-25">
                        <div class="perf-item text-center">
                            <small class="text-white-50 d-block mb-2">Mensuel</small>
                            <h3 class="fw-800 text-white m-0"><?= $obj_mensuel ?>%</h3>
                            <div class="progress-bar-gia mt-2">
                                <div class="progress-fill-red" style="width: <?= $obj_mensuel ?>%; background: #64748b;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions Prioritaires -->
        <div class="col-xl-4 col-lg-5">
            <div class="hub-card-pro p-4 h-100 border-0 shadow-lg">
                <h6 class="fw-800 text-dark mb-4 text-uppercase small letter-spacing-1">Actions Prioritaires</h6>
                <div class="d-grid gap-3">
                    <a href="<?= $base_url ?>/sales/new" class="btn btn-dark-ycs py-3 fw-800 text-uppercase shadow-sm" style="font-size: 0.75rem;">
                        <i class="fas fa-file-signature me-2 text-danger-ycs"></i> ÉTABLIR UN DEVIS PROFORMA
                    </a>
                    <button class="btn btn-outline-dark py-3 fw-800 text-uppercase" style="font-size: 0.75rem;" onclick="GIA_ENGINE.toggleChat()">
                        <i class="fas fa-headset me-2 text-primary"></i> SUPPORT TECHNIQUE GIA
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- -----------------------------------------------------------------------
       SECTION 3 : INFOS & CONSEILS (Fidèle Image 1)
    ------------------------------------------------------------------------ -->
    <div class="row g-4">
        <!-- Cybersécurité -->
        <div class="col-md-4">
            <div class="hub-card-pro border-start border-5 border-info p-4">
                <h6 class="fw-800 text-info text-uppercase small mb-3">
                    <i class="fas fa-shield-alt me-2"></i> CYBERSÉCURITÉ
                </h6>
                <p class="small text-muted mb-4">Signalez immédiatement toute activité suspecte sur votre compte à votre responsable.</p>
                <div class="text-end">
                    <button class="btn btn-outline-secondary btn-sm rounded-pill fw-bold px-3">En savoir plus</button>
                </div>
            </div>
        </div>

        <!-- Conseil du jour -->
        <div class="col-md-4">
            <div class="hub-card-pro border-start border-5 border-success p-4" style="background: #ecfdf5;">
                <h6 class="fw-800 text-success text-uppercase small mb-3">
                    <i class="fas fa-lightbulb me-2"></i> CONSEIL DU JOUR
                </h6>
                <p class="small text-dark fw-500 mb-0">Analysez les données de vos ventes pour identifier les tendances saisonnières et optimiser vos stocks.</p>
                <div class="mt-4 d-flex justify-content-between align-items-center opacity-50">
                    <i class="fas fa-share-alt"></i>
                    <i class="fas fa-heart"></i>
                </div>
            </div>
        </div>

        <!-- Assistant IA -->
        <div class="col-md-4">
            <div class="hub-card-pro card-dark-pro p-4">
                <h6 class="fw-800 text-warning-ycs text-uppercase small mb-3">
                    <i class="fas fa-robot me-2"></i> MON ASSISTANT IA
                </h6>
                <p class="small text-white-50 mb-4">GIA est prêt à vous aider dans vos analyses complexes, calculs de marges et vérification de stocks.</p>
                <button class="btn btn-warning-ycs btn-sm fw-800 w-100 rounded-3" onclick="GIA_ENGINE.toggleChat()">DÉMARRER</button>
            </div>
        </div>
    </div>
</div>

<!-- ---------------------------------------------------------------------------
     STYLE SCOPÉ POUR LE HUB (ELITE RENDERING)
---------------------------------------------------------------------------- -->
<style>
    /* Typographie */
    .fw-800 { font-weight: 800 !important; }
    .letter-spacing-1 { letter-spacing: 1px; }
    .letter-spacing-2 { letter-spacing: 2px; }
    .text-warning-ycs { color: var(--yellow-ycs) !important; }
    .btn-warning-ycs { background: var(--yellow-ycs); border: none; color: #000; transition: var(--transition); }
    .btn-warning-ycs:hover { background: #f9a825; transform: scale(1.02); }

    /* Watermark g */
    .banner-watermark {
        position: absolute; right: 40px; top: 50%; transform: translateY(-50%);
        font-family: 'Poppins', sans-serif; font-weight: 900; font-size: 8rem;
        color: rgba(255,255,255,0.05); pointer-events: none;
    }

    /* Animation wave */
    @keyframes wave {
        0% { transform: rotate( 0.0deg) }
        10% { transform: rotate(14.0deg) }
        20% { transform: rotate(-8.0deg) }
        30% { transform: rotate(14.0deg) }
        40% { transform: rotate(-4.0deg) }
        50% { transform: rotate(10.0deg) }
        60% { transform: rotate( 0.0deg) }
        100% { transform: rotate( 0.0deg) }
    }
    .animate-wave { animation-name: wave; animation-duration: 2.5s; animation-iteration-count: infinite; transform-origin: 70% 70%; display: inline-block; }

    /* Buttons Pro */
    .btn-dark-ycs { background: var(--black-matte); border: none; color: white; transition: var(--transition); }
    .btn-dark-ycs:hover { background: #000; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
    
    .btn-xs { padding: 4px 10px; font-size: 0.65rem; }
</style>

<script>
    /**
     * LOGIQUE TEMPS RÉEL DU HUB
     */
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('fr-FR');
        const el = document.getElementById('clock-hub');
        if(el) el.innerText = timeString;
    }
    setInterval(updateClock, 1000);
</script>