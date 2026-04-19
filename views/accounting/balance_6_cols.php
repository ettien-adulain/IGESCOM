<div class="p-4 animate-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark m-0">BALANCE DE VÉRIFICATION (6 COLONNES)</h3>
        <div class="badge bg-dark px-3 py-2 fs-6">EXERCICE <?= date('Y') ?></div>
    </div>

    <div class="hub-card bg-white shadow-xl border-0 overflow-hidden" style="border-radius: 20px;">
        <div class="table-responsive">
            <table class="table table-bordered table-finance m-0">
                <thead class="text-center">
                    <tr class="bg-dark text-white small">
                        <th rowspan="2" class="align-middle">COMPTE</th>
                        <th rowspan="2" class="align-middle">INTITULÉ</th>
                        <th colspan="2">SOLDE INITIAL</th>
                        <th colspan="2">MOUVEMENTS</th>
                        <th colspan="2">SOLDE FINAL</th>
                    </tr>
                    <tr class="bg-secondary text-white extra-small">
                        <th width="12%">DÉBIT</th><th width="12%">CRÉDIT</th>
                        <th width="12%">DÉBIT</th><th width="12%">CRÉDIT</th>
                        <th width="12%">DÉBIT</th><th width="12%">CRÉDIT</th>
                    </tr>
                </thead>
                <tbody class="amount-mono" style="font-size: 0.75rem;">
                    <?php foreach($balance as $b): ?>
                    <tr>
                        <td class="fw-bold text-primary ps-3"><?= $b['numero'] ?></td>
                        <td class="text-start"><?= $b['libelle'] ?></td>
                        <!-- Solde Initial -->
                        <td><?= number_format($b['initial_dr'], 0, '.', ' ') ?></td>
                        <td><?= number_format($b['initial_cr'], 0, '.', ' ') ?></td>
                        <!-- Mouvements -->
                        <td class="bg-light"><?= number_format($b['move_debit'], 0, '.', ' ') ?></td>
                        <td class="bg-light"><?= number_format($b['move_credit'], 0, '.', ' ') ?></td>
                        <!-- Solde Final -->
                        <td class="fw-bold text-success"><?= number_format($b['final_dr'], 0, '.', ' ') ?></td>
                        <td class="fw-bold text-danger"><?= number_format($b['final_cr'], 0, '.', ' ') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .table-finance td { padding: 8px 12px; border-color: #e2e8f0; }
    .amount-mono { font-family: 'JetBrains Mono', monospace; text-align: right; }
</style>

