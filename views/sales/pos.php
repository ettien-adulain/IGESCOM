<div class="p-3 animate-up">
    <div class="row g-3">
        
        <!-- 1. ZONE DE SAISIE ET PANIER (GAUCHE) -->
        <div class="col-lg-8">
            <div class="hub-card bg-white shadow-sm border-0 h-100 p-4" style="border-radius: 20px; min-height: 650px;">
                
                <!-- CHAMP SCANNER / RECHERCHE (CENTRE NÉVRALGIQUE) -->
                <div class="mb-4">
                    <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden border border-2 border-danger">
                        <span class="input-group-text bg-white border-0 ps-4">
                            <i class="fas fa-barcode text-danger"></i>
                        </span>
                        <input type="text" id="pos_search" class="form-control border-0 fs-4 shadow-none" 
                               placeholder="Scanner code-barres ou taper nom/référence article..." autofocus>
                    </div>
                    <!-- RÉSULTATS AUTO-COMPLÉTION -->
                    <div id="pos_results" class="list-group shadow-lg position-absolute w-75 z-3 mt-2" 
                         style="display:none; max-height: 350px; overflow-y: auto; border-radius: 15px;"></div>
                </div>

                <!-- TABLEAU DES ARTICLES EN CAISSE -->
                <div class="table-responsive">
                    <table class="table align-middle" id="pos_table">
                        <thead class="text-uppercase small fw-bold text-muted border-bottom">
                            <tr>
                                <th class="ps-3">Article / Désignation</th>
                                <th width="120" class="text-center">Qté</th>
                                <th width="150" class="text-end">Prix Unit.</th>
                                <th width="150" class="text-end pe-4">Total HT</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody id="pos_cart_body">
                            <!-- Les articles scannés s'ajoutent ici -->
                        </tbody>
                    </table>
                </div>

                <!-- ÉTAT VIDE -->
                <div id="cart_empty_msg" class="text-center py-5 opacity-25">
                    <i class="fas fa-shopping-basket fa-5x mb-3"></i>
                    <h4>CAISSE PRÊTE</h4>
                    <p>En attente du premier article...</p>
                </div>
            </div>
        </div>

        <!-- 2. RÉSUMÉ ET ENCAISSEMENT (DROITE) -->
        <div class="col-lg-4">
            <div class="hub-card bg-dark text-white border-0 shadow-lg p-4 sticky-top" style="top: 110px; border-radius: 25px;">
                <h5 class="fw-bold mb-4 text-danger border-bottom border-secondary pb-2 text-uppercase">Encaissement Direct</h5>
                
                <!-- CLIENT -->
                <div class="mb-4">
                    <label class="extra-small fw-bold opacity-50 text-uppercase mb-2">Client</label>
                    <select id="pos_client" class="form-select bg-secondary border-0 text-white shadow-none rounded-3">
                        <option value="1">VENTE COMPTOIR (CASH)</option>
                        <?php foreach($clients_list as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom_prenom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- RÉSUMÉ FINANCIER -->
                <div class="bg-black bg-opacity-25 p-4 rounded-4 mb-4 border border-secondary">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="opacity-75">Sous-total HT</span>
                        <span id="pos_sum_ht" class="fw-bold">0 F</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="opacity-75">TVA (18%)</span>
                        <span id="pos_sum_tva" class="fw-bold">0 F</span>
                    </div>
                    <hr class="border-secondary">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-danger">NET À PAYER</span>
                        <h2 class="fw-bold m-0 text-warning" id="pos_sum_ttc">0 FCFA</h2>
                    </div>
                </div>

                <!-- MODE DE PAIEMENT -->
                <div class="row g-2 mb-4">
                    <div class="col-4">
                        <input type="radio" class="btn-check" name="pay_mode" id="m_cash" checked>
                        <label class="btn btn-outline-light w-100 py-3 rounded-3" for="m_cash">
                            <i class="fas fa-money-bill-wave d-block mb-1"></i> CASH
                        </label>
                    </div>
                    <div class="col-4">
                        <input type="radio" class="btn-check" name="pay_mode" id="m_momo">
                        <label class="btn btn-outline-light w-100 py-3 rounded-3" for="m_momo">
                            <i class="fas fa-mobile-alt d-block mb-1"></i> MOMO
                        </label>
                    </div>
                    <div class="col-4">
                        <input type="radio" class="btn-check" name="pay_mode" id="m_card">
                        <label class="btn btn-outline-light w-100 py-3 rounded-3" for="m_card">
                            <i class="fas fa-credit-card d-block mb-1"></i> CARTE
                        </label>
                    </div>
                </div>

                <button class="btn btn-danger w-100 py-3 fw-bold fs-5 shadow-lg rounded-3 animate-pulse" id="btn_finalize" onclick="finalizeSale()">
                    ENCAISSER & FACTURER <i class="fas fa-print ms-2"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MOTEUR DE CAISSE INTELLIGENT (JAVASCRIPT) -->
<script>
let cart = [];
const searchInput = document.getElementById('pos_search');
const resultsBox = document.getElementById('pos_results');

/** 1. RECHERCHE / SCAN ARTICLES **/
searchInput.addEventListener('input', function(e) {
    const q = this.value;
    if (q.length < 1) { resultsBox.style.display = 'none'; return; }

    fetch(`<?= $base_url ?>/api/articles/search?q=${encodeURIComponent(q)}&type=INFO`)
        .then(res => res.json())
        .then(data => {
            resultsBox.innerHTML = '';
            if (data.length > 0) {
                data.forEach(a => {
                    const btn = document.createElement('button');
                    btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3';
                    btn.innerHTML = `
                        <div>
                            <b class='text-dark'>${a.nom}</b><br>
                            <small class='text-muted'>Réf: ${a.reference} | Stock: <span class="badge bg-light text-dark">${a.stock_actuel}</span></small>
                        </div>
                        <b class='text-danger'>${parseInt(a.prix_vente).toLocaleString()} F</b>
                    `;
                    btn.onclick = () => addItemToCart(a);
                    resultsBox.appendChild(btn);
                });
                resultsBox.style.display = 'block';
                
                // INTELLIGENCE SCANNER : Si un seul résultat et correspond exactement à la réf (Scan)
                if (data.length === 1 && data[0].reference.toLowerCase() === q.toLowerCase()) {
                    addItemToCart(data[0]);
                }
            } else {
                resultsBox.style.display = 'none';
            }
        });
});

/** 2. GESTION DU PANIER **/
function addItemToCart(item) {
    const existing = cart.find(i => i.id === item.id);
    if (existing) {
        existing.qty++;
    } else {
        cart.push({
            id: item.id,
            nom: item.nom,
            reference: item.reference,
            pu: item.prix_vente,
            qty: 1
        });
    }
    
    // Reset recherche
    searchInput.value = '';
    searchInput.focus();
    resultsBox.style.display = 'none';
    
    renderCart();
    playBeep(); // Optionnel : petit son de scan
}

function renderCart() {
    const body = document.getElementById('pos_cart_body');
    const emptyMsg = document.getElementById('cart_empty_msg');
    body.innerHTML = '';
    
    if (cart.length === 0) {
        emptyMsg.style.display = 'block';
        updateTotals();
        return;
    }
    emptyMsg.style.display = 'none';

    cart.forEach((item, index) => {
        const totalLine = item.pu * item.qty;
        body.innerHTML += `
            <tr class="animate-up">
                <td class="ps-3">
                    <b class="text-dark">${item.nom}</b><br>
                    <small class="text-muted">${item.reference}</small>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm text-center fw-bold border-0 bg-light" 
                           value="${item.qty}" min="1" onchange="updateQty(${index}, this.value)">
                </td>
                <td class="text-end fw-bold">${parseInt(item.pu).toLocaleString()} F</td>
                <td class="text-end pe-4 fw-bold text-danger">${totalLine.toLocaleString()} F</td>
                <td class="text-center">
                    <i class="fas fa-times-circle text-muted cursor-pointer" onclick="removeItem(${index})"></i>
                </td>
            </tr>
        `;
    });
    updateTotals();
}

function updateQty(idx, val) {
    cart[idx].qty = parseInt(val) || 1;
    renderCart();
}

function removeItem(idx) {
    cart.splice(idx, 1);
    renderCart();
}

/** 3. CALCULS FINANCIERS **/
function updateTotals() {
    let ht = 0;
    cart.forEach(i => ht += (i.pu * i.qty));
    
    const tva = ht * 0.18;
    const ttc = ht + tva;

    document.getElementById('pos_sum_ht').innerText = ht.toLocaleString() + ' F';
    document.getElementById('pos_sum_tva').innerText = tva.toLocaleString() + ' F';
    document.getElementById('pos_sum_ttc').innerText = ttc.toLocaleString() + ' FCFA';
}

/** 4. ENCAISSEMENT ET FACTURATION **/
function finalizeSale() {
    if (cart.length === 0) {
        alert("⚠️ Le panier est vide.");
        return;
    }

    const btn = document.getElementById('btn_finalize');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> TRAITEMENT...';

    const payload = {
        id_client: document.getElementById('pos_client').value,
        total_ht: parseFloat(document.getElementById('pos_sum_ht').innerText.replace(/\s|F/g, '')),
        total_tva: parseFloat(document.getElementById('pos_sum_tva').innerText.replace(/\s|F/g, '')),
        total_ttc: parseFloat(document.getElementById('pos_sum_ttc').innerText.replace(/\s|FCFA/g, '')),
        items: cart
    };

    fetch(`<?= $base_url ?>/sales/pos/process`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            window.open(data.pdf_url, '_blank'); // Ouvre la facture
            window.location.reload(); // Reset caisse
        } else {
            alert("Erreur: " + data.message);
            btn.disabled = false;
            btn.innerHTML = 'ENCAISSER & FACTURER <i class="fas fa-check-circle ms-2"></i>';
        }
    });
}

function playBeep() {
    // Si vous voulez un son de scan (mettre un fichier beep.mp3 dans assets/sounds)
    // new Audio('<?= $base_url ?>/assets/sounds/beep.mp3').play();
}
</script>

<style>
    .gia-sidebar { width: 70px !important; } /* On force la sidebar reduite en caisse */
    #content { margin-left: 70px !important; }
    .animate-pulse { animation: pulse 2s infinite; }
    @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.02); } 100% { transform: scale(1); } }
</style>

