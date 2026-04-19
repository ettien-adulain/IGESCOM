<?php 
/**
 * WINTECH ERP V2.5 - RÉPERTOIRE ÉLITE DES TIERS
 * Architecture : Programmation Défensive & Orientée Objet
 * Cible : Respect visuel des logos et identités (V3 Elite)
 */

// Sécurité : Initialisation défensive pour éviter tout warning en cas d'absence de données
$stats = $stats ?? ['total_fiches' => 0, 'total_pros' => 0, 'risques' => 0, 'nouveaux' => 0];
$clients = $clients ?? [];
$active = $active ?? 'clients';
?>

<div class="p-4 animate-up">
    <!-- 1. NAVIGATION & BREADCRUMB -->
    <nav class="mb-2">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= $base_url ?>/management" class="text-muted text-decoration-none">Management</a></li>
            <li class="breadcrumb-item active text-danger fw-bold">Répertoire des Tiers</li>
        </ol>
    </nav>
    
    <!-- EN-TÊTE D'ACTION -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0 text-uppercase" style="letter-spacing: -1px;">Gestion des Clients</h2>
            <p class="text-muted small m-0">Pilotage de la base CRM et analyse de solvabilité Sage 100</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-dark btn-sm fw-bold px-3 border-2">
                <i class="fas fa-file-csv me-1"></i> EXPORT CSV
            </button>
            <a href="<?= $base_url ?>/clients/create" class="btn btn-danger btn-sm fw-bold px-4 shadow-pro rounded-pill">
                <i class="fas fa-user-plus me-1"></i> NOUVEAU CLIENT
            </a>
        </div>
    </div>

    <!-- 2. PANNEAU DE PERFORMANCE (KPIs) -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="hub-card border-start border-primary border-5 h-100">
                <div class="p-3">
                    <small class="text-muted fw-bold text-uppercase" style="font-size:0.6rem; letter-spacing: 1px;">Base installée</small>
                    <div class="d-flex align-items-end justify-content-between">
                        <h3 class="fw-bold m-0"><?= number_format((float)$stats['total_fiches'], 0) ?></h3>
                        <i class="fas fa-users text-primary opacity-25 fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="hub-card border-start border-success border-5 h-100">
                <div class="p-3">
                    <small class="text-muted fw-bold text-uppercase" style="font-size:0.6rem; letter-spacing: 1px;">Comptes Entreprises</small>
                    <div class="d-flex align-items-end justify-content-between">
                        <h3 class="fw-bold m-0 text-success"><?= number_format((float)$stats['total_pros'], 0) ?></h3>
                        <i class="fas fa-building text-success opacity-25 fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="hub-card border-start border-danger border-5 h-100">
                <div class="p-3">
                    <small class="text-muted fw-bold text-uppercase" style="font-size:0.6rem; letter-spacing: 1px;">Risques / Bloqués</small>
                    <div class="d-flex align-items-end justify-content-between">
                        <h3 class="fw-bold m-0 text-danger"><?= number_format((float)$stats['risques'], 0) ?></h3>
                        <i class="fas fa-exclamation-triangle text-danger opacity-25 fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="hub-card bg-dark text-white p-3 shadow-pro h-100">
                <small class="text-danger fw-bold text-uppercase" style="font-size:0.6rem; letter-spacing: 1px;">Nouveaux (Ce mois)</small>
                <div class="d-flex align-items-end justify-content-between mt-1">
                    <h3 class="fw-bold m-0 text-white"><?= number_format((float)$stats['nouveaux'], 0) ?></h3>
                    <div class="text-success small mb-1"><i class="fas fa-chart-line me-1"></i> +<?= $stats['nouveaux'] ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. BARRE DE RECHERCHE & FILTRES INTELLIGENTS -->
    <div class="hub-card mb-4 border-0 p-3 shadow-sm bg-white" style="border-radius: 15px;">
        <div class="row g-3 align-items-center">
            <div class="col-md-7">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="smartSearch" class="form-control bg-light border-0 small" 
                           placeholder="Recherche globale (Nom, Code CLI, Téléphone, Enseigne)...">
                </div>
            </div>
            <div class="col-md-3">
                <select id="filterType" class="form-select bg-light border-0 small fw-bold">
                    <option value="ALL">TOUS LES PROFILS</option>
                    <option value="PARTICULIER">PARTICULIERS</option>
                    <option value="PROFESSIONNEL">PROFESSIONNELS</option>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-dark fw-bold btn-sm py-2 rounded-3" onclick="resetFilters()">
                    <i class="fas fa-sync-alt me-1"></i> RÉINITIALISER
                </button>
            </div>
        </div>
    </div>

    <!-- 4. TABLEAU DES CLIENTS (Moteur de Données Objet) -->
    <div class="hub-card bg-white shadow-lg border-0 overflow-hidden" style="border-radius: 20px;">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0" id="mainTable">
                <thead class="bg-dark text-white">
                    <tr style="font-size: 0.72rem; text-transform: uppercase; font-weight: 800; letter-spacing: 1px;">
                        <th class="ps-4 py-3">Identité & Logo</th>
                        <th>Coordonnées</th>
                        <th>Typologie / Solvabilité</th>
                        <th>Établissement & Zone</th>
                        <th class="text-end pe-4">Actions Commerciales</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.85rem;">
                    <?php if(!empty($clients)): foreach($clients as $c): ?>
                    <tr class="client-row animate-fade-in" data-type="<?= $c->type_client ?>">
                        
                        <!-- APPEL AUX MÉTHODES DE L'OBJET $c (INTELLIGENCE D'AFFICHAGE) -->
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <!-- Gestion dynamique du logo avec fallback -->
                                <div class="logo-box-wrapper me-3 shadow-sm">
                                    <img src="<?= htmlspecialchars($c->getLogoSrc($base_url), ENT_QUOTES, 'UTF-8') ?>"
                                         width="50" height="50" loading="lazy" decoding="async"
                                         onerror="this.onerror=null;this.src='<?= htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') ?>/assets/img/static/default_user.png'"
                                         class="img-client-logo" alt="">
                                </div>
                                
                                <div>
                                    <!-- Label intelligent : Magasin si Pro, sinon Nom/Prénom -->
                                    <div class="fw-bold text-dark text-uppercase" style="font-size: 0.92rem; line-height: 1.1;">
                                        <?= htmlspecialchars($c->getLabel()) ?>
                                    </div>
                                    <code class="text-danger fw-bold" style="font-size: 0.65rem;">ID: <?= $c->id_unique_client ?></code>
                                </div>
                            </div>
                        </td>

                        <!-- COORDONNÉES -->
                        <td>
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-phone-alt text-muted me-2" style="font-size: 0.7rem;"></i>
                                <span class="fw-bold text-secondary"><?= $c->telephone ?></span>
                            </div>
                            <div class="text-muted small">
                                <i class="far fa-envelope me-2"></i><?= $c->email ?: '---' ?>
                            </div>
                        </td>

                        <!-- TYPOLOGIE & RISQUE (SAGE 100 LOGIC) -->
                        <td>
                            <span class="badge <?= $c->type_client == 'PROFESSIONNEL' ? 'bg-primary-subtle text-primary border border-primary' : 'bg-secondary-subtle text-secondary border border-secondary' ?> px-2 py-1 mb-1" style="font-size: 0.6rem; font-weight: 800;">
                                <?= $c->type_client ?>
                            </span>
                            <div class="mt-1 small fw-bold <?= ($c->is_blocked) ? 'text-danger' : 'text-muted' ?>">
                                <?php if($c->is_blocked): ?>
                                    <i class="fas fa-ban me-1"></i> COMPTE BLOQUÉ
                                <?php else: ?>
                                    <i class="fas fa-shield-alt me-1"></i> 
                                    <?= number_format((float)($c->solvabilite_max ?? 0), 0, '.', ' ') ?> F max
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- ÉTABLISSEMENT & LOCALISATION -->
                        <td>
                            <?php if($c->type_client === 'PROFESSIONNEL'): ?>
                                <div class="fw-bold text-dark small text-uppercase"><?= $c->nom_magasin ?></div>
                                <div class="extra-small text-muted text-truncate" style="max-width: 180px;">
                                    <i class="fas fa-map-marker-alt me-1 text-danger"></i><?= $c->localisation_magasin ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted italic small">Usage Particulier</span>
                            <?php endif; ?>
                        </td>

                        <!-- ACTIONS ÉLITE -->
                        <td class="text-end pe-4">
                            <div class="btn-group shadow-sm border rounded-3 overflow-hidden">
                                <a href="<?= $base_url ?>/sales/new?client=<?= $c->id ?>" 
                                   class="btn btn-sm btn-white text-primary fw-bold px-3 border-end" 
                                   title="Nouvelle Cotation" style="font-size: 0.65rem;">+ ACHAT</a>
                                
                                <a href="<?= $base_url ?>/services/new?client=<?= $c->id ?>" 
                                   class="btn btn-sm btn-white text-danger fw-bold px-3 border-end" 
                                   title="Maintenance / SAV" style="font-size: 0.65rem;">+ SERVICE</a>
                                
                                <a href="<?= $base_url ?>/clients/history/<?= $c->id ?>" 
                                   class="btn btn-sm btn-white text-dark fw-bold px-3" 
                                   title="Consulter dossier" style="font-size: 0.65rem;">DOSSIER</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="opacity-10 mb-3"><i class="fas fa-folder-open fa-5x"></i></div>
                            <h5 class="text-muted">Aucun tiers n'est encore enregistré dans la base.</h5>
                            <a href="<?= $base_url ?>/clients/create" class="btn btn-danger btn-sm mt-3 px-4 fw-bold shadow-pro rounded-pill">
                                CRÉER LA PREMIÈRE FICHE
                            </a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5. PIED DE PAGE ANALYTIQUE -->
    <div class="mt-4 d-flex justify-content-between align-items-center">
        <p class="small text-muted m-0">Agence active : <b class="text-dark"><?= $_SESSION['agence_nom'] ?></b></p>
        <p class="extra-small text-muted m-0">GIA ERP Engine V2.5 — Système de certification des données</p>
    </div>
</div>

<!-- 6. LOGIQUE JAVASCRIPT (Performance Client-Side) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('smartSearch');
    const typeFilter = document.getElementById('filterType');
    const rows = document.querySelectorAll('.client-row');

    function executeFilter() {
        const query = searchInput.value.toLowerCase();
        const selectedType = typeFilter.value;

        rows.forEach(row => {
            const rowContent = row.textContent.toLowerCase();
            const clientType = row.dataset.type;
            
            const matchQuery = rowContent.includes(query);
            const matchType = (selectedType === 'ALL' || clientType === selectedType);

            if (matchQuery && matchType) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    searchInput.addEventListener('input', executeFilter);
    typeFilter.addEventListener('change', executeFilter);
});

function resetFilters() {
    document.getElementById('smartSearch').value = '';
    document.getElementById('filterType').value = 'ALL';
    document.querySelectorAll('.client-row').forEach(r => {
        r.style.display = "";
    });
}
</script>

<style>
/* CSS DÉDIÉ POUR LE RÉPERTOIRE ÉLITE */
.logo-box-wrapper {
    width: 50px;
    height: 50px;
    background: #f8fafc;
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e2e8f0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.img-client-logo {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.client-row:hover .logo-box-wrapper {
    transform: scale(1.1) rotate(2deg);
    border-color: var(--primary-red);
    box-shadow: 0 4px 12px rgba(225, 29, 72, 0.2);
}

.btn-white { background: #fff; transition: 0.2s; }
.btn-white:hover { background: #f8fafc; color: #000; }

.bg-primary-subtle { background-color: #e0f2fe; color: #0369a1 !important; }
.bg-secondary-subtle { background-color: #f1f5f9; color: #475569 !important; }
.bg-danger-subtle { background-color: #fff1f2; color: #be123c !important; }

.shadow-pro { box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }

.animate-fade-in {
    animation: fadeInUI 0.4s ease-out;
}

@keyframes fadeInUI {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.extra-small { font-size: 0.65rem; }
.italic { font-style: italic; }
</style>

