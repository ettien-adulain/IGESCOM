<?php
/**
 * WINTECH ERP V2.5 - GESTION DU CATALOGUE ATIC & STOCKS
 * Expert Architecture - Système de Tuiles Dynamiques
 * Cible : Productivité maximale, Zéro espace vide, Design Elite
 */

use App\Utils\Formatter;

// Initialisation des données (Sécurité anti-null pour le count)
$articles = $articles ?? [];
$activeAgId = $_SESSION['agence_id'] ?? 1;
?>

<style>
/* --- DESIGN ENGINE : CATALOGUE ÉLITE V3 --- */

/* Conteneur principal sans marges excessives */
.inventory-wrapper {
    padding: 10px 0;
    margin-top: -10px;
}

/* Grille de tuiles ultra-optimisée */
.atic-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 15px;
    padding: 5px;
}

/* Carte Article Style Sage 100 / High-Tech */
.atic-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.atic-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 20px -5px rgba(0,0,0,0.1);
    border-color: var(--primary-red);
}

/* Zone Image Maximisée */
.atic-image-zone {
    height: 160px;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 15px;
    position: relative;
    border-bottom: 1px solid #f1f5f9;
}

.atic-image-zone img {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
    filter: drop-shadow(0 5px 15px rgba(0,0,0,0.08));
    transition: transform 0.4s ease;
}

.atic-card:hover .atic-image-zone img {
    transform: scale(1.1);
}

/* Badge de Type (Informatique, Logiciel...) */
.atic-type-tag {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(15, 23, 42, 0.85);
    color: #fff;
    font-size: 0.6rem;
    font-weight: 800;
    padding: 3px 10px;
    border-radius: 50px;
    backdrop-filter: blur(4px);
    text-transform: uppercase;
    z-index: 5;
}

/* Badge Stock Intelligent */
.atic-stock-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    padding: 4px 10px;
    border-radius: 6px;
    font-weight: 800;
    font-size: 0.7rem;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    z-index: 5;
}

.stock-ok { background: #10b981; color: white; }
.stock-low { background: #f59e0b; color: white; }
.stock-empty { background: #e11d48; color: white; animation: blinker 1.5s linear infinite; }

@keyframes blinker { 50% { opacity: 0.6; } }

/* Corps de la carte */
.atic-info {
    padding: 15px;
    flex-grow: 1;
    background: #fff;
}

.atic-ref {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.7rem;
    color: var(--primary-red);
    font-weight: 700;
    display: block;
    margin-bottom: 2px;
}

.atic-title {
    font-size: 0.9rem;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 12px;
    height: 2.4em;
    line-height: 1.2;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

/* Bloc Financier Sage Style */
.atic-finance-box {
    background: #f1f5f9;
    border-radius: 8px;
    padding: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.atic-price-label { font-size: 0.6rem; font-weight: 700; color: #64748b; text-transform: uppercase; }
.atic-price-value { font-family: 'JetBrains Mono', monospace; font-size: 1rem; font-weight: 800; color: #0f172a; }

/* Footer d'actions rapides */
.atic-actions {
    padding: 10px 15px;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
    display: flex;
    gap: 8px;
}

.btn-atic {
    flex: 1;
    padding: 8px 0;
    border-radius: 6px;
    font-weight: 700;
    font-size: 0.7rem;
    text-transform: uppercase;
    transition: 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.btn-atic-dark { background: #334155; color: white; border: none; }
.btn-atic-dark:hover { background: #0f172a; }
.btn-atic-outline { background: white; color: #334155; border: 1px solid #e2e8f0; }
.btn-atic-outline:hover { background: #f1f5f9; border-color: #cbd5e1; }

/* --- BARRE D'OUTILS FILTRES --- */
.inventory-toolbar {
    background: white;
    padding: 15px 25px;
    border-radius: 15px;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    margin-bottom: 20px;
    border: 1px solid #e2e8f0;
}

</style>

<div class="inventory-wrapper animate-up">
    
    <!-- HEADER DE PAGE -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark m-0"><i class="fas fa-boxes me-2 text-danger"></i> RÉFÉRENTIEL ARTICLES ATIC</h3>
            <p class="text-muted small m-0">Gestion centralisée du catalogue et des marges bénéficiaires</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-dark fw-bold px-3 py-2 rounded-3 shadow-sm border-0" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i>
            </button>
            <a href="<?= $base_url ?>/catalog/create" class="btn btn-danger fw-bold px-4 py-2 rounded-3 shadow-pro border-0">
                <i class="fas fa-plus-circle me-2"></i> AJOUTER UN ARTICLE
            </a>
        </div>
    </div>

    <!-- BARRE D'OUTILS ET RECHERCHE -->
    <div class="inventory-toolbar">
        <div class="row g-3 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="aticSearch" class="form-control bg-light border-0" placeholder="Référence, désignation ou marque...">
                </div>
            </div>
            <div class="col-md-3">
                <select id="filterType" class="form-select bg-light border-0 fw-bold small">
                    <option value="">Toutes catégories</option>
                    <option value="INFO">Informatique</option>
                    <option value="BIOTIQUE">Biotique</option>
                    <option value="LOGICIEL">Logiciels</option>
                    <option value="SERVICE">Services</option>
                </select>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group shadow-sm">
                    <button class="btn btn-white border btn-sm fw-bold active"><i class="fas fa-th-large me-1"></i> Grille</button>
                    <button class="btn btn-white border btn-sm fw-bold"><i class="fas fa-list me-1"></i> Liste</button>
                </div>
            </div>
        </div>
    </div>

    <!-- GRILLE DYNAMIQUE -->
    <div class="atic-grid" id="containerGrid">
        <?php if(!empty($articles)): foreach($articles as $a): 
            // Intelligence de calcul du stock
            $stk = (int)($a['stock_reel'] ?? 0);
            $limit = (int)($a['stock_alerte'] ?? 5);
            $stockClass = ($stk <= 0) ? 'stock-empty' : (($stk <= $limit) ? 'stock-low' : 'stock-ok');
            $stockIcon = ($stk <= 0) ? 'fa-times-circle' : (($stk <= $limit) ? 'fa-exclamation-circle' : 'fa-check-circle');
        ?>
        <div class="atic-card animate-up" data-type="<?= $a['type_article'] ?>" data-search="<?= strtolower($a['designation'].' '.$a['reference_atic']) ?>">
            
            <!-- Tags & Badges -->
            <div class="atic-type-tag"><?= $a['type_article'] ?></div>
            <div class="atic-stock-badge <?= $stockClass ?>">
                <i class="fas <?= $stockIcon ?> me-1"></i> <?= $stk ?> en stock
            </div>

            <!-- Image Zone -->
            <div class="atic-image-zone">
                <img src="<?= htmlspecialchars(Formatter::articlePhotoUrl($base_url, $a['photo'] ?? null)) ?>" 
                     onerror="this.onerror=null;this.src='<?= htmlspecialchars($base_url) ?>/assets/img/static/atic_placeholder.png'">
            </div>

            <!-- Info Zone -->
            <div class="atic-info">
                <span class="atic-ref"><?= $a['reference_atic'] ?></span>
                <h6 class="atic-title"><?= htmlspecialchars($a['designation']) ?></h6>
                
                <div class="atic-finance-box">
                    <div>
                        <div class="atic-price-label">Prix de vente TTC</div>
                        <div class="atic-price-value text-danger"><?= Formatter::fcfa($a['prix_vente_revient'] ?? 0) ?></div>
                    </div>
                    <div class="text-end">
                        <div class="atic-price-label">Bénéfice/U</div>
                        <div class="atic-price-value text-success" style="font-size: 0.8rem;">+ <?= Formatter::fcfa($a['benefice_unitaire'] ?? 0) ?></div>
                    </div>
                </div>
            </div>

            <!-- Actions Zone -->
            <div class="atic-actions">
                <a href="<?= $base_url ?>/catalog/show/<?= $a['id'] ?>" class="btn-atic btn-atic-dark">
                    <i class="fas fa-eye"></i> Détails
                </a>
                <button class="btn-atic btn-atic-outline" onclick="openMovementModal(<?= $a['id'] ?>)">
                    <i class="fas fa-exchange-alt"></i> Stock
                </button>
            </div>
        </div>
        <?php endforeach; else: ?>
            <!-- État vide si aucun article -->
            <div class="text-center py-5 w-100" style="grid-column: 1 / -1;">
                <i class="fas fa-box-open fa-5x text-muted opacity-20 mb-3"></i>
                <h4 class="text-muted">Aucun article dans le catalogue</h4>
                <p class="small text-muted">Commencez par ajouter des produits au référentiel ATIC.</p>
                <a href="<?= $base_url ?>/catalog/create" class="btn btn-danger btn-sm mt-3 px-4">Créer le premier article</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- SCRIPTS D'INTELLIGENCE CLIENT-SIDE -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('aticSearch');
    const typeFilter = document.getElementById('filterType');
    const cards = document.querySelectorAll('.atic-card');

    /**
     * Moteur de recherche hybride (Texte + Catégorie)
     * Ultra-fluide sans rechargement
     */
    function filterArticles() {
        const query = searchInput.value.toLowerCase();
        const type = typeFilter.value;

        cards.forEach(card => {
            const matchesSearch = card.dataset.search.includes(query);
            const matchesType = type === "" || card.dataset.type === type;

            if (matchesSearch && matchesType) {
                card.style.display = 'flex';
                card.classList.add('animate-up');
            } else {
                card.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filterArticles);
    typeFilter.addEventListener('change', filterArticles);
});

function openMovementModal(id) {
    // Logique AJAX pour ouvrir le modal de mouvement de stock
    alert("Ouverture du module de transfert/ajustement pour l'ID : " + id);
}
</script>