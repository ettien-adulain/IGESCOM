<div class="p-4 animate-up">
    <div class="text-center mb-5">
        <h2 class="fw-bold text-dark text-uppercase">Liste des Agences</h2>
        <hr class="mx-auto border-danger border-2" style="width: 100px;">
    </div>

    <div class="row g-4 justify-content-center">
        <?php foreach($agences as $ag): ?>
        <div class="col-md-5">
            <div class="hub-card bg-white shadow-lg p-5 text-center border-0 h-100" style="border-radius: 20px;">
                <div class="mb-3">
                    <i class="fas fa-map-pin text-danger fs-1"></i>
                </div>
                <h4 class="fw-bold text-success text-uppercase"><?= $ag['nom'] ?></h4>
                <p class="text-muted small"><?= $ag['adresse'] ?? $ag['ville'] ?></p>
                <?php if($ag['id'] == 1): ?>
                    <span class="badge bg-success-subtle text-success border border-success px-4 py-2">PRINCIPAL</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

