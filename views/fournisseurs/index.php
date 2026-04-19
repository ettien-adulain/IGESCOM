<?php 
/**
 * WINTECH ERP V2.5 - RÉPERTOIRE DES FOURNISSEURS (ÉLITE)
 * Cible : Gestion des flux d'approvisionnement et tiers.
 */
?>

<div class="p-4 animate-up">
    <!-- 1. BARRE D'ACTION ET FILTRES -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark m-0">
                <i class="fas fa-truck-loading text-danger me-2"></i> RÉPERTOIRE DES FOURNISSEURS
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small m-0 p-0 bg-transparent">
                    <li class="breadcrumb-item"><a href="<?= $base_url ?>/management" class="text-muted">Management</a></li>
                    <li class="breadcrumb-item active text-danger fw-bold">Fournisseurs</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <!-- BOUTON EXPORT (LOGIQUE SAGE 100) -->
            <button class="btn btn-outline-dark btn-sm fw-bold px-3 border-2">
                <i class="fas fa-file-excel me-1"></i> EXPORTER
            </button>
            <!-- BOUTON VERS LA PAGE DE CRÉATION PLEIN ÉCRAN -->
            <a href="<?= $base_url ?>/fournisseurs/create" class="btn btn-danger btn-sm fw-bold px-4 shadow-sm rounded-3">
                <i class="fas fa-plus-circle me-1"></i> NOUVEAU FOURNISSEUR
            </a>
        </div>
    </div>

    <!-- 2. RÉSUMÉ ANALYTIQUE DES PARTENAIRES -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="hub-card bg-white p-3 shadow-sm border-start border-5 border-primary">
                <small class="text-muted fw-bold">TOTAL PARTENAIRES</small>
                <h4 class="fw-bold m-0"><?= count($fournisseurs ?? []) ?> Fournisseurs</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="hub-card bg-white p-3 shadow-sm border-start border-5 border-success">
                <small class="text-muted fw-bold">CATÉGORIES ACTIVES</small>
                <h4 class="fw-bold m-0">4 Secteurs</h4>
            </div>
        </div>
    </div>

    <!-- 3. BARRE DE RECHERCHE INTELLIGENTE -->
    <div class="hub-card bg-white shadow-sm mb-4 border-0 p-3">
        <div class="row g-3 align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="supplierSearch" class="form-control bg-light border-0 small" placeholder="Filtrer par nom, abréviation, ville ou code comptable...">
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="form-check form-check-inline small">
                    <input class="form-check-input" type="checkbox" id="filterStock" value="option1">
                    <label class="form-check-label text-muted fw-bold" for="filterStock">En commande</label>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. TABLEAU DES TIERS (STYLE COMPTABILITÉ EXPERTE) -->
    <div class="hub-card bg-white shadow-lg border-0 overflow-hidden" style="border-radius: 20px;">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0" id="supplierTable">
                <thead class="bg-dark text-white">
                    <tr style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 1.5px;">
                        <th class="ps-4 py-3">Code / Abrégé</th>
                        <th>Raison Sociale / Catégorie</th>
                        <th>Coordonnées</th>
                        <th>Localisation</th>
                        <th class="text-end pe-4">Actions de Gestion</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.85rem;">
                    <?php if(!empty($fournisseurs)): foreach($fournisseurs as $f): ?>
                    <tr class="supplier-row">
                        <!-- CODE ET ABRÉGÉ -->
                        <td class="ps-4">
                            <code class="fw-bold text-danger fs-6"><?= $f['code_fournisseur'] ?></code><br>
                            <span class="text-muted extra-small fw-bold text-uppercase"><?= $f['abbreviation'] ?? 'N/A' ?></span>
                        </td>
                        <!-- NOM ET CATÉGORIE -->
                        <td>
                            <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($f['nom']) ?></div>
                            <span class="badge bg-light text-muted border py-1 px-2 mt-1" style="font-size: 0.6rem;">
                                <?= $f['type_article'] ?? 'NON SPÉCIFIÉ' ?>
                            </span>
                        </td>
                        <!-- CONTACTS -->
                        <td>
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-phone-alt text-muted me-2 small"></i>
                                <span class="fw-bold"><?= htmlspecialchars($f['contact']) ?></span>
                            </div>
                            <div class="text-muted extra-small">
                                <i class="far fa-envelope me-2"></i> <?= htmlspecialchars($f['email'] ?? '---') ?>
                            </div>
                        </td>
                        <!-- VILLE -->
                        <td>
                            <div class="text-dark"><i class="fas fa-map-marker-alt text-danger me-2"></i><?= htmlspecialchars($f['localisation']) ?></div>
                            <small class="text-muted italic">Abidjan, Côte d'Ivoire</small>
                        </td>
                        <!-- ACTIONS -->
                        <td class="text-end pe-4">
                            <div class="btn-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                                <a href="<?= $base_url ?>/fournisseurs/edit/<?= $f['id'] ?>" class="btn btn-sm btn-white border" title="Fiche Complète">
                                    <i class="fas fa-edit text-dark"></i>
                                </a>
                                <a href="<?= $base_url ?>/purchases/new?supplier=<?= $f['id'] ?>" class="btn btn-sm btn-white border" title="Passer commande">
                                    <i class="fas fa-shopping-basket text-success"></i>
                                </a>
                                <button class="btn btn-sm btn-white border text-danger" title="Supprimer">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-box-open fa-4x mb-3 opacity-25"></i>
                            <p class="fw-bold">Aucun partenaire enregistré dans la base GIA.</p>
                            <a href="<?= $base_url ?>/fournisseurs/create" class="btn btn-danger btn-sm px-4 fw-bold">ENRÔLER MAINTENANT</a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// --- FILTRE DE RECHERCHE INSTANTANÉ (UX) ---
document.getElementById('supplierSearch').addEventListener('input', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('.supplier-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
});
</script>

<style>
    /* Finesse du tableau Elite */
    .btn-white { background: #fff; border-color: #e2e8f0; }
    .btn-white:hover { background: #f8fafc; border-color: var(--primary-red); }
    .extra-small { font-size: 0.65rem; }
    .table-hover tbody tr:hover { background-color: #fff5f5 !important; }
    .breadcrumb-item + .breadcrumb-item::before { content: ">"; color: #cbd5e1; }
</style>

