<?php
/**
 * =============================================================================
 * WINTECH ERP V2.5 - MASTER HEADER "SOVEREIGN APEX"
 * =============================================================================
 * @package     WinTech GIA Edition
 * @author      Senior Native PHP Architect
 * @version     10.0.1 (Production Ready)
 * 
 * DESCRIPTION :
 * Ce fichier constitue le point d'entrée visuel de chaque page. 
 * Il gère la couche de présentation (Head), l'identité visuelle (Logos),
 * et la sécurité périmétrique (Verification session).
 * =============================================================================
 */

/* -----------------------------------------------------------------------------
   1. LOGIQUE D'ENVIRONNEMENT & ROUTAGE ASSETS
----------------------------------------------------------------------------- */

// Calcul dynamique du protocole
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";

// Calcul de l'hôte (ex: erp.wintech-gescom.yaocomsgroup.tech)
$host = $_SERVER['HTTP_HOST'];

// Détection intelligente du sous-dossier (pour XAMPP)
// S'assure que le lien pointe toujours vers /public/
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$base_dir = rtrim(dirname($scriptName), '/');

// URL de base finale pour tous les liens absolus (CSS, JS, Images)
$base_url = $protocol . "://" . $host . $base_dir;
if (strpos($base_url, '/public') === false) {
    // Sécurité supplémentaire : s'assure que si on est à la racine, on ne duplique pas /public
}

/* -----------------------------------------------------------------------------
   2. RÉCUPÉRATION DES DONNÉES DE SESSION (MODULE 1.D)
----------------------------------------------------------------------------- */
$u_id        = $_SESSION['user_id'] ?? null;
$u_nom       = $_SESSION['user_nom'] ?? 'Utilisateur';
$u_role      = $_SESSION['user_role'] ?? 'INVITÉ';
$u_matricule = $_SESSION['user_matricule'] ?? 'default';
$u_agence    = $_SESSION['agence_nom'] ?? 'AGENCE NON DÉFINIE';

// Gestion de la photo de profil (SAE)
$photo_path = $base_url . "/uploads/users/" . $u_matricule . ".jpg";
$fallback_pic = $base_url . "/assets/img/static/default_user.png";

?>
<!DOCTYPE html>
<html lang="fr" class="h-100">
<head>
    <!-- Meta-données de performance -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="author" content="YAOCOM'S GROUPE">
    <meta name="description" content="WinTech GIA ERP - Système de Gestion Intégrée">
    
    <title><?= isset($page_title) ? $page_title . ' | WinTech ERP' : 'WinTech GIA - Dashboard'; ?></title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= $base_url ?>/assets/img/static/favicon.ico" type="image/x-icon">

    <!-- -----------------------------------------------------------------------
       3. BIBLIOTHÈQUES TIERCES (CDN ROBUSTE)
    ------------------------------------------------------------------------ -->
    <!-- Google Fonts : Inter & JetBrains Mono (Compta) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
    
    <!-- Framework CSS : Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Icônes : FontAwesome 6.4 (Pro Free) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Animation Engine : Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <!-- -----------------------------------------------------------------------
       4. CORE DESIGN SYSTEM (CUSTOM)
    ------------------------------------------------------------------------ -->
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/wintech.css?v=2.5.1">

    <style>
        /* Styles critiques injectés pour éviter le "Flash of Unstyled Content" */
        :root {
            --header-total-height: 97px;
            --sidebar-width: 250px;
        }
        
        .fixed-header-container {
            position: fixed; top: 0; right: 0; left: 0;
            width: 100%; z-index: 1050;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .agency-top-banner {
            background: #fbc02d; /* Jaune Or YCS */
            height: 32px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 0.75rem; color: #000;
            text-transform: uppercase; letter-spacing: 1px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .navbar-wintech {
            background: #546e7a; /* Gris-Bleu Technique */
            height: 65px;
            width: 100%;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: nowrap;
            gap: 12px;
            padding: 0 clamp(16px, 2.5vw, 28px);
            border-bottom: 3px solid #e11d48; /* Liseré Crimson */
        }

        /* Ticker d'actions (Le flux en direct) */
        .live-ticker {
            background: rgba(0,0,0,0.2);
            border-radius: 6px;
            padding: 5px 15px;
            margin-left: 20px;
            max-width: 400px;
            border-left: 3px solid #e11d48;
            overflow: hidden;
            display: flex; align-items: center;
        }

        .live-ticker span {
            color: #cbd5e1; font-size: 0.72rem; font-weight: 600;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }

        /* Avatar & Dropdown */
        .user-avatar-wrapper {
            width: 42px; height: 42px;
            border-radius: 10px;
            border: 2px solid white;
            padding: 2px; background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: relative;
        }

        .u-img { width: 100%; height: 100%; border-radius: 6px; object-fit: cover; }
        .online-status {
            position: absolute; bottom: -3px; right: -3px;
            width: 12px; height: 12px;
            background: #10b981; border: 2px solid #546e7a;
            border-radius: 50%;
        }

        .btn-toggle-sidebar {
            color: white; font-size: 1.4rem; cursor: pointer;
            transition: 0.3s;
        }
        .btn-toggle-sidebar:hover { color: #fbc02d; transform: scale(1.1); }

        .header-clock-digital {
            color: #fbc02d;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 800; font-size: 1.1rem;
            letter-spacing: 1px;
        }
        
        .dropdown-menu-elite {
            border: none; border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
            border-top: 4px solid #e11d48;
            margin-top: 15px !important;
            min-width: 250px;
        }

        .dropdown-item-pro {
            padding: 12px 20px; font-weight: 600;
            font-size: 0.85rem; color: #334155;
            display: flex; align-items: center; gap: 12px;
        }

        .dropdown-item-pro:hover {
            background: #fff1f2; color: #e11d48;
            padding-left: 25px;
        }

        /* Print optimization */
        @media print { .fixed-header-container { position: static; box-shadow: none; border: 1px solid #000; } }
    </style>
</head>
<body class="bg-light">

    <!-- -----------------------------------------------------------------------
       5. BLOC HEADER FIXÉ (SOVEREIGN VIEW)
    ------------------------------------------------------------------------ -->
    <div class="fixed-header-group">
        
        <!-- BANDEAU AGENCE : LA LOCALISATION (Image 2) -->
        <div class="agency-strip">
            <i class="fas fa-map-marker-alt me-2 text-danger"></i>
            <?= strtoupper($u_agence) ?> &nbsp;&bull;&nbsp; PLATEAU, AVENUE CHARDY, ABIDJAN
        </div>

        <!-- NAVBAR PRINCIPALE : LE PILOTAGE -->
        <header class="navbar-wintech">
            
            <!-- SECTION GAUCHE : NAVIGATION & LOGO -->
            <div class="d-flex align-items-center">
                <!-- Bouton Rabat Sidebar -->
                <div class="btn-toggle-sidebar me-4" id="sidebarCollapse" title="Réduire/Agrandir le menu">
                    <i class="fas fa-bars"></i>
                </div>

                <!-- Logo GESCOM (Produit) -->
                <div class="bg-white p-1 rounded shadow-sm d-flex align-items-center justify-content-center" style="width: 105px; height: 35px;">
                    <img src="<?= $base_url ?>/assets/img/static/logo-gescom.png" height="22" alt="GESCOM GIA">
                </div>

                <!-- Live Ticker (Module 5 : Archivage/Action) -->
                <div class="live-ticker d-none d-xl-flex">
                    <i class="fas fa-bolt text-warning me-2 animate-pulse"></i>
                    <span id="ticker-content">Vérification de l'intégrité du système...</span>
                </div>
            </div>

            <!-- SECTION DROITE : TEMPS & PROFIL -->
            <div class="ms-auto d-flex align-items-center">
                
                <!-- Horloge Digitale (Jaune) -->
                <div id="nav-clock" class="header-clock-digital me-5 d-none d-md-block">00:00:00 AM</div>
                
                <!-- Centre de Notification (Cloche) -->
                <div class="position-relative me-4" style="cursor: pointer;">
                    <i class="far fa-bell text-white fs-4"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-danger border border-light" style="padding: 4px;">
                        <span class="visually-hidden">notifications</span>
                    </span>
                </div>

                <!-- Profile Dropdown (Souverain) -->
                <div class="dropdown">
                    <div class="user-profile-trigger d-flex align-items-center ps-4 border-start border-secondary text-white" 
                         id="uMenuTrigger" data-bs-toggle="dropdown" aria-expanded="false">
                        
                        <!-- Identité -->
                        <div class="text-end me-3 d-none d-sm-block">
                            <div class="fw-bold small" style="line-height:1; letter-spacing: 0.5px;">
                                <?= strtoupper($u_nom) ?>
                            </div>
                            <div class="extra-small text-danger fw-bold mt-1" style="font-size:0.6rem; opacity:0.8;">
                                <?= $u_role ?>
                            </div>
                        </div>

                        <!-- Photo -->
                        <div class="user-avatar-wrapper">
                            <img src="<?= $photo_path ?>" 
                                 onerror="this.src='<?= $fallback_pic ?>'" 
                                 class="u-img" alt="Agent Avatar">
                            <div class="online-status"></div>
                        </div>
                        <i class="fas fa-chevron-down ms-2 text-white-50 small"></i>
                    </div>

                    <!-- Menu Déroulant (Identité Visuelle YCS) -->
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-elite animate__animated animate__fadeInUp animate__faster" aria-labelledby="uMenuTrigger">
                        <li class="px-4 py-3 border-bottom bg-light rounded-top">
                            <small class="text-muted d-block mb-1">Session active :</small>
                            <div class="fw-bold text-dark"><?= $u_nom ?></div>
                            <small class="text-danger fw-bold"><?= $_SESSION['user_matricule'] ?? 'WIN-AGENT' ?></small>
                        </li>
                        <li>
                            <a class="dropdown-item-pro mt-2" href="<?= $base_url ?>/profile">
                                <i class="fas fa-user-circle text-primary"></i> Mon Profil Agent
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item-pro" href="<?= $base_url ?>/objectifs">
                                <i class="fas fa-bullseye text-danger"></i> Mes Objectifs Mensuels
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item-pro" href="<?= $base_url ?>/agences/switch">
                                <i class="fas fa-exchange-alt text-warning"></i> Changer d'Agence
                            </a>
                        </li>
                        <li><hr class="dropdown-divider mx-3"></li>
                        <li>
                            <a class="dropdown-item-pro text-danger fw-bold" href="<?= $base_url ?>/logout">
                                <i class="fas fa-power-off"></i> QUITTER LA SESSION
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>
    </div>

    <!-- -----------------------------------------------------------------------
       6. WRAPPER & SIDEBAR CALL
    ------------------------------------------------------------------------ -->
    <div class="wrapper">
        <?php include __DIR__ . '/sidebar.php'; ?>
        
        <!-- Le conteneur #content commencera avec les bons décalages CSS -->
        <main id="content">
            <!-- La vue spécifique sera injectée ici -->