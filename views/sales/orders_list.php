<?php 
/**
 * WINTECH ERP V2.5 - REPERTOIRE DES COMMANDES (LIVRÉES / EN ATTENTE)
 * Fidélité Image 11 : Design Bleu Sage 100 & Ergonomie Logistique
 */

// Programmation Défensive : Initialisation si le contrôleur faillit
$documents = $documents ?? [];
$status = $status ?? 'EN_ATTENTE'; // Par défaut
$active = $active ?? 'management';
?>

<div class="p-4 animate-up">
    <!-- 1. ENTÊTE DE NAVIGATION (Fidèle Image 11) -->
    <div class="text-center mb-5">
        <h2 class="fw-bold text-dark text-uppercase" style="letter-spacing: -1px; font-size: 2.2rem;">
            <?= ($status === 'LIVRE') ? 'Commandes Livrées' : 'Commandes en Attente' ?>
        </h2>
        <div class="mt-3">
            <a href="<?= $base_url ?>/sales/orders" class="text-danger fw-bold text-decoration-none small hover-underline">
                <i class="fas fa-arrow-left me-2"></i> RETOUR AU RÉSUMÉ
            </a>
        </div>
        <div class="d-flex justify-content-center mt-3">
            <hr class="border-success border-3 opacity-100" style="width: 60px;">
        </div>
    </div>

    <!-- 2. ZONE DE FILTRAGE RAPIDE (Finesse SaaS) -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-10">
            <div class="hub-card bg-white shadow-sm border-0 p-3 d-flex justify-content-between align-items-center" style="border-radius: 12px;">
                <div class="input-group w-50">
                    <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="orderSearch" class="form-control bg-light border-0 small" placeholder="Filtrer par numéro ou client...">
                </div>
                <div class="text-end">
                    <span class="badge bg-light text-muted border px-3 py-2 rounded-pill">
                        Agence : <b class="text-dark"><?= $_SESSION['agence_nom'] ?></b>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. LE TABLEAU BLEU SAGE 100 (Cœur de la page) -->
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="hub-card bg-white shadow-lg border-0 overflow-hidden" style="border-radius: 20px;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0" id="orderTable">
                        <thead style="background: #2196f3; color: white;">
                            <tr class="small text-uppercase fw-800" style="letter-spacing: 1px;">
                                <th class="ps-4 py-3">Numéro</th>
                                <th>Client</th>
                                <th>Date</th>
                                <th class="text-center">Statut</th>
                                <th class="text-end pe-4">Options</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.88rem;">
                            <?php if(!empty($documents)): foreach($documents as $d): ?>
                            <tr class="order-row transition-hover">
                                <!-- NUMÉRO -->
                                <td class="ps-4">
                                    <div class="fw-bold text-muted"><?= $d['numero_officiel'] ?></div>
                                </td>
                                
                                <!-- CLIENT -->
                                <td class="fw-bold text-dark text-uppercase">
                                    <?= htmlspecialchars($d['client_nom']) ?>
                                </td>

                                <!-- DATE -->
                                <td>
                                    <?= date('d/m/Y', strtotime($d['date_creation'])) ?>
                                </td>

                                <!-- STATUT (Fidèle Image 11) -->
                                <td class="text-center">
                                    <?php if($status === 'LIVRE'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success px-3 py-1 fw-bold" style="font-size: 0.65rem;">LIVRÉE</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning px-3 py-1 fw-bold" style="font-size: 0.65rem;">EN ATTENTE</span>
                                    <?php endif; ?>
                                </td>

                                <!-- ACTIONS -->
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-sm border rounded-pill overflow-hidden">
                                        <a href="<?= $base_url ?>/<?= $d['pdf_path'] ?>" target="_blank" class="btn btn-sm btn-white border-0" title="Visualiser PDF">
                                            <i class="fas fa-file-pdf text-danger"></i>
                                        </a>
                                        <button class="btn btn-sm btn-white border-0" title="Détails de la commande">
                                            <i class="fas fa-eye text-primary"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="opacity-25 mb-3">
                                        <i class="fas fa-inbox fa-4x text-light"></i>
                                    </div>
                                    <h6 class="text-muted italic">Aucune donnée disponible dans cette catégorie.</h6>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. PIED DE PAGE INFOS -->
    <div class="mt-4 text-center">
        <p class="small text-muted">Système de Certification Logistique V2.5 — <span class="text-danger fw-bold">YAOCOM'S GROUPE</span></p>
    </div>
</div>

<style>
/* STYLES LOCAUX SAGE 100 STYLE */
.fw-800 { font-weight: 800; }

.bg-success-subtle { background-color: #e8f5e9; color: #1b5e20 !important; }
.bg-warning-subtle { background-color: #fff8e1; color: #f57f17 !important; }

.btn-white { background: #fff; }
.btn-white:hover { background: #f1f5f9; }

.transition-hover {
    transition: all 0.2s ease;
}

.transition-hover:hover {
    background-color: #f0f7ff !important; /* Bleu très léger au survol */
}

.hover-underline:hover {
    text-decoration: underline !important;
}

/* Finesse de la table */
#orderTable th {
    border: none;
}
#orderTable td {
    padding-top: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f1f5f9;
}

/* Animation */
.animate-up {
    animation: fadeInUp 0.5s ease-out forwards;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
// Moteur de recherche instantané pour la table
document.getElementById('orderSearch').addEventListener('input', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('.order-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(val) ? "" : "none";
    });
});
</script>

