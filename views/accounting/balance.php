<div class="p-4 animate-up">
    <!-- FILTRES DE PÉRIODE -->
    <div class="hub-card bg-white p-3 mb-4 border-0 shadow-sm d-flex justify-content-between align-items-center">
        <h5 class="fw-bold m-0 text-dark">BALANCE GÉNÉRALE DES COMPTES</h5>
        <form class="d-flex gap-3">
            <input type="date" name="debut" class="form-control form-control-sm" value="<?= $dates['debut'] ?>">
            <input type="date" name="fin" class="form-control form-control-sm" value="<?= $dates['fin'] ?>">
            <button class="btn btn-dark btn-sm px-4">FILTRER</button>
        </form>
    </div>

    <div class="hub-card bg-white shadow-lg border-0 overflow-hidden">
        <table class="table table-hover align-middle m-0 table-finance">
            <thead class="bg-dark text-white">
                <tr class="extra-small-sage">
                    <th class="ps-4 py-3">N° COMPTE</th>
                    <th>INTITULÉ DU COMPTE</th>
                    <th class="text-end">MOUVEMENT DÉBIT</th>
                    <th class="text-end">MOUVEMENT CRÉDIT</th>
                    <th class="text-end pe-4">SOLDE FINAL</th>
                </tr>
            </thead>
            <tbody class="amount-mono">
                <?php foreach($balance as $b): ?>
                <tr onclick="window.location.href='<?= $base_url ?>/compta/grand-livre/<?= $b['numero'] ?>'" style="cursor: pointer;">
                    <td class="ps-4 fw-bold text-primary"><?= $b['numero'] ?></td>
                    <td class="text-dark fw-bold"><?= $b['libelle'] ?></td>
                    <td class="text-success"><?= number_format($b['total_debit'], 0, '.', ' ') ?></td>
                    <td class="text-danger"><?= number_format($b['total_credit'], 0, '.', ' ') ?></td>
                    <td class="pe-4 fw-bold <?= ($b['solde'] >= 0) ? 'text-success' : 'text-danger' ?>">
                        <?= number_format(abs($b['solde']), 0, '.', ' ') ?> <?= ($b['solde'] >= 0) ? 'D' : 'C' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

