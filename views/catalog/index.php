<?php
/**
 * WINTECH ERP V2.5 - CATALOGUE ATIC PRO EDITION
 * Architecture : Haute Disponibilité / 300+ Références
 * Intelligence : Moteur de filtrage instantané & Calcul de rentabilité Sage 100
 */

use App\Utils\Formatter;

// Initialisation sécurisée
$articles = $articles ?? [];
$categories = $categories ?? [];
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

    .article-card.article-card--inactive {
        opacity: 0.92;
        border-style: dashed;
        border-color: #cbd5e1;
        background: #fafafa;
    }
    .article-card.article-card--inactive:hover {
        border-color: #94a3b8;
    }
    .badge-hors-caisse {
        font-size: 0.6rem;
        letter-spacing: 0.5px;
    }

    .article-card-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
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
        max-width: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
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

    /* --- MODE LISTE : grille stricte, vignette fixe (les images ne peuvent pas casser la ligne) --- */
    .view-list .article-grid {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .view-list .article-wrapper {
        width: 100%;
        max-width: 100%;
    }
    .view-list .article-card {
        display: grid;
        grid-template-columns: 108px minmax(0, 1fr);
        align-items: start;
        column-gap: 0;
        min-height: 0;
        height: auto;
        overflow: hidden;
    }
    /* Cellule image : dimensions figées, tout débordement masqué */
    .view-list .article-card .img-wrapper {
        grid-column: 1;
        grid-row: 1;
        width: 108px;
        min-width: 108px;
        max-width: 108px;
        height: 96px;
        min-height: 96px;
        max-height: 96px;
        padding: 6px;
        box-sizing: border-box;
        flex-shrink: 0;
        overflow: hidden;
        align-self: start;
        margin-top: 8px;
    }
    .view-list .article-card .img-wrapper img {
        display: block;
        max-width: 100% !important;
        max-height: 100% !important;
        width: auto !important;
        height: auto !important;
        object-fit: contain;
        object-position: center;
        transform: none !important;
    }
    .view-list .article-card:hover .img-wrapper img {
        transform: none !important;
    }
    .view-list .article-card:hover {
        transform: translateY(-2px);
    }
    .view-list .article-card-inner {
        grid-column: 2;
        grid-row: 1;
        min-width: 0;
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 10px 14px;
        padding: 10px 14px 10px 10px !important;
    }
    .view-list .article-card-head {
        flex: 1 1 200px;
        min-width: 0;
    }
    .view-list .article-card-head h6 {
        height: auto !important;
        max-height: none !important;
        margin-bottom: 0 !important;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .view-list .article-card-actions {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        flex: 1 1 260px;
        margin-top: 0 !important;
        padding-top: 0 !important;
        width: auto;
        max-width: 100%;
    }
    .view-list .price-container {
        margin: 0 !important;
        padding: 10px 12px !important;
        flex: 1 1 160px;
        min-width: 140px;
        max-width: 280px;
    }
    .view-list .article-card-actions .d-grid {
        flex: 0 0 auto;
        width: auto;
        min-width: 118px;
    }
    .view-list .stock-tag {
        top: 8px;
        left: 8px;
        font-size: 0.55rem;
        padding: 3px 8px;
        max-width: calc(100% - 16px);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .view-list .type-badge {
        font-size: 0.55rem;
        padding: 2px 8px;
        bottom: 6px;
        right: 6px;
    }
    @media (max-width: 575px) {
        .view-list .article-card {
            grid-template-columns: 88px minmax(0, 1fr);
        }
        .view-list .article-card .img-wrapper {
            width: 88px;
            min-width: 88px;
            max-width: 88px;
            height: 88px;
            min-height: 88px;
            max-height: 88px;
        }
        .view-list .article-card-inner {
            flex-direction: column;
            align-items: stretch;
        }
        .view-list .article-card-actions {
            flex-direction: column;
            align-items: stretch;
        }
        .view-list .price-container {
            max-width: none;
        }
    }
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
                <button type="button" class="btn btn-sm rounded-pill px-3 btn-danger text-white" id="gridBtn" onclick="changeView('grid')" title="Vue cartes">
                    <i class="fas fa-th-large"></i>
                </button>
                <button type="button" class="btn btn-sm rounded-pill px-3" id="listBtn" onclick="changeView('list')" title="Vue liste">
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
        <div class="row g-3 align-items-end">
            <div class="col-xl-4 col-lg-12">
                <label class="small text-muted fw-bold text-uppercase mb-1 d-block" style="font-size:0.65rem;">Recherche</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="smartSearch" class="form-control custom-input" 
                           placeholder="Référence, désignation, nom de catégorie…" 
                           onkeyup="runSmartFilter()">
                </div>
            </div>
            <div class="col-xl-2 col-md-6">
                <label class="small text-muted fw-bold text-uppercase mb-1 d-block" style="font-size:0.65rem;">Catégorie (famille)</label>
                <select id="categoryFilter" class="form-select custom-input fw-bold" onchange="runSmartFilter()">
                    <option value="ALL">Toutes les catégories</option>
                    <option value="0">Non classé</option>
                    <?php foreach ($categories as $cat):
                        $cid = (int)($cat['id'] ?? 0);
                        if ($cid === 0) {
                            continue;
                        }
                        ?>
                        <option value="<?= $cid ?>"><?= htmlspecialchars($cat['libelle'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xl-2 col-md-6">
                <label class="small text-muted fw-bold text-uppercase mb-1 d-block" style="font-size:0.65rem;">Type métier</label>
                <select id="typeFilter" class="form-select custom-input" onchange="runSmartFilter()">
                    <option value="ALL">Tous les types</option>
                    <option value="INFO">Informatique</option>
                    <option value="BIOTIQUE">Biotique</option>
                    <option value="LOGICIEL">Logiciels</option>
                    <option value="SERVICE">Services</option>
                </select>
            </div>
            <div class="col-xl-2 col-md-6">
                <label class="small text-muted fw-bold text-uppercase mb-1 d-block" style="font-size:0.65rem;">Visibilité caisse</label>
                <select id="visibilityFilter" class="form-select custom-input fw-bold" onchange="runSmartFilter()" title="Les articles « hors caisse » restent visibles ici pour les gérer ; ils n’apparaissent pas à la caisse.">
                    <option value="ALL">Tous les articles (gestion)</option>
                    <option value="CAISSE">Actifs à la caisse</option>
                    <option value="HORS">Hors caisse (désactivés)</option>
                </select>
            </div>
            <div class="col-xl-2 col-md-12 text-xl-end">
                <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" onclick="clearAllFilters()">
                    <i class="fas fa-sync-alt me-1"></i> Réinitialiser
                </button>
            </div>
        </div>
    </div>

    <!-- 4. LA GRILLE DE PRODUITS -->
    <div class="article-grid" id="mainCatalog">
        <?php if(!empty($articles)): foreach($articles as $a): ?>
        <?php
            $actifVal = isset($a['actif']) ? (int) $a['actif'] : 1;
            $isInactive = ($actifVal === 0);
            $catId = isset($a['id_categorie']) && $a['id_categorie'] !== null && $a['id_categorie'] !== '' ? (int) $a['id_categorie'] : 0;
            $catLabelSearch = strtolower((string) ($a['categorie_nom'] ?? ''));
        ?>
        <div class="article-wrapper" 
             data-name="<?= htmlspecialchars(strtolower($a['designation'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" 
             data-ref="<?= htmlspecialchars(strtolower($a['reference_atic'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
             data-categorie="<?= $catId ?>"
             data-catlabel="<?= htmlspecialchars($catLabelSearch, ENT_QUOTES, 'UTF-8') ?>"
             data-type="<?= htmlspecialchars($a['type_article'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
             data-actif="<?= $actifVal ?>">
            
            <div class="article-card shadow-pro <?= $isInactive ? 'article-card--inactive' : '' ?>">
                <!-- Stock Badge -->
                <?php $isLow = ($a['stock_actuel'] <= $a['stock_alerte']); ?>
                <div class="stock-tag <?= $isLow ? 'stock-low' : 'stock-ok' ?>">
                    <?= $isLow ? '<i class="fas fa-exclamation-circle"></i> ALERTE : ' : '<i class="fas fa-check"></i> EN STOCK : ' ?> 
                    <?= $a['stock_actuel'] ?>
                </div>

                <!-- Image -->
                <div class="img-wrapper">
                    <img src="<?= htmlspecialchars(Formatter::articlePhotoUrl($base_url, $a['photo'] ?? null)) ?>" 
                         alt=""
                         onerror="this.onerror=null;this.src='<?= htmlspecialchars($base_url) ?>/assets/img/static/atic_default.png'">
                    <div class="type-badge"><?= htmlspecialchars($a['type_article']) ?></div>
                </div>

                <!-- Content -->
                <div class="article-card-inner p-3 flex-grow-1 d-flex flex-column">
                    <div class="article-card-head">
                        <?php if ($isInactive): ?>
                            <span class="badge bg-secondary badge-hors-caisse mb-2"><i class="fas fa-ban me-1"></i> Hors caisse</span>
                        <?php endif; ?>
                        <code class="text-danger fw-bold small mb-1 d-block"><?= htmlspecialchars($a['reference_atic']) ?></code>
                        <h6 class="fw-bold text-dark mb-0" style="line-height: 1.25; height: 2.5rem; overflow: hidden;">
                            <?= htmlspecialchars($a['designation']) ?>
                        </h6>
                    </div>

                    <div class="article-card-actions mt-auto pt-2 w-100">
                        <!-- Price Box (Logic Sage 100) -->
                        <div class="price-container">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="price-label d-block">Prix vente</span>
                                    <span class="price-value"><?= number_format($a['prix_vente_revient'], 0, '.', ' ') ?> F</span>
                                </div>
                                <div class="text-end">
                                    <span class="price-label d-block">Marge</span>
                                    <span class="margin-value">+ <?= number_format($a['benefice_unitaire'], 0, '.', ' ') ?> F</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <a href="<?= $base_url ?>/catalog/show/<?= (int)$a['id'] ?>" class="btn btn-sm btn-outline-dark fw-bold border-2 rounded-pill">
                                <i class="fas fa-file-alt me-1"></i> Fiche
                            </a>
                        </div>
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
    const cat = document.getElementById('categoryFilter').value;
    const type = document.getElementById('typeFilter').value;
    const vis = document.getElementById('visibilityFilter').value;
    const items = document.querySelectorAll('.article-wrapper');

    items.forEach(item => {
        const name = item.dataset.name || '';
        const ref = item.dataset.ref || '';
        const catlabel = item.dataset.catlabel || '';
        const itemCat = item.dataset.categorie !== undefined ? String(item.dataset.categorie) : '0';
        const itemType = item.dataset.type || '';
        const actif = item.dataset.actif !== undefined ? item.dataset.actif : '1';

        const matchesSearch = !query || name.includes(query) || ref.includes(query) || catlabel.includes(query);
        const matchesCategory = (cat === 'ALL' || itemCat === cat);
        const matchesType = (type === 'ALL' || itemType === type);
        let matchesVis = true;
        if (vis === 'CAISSE') {
            matchesVis = (actif === '1');
        } else if (vis === 'HORS') {
            matchesVis = (actif === '0');
        }

        if (matchesSearch && matchesCategory && matchesType && matchesVis) {
            item.style.display = '';
            item.classList.add('animate-up');
        } else {
            item.style.display = 'none';
        }
    });
}

function clearAllFilters() {
    document.getElementById('smartSearch').value = '';
    document.getElementById('categoryFilter').value = 'ALL';
    document.getElementById('typeFilter').value = 'ALL';
    document.getElementById('visibilityFilter').value = 'ALL';
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
        localStorage.setItem('catalogView', 'list');
    } else {
        document.querySelector('.catalog-container').classList.remove('view-list');
        gridBtn.classList.add('btn-danger', 'text-white');
        listBtn.classList.remove('btn-danger', 'text-white');
        localStorage.setItem('catalogView', 'grid');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('catalogView') === 'list') {
        changeView('list');
    } else {
        changeView('grid');
    }
});
</script>

