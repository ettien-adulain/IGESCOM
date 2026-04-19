/**
 * WINTECH ERP V2.5 - MOTEUR D'AUTO-COMPLÉTION ÉLITE
 * Cible : Recherche instantanée d'Articles (Catalogue ATIC) et Clients
 * Performance : Système de Debounce intégré (300ms)
 */

const WinTechSearch = {
    timer: null,

    /**
     * Initialise la recherche sur un champ
     * @param {string} inputId - ID de l'input de saisie
     * @param {string} resultsId - ID de la div d'affichage des résultats
     * @param {string} type - 'articles' ou 'clients'
     */
    init: function(inputId, resultsId, type) {
        const input = document.getElementById(inputId);
        const resultsBox = document.getElementById(resultsId);

        if (!input || !resultsBox) return;

        input.addEventListener('input', () => {
            clearTimeout(this.timer);
            const query = input.value.trim();

            if (query.length < 1) {
                resultsBox.style.display = 'none';
                resultsBox.innerHTML = '';
                return;
            }

            // Attendre que l'utilisateur arrête de taper (Performance)
            this.timer = setTimeout(() => {
                this.fetchData(query, resultsBox, type, input);
            }, 300);
        });

        // Fermer la liste si on clique ailleurs
        document.addEventListener('click', (e) => {
            if (e.target !== input) resultsBox.style.display = 'none';
        });
    },

    /**
     * Récupère les données depuis l'API PHP
     */
    fetchData: function(query, container, type, inputEl) {
        // Détection dynamique du type d'achat pour les articles (Module 3)
        const typeOp = document.getElementById('type_op') ? document.getElementById('type_op').value : 'INFO';
        const url = type === 'articles' 
            ? `${window.location.origin}/wintech_erp1/public/api/articles/search?q=${encodeURIComponent(query)}&type=${typeOp}`
            : `${window.location.origin}/wintech_erp1/public/api/clients/search?q=${encodeURIComponent(query)}`;

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.json())
            .then(data => {
                this.render(data, container, type, inputEl);
            })
            .catch(err => console.error("Erreur Recherche WinTech:", err));
    },

    /**
     * Génère le rendu visuel des résultats (Design Elite V3)
     */
    render: function(data, container, type, inputEl) {
        container.innerHTML = '';
        
        if (data.length === 0) {
            container.innerHTML = '<div class="list-group-item small text-muted italic">Aucun résultat trouvé...</div>';
            container.style.display = 'block';
            return;
        }

        data.forEach(item => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 border-0 border-bottom';
            
            if (type === 'articles') {
                // Rendu spécifique Article : Nom + Réf + Prix (Image 3)
                btn.innerHTML = `
                    <div class="text-start">
                        <b class="text-dark d-block" style="font-size:0.85rem;">${item.nom}</b>
                        <code class="text-danger extra-small">${item.reference}</code>
                    </div>
                    <span class="badge bg-dark-subtle text-dark border fw-bold">${parseInt(item.prix_vente).toLocaleString()} F</span>
                `;
            } else {
                // Rendu spécifique Client : Nom + Tel (Image 4)
                btn.innerHTML = `
                    <div class="text-start">
                        <b class="text-dark d-block">${item.nom_prenom}</b>
                        <small class="text-muted"><i class="fas fa-phone-alt me-1"></i>${item.telephone}</small>
                    </div>
                `;
            }

            btn.onclick = () => this.select(item, type, inputEl, container);
            container.appendChild(btn);
        });

        container.style.display = 'block';
    },

    /**
     * Gère la sélection d'un élément
     */
    select: function(item, type, inputEl, container) {
        if (type === 'articles') {
            const row = inputEl.closest('tr');
            inputEl.value = item.nom;
            row.querySelector('.art-id').value = item.id;
            row.querySelector('.pu-input').value = item.prix_vente;
            
            // Déclencher le calcul automatique (Module 2.3)
            if (typeof calculateQuote === "function") calculateQuote();
            else if (typeof calculateRow === "function") calculateRow(inputEl);
            
        } else {
            // Sélection Client (Module 2.1)
            document.getElementById('id_client_final').value = item.id;
            document.getElementById('v_nom').innerText = item.nom_prenom;
            document.getElementById('v_tel').innerText = item.telephone;
            
            document.getElementById('client_placeholder').style.display = 'none';
            inputEl.parentElement.style.display = 'none';
            document.getElementById('client_selected_card').classList.remove('d-none');
        }

        container.style.display = 'none';
    }
};

// Initialisation globale pour les pages de facturation
document.addEventListener('DOMContentLoaded', () => {
    WinTechSearch.init('client_search', 'client_results', 'clients');
});