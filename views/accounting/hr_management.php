<div class="p-4 animate-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark m-0"><i class="fas fa-user-graduate me-2 text-danger"></i> GESTION DU PERSONNEL</h4>
            <p class="text-muted small m-0">Suivi de la masse salariale et conformité sociale</p>
        </div>
        <button class="btn btn-danger btn-sm fw-bold px-4 shadow-sm">
            <i class="fas fa-plus-circle me-1"></i> ENRÔLER UN EMPLOYÉ
        </button>
    </div>

    <!-- RÉSUMÉ ANALYTIQUE RH -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="hub-card bg-white border-start border-5 border-primary p-3 shadow-sm">
                <small class="text-muted fw-bold">EFFECTIF TOTAL</small>
                <h3 class="fw-bold m-0"><?= count($employees ?? []) ?> Agents</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="hub-card bg-dark text-white p-3 shadow-lg">
                <small class="text-danger fw-bold">MASSE SALARIALE MENSUELLE</small>
                <h3 class="fw-bold m-0 text-white">
                    <?php 
                        $total = array_sum(array_column($employees ?? [], 'cout_total'));
                        echo number_format($total, 0, '.', ' ');
                    ?> F
                </h3>
            </div>
        </div>
    </div>

    <!-- TABLEAU ÉLITE -->
    <div class="hub-card bg-white shadow-sm p-0 border-0 overflow-hidden">
        <table class="table table-hover align-middle m-0">
            <thead class="bg-dark text-white">
                <tr style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px;">
                    <th class="ps-4 py-3">Matricule / Nom</th>
                    <th>Poste & Catégorie</th>
                    <th>Salaire Net</th>
                    <th>Charges Sociales</th>
                    <th class="text-end">Coût Total</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody style="font-size: 0.85rem;">
                <?php if(!empty($employees)): foreach($employees as $e): ?>
                <tr>
                    <td class="ps-4">
                        <div class="fw-bold text-dark"><?= $e['nom'] ?> <?= $e['prenom'] ?></div>
                        <code class="text-danger" style="font-size: 0.65rem;"><?= $e['matricule_hr'] ?></code>
                    </td>
                    <td>
                        <div class="fw-bold"><?= $e['poste'] ?></div>
                        <small class="text-muted"><?= $e['categorie'] ?></small>
                    </td>
                    <td class="fw-bold"><?= number_format($e['salaire_base'], 0, '.', ' ') ?> F</td>
                    <td class="text-danger fw-bold">+ <?= number_format($e['charges_patronales'], 0, '.', ' ') ?> F</td>
                    <td class="text-end fw-bold text-primary pe-3">
                        <?= number_format($e['cout_total'], 0, '.', ' ') ?> F
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-success fw-bold" onclick="paySalary(<?= $e['id'] ?>, '<?= $e['nom'] ?>')">
                            <i class="fas fa-money-check-alt me-1"></i> PAYER
                        </button>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="6" class="text-center py-5">Aucun employé enregistré.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function paySalary(id, name) {
    if(confirm(`Voulez-vous valider le paiement du salaire pour ${name} pour le mois en cours ?`)) {
        // Logique vers HRController::pay
        alert("Traitement du virement pour " + name);
    }
}
</script>

