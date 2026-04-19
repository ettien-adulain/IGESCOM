<div class="p-4 animate-up">
    <div class="welcome-card-slim mb-4 bg-dark text-white p-4 rounded-3 d-flex justify-content-between align-items-center">
        <h4 class="m-0 fw-bold"><i class="fas fa-university me-2 text-warning"></i> RAPPROCHEMENT BANCAIRE</h4>
        <div class="text-end">
            <small class="d-block opacity-50">Compte 521100 - BOA CI</small>
            <b class="text-warning">SOLDE : 12.125.000 F</b>
        </div>
    </div>

    <div class="hub-card bg-white shadow-lg p-0 overflow-hidden border-0">
        <div class="p-3 bg-light text-muted small fw-bold">ÉCRITURES COMPTABLES À POINTER AVEC LE RELEVÉ</div>
        <table class="table table-hover align-middle m-0">
            <thead>
                <tr class="extra-small-sage">
                    <th class="ps-4 py-3">Date Système</th>
                    <th>Libellé / Pièce</th>
                    <th class="text-end">Montant</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody class="amount-mono">
                <?php foreach($pending as $p): ?>
                <tr>
                    <td class="ps-4"><?= date('d/m/Y', strtotime($p['date_operation'])) ?></td>
                    <td>
                        <div class="fw-bold"><?= $p['libelle_operation'] ?></div>
                        <small class="text-muted"><?= $p['reference_piece'] ?></small>
                    </td>
                    <td class="text-end fw-bold <?= $p['debit'] > 0 ? 'text-success' : 'text-danger' ?>">
                        <?= number_format($p['debit'] + $p['credit'], 0, '.', ' ') ?> F
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-success rounded-pill px-3 fw-bold" onclick="pointerOp(<?= $p['id'] ?>)">
                            <i class="fas fa-check me-1"></i> POINTER
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function pointerOp(id) {
    const dateBank = prompt("Saisir la date de valeur sur le relevé (AAAA-MM-JJ) :", "<?= date('Y-m-d') ?>");
    if(dateBank) {
        // Envoi vers un endpoint de sauvegarde du rapprochement
        alert("Opération " + id + " pointée au " + dateBank);
    }
}
</script>

