<div class="p-4 animate-fade-in">
    <!-- BARRE D'ÉTAT SAGE STYLE -->
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 shadow-sm rounded-4 border-start border-5 border-danger">
        <div>
            <h4 class="fw-bold text-dark m-0">REGISTRE DES PIÈCES</h4>
            <p class="text-muted small m-0">Suivi des Proformas, Factures et Workflow de validation</p>
        </div>
        <div class="d-flex gap-3">
            <div class="text-center px-3 border-end">
                <small class="text-muted d-block fw-bold">EN ATTENTE</small>
                <b class="text-danger fs-5"><?= count(array_filter($proformas, fn($p) => $p['type_doc'] == 'PROFORMA')) ?></b>
            </div>
            <a href="<?= $base_url ?>/sales/new" class="btn btn-danger fw-bold px-4 rounded-3 shadow">
                <i class="fas fa-plus me-2"></i> NOUVELLE PIÈCE
            </a>
        </div>
    </div>

    <!-- GRILLE DE DONNÉES -->
    <div class="hub-card bg-white shadow-lg border-0 overflow-hidden" style="border-radius: 20px;">
        <div class="p-3 bg-dark d-flex justify-content-between align-items-center">
            <h6 class="text-white m-0 fw-bold small text-uppercase">Journal des ventes de l'agence</h6>
            <input type="text" id="searchRegistry" class="form-control form-control-sm w-25 border-0 bg-secondary bg-opacity-25 text-white" placeholder="Filtrer (N°, Client)...">
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle m-0" id="registryTable">
                <thead class="table-light">
                    <tr class="extra-small fw-bold text-muted text-uppercase">
                        <th class="ps-4 py-3">Réf. Document</th>
                        <th>Client / Tiers</th>
                        <th>Date</th>
                        <th class="text-end">Montant TTC</th>
                        <th class="text-center">Statut Workflow</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.85rem;">
                    <?php if(!empty($proformas)): foreach($proformas as $p): ?>
                    <tr class="reg-row">
                        <td class="ps-4">
                            <div class="fw-bold <?= $p['type_doc'] == 'FACTURE' ? 'text-dark' : 'text-danger' ?>">
                                <?= $p['numero_officiel'] ?>
                            </div>
                            <small class="text-muted italic"><?= $p['type_doc'] ?></small>
                        </td>
                        <td>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($p['client_nom']) ?></div>
                         <small class="text-muted">Code: <?= htmlspecialchars($p['id_unique_client'] ?? 'NON DÉFINI') ?></small>
                        </td>
                        <td><?= date('d/m/Y', strtotime($p['date_creation'])) ?></td>
                        <td class="text-end fw-bold amount-mono text-primary">
                            <?= number_format($p['net_a_payer'], 0, '.', ' ') ?> F
                        </td>
                        <td class="text-center">
                            <?php 
                                $badge = ($p['type_doc'] == 'FACTURE') ? 'bg-dark' : 'bg-warning-subtle text-warning border-warning';
                                $label = ($p['type_doc'] == 'FACTURE') ? 'COMPTABILISÉ' : 'EN ATTENTE OK';
                            ?>
                            <span class="badge <?= $badge ?> border px-2 py-1" style="font-size: 0.6rem;">
                                <?= $label ?>
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                                <!-- VOIR PDF ARCHIVÉ -->
                                <a href="<?= $base_url ?>/<?= $p['pdf_path'] ?>" target="_blank" class="btn btn-sm btn-white border" title="Visualiser">
                                    <i class="fas fa-eye text-muted"></i>
                                </a>
                                
                                <!-- ACTION SAGE : OK CLIENT -->
                                <?php if($p['type_doc'] == 'PROFORMA'): ?>
                                <a href="<?= $base_url ?>/sales/validate/<?= $p['id'] ?>" 
                                   class="btn btn-sm btn-success fw-bold px-3" 
                                   onclick="return confirm('Confirmer l\'accord client ? Génération de la facture définitive.')">
                                    <i class="fas fa-check-circle me-1"></i> OK CLIENT
                                </a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-light border text-muted" disabled>
                                        <i class="fas fa-lock me-1"></i> SCELLÉ
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">Aucune pièce enregistrée.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Filtre instantané sur le registre
document.getElementById('searchRegistry').addEventListener('input', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('.reg-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
});
</script>

<style>
    .btn-white { background: #fff; }
    .btn-white:hover { background: #f8fafc; }
    .bg-warning-subtle { background-color: #fffbeb; color: #92400e !important; }
    .amount-mono { font-family: 'JetBrains Mono', monospace; }
</style>

