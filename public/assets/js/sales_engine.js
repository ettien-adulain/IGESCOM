/**
 * RECHERCHE CLIENTS (Inspiré de Sage 100)
 */
const clientInput = document.getElementById('client_search');
const clientResults = document.getElementById('client_results');

if(clientInput) {
    clientInput.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        clientResults.innerHTML = '';
        if(q.length < 1) { clientResults.style.display = 'none'; return; }

        const filtered = allClients.filter(c => 
            c.nom_prenom.toLowerCase().includes(q) || c.telephone.includes(q)
        );

        filtered.forEach(c => {
            const div = document.createElement('div');
            div.className = 'search-item';
            div.innerHTML = `<b>${c.nom_prenom}</b> <small class="text-muted">(${c.telephone})</small>`;
            div.onclick = () => {
                document.getElementById('id_client_final').value = c.id;
                clientInput.value = c.nom_prenom;
                document.getElementById('v_tel').value = c.telephone;
                clientResults.style.display = 'none';
            };
            clientResults.appendChild(div);
        });
        clientResults.style.display = 'block';
    });
}

/**
 * RECHERCHE ARTICLES DANS LE TABLEAU
 */
function searchArt(input) {
    const q = input.value.toLowerCase();
    const resBox = input.nextElementSibling;
    resBox.innerHTML = '';
    if(q.length < 1) { resBox.style.display = 'none'; return; }

    const filtered = allArticles.filter(a => 
        a.designation.toLowerCase().includes(q) || a.reference.toLowerCase().includes(q)
    );

    filtered.forEach(a => {
        const div = document.createElement('div');
        div.className = 'search-item';
        div.innerHTML = `<b>${a.designation}</b> <small class="float-end text-danger">${a.prix_vente} F</small>`;
        div.onclick = () => {
            const row = input.closest('tr');
            input.value = a.designation;
            row.querySelector('.ref-art').value = a.reference;
            row.querySelector('.pu').value = a.prix_vente;
            resBox.style.display = 'none';
            calculateFne();
        };
        resBox.appendChild(div);
    });
    resBox.style.display = 'block';
}

/**
 * CALCULS FINANCIERS (Module 2.4)
 */
function calculateFne() {
    let grandHT = 0;
    const items = document.querySelectorAll('#quoteBody tr');
    
    items.forEach(tr => {
        const qty = parseFloat(tr.querySelector('.qty').value) || 0;
        const pu = parseFloat(tr.querySelector('.pu').value) || 0;
        const rem = parseFloat(tr.querySelector('.rem').value) || 0;
        
        const rowHT = (qty * pu) * (1 - rem/100);
        tr.querySelector('.line-total').innerText = Math.round(rowHT).toLocaleString('fr-FR');
        grandHT += rowHT;
    });

    const port = parseFloat(document.getElementById('frais_port').value) || 0;
    const tva = (grandHT + port) * 0.18;
    const net = grandHT + port + tva;

    document.getElementById('display_ht').innerText = Math.round(grandHT).toLocaleString() + ' F';
    document.getElementById('display_tva').innerText = Math.round(tva).toLocaleString() + ' F';
    document.getElementById('display_net').innerText = Math.round(net).toLocaleString() + ' FCFA';
}