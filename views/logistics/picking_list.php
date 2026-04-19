<?php
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$scriptName = $_SERVER['SCRIPT_NAME'];
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . str_replace('\\', '/', dirname($scriptName));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'WinTech ERP V2.5' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/wintech.css?v=<?= time() ?>">
</head>
<body>

<div class="fixed-top-container" style="position: fixed; top: 0; width: 100%; z-index: 1050;">
    <div class="agency-strip" style="background:#e6db00; height:32px; text-align:center; line-height:32px; font-weight:800; font-size:0.75rem; color:#000;">
        <i class="fas fa-map-marker-alt text-danger me-2"></i> <?= strtoupper($_SESSION['agence_nom'] ?? 'SIÈGE WINTECH YCS — PLATEAU, ABIDJAN') ?>
    </div>

    <header class="navbar-main" style="background:#546e7a; height:65px; display:flex; align-items:center; padding:0 25px; border-bottom:3px solid #e11d48;">
        <div class="d-flex align-items-center">
            <button id="sidebarCollapse" class="btn btn-link text-white p-0 me-4 shadow-none"><i class="fas fa-bars fs-4"></i></button>
            <div class="bg-white p-1 rounded shadow-sm d-flex align-items-center justify-content-center" style="width: 100px; height: 35px;">
                <img src="<?= $base_url ?>/assets/img/logo.png" height="25" alt="LOGO">
            </div>
            <h6 class="ms-4 text-white fw-bold m-0 d-none d-lg-block">PANNEAU DE GESTION INTEGRÉE</h6>
        </div>

        <div class="ms-auto d-flex align-items-center">
            <div id="nav-clock" class="text-warning fw-bold me-4" style="font-family:monospace; font-size:1.1rem;">00:00:00 PM</div>
            
            <div class="d-flex align-items-center text-white border-start ps-4 border-secondary">
                <div class="text-end me-3">
                    <div class="fw-bold small" style="line-height:1;"><?= strtoupper($_SESSION['user_nom']) ?></div>
                    <div class="extra-small text-danger fw-bold" style="font-size:0.6rem;"><?= $_SESSION['user_role'] ?></div>
                </div>
                <!-- FIX IMAGE & FALLBACK -->
                <img src="<?= $base_url ?>/uploads/users/<?= $_SESSION['user_matricule'] ?>.jpg" 
                     onerror="this.onerror=null; this.src='<?= $base_url ?>/assets/img/static/default_user.png';" 
                     class="rounded border border-2 border-white shadow-sm" width="45" height="45" style="object-fit: cover;">
            </div>
        </div>
    </header>
</div>

<div class="wrapper" style="display:flex;">
    <?php include dirname(__DIR__) . '/layouts/sidebar.php'; ?>
    <div id="content" style="flex:1; margin-left:250px; padding-top:115px; padding-left:30px; padding-right:30px; transition:0.3s; width: calc(100% - 250px);">