<div class="p-4 animate-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark m-0"><i class="fas fa-box-open me-2 text-danger"></i> COORDINATION LOGISTIQUE</h4>
            <p class="text-muted small m-0">Préparation des colis et gestion du picking (Module 5)</p>
        </div>
        <div class="bg-white px-3 py-2 rounded-3 shadow-sm border border-danger">
            <small class="fw-bold text-danger">À PRÉPARER :</small>
            <span class="fs-5 fw-bold ms-2 text-dark"><?= count($orders) ?></span>
        </div>
    </div>

    <div class="row g-4">
        <?php if(!empty($orders)): foreach($orders as $o): ?>
        <div class="col-md-6 col-xl-4">
            <div class="hub-card bg-white shadow-sm border-0 p-0 overflow-hidden">
                <!-- En-tête de carte -->
                <div class="bg-dark text-white p-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold small"><?= $o['numero_officiel'] ?></span>
                    <span class="badge bg-warning text-dark" style="font-size: 0.6rem;">EN ATTENTE PICKING</span>
                </div>
                
                <div class="p-4">
                    <div class="mb-3">
                        <small class="text-muted d-block">CLIENT :</small>
                        <b class="text-dark"><?= $o['client'] ?></b>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">DESTINATION :</small>
                        <i class="fas fa-map-marker-alt text-danger me-1"></i> <b><?= $o['zone'] ?? 'Non spécifiée' ?></b>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted italic">Émis le <?= date('d/m/Y', strtotime($o['date_creation'])) ?></small>
                        <div class="btn-group shadow-sm">
                            <button class="btn btn-sm btn-white border" title="Liste de Picking"><i class="fas fa-list-ul"></i></button>
                            <button class="btn btn-sm btn-success fw-bold px-3">PRÊT <i class="fas fa-check-circle ms-1"></i></button>
                        </div>
                    </div>
                </div>
                
                <!-- Alerte de blocage (Module 5.5) -->
                <button class="btn btn-outline-danger btn-sm w-100 border-0 rounded-0 py-2 fw-bold" style="font-size: 0.7rem; background: #fff5f5;">
                    <i class="fas fa-exclamation-triangle me-1"></i> SIGNALER UN BLOCAGE STOCK
                </button>
            </div>
        </div>
        <?php endforeach; else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-truck-loading fa-4x text-light mb-3"></i>
                <p class="text-muted">Aucune commande validée en attente de préparation.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .btn-white { background: #fff; }
    .btn-white:hover { background: #f8fafc; }
</style>

