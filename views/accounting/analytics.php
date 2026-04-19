<div class="p-4 animate-up">
    <!-- 1. HEADER DYNAMIQUE -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0">GESTION COMPTABLE EXPERT</h2>
            <p class="text-muted small">Modèle Sage 100 • Référentiel SYSCOHADA Révisé</p>
        </div>
        <div class="d-flex gap-3 align-items-center">
            <button class="btn btn-dark fw-bold px-4 rounded-3 shadow-sm" style="background:#00695c;" data-bs-toggle="modal" data-bs-target="#modalEntry">
                <i class="fas fa-plus me-2"></i> PASSER UNE ÉCRITURE
            </button>
            <div class="bg-success text-white p-3 rounded-4 shadow-sm" style="min-width: 220px; background: #00897b !important;">
                <small class="d-block opacity-75 fw-bold">SOLDE TRÉSORERIE GLOBAL</small>
                <div class="amount-mono fs-4" id="global-total-cash">0 F</div>
            </div>
        </div>
    </div>

    <!-- 2. ONGLETS (Fidèle aux images) -->
    <div class="hub-card bg-white p-2 mb-4 border-0 shadow-sm" style="border-radius: 20px;">
        <ul class="nav nav-pills nav-justified" id="comptaTabs">
            <li class="nav-item">
                <a class="nav-link active py-3" data-bs-toggle="tab" href="#tab-journal" onclick="fetchTab('journal')">
                    <i class="fas fa-book-open me-2"></i> JOURNAL DE SAISIE
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3" data-bs-toggle="tab" href="#tab-grandlivre">
                    <i class="fas fa-layer-group me-2"></i> GRAND LIVRE
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3" data-bs-toggle="tab" href="#tab-treso" onclick="fetchTab('tresorerie')">
                    <i class="fas fa-university me-2"></i> TRÉSORERIE / BANQUE
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3" data-bs-toggle="tab" href="#tab-bilan">
                    <i class="fas fa-balance-scale me-2"></i> BILAN & RÉSULTATS
                </a>
            </li>
        </ul>
    </div>

    <!-- 3. CONTENUS DES ONGLETS -->
    <div class="tab-content">
        <div class="tab-pane fade show active" id="tab-journal">
             <div class="hub-card bg-white shadow-lg border-0 overflow-hidden">
                <table class="table table-hover align-middle m-0 table-compta">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="ps-4 py-3">Date</th>
                            <th>N° Compte</th>
                            <th>Opération</th>
                            <th>Pièce</th>
                            <th class="text-end">Débit</th>
                            <th class="text-end">Crédit</th>
                            <th class="text-center">Let.</th>
                        </tr>
                    </thead>
                    <tbody id="journal-body" class="amount-mono">
                        <!-- Chargé en AJAX -->
                    </tbody>
                </table>
             </div>
        </div>

        <div class="tab-pane fade" id="tab-treso">
            <div id="treso-content" class="row g-4"></div>
        </div>
    </div>
</div>

<!-- 4. MODAL DE SAISIE ÉCRITURE (IMAGE 4) -->
<div class="modal fade" id="modalEntry" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 25px; background: #00695c;">
            <div class="modal-header border-0 text-white p-4">
                <h5 class="modal-title fw-bold">SAISIE D'ÉCRITURE JOURNAL</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-white m-1 rounded-bottom-4 p-4">
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="small fw-bold text-muted">DATE VALEUR</label>
                        <input type="date" id="form-date" class="form-control bg-light border-0" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-6">
                        <label class="small fw-bold text-muted">PIÈCE JUSTIFICATIVE</label>
                        <input type="text" id="form-piece" class="form-control bg-light border-0" placeholder="ex: FACT-102">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold text-muted">COMPTE SYSCOHADA</label>
                    <input type="text" id="form-compte" class="form-control bg-light border-0" placeholder="ex: 411100 (Clients)">
                </div>
                <div class="mb-4">
                    <label class="small fw-bold text-muted">LIBELLÉ DE L'ÉCRITURE</label>
                    <input type="text" id="form-libelle" class="form-control bg-light border-0" placeholder="Désignation claire...">
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="p-3 rounded-4" style="background: #e0f2f1;">
                            <label class="extra-small fw-bold text-success d-block">DÉBIT (CFA)</label>
                            <input type="number" id="form-debit" class="form-control border-0 bg-transparent fw-bold fs-5 p-0" value="0">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-4" style="background: #fbe9e7;">
                            <label class="extra-small fw-bold text-danger d-block">CRÉDIT (CFA)</label>
                            <input type="number" id="form-credit" class="form-control border-0 bg-transparent fw-bold fs-5 p-0" value="0">
                        </div>
                    </div>
                </div>
                <button class="btn btn-dark w-100 py-3 rounded-pill fw-bold shadow" onclick="submitEntry()">ENREGISTRER AU GRAND LIVRE</button>
            </div>
        </div>
    </div>
</div>

<script>
/** MOTEUR D'INTERACTION AJAX */
function fetchTab(tabName) {
    fetch(`<?= $base_url ?>/api/compta/load?tab=${tabName}`)
    .then(r => r.json())
    .then(data => {
        if(tabName === 'journal') renderJournal(data);
        if(tabName === 'tresorerie') renderTreso(data);
    });
}

function renderJournal(data) {
    const body = document.getElementById('journal-body');
    body.innerHTML = data.map(e => `
        <tr>
            <td class="ps-4 text-muted">${e.date_operation}</td>
            <td class="fw-bold text-primary">${e.compte_numero}</td>
            <td class="text-dark">${e.libelle_operation}</td>
            <td class="text-muted italic small">${e.numero_piece}</td>
            <td class="text-success text-end">${e.debit > 0 ? e.debit.toLocaleString() : '-'}</td>
            <td class="text-danger text-end">${e.credit > 0 ? e.credit.toLocaleString() : '-'}</td>
            <td class="text-center">${e.lettrage || '-'}</td>
        </tr>
    `).join('');
}

function submitEntry() {
    // Collecte des données du modal et envoi vers postEntry...
    alert("Vérification de l'équilibre et enregistrement...");
}

// Lancement automatique au chargement
document.addEventListener('DOMContentLoaded', () => fetchTab('journal'));
</script>

