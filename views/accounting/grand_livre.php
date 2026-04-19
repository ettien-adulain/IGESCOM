<div class="p-4 animate-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold"><i class="fas fa-book me-2 text-danger"></i> GRAND LIVRE : COMPTE <?= $compte ?></h4>
        <a href="<?= $base_url ?>/compta/balance" class="btn btn-outline-dark btn-sm fw-bold">RETOUR BALANCE</a>
    </div>

    <div class="hub-card bg-white shadow-lg border-0 overflow-hidden">
        <table class="table align-middle m-0 table-finance">
            <thead class="bg-secondary text-white">
                <tr class="extra-small-sage">
                    <th class="ps-4">Date</th>
                    <th>Jnl</th>
                    <th>Pièce</th>
                    <th>Libellé de l'opération</th>
                    <th class="text-end">Débit</th>
                    <th class="text-end">Crédit</th>
                    <th class="text-center">Let.</th>
                </tr>
            </thead>
            <tbody class="amount-mono">
                <?php 
                $cumul = 0;
                foreach($mouvements as $m): 
                $cumul += ($m['debit'] - $m['credit']);
                ?>
                <tr>
                    <td class="ps-4 text-muted"><?= date('d/m/Y', strtotime($m['date_operation'])) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= $m['journal'] ?></span></td>
                    <td class="small text-muted italic"><?= $m['numero_piece'] ?></td>
                    <td class="text-dark fw-bold"><?= $m['libelle_operation'] ?></td>
                    <td class="text-success"><?= number_format($m['debit'], 0, '.', ' ') ?></td>
                    <td class="text-danger"><?= number_format($m['credit'], 0, '.', ' ') ?></td>
                    <td class="text-center">
                        <?php if($m['lettrage_code']): ?>
                            <span class="badge bg-primary-subtle text-primary"><?= $m['lettrage_code'] ?></span>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-dark text-white fw-bold">
                <tr>
                    <td colspan="4" class="ps-4 text-end">SOLDE PROGRESSIF DU COMPTE</td>
                    <td colspan="3" class="text-center text-warning fs-5">
                        <?= number_format(abs($cumul), 0, '.', ' ') ?> <?= ($cumul >= 0) ? 'DÉBITEUR' : 'CRÉDITEUR' ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

