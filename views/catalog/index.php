<?php
/**
 * WINTECH ERP V2.5 - CATALOGUE ATIC PRO EDITION
 * Architecture : Haute Disponibilité / 300+ Références
 * Intelligence : Moteur de filtrage instantané & Calcul de rentabilité Sage 100
 */

use App\Utils\Formatter;

// Initialisation sécurisée
$articles = $articles ?? [];
$active = 'catalog';
$title = "Catalogue ATIC - Référentiel Élite";
?>

<!-- STYLE CSS INTERNE (SOPHISTICATION ÉLITE) -->
<style>
    /* --- SYSTÈME DE GRILLE & CARTES --- */
    .catalog-container { padding: 20px; animation: fadeInUp 0.6s ease-out; }
    
    .article-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 25px;
        transition: all 0.4s ease;
    }

    .article-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #edf2f7;
        overflow: hidden;
        position: relative;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        height: 100%;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .article-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        border-color: var(--primary-red);
    }

    /* --- GESTION DES IMAGES (FORMAT ATIC) --- */
    .img-wrapper {
        background: #f8fafc;
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 30px;
        position: relative;
        overflow: hidden;
    }

    .img-wrapper img {
        max-height: 100%;
        width: auto;
        mix-blend-mode: multiply;
        transition: transform 0.5s ease;
        z-index: 1;
    }

    .article-card:hover .img-wrapper img { transform: scale(1.15); }

    .type-badge {
        position: absolute;
        bottom: 10px;
        right: 15px;
        background: rgba(15, 23, 42, 0.8);
        backdrop-filter: blur(4px);
        color: white;
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 0.65rem;
        font-weight: 700;
        z-index: 2;
    }

    /* --- BLOC FINANCIER (SAGE 100 STYLE) --- */
    .price-container {
        background: #0f172a;
        margin: 15px;
        padding: 15px;
        border-radius: 15px;
        border-left: 4px solid var(--primary-red);
    }

    .price-label { color: #94a3b8; font-size: 0.6rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
    .price-value { color: #facc15; font-size: 1.25rem; font-weight: 900; font-family: 'Inter', sans-serif; }
    .margin-value { color: #4ade80; font-size: 0.7rem; font-weight: 700; }

    /* --- ÉTAT DES STOCKS --- */
    .stock-tag {
        position: absolute;
        top: 15px;
        left: 15px;
        z-index: 3;
        padding: 6px 15px;
        border-radius: 50px;
        font-weight: 800;
        font-size: 0.7rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .stock-ok { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .stock-low { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; animation: pulseRed 2s infinite; }

    @keyframes pulseRed { 0% { opacity: 1; } 50% { opacity: 0.7; } 100% { opacity: 1; } }

    /* --- FILTRES & BARRE DE RECHERCHE --- */
    .search-zone {
        background: white;
        padding: 20px;
        border-radius: 20px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        margin-bottom: 30px;
        border: 1px solid #e2e8f0;
    }

    .custom-input {
        background: #f1f5f9 !important;
        border: 2px solid transparent !important;
        padding: 12px 20px !important;
        border-radius: 12px !important;
        font-weight: 600;
        transition: all 0.3s;
    }

    .custom-input:focus {
        background: white !important;
        border-color: var(--primary-red) !important;
        box-shadow: 0 0 0 4px rgba(225, 29, 72, 0.1) !important;
    }

    /* --- ANIMATIONS --- */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* --- MODE LISTE (COMPACT) --- */
    .view-list .article-grid { grid-template-columns: 1fr; }
    .view-list .article-card { flex-direction: row; height: 100px; }
    .view-list .img-wrapper { width: 120px; height: 100px; padding: 10px; }
    .view-list .price-container { margin: 10px; padding: 10px; min-width: 180px; }
    .view-list .article-card h6 { margin: 0; padding-top: 25px; }
</style>

<div class="catalog-container">
    <!-- 1. HEADER ET ACTIONS -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-extrabold text-dark m-0" style="font-size: 2rem; letter-spacing: -1.5px;">
                CATALOGUE <span class="text-danger">ATIC</span>
            </h1>
            <p class="text-muted fw-500">Gestion de <?= count($articles) ?> références informatiques & biotiques</p>
        </div>
        <div class="d-flex gap-3">
            <div class="bg-white p-1 rounded-pill shadow-sm border">
                <button class="btn btn-sm rounded-pill px-3 active-view" id="gridBtn" onclick="changeView('grid')">
                    <i class="fas fa-th-large"></i>
                </button>
                <button class="btn btn-sm rounded-pill px-3" id="listBtn" onclick="changeView('list')">
                    <i class="fas fa-list"></i>
                </button>
            </div>
            <a href="<?= $base_url ?>/catalog/create" class="btn btn-danger fw-bold px-4 py-2 rounded-pill shadow-lg">
                <i class="fas fa-plus-circle me-2"></i> AJOUTER UN ARTICLE
            </a>
        </div>
    </div>

    <!-- 2. KPI ANALYTICS (SYNCHRONISÉ) -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="hub-card bg-dark text-white p-4">
                <small class="text-danger fw-bold text-uppercase" style="font-size:0.6rem;">Valeur Stock Achat</small>
                <h3 class="fw-bold m-0 mt-1">
                    <?php 
                        $totalAchat = array_sum(array_map(fn($a) => (float)$a['prix_achat'] * (int)$a['stock_actuel'], $articles));
                        echo number_format($totalAchat, 0, '.', ' ');
                    ?> <small class="fs-6 opacity-50">F</small>
                </h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="hub-card p-4 border-start border-5 border-danger">
                <small class="text-muted fw-bold text-uppercase" style="font-size:0.6rem;">Ruptures Critiques</small>
                <h3 class="fw-bold m-0 text-danger">
                    <?= count(array_filter($articles, fn($a) => (int)$a['stock_actuel'] <= (int)$a['stock_alerte'])) ?>
                </h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="hub-card p-4 border-start border-5 border-success">
                <small class="text-muted fw-bold text-uppercase" style="font-size:0.6rem;">Bénéfice Prévisionnel</small>
                <h3 class="fw-bold m-0 text-success">+ 30.00%</h3>
            </div>
        </div>
    </div>

    <!-- 3. MOTEUR DE FILTRAGE (INTELLIGENT) -->
    <div class="search-zone">
        <div class="row g-3">
            <div class="col-md-7">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="smartSearch" class="form-control custom-input" 
                           placeholder="Recherche instantanée (tapez une lettre, une marque, ou une référence)..." 
                           onkeyup="runSmartFilter()">
                </div>
            </div>
            <div class="col-md-3">
                <select id="typeFilter" class="form-select custom-input fw-bold" onchange="runSmartFilter()">
                    <option value="ALL">TOUTES LES FAMILLES</option>
                    <option value="INFO">INFORMATIQUE</option>
                    <option value="BIOTIQUE">BIOTIQUE</option>
                    <option value="LOGICIEL">LOGICIELS</option>
                    <option value="SERVICE">SERVICES</option>
                </select>
            </div>
            <div class="col-md-2 text-end">
                <button class="btn btn-link text-muted fw-bold text-decoration-none pt-2" onclick="clearAllFilters()">
                    <i class="fas fa-sync-alt me-1"></i> RESET
                </button>
            </div>
        </div>
    </div>

    <!-- 4. LA GRILLE DE PRODUITS -->
    <div class="article-grid" id="mainCatalog">
        <?php if(!empty($articles)): foreach($articles as $a): ?>
        <div class="article-wrapper" 
             data-name="<?= strtolower($a['designation']) ?>" 
             data-ref="<?= strtolower($a['reference_atic']) ?>"
             data-type="<?= $a['type_article'] ?>">
            
            <div class="article-card shadow-pro">
                <!-- Stock Badge -->
                <?php $isLow = ($a['stock_actuel'] <= $a['stock_alerte']); ?>
                <div class="stock-tag <?= $isLow ? 'stock-low' : 'stock-ok' ?>">
                    <?= $isLow ? '<i class="fas fa-exclamation-circle"></i> ALERTE : ' : '<i class="fas fa-check"></i> EN STOCK : ' ?> 
                    <?= $a['stock_actuel'] ?>
                </div>

                <!-- Image -->
                <div class="img-wrapper">
                    <img src="<?= htmlspecialchars(Formatter::articlePhotoUrl($base_url, $a['photo'] ?? null)) ?>" 
                         onerror="this.onerror=null;this.src='<?= htmlspecialchars($base_url) ?>/assets/img/static/atic_default.png'">
                    <div class="type-badge"><?= $a['type_article'] ?></div>
                </div>

                <!-- Content -->
                <div class="p-3 flex-grow-1 d-flex flex-column">
                    <code class="text-danger fw-bold small mb-1"><?= $a['reference_atic'] ?></code>
                    <h6 class="fw-bold text-dark mb-3" style="line-height: 1.2; height: 2.4rem; overflow: hidden;">
                        <?= htmlspecialchars($a['designation']) ?>
                    </h6>

                    <!-- Price Box (Logic Sage 100) -->
                    <div class="price-container mt-auto">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="price-label d-block">Prix Vente TTC</span>
                                <span class="price-value"><?= number_format($a['prix_vente_revient'], 0, '.', ' ') ?> F</span>
                            </div>
                            <div class="text-end">
                                <span class="price-label d-block">Marge</span>
                                <span class="margin-value">+ <?= number_format($a['benefice_unitaire'], 0, '.', ' ') ?> F</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid mt-2">
                        <a href="<?= $base_url ?>/catalog/show/<?= $a['id'] ?>" class="btn btn-sm btn-outline-dark fw-bold border-2 rounded-pill">
                            <i class="fas fa-file-alt me-1"></i> FICHE TECHNIQUE
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; else: ?>
        <!-- Empty State -->
        <div class="col-12 text-center py-5">
            <i class="fas fa-box-open fa-5x text-light mb-3"></i>
            <h4 class="text-muted">Votre catalogue ATIC est vide</h4>
            <a href="<?= $base_url ?>/catalog/create" class="btn btn-danger mt-3">Ajouter le premier article</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- 5. LOGIQUE JAVASCRIPT ÉLITE (PERFORMANCE & FLUIDITÉ) -->
<script>
/**
 * Moteur de filtrage instantané (0ms de latence)
 */
function runSmartFilter() {
    const query = document.getElementById('smartSearch').value.toLowerCase();
    const type = document.getElementById('typeFilter').value;
    const items = document.querySelectorAll('.article-wrapper');

    items.forEach(item => {
        const name = item.dataset.name;
        const ref = item.dataset.ref;
        const itemType = item.dataset.type;

        const matchesSearch = name.includes(query) || ref.includes(query);
        const matchesType = (type === 'ALL' || itemType === type);

        if (matchesSearch && matchesType) {
            item.style.display = 'block';
            item.classList.add('animate-up');
        } else {
            item.style.display = 'none';
        }
    });
}

function clearAllFilters() {
    document.getElementById('smartSearch').value = '';
    document.getElementById('typeFilter').value = 'ALL';
    runSmartFilter();
}

/**
 * Switch Grid/List View
 */
function changeView(mode) {
    const container = document.getElementById('mainCatalog');
    const gridBtn = document.getElementById('gridBtn');
    const listBtn = document.getElementById('listBtn');

    if (mode === 'list') {
        document.querySelector('.catalog-container').classList.add('view-list');
        listBtn.classList.add('btn-danger', 'text-white');
        gridBtn.classList.remove('btn-danger', 'text-white');
    } else {
        document.querySelector('.catalog-container').classList.remove('view-list');
        gridBtn.classList.add('btn-danger', 'text-white');
        listBtn.classList.remove('btn-danger', 'text-white');
    }
}
</script>

