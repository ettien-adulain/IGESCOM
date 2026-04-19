<div class="p-4 animate-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark m-0">PRESTATIONS DE SERVICES</h3>
        <button class="btn btn-wintech-green text-white fw-bold px-4" style="background:#00695c;">Nouveau Service</button>
    </div>

    <div class="hub-card bg-white shadow-lg border-0 overflow-hidden" style="border-radius: 15px;">
        <table class="table table-hover align-middle m-0">
            <thead style="background: #2196f3; color: white;">
                <tr class="small text-uppercase fw-bold">
                    <th class="ps-4 py-3">Code</th>
                    <th>Libellé du Service</th>
                    <th>Tarif Base</th>
                    <th class="text-center">Statut</th>
                </tr>
            </thead>
            <tbody style="font-size: 0.85rem;">
                <?php foreach($services as $s): ?>
                <tr>
                    <td class="ps-4 text-muted"><?= $s['code'] ?></td>
                    <td class="fw-bold text-dark"><?= $s['nom'] ?></td>
                    <td><?= $s['tarif'] ?></td>
                    <td class="text-center">
                        <span class="badge bg-primary-subtle text-primary border border-primary px-3"><?= $s['statut'] ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .bg-primary-subtle { background: #e3f2fd; color: #0d47a1 !important; }
    .bg-success-subtle { background: #e8f5e9; color: #1b5e20 !important; }
</style>

