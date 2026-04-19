<?php
/**
 * WINTECH ERP V2.5 — Modification article ATIC
 */
use App\Utils\Formatter;

$article = $article ?? [];
$categories = $categories ?? [];
$types = [
    'INFO'     => 'ÉQUIPEMENTS INFORMATIQUES',
    'BIOTIQUE' => 'ÉQUIPEMENTS BIOTIQUES',
    'LOGICIEL' => 'LOGICIELS & LICENCES',
    'SERVICE'  => 'PRESTATION DE SERVICES',
];
$err = $_GET['error'] ?? '';
?>

<div class="p-4 animate-up">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold text-dark m-0">
                <i class="fas fa-edit text-danger me-2"></i> MODIFIER L'ARTICLE (ATIC)
            </h4>
            <p class="text-muted small m-0">Référence figée · Marge fixe <span class="badge bg-danger">30%</span></p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $base_url ?>/catalog/show/<?= (int)$article['id'] ?>" class="btn btn-outline-dark btn-sm fw-bold px-4 rounded-3 shadow-sm">
                <i class="fas fa-eye me-1"></i> FICHE
            </a>
            <a href="<?= $base_url ?>/catalog" class="btn btn-outline-secondary btn-sm fw-bold px-4 rounded-3">
                <i class="fas fa-list me-1"></i> CATALOGUE
            </a>
        </div>
    </div>

    <?php if ($err !== ''): ?>
        <div class="alert alert-danger rounded-3 mb-3"><?= htmlspecialchars(urldecode($err)) ?></div>
    <?php endif; ?>

    <form id="formATICEdit" action="<?= $base_url ?>/catalog/update" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= (int)$article['id'] ?>">

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="hub-card bg-white border-0 shadow-sm p-4 h-100" style="border-radius: 20px;">
                    <h6 class="fw-bold text-dark text-uppercase small mb-4 border-bottom pb-2">
                        <i class="fas fa-info-circle me-2 text-danger"></i> Informations
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label-custom">RÉFÉRENCE (NON MODIFIABLE)</label>
                            <input type="text" class="form-control-pro bg-light" value="<?= htmlspecialchars($article['reference_atic'] ?? '') ?>" readonly>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label-custom">DÉSIGNATION *</label>
                            <input type="text" name="designation" id="atic_name" class="form-control-pro"
                                   value="<?= htmlspecialchars($article['designation'] ?? '') ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-custom">FAMILLE / TYPE</label>
                            <select name="type_article" class="form-select-pro">
                                <?php foreach ($types as $val => $label): ?>
                                    <option value="<?= htmlspecialchars($val) ?>" <?= (($article['type_article'] ?? '') === $val) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-custom">CATÉGORIE COMPTABLE</label>
                            <select name="id_categorie" class="form-select-pro">
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>" <?= ((int)($article['id_categorie'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['libelle'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-custom">SEUIL D'ALERTE STOCK</label>
                            <input type="number" name="stock_alerte" class="form-control-pro" min="0"
                                   value="<?= (int)($article['stock_alerte'] ?? 5) ?>">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label-custom">FICHE TECHNIQUE</label>
                            <textarea name="fiche_technique" class="form-control-pro" rows="5"><?= htmlspecialchars($article['fiche_technique'] ?? '') ?></textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label-custom">PHOTO (laisser vide pour conserver l'actuelle)</label>
                            <div class="d-flex align-items-start gap-3 flex-wrap">
                                <div class="border rounded p-2 bg-light">
                                    <img src="<?= htmlspecialchars(Formatter::articlePhotoUrl($base_url, $article['photo'] ?? null)) ?>"
                                         alt="" style="max-height: 100px; max-width: 140px; object-fit: contain;"
                                         onerror="this.onerror=null;this.src='<?= htmlspecialchars($base_url) ?>/assets/img/static/atic_default.png'">
                                </div>
                                <div class="upload-zone rounded-3 flex-grow-1" onclick="document.getElementById('photo_input').click()">
                                    <input type="file" name="photo" id="photo_input" accept="image/jpeg,image/png,image/webp" hidden onchange="previewImage(this)">
                                    <div id="photo_preview_text">
                                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                        <p class="m-0 small text-muted">Nouvelle image (JPG, PNG, WEBP)</p>
                                    </div>
                                    <img id="image_preview" src="#" alt="Preview" class="d-none rounded" style="max-height: 150px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="hub-card bg-dark text-white border-0 shadow-lg p-4 h-100" style="border-radius: 25px;">
                    <h6 class="fw-bold text-danger text-uppercase small mb-4 border-bottom border-secondary pb-2">
                        <i class="fas fa-calculator me-2"></i> Analyse financière
                    </h6>

                    <div class="mb-4">
                        <label class="form-label-custom text-white-50">COÛT D'ACHAT UNITAIRE HT (F) *</label>
                        <input type="number" name="prix_achat" id="p_achat" class="form-control-pro-dark"
                               step="0.01" required oninput="calculateMargin()"
                               value="<?= htmlspecialchars((string)($article['prix_achat'] ?? '0')) ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label-custom text-white-50">MARGE</label>
                        <div class="d-flex justify-content-between align-items-center bg-secondary bg-opacity-25 p-3 rounded-3 border border-secondary">
                            <h4 class="m-0 fw-bold text-white">30.00 %</h4>
                            <i class="fas fa-lock text-muted"></i>
                        </div>
                    </div>

                    <div class="p-4 rounded-4 shadow-sm" style="background: rgba(225, 29, 72, 0.1); border: 1px solid rgba(225, 29, 72, 0.3);">
                        <div class="mb-3 d-flex justify-content-between">
                            <span class="small opacity-75">Bénéfice unitaire :</span>
                            <b class="text-success" id="res_benefice">0 F</b>
                        </div>
                        <hr class="border-secondary">
                        <div class="text-center">
                            <label class="extra-small fw-bold text-danger">PRIX DE VENTE REVIENT</label>
                            <h2 class="fw-bold m-0 text-white amount-mono" id="res_vente">0 FCFA</h2>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-danger w-100 py-3 fw-bold fs-6 shadow-lg rounded-3">
                            <i class="fas fa-save me-2"></i> ENREGISTRER LES MODIFICATIONS
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .form-label-custom {
        font-size: 0.7rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
        display: block;
    }
    .form-control-pro {
        width: 100%;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px 15px;
        font-weight: 600;
        color: #1e293b;
        transition: 0.3s;
    }
    .form-control-pro:focus {
        border-color: var(--primary-red);
        background-color: white;
        outline: none;
    }
    .form-control-pro-dark {
        width: 100%;
        background-color: #334155;
        border: 1px solid #475569;
        border-radius: 10px;
        padding: 15px;
        color: white;
        font-weight: 800;
        font-size: 1.5rem;
        text-align: right;
    }
    .form-select-pro {
        width: 100%;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px 15px;
        font-weight: 700;
        cursor: pointer;
    }
    .upload-zone {
        border: 2px dashed #cbd5e1;
        background: #f8fafc;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: 0.3s;
    }
    .upload-zone:hover { background: #f1f5f9; border-color: var(--primary-red); }
    .amount-mono { font-family: 'JetBrains Mono', monospace; letter-spacing: -1px; }
    .extra-small { font-size: 0.6rem; }
</style>

<script>
function calculateMargin() {
    const achat = parseFloat(document.getElementById('p_achat').value) || 0;
    const margePct = 30 / 100;
    const benefice = achat * margePct;
    const vente = achat + benefice;
    document.getElementById('res_benefice').innerText = Math.round(benefice).toLocaleString('fr-FR') + " F";
    document.getElementById('res_vente').innerText = Math.round(vente).toLocaleString('fr-FR') + " FCFA";
}
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('image_preview').src = e.target.result;
            document.getElementById('image_preview').classList.remove('d-none');
            document.getElementById('photo_preview_text').classList.add('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
document.addEventListener('DOMContentLoaded', calculateMargin);
</script>
