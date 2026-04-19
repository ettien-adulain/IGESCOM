<div class="p-4 animate-up">
    <!-- BARRE D'OUTILS SUPÉRIEURE (SOPHISTIQUÉE) -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb m-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="<?= $base_url ?>/catalog" class="text-muted text-decoration-none">Catalogue ATIC</a></li>
                <li class="breadcrumb-item active text-danger fw-bold" aria-current="page"><?= $article['reference_atic'] ?></li>
            </ol>
        </nav>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-dark btn-sm fw-bold px-3 border-2" onclick="window.print()">
                <i class="fas fa-print me-1 text-danger"></i> IMPRIMER FICHE
            </button>
            <a href="<?= $base_url ?>/catalog" class="btn btn-dark btn-sm fw-bold px-4 shadow-sm rounded-pill">
                <i class="fas fa-arrow-left me-1"></i> RETOUR
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- COLONNE GAUCHE : VISUEL ET IDENTITÉ (33%) -->
        <div class="col-lg-4">
            <div class="hub-card bg-white shadow-sm border-0 p-0 overflow-hidden" style="border-radius: 20px;">
                <div class="p-5 bg-light text-center border-bottom">
                    <img src="<?= $base_url ?>/uploads/<?= $article['photo'] ?>" 
                         onerror="this.src='<?= $base_url ?>/assets/img/static/atic_default.png'" 
                         class="img-fluid rounded" style="max-height: 280px; mix-blend-mode: multiply;">
                </div>
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-danger px-3 py-2" style="font-size: 0.65rem; letter-spacing: 1px;">
                            <i class="fas fa-microchip me-1"></i> <?= $article['type_article'] ?>
                        </span>
                        <code class="text-dark fw-bold"><?= $article['reference_atic'] ?></code>
                    </div>
                    <h3 class="fw-bold text-dark mt-3 mb-1"><?= htmlspecialchars($article['designation']) ?></h3>
                    <p class="text-muted small">Enregistré le <?= date('d/m/Y', strtotime($article['created_at'])) ?></p>
                    
                    <!-- Indicateur de Stock GIA -->
                    <div class="mt-4 p-3 rounded-4 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted fw-bold text-uppercase" style="font-size: 0.6rem;">Disponibilité Agence</small>
                            <b class="<?= ($article['stock_actuel'] <= 5) ? 'text-danger' : 'text-success' ?>">
                                <?= $article['stock_actuel'] ?> Unité(s)
                            </b>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar <?= ($article['stock_actuel'] <= 5) ? 'bg-danger' : 'bg-success' ?>" 
                                 style="width: <?= min(($article['stock_actuel'] / 20) * 100, 100) ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLONNE DROITE : FINANCE ET TECHNIQUE (67%) -->
        <div class="col-lg-8">
            <!-- 1. BLOC ANALYSE FINANCIÈRE ÉLITE (Marge 30%) -->
            <div class="hub-card bg-dark text-white shadow-lg border-0 p-4 mb-4" style="border-radius: 20px; border-left: 5px solid #e11d48;">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
                    <h6 class="fw-bold text-danger text-uppercase m-0 small">
                        <i class="fas fa-chart-line me-2"></i> Évaluation de la Rentabilité (Module 1.a)
                    </h6>
                    <span class="badge bg-secondary">Marge Fixe 30.00 %</span>
                </div>
                <div class="row text-center align-items-center">
                    <div class="col-md-4 border-end border-secondary">
                        <small class="text-white-50 d-block mb-1 text-uppercase fw-bold" style="font-size: 0.6rem;">Coût d'Achat HT</small>
                        <h4 class="fw-bold m-0"><?= number_format($article['prix_achat'], 0, '.', ' ') ?> <small class="fs-6 text-white-50">F</small></h4>
                    </div>
                    <div class="col-md-4 border-end border-secondary">
                        <small class="text-white-50 d-block mb-1 text-uppercase fw-bold" style="font-size: 0.6rem;">Bénéfice Unitaire</small>
                        <h4 class="fw-bold m-0 text-warning">+ <?= number_format($article['benefice_unitaire'], 0, '.', ' ') ?> <small class="fs-6">F</small></h4>
                    </div>
                    <div class="col-md-4">
                        <small class="text-white-50 d-block mb-1 text-uppercase fw-bold" style="font-size: 0.6rem;">Prix de Revient Final</small>
                        <h2 class="fw-bold m-0 text-danger" style="letter-spacing: -1px;"><?= number_format($article['prix_vente_revient'], 0, '.', ' ') ?> <small class="fs-5">F</small></h2>
                    </div>
                </div>
            </div>

            <!-- 2. FICHE TECHNIQUE DÉTAILLÉE (Module 6.1) -->
            <div class="hub-card bg-white shadow-sm border-0 p-4" style="border-radius: 20px;">
                <h6 class="fw-bold text-dark text-uppercase small mb-4 border-bottom pb-3">
                    <i class="fas fa-file-alt me-2 text-danger"></i> Caractéristiques Techniques
                </h6>
                <div class="bg-light p-4 rounded-4 text-dark" style="white-space: pre-wrap; line-height: 1.8; font-size: 0.95rem; border: 1px solid #e2e8f0;">
                    <?php if(!empty($article['fiche_technique'])): ?>
                        <?= htmlspecialchars($article['fiche_technique']) ?>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-info-circle mb-2 fa-2x"></i><br>
                            Aucune spécification enregistrée pour cet article.
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- ACTIONS CONTEXTUELLES -->
                <div class="mt-4 d-flex gap-3">
                    <button class="btn btn-dark fw-bold px-4 rounded-pill shadow-sm">
                        <i class="fas fa-edit me-2 text-warning"></i> MODIFIER LES DONNÉES
                    </button>
                    <button class="btn btn-outline-danger fw-bold px-4 rounded-pill">
                        <i class="fas fa-history me-2"></i> HISTORIQUE DE VENTE
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Optimisation Impression pour une Fiche Produit Clean */
    @media print {
        #sidebar, .navbar-custom, .btn, .breadcrumb, .agency-strip, .gia-fab, .ai-window { display: none !important; }
        #content { margin: 0 !important; padding: 0 !important; width: 100% !important; background: white !important; }
        .hub-card { box-shadow: none !important; border: 1px solid #ddd !important; border-radius: 0 !important; }
        .bg-dark { background-color: #f8fafc !important; color: #000 !important; }
        .text-white, .text-white-50 { color: #000 !important; }
        .border-secondary { border-color: #ddd !important; }
        .welcome-card-slim { border: 2px solid #000 !important; color: #000 !important; background: white !important; }
    }
</style>

