<div class="p-4 animate-up">
    <!-- BARRE D'ÉTAT FINANCIER -->
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="hub-card bg-dark text-white p-3 d-flex justify-content-between align-items-center" style="border-radius: 12px; border-left: 5px solid #e11d48;">
                <div>
                    <small class="text-white-50 fw-bold uppercase">Statut Équilibre Journal</small>
                    <?php 
                        $ecart = abs($totals['total_debit'] - $totals['total_credit']);
                        $isBalanced = ($ecart < 0.01);
                    ?>
                    <h5 class="m-0 fw-bold <?= $isBalanced ? 'text-success' : 'text-danger' ?>">
                        <?= $isBalanced ? 'CONFORME (ÉQUILIBRÉ)' : 'DÉSÉQUILIBRÉ - ÉCART : ' . number_format($ecart, 0, '.', ' ') . ' F' ?>
                    </h5>
                </div>
                <div class="text-end">
                    <span class="badge bg-secondary">DÉBIT : <?= number_format($totals['total_debit'], 0, '.', ' ') ?></span>
                    <span class="badge bg-secondary">CRÉDIT : <?= number_format($totals['total_credit'], 0, '.', ' ') ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <button class="btn btn-danger w-100 h-100 fw-bold fs-5 shadow" data-bs-toggle="modal" data-bs-target="#modalEcriture">
                <i class="fas fa-pen-fancy me-2"></i> PASSER UNE ÉCRITURE
            </button>
        </div>
    </div>

    <!-- TABLEAU COMPTABLE MONOSPACÉ -->
    <div class="hub-card bg-white shadow-lg border-0 overflow-hidden" style="border-radius: 15px;">
        <table class="table table-hover align-middle m-0">
            <thead class="bg-dark text-white">
                <tr style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 1px;">
                    <th class="ps-4 py-3">Date</th>
                    <th>N° Compte</th>
                    <th>Libellé de l'opération</th>
                    <th>Réf. Pièce</th>
                    <th class="text-end">Débit (F)</th>
                    <th class="text-end">Crédit (F)</th>
                    <th class="text-center pe-4">Let.</th>
                </tr>
            </thead>
            <tbody class="amount-mono" style="font-size: 0.85rem;">
                <?php foreach($data['ecritures'] as $e): ?>
                <tr class="border-bottom">
                    <td class="ps-4 text-muted"><?= date('d/m/Y', strtotime($e['date'])) ?></td>
                    <td class="fw-bold text-primary"><?= $e['numero'] ?></td>
                    <td class="text-dark"><?= htmlspecialchars($e['libelle']) ?></td>
                    <td class="text-muted italic small"><?= $e['piece'] ?></td>
                    <td class="text-end fw-bold text-success">
                        <?= $e['debit'] > 0 ? number_format($e['debit'], 0, '.', ' ') : '-' ?>
                    </td>
                    <td class="text-end fw-bold text-danger">
                        <?= $e['credit'] > 0 ? number_format($e['credit'], 0, '.', ' ') : '-' ?>
                    </td>
                    <td class="text-center pe-4">
                        <?php if($e['let']): ?>
                            <span class="badge bg-primary-subtle text-primary" style="font-size: 0.6rem;"><?= $e['let'] ?></span>
                        <?php else: ?>
                            <i class="fas fa-circle-notch text-light"></i>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    /* Police monospacée pour un alignement comptable parfait (comme Sage) */
    .amount-mono {
        font-family: 'JetBrains Mono', 'Courier New', monospace;
        letter-spacing: -0.5px;
    }
    .table-hover tbody tr:hover { background-color: #f8fafc !important; }
    .bg-primary-subtle { background: #e0f2fe; }
</style>

