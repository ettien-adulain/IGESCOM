<?php
/**
 * WINTECH ERP V2.5 - MOTEUR DE COTATION PROFORMA (ÉDITION ÉLITE)
 * Intelligence : Filtrage instantané, Cascade Sage 100, Calculs certifiés
 * Design : Noir Matte / Rouge Crimson / Finesse SaaS
 */

// Initialisation défensive des variables transmises par le contrôleur
$clients_list = $clients_list ?? [];
$temp_ref = $temp_ref ?? 'PF-' . date('Ymd-Hi');
$active = 'sales';
?>

<div class="p-4 animate-up">
    <!-- 1. BARRE DE TITRE & RÉFÉRENCE DYNAMIQUE -->
    <div class="welcome-card-slim mb-4 d-flex justify-content-between align-items-center shadow-lg" style="background: #0f172a; border-left: 6px solid #e11d48; border-radius: 15px;">
        <div>
            <h3 class="m-0 fw-bold text-white"><i class="fas fa-file-invoice-dollar me-2 text-danger"></i> ÉDITION DE PIÈCE COMMERCIALE</h3>
            <p class="text-white-50 small m-0">YAOCOM'S GROUPE — Moteur de calcul certifié conforme Sage 100 & DGI</p>
        </div>
        <div class="text-end">
            <span class="text-warning fw-bold d-block fs-4" id="current_doc_ref" style="font-family: 'JetBrains Mono', monospace;"><?= $temp_ref ?></span>
            <select id="type_op" class="form-select form-select-sm bg-dark text-white border-secondary mt-1 shadow-none">
                <option value="INFO">ÉQUIPEMENTS INFORMATIQUES</option>
                <option value="BIOTIQUE">ÉQUIPEMENTS BIOTIQUES</option>
                <option value="LOGICIEL">LOGICIELS & LICENCES</option>
                <option value="SERVICE">PRESTATION DE SERVICES</option>
            </select>
        </div>
    </div>

    <div class="row g-4">
        <!-- 2. MODULE CLIENT (SELECTION & CREATION) -->
        <div class="col-lg-4">
            <div class="hub-card bg-white shadow-pro border-0 h-100 p-4" style="border-radius: 20px;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold text-dark m-0 text-uppercase small">1. Identification Tiers</h6>
                    <a href="<?= $base_url ?>/clients/create" class="btn btn-outline-danger btn-sm rounded-pill fw-bold border-2">
                        <i class="fas fa-user-plus me-1"></i> NOUVEAU
                    </a>
                </div>

                <div class="position-relative mb-4" id="search_zone">
                    <div class="input-group shadow-sm rounded-3 overflow-hidden border">
                        <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="client_search" class="form-control border-0 shadow-none py-2" placeholder="Taper le nom ou téléphone...">
                    </div>
                    <!-- Liste des suggestions AJAX -->
                    <div id="client_results" class="list-group shadow-lg position-absolute w-100 z-3 mt-1" style="display:none; max-height: 300px; overflow-y: auto; border-radius: 12px;"></div>
                </div>

                <!-- Carte Client Sélectionné -->
                <div id="selected_client_card" class="p-4 rounded-4 border-start border-5 border-success bg-light d-none animate-up">
                    <input type="hidden" id="id_client_final" value="">
                    <div class="d-flex justify-content-between">
                        <small class="text-success fw-bold">CLIENT VALIDÉ</small>
                        <i class="fas fa-check-double text-success"></i>
                    </div>
                    <h5 class="fw-bold text-dark mt-2 mb-1" id="v_nom">---</h5>
                    <div class="text-muted small mb-3"><i class="fas fa-phone-alt me-2"></i> <span id="v_tel">---</span></div>
                    <div id="v_adr" class="extra-small text-muted p-2 bg-white rounded border border-dashed mb-3">---</div>
                    <button class="btn btn-link btn-sm text-danger p-0 fw-bold text-decoration-none" onclick="resetClient()">
                        <i class="fas fa-sync-alt me-1"></i> Changer de client
                    </button>
                </div>

                <div id="client_placeholder" class="text-center py-5 border border-dashed rounded-4">
                    <i class="fas fa-user-clock fa-3x text-light mb-3"></i>
                    <p class="text-muted small px-3">Veuillez sélectionner un client pour débloquer la facturation.</p>
                </div>
            </div>
        </div>

        <!-- 3. PARAMÈTRES LOGISTIQUES & REMISES -->
        <div class="col-lg-8">
            <div class="hub-card bg-white shadow-pro border-0 p-4" style="border-radius: 20px;">
                <h6 class="fw-bold text-dark m-0 text-uppercase small mb-4">2. Conditions d'expédition & Règlements</h6>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="extra-small fw-bold text-muted mb-1 uppercase">Frais de Port (F)</label>
                        <input type="number" id="frais_port" class="form-control bg-light border-0 fw-bold" value="0" oninput="calculateAll()">
                    </div>
                    <div class="col-md-3">
                        <label class="extra-small fw-bold text-muted mb-1 uppercase">Remise Globale (%)</label>
                        <input type="number" id="remise_pct" class="form-control bg-light border-0 fw-bold text-danger" value="0" oninput="calculateAll()">
                    </div>
                    <div class="col-md-3">
                        <label class="extra-small fw-bold text-muted mb-1 uppercase">Escompte (%)</label>
                        <input type="number" id="escompte_pct" class="form-control bg-light border-0 fw-bold text-warning" value="0" oninput="calculateAll()">
                    </div>
                    <div class="col-md-3">
                        <label class="extra-small fw-bold text-muted mb-1 uppercase">Date Livraison</label>
                        <input type="date" id="log_date" class="form-control bg-light border-0" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. TABLEAU DYNAMIQUE DES ARTICLES -->
        <div class="col-12">
            <div class="hub-card bg-white shadow-pro border-0 p-0 overflow-hidden" style="border-radius: 20px;">
                <div class="bg-dark text-white p-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-uppercase small">3. Détail des articles & prestations</h6>
                    <button class="btn btn-danger btn-sm shadow fw-bold px-4 rounded-3" onclick="addNewLine()">
                        <i class="fas fa-plus me-1"></i> AJOUTER LIGNE
                    </button>
                </div>

                <div class="table-responsive" style="min-height: 300px;">
                    <table class="table align-middle m-0" id="mainTable">
                        <thead class="table-light">
                            <tr class="extra-small fw-bold text-muted text-uppercase" style="letter-spacing: 1px;">
                                <th class="ps-4">Désignation de l'article / Service</th>
                                <th width="100" class="text-center">Qté</th>
                                <th width="180">P.U HT (F)</th>
                                <th width="180" class="text-end pe-4">Total HT</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody id="quoteBody">
                            <!-- JS injecte ici -->
                        </tbody>
                    </table>
                </div>

                <!-- 5. ZONE FINANCIÈRE SAGE 100 -->
                <div class="p-4 bg-light border-top">
                    <div class="row justify-content-end text-end">
                        <div class="col-md-5 col-xl-4">
                            <div class="bg-white p-4 rounded-4 shadow-sm border border-secondary border-opacity-10">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Total Brut HT</span>
                                    <b id="res_ht_brut">0 F</b>
                                </div>
                                <div class="d-flex justify-content-between mb-2 text-danger">
                                    <span class="small">Remise appliquée</span>
                                    <b id="res_remise_v">0 F</b>
                                </div>
                                <div class="d-flex justify-content-between mb-2 text-primary">
                                    <span class="small">Frais de Port</span>
                                    <b id="res_port">0 F</b>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">TVA (18%)</span>
                                    <b id="res_tva">0 F</b>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <span class="fw-bold text-dark fs-5">NET À PAYER</span>
                                    <h2 class="fw-bold text-danger m-0" id="res_ttc">0 FCFA</h2>
                                </div>
                                <button class="btn btn-danger w-100 py-3 fw-bold fs-5 shadow-lg rounded-3" id="btn_finalize" onclick="saveFullQuote()">
                                    <i class="fas fa-file-pdf me-2"></i> GÉNÉRER LE DOCUMENT PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MOTEUR D'INTELLIGENCE COMMERCIALE (JAVASCRIPT) -->
<script>
let rowCount = 0;
const clientsList = <?= json_encode($clients_list) ?>;

/**
 * RECHERCHE CLIENT INSTANTANÉE (Fidèle à votre demande)
 */
document.getElementById('client_search').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    const resBox = document.getElementById('client_results');
    resBox.innerHTML = '';
    
    if(term.length < 1) { resBox.style.display = 'none'; return; }
    
    // Filtrage instantané dans le tableau local (Vitesse Elite)
    const filtered = clientsList.filter(c => 
        c.nom_prenom.toLowerCase().includes(term) || 
        (c.telephone && c.telephone.includes(term))
    );

    if(filtered.length > 0) {
        filtered.forEach(c => {
            const btn = document.createElement('button');
            btn.type = "button";
            btn.className = "list-group-item list-group-item-action d-flex justify-content-between py-3 border-0 border-bottom shadow-none";
            btn.innerHTML = `<div><b class='text-dark'>${c.nom_prenom}</b><br><small class='text-muted'>${c.telephone} | ${c.nom_magasin || 'Particulier'}</small></div> <i class='fas fa-plus-circle text-danger mt-2'></i>`;
            btn.onclick = () => selectClient(c);
            resBox.appendChild(btn);
        });
        resBox.style.display = 'block';
    } else {
        resBox.style.display = 'none';
    }
});

function selectClient(c) {
    document.getElementById('id_client_final').value = c.id;
    document.getElementById('v_nom').innerText = c.nom_prenom;
    document.getElementById('v_tel').innerText = c.telephone;
    document.getElementById('v_adr').innerText = c.adresse_complete || 'Aucune adresse';
    
    document.getElementById('search_zone').style.display = 'none';
    document.getElementById('client_placeholder').style.display = 'none';
    document.getElementById('selected_client_card').classList.remove('d-none');
    document.getElementById('client_results').style.display = 'none';
    
    // Si c'est le premier client, on génère la référence avec son initiale
    updateRef(c.nom_prenom);
}

function resetClient() {
    document.getElementById('id_client_final').value = '';
    document.getElementById('search_zone').style.display = 'block';
    document.getElementById('client_placeholder').style.display = 'block';
    document.getElementById('selected_client_card').classList.add('d-none');
    document.getElementById('client_search').value = '';
}

/**
 * RECHERCHE ARTICLES (FIXÉ : Instantané à la première lettre)
 */
function searchArt(input) {
    const q = input.value;
    const resBox = input.nextElementSibling;
    const type = document.getElementById('type_op').value;

    if(q.length < 1) { resBox.style.display = 'none'; return; }

    fetch(`<?= $base_url ?>/api/articles/search?q=${encodeURIComponent(q)}&type=${type}`)
        .then(r => r.json())
        .then(data => {
            resBox.innerHTML = '';
            if(data.length > 0) {
                data.forEach(a => {
                    const btn = document.createElement('button');
                    btn.type = "button";
                    btn.className = "list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2";
                    btn.innerHTML = `<div><b>${a.nom}</b><br><small class='text-muted'>${a.reference}</small></div> <b class='text-danger'>${parseInt(a.prix_vente).toLocaleString()} F</b>`;
                    btn.onclick = () => {
                        input.value = a.nom;
                        const row = input.closest('tr');
                        row.querySelector('.art-id').value = a.id;
                        row.querySelector('.pu-input').value = a.prix_vente;
                        resBox.style.display = 'none';
                        calculateAll();
                    };
                    resBox.appendChild(btn);
                });
                resBox.style.display = 'block';
            }
        });
}

/**
 * MOTEUR DE CALCUL CASCADE (Standard Sage 100)
 */
function calculateAll() {
    let ht_brut = 0;
    document.querySelectorAll('#quoteBody tr').forEach(tr => {
        const q = parseFloat(tr.querySelector('.qty-input').value) || 0;
        const p = parseFloat(tr.querySelector('.pu-input').value) || 0;
        const line = q * p;
        tr.querySelector('.line-total').innerText = line.toLocaleString('fr-FR') + ' F';
        ht_brut += line;
    });

    const remise_pct = parseFloat(document.getElementById('remise_pct').value) || 0;
    const escompte_pct = parseFloat(document.getElementById('escompte_pct').value) || 0;
    const port = parseFloat(document.getElementById('frais_port').value) || 0;

    const val_remise = ht_brut * (remise_pct / 100);
    const ht_net_commercial = ht_brut - val_remise;
    
    const val_escompte = ht_net_commercial * (escompte_pct / 100);
    const ht_net_financier = ht_net_commercial - val_escompte;
    
    const base_tva = ht_net_financier + port;
    const val_tva = base_tva * 0.18;
    const net_a_payer = base_tva + val_tva;

    // Mise à jour UI
    document.getElementById('res_ht_brut').innerText = ht_brut.toLocaleString('fr-FR') + ' F';
    document.getElementById('res_remise').innerText = '-' + val_remise.toLocaleString('fr-FR') + ' F';
    document.getElementById('res_port').innerText = port.toLocaleString('fr-FR') + ' F';
    document.getElementById('res_tva').innerText = Math.round(val_tva).toLocaleString('fr-FR') + ' F';
    document.getElementById('res_ttc').innerText = Math.round(net_a_payer).toLocaleString('fr-FR') + ' FCFA';
}

function addNewLine() {
    rowCount++;
    const html = `
        <tr id="row-${rowCount}" class="animate-up">
            <td class="ps-4">
                <div class="position-relative">
                    <input type="text" class="form-control border-0 bg-light fw-bold art-input" placeholder="Article..." onkeyup="searchArt(this)">
                    <div class="list-group shadow-lg position-absolute w-100 z-3 art-results" style="display:none;"></div>
                    <input type="hidden" class="art-id">
                </div>
            </td>
            <td><input type="number" class="form-control text-center border-0 bg-light qty-input" value="1" min="1" oninput="calculateAll()"></td>
            <td><input type="number" class="form-control border-0 bg-light pu-input" value="0" oninput="calculateAll()"></td>
            <td class="text-end pe-4 fw-bold text-dark"><span class="line-total">0</span> F</td>
            <td><i class="fas fa-times-circle text-danger cursor-pointer" onclick="this.closest('tr').remove();calculateAll()"></i></td>
        </tr>`;
    document.getElementById('quoteBody').insertAdjacentHTML('beforeend', html);
}

function updateRef(clientName) {
    const initials = clientName.charAt(0).toUpperCase();
    const dateStr = new Date().toISOString().slice(0,10).replace(/-/g,"");
    document.getElementById('current_doc_ref').innerText = `PF-${initials}-${dateStr}-<?= $_SESSION['user_id'] ?>`;
}

/**
 * ENVOI TRANSACTIONNEL AU SERVEUR
 */
function saveFullQuote() {
    const clientId = document.getElementById('id_client_final').value;
    if(!clientId) return alert("❌ Veuillez identifier un client !");

    const items = [];
    document.querySelectorAll('#quoteBody tr').forEach(tr => {
        const desc = tr.querySelector('.art-input').value;
        if(desc) items.push({
            designation: desc,
            qty: tr.querySelector('.qty-input').value,
            pu: tr.querySelector('.pu-input').value,
            id: tr.querySelector('.art-id').value || null
        });
    });

    if(items.length === 0) return alert("❌ Le document est vide !");

    const btn = document.getElementById('btn_finalize');
    btn.disabled = true; 
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> SÉCURISATION...';

    const payload = {
        id_client: clientId,
        items: items,
        log_methode: 'LIVRAISON CLIENT',
        log_date: document.getElementById('log_date').value,
        total_ht_brut: document.getElementById('res_ht_brut').innerText.replace(/[^0-9.]/g, ''),
        remise_globale: document.getElementById('remise_pct').value,
        escompte: document.getElementById('escompte_pct').value,
        frais_port: document.getElementById('frais_port').value,
        total_tva: document.getElementById('res_tva').innerText.replace(/[^0-9.]/g, ''),
        total_ttc: document.getElementById('res_ttc').innerText.replace(/[^0-9.]/g, '')
    };

    fetch('<?= $base_url ?>/sales/save', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'success') {
            window.open('<?= $base_url ?>/' + data.pdf_url, '_blank');
            window.location.reload();
        } else {
            alert(data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-file-pdf me-2"></i> GÉNÉRER LE DOCUMENT PDF';
        }
    });
}

window.onload = addNewLine;
</script>

<style>
    .shadow-pro { box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
    .extra-small { font-size: 0.65rem; }
    .art-results { max-height: 200px; overflow-y: auto; font-size: 0.8rem; }
    .animate-pulse { animation: pulse 2s infinite; }
    @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.03); } 100% { transform: scale(1); } }
</style>

