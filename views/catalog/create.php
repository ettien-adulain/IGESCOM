<?php 
/**
 * WINTECH ERP V2.5 - CATALOGUE ATIC : CRÉATION AVANCÉE
 * Concepteur : Expert PHP Natif
 * Standards : Sage 100 Commercial / SYSCOHADA
 */
// Génération d'une pré-référence visuelle pour l'UX
$userId = $_SESSION['user_id'] ?? 0;
$preRef = "REF-X-" . date('Ymd-Hi') . "-" . $userId;
?>

<div class="p-4 animate-up">
    <!-- BARRE DE TITRE COMPACTE -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold text-dark m-0">
                <i class="fas fa-plus-circle text-danger me-2"></i> ENREGISTREMENT NOUVEL ARTICLE (ATIC)
            </h4>
            <p class="text-muted small m-0">Marge commerciale fixe : <span class="badge bg-danger">30%</span> | Référentiel Sage 100</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $base_url ?>/catalog" class="btn btn-outline-dark btn-sm fw-bold px-4 rounded-3 shadow-sm">
                <i class="fas fa-list me-1"></i> VOIR CATALOGUE
            </a>
            <button type="button" class="btn btn-danger btn-sm fw-bold px-4 rounded-3 shadow" onclick="document.getElementById('formATIC').submit();">
                <i class="fas fa-save me-1"></i> VALIDER L'ARTICLE
            </button>
        </div>
    </div>

    <form id="formATIC" action="<?= $base_url ?>/catalog/save" method="POST" enctype="multipart/form-data">
        <div class="row g-3">
            
            <!-- COLONNE GAUCHE : FICHE TECHNIQUE ET IDENTITÉ (RÉDUCTION DES ESPACES) -->
            <div class="col-lg-8">
                <div class="hub-card bg-white border-0 shadow-sm p-4 h-100" style="border-radius: 20px;">
                    <h6 class="fw-bold text-dark text-uppercase small mb-4 border-bottom pb-2">
                        <i class="fas fa-info-circle me-2 text-danger"></i> Informations Générales
                    </h6>
                    
                    <div class="row g-3">
                        <!-- Désignation -->
                        <div class="col-md-12">
                            <label class="form-label-custom">DÉSIGNATION DE L'ARTICLE *</label>
                            <input type="text" name="designation" id="atic_name" class="form-control-pro" 
                                   placeholder="Ex: PC PORTABLE HP PROBOOK 450 G9..." required oninput="updateLiveRef()">
                        </div>

                        <!-- Catégorie & Type -->
                        <div class="col-md-6">
                            <label class="form-label-custom">FAMILLE / CATÉGORIE</label>
                            <select name="type_article" class="form-select-pro">
                                <option value="INFO">ÉQUIPEMENTS INFORMATIQUES</option>
                                <option value="BIOTIQUE">ÉQUIPEMENTS BIOTIQUES</option>
                                <option value="LOGICIEL">LOGICIELS & LICENCES</option>
                                <option value="SERVICE">PRESTATION DE SERVICES</option>
                            </select>
                        </div>

                        <!-- Stock Initial (DEMANDÉ) -->
                        <div class="col-md-6">
                            <label class="form-label-custom">QUANTITÉ INITIALE ACHETÉE (STOCK)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-warehouse text-muted"></i></span>
                                <input type="number" name="stock_initial" class="form-control-pro" value="0" min="0">
                            </div>
                        </div>

                        <!-- Fiche Technique -->
                        <div class="col-md-12">
                            <label class="form-label-custom">CARACTÉRISTIQUES / FICHE TECHNIQUE</label>
                            <textarea name="fiche_technique" class="form-control-pro" rows="5" 
                                      placeholder="Processeur, RAM, Stockage, Écran, Garantie..."></textarea>
                        </div>

                        <!-- Upload Photo -->
                        <div class="col-md-12">
                            <label class="form-label-custom">VISUEL DE L'ARTICLE (PHOTO)</label>
                            <div class="upload-zone rounded-3" onclick="document.getElementById('photo_input').click()">
                                <input type="file" name="photo" id="photo_input" hidden onchange="previewImage(this)">
                                <div id="photo_preview_text">
                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                    <p class="m-0 small text-muted">Cliquer pour charger une image (JPG, PNG)</p>
                                </div>
                                <img id="image_preview" src="#" alt="Preview" class="d-none rounded" style="max-height: 150px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLONNE DROITE : CALCULS ET RENTABILITÉ (SAGE 100 STYLE) -->
            <div class="col-lg-4">
                <div class="hub-card bg-dark text-white border-0 shadow-lg p-4 h-100" style="border-radius: 25px;">
                    <h6 class="fw-bold text-danger text-uppercase small mb-4 border-bottom border-secondary pb-2">
                        <i class="fas fa-calculator me-2"></i> Analyse Financière
                    </h6>

                    <!-- Référence Auto-générée -->
                    <div class="bg-black bg-opacity-25 p-3 rounded-3 mb-4 border border-secondary text-center">
                        <label class="d-block extra-small fw-bold opacity-50 mb-1">RÉFÉRENCE UNIQUE AUTO-GÉNÉRÉE</label>
                        <code class="text-warning fw-bold fs-6" id="live_ref_display"><?= $preRef ?></code>
                    </div>

                    <!-- Prix d'achat -->
                    <div class="mb-4">
                        <label class="form-label-custom text-white-50">COÛT D'ACHAT UNITAIRE HT (F)</label>
                        <input type="number" name="prix_achat" id="p_achat" class="form-control-pro-dark" 
                               placeholder="0" step="0.01" required oninput="calculateMargin()">
                    </div>

                    <!-- Marge fixe (Bloquée à 30%) -->
                    <div class="mb-4">
                        <label class="form-label-custom text-white-50">MARGE COMMERCIALE (SAGE MODEL)</label>
                        <div class="d-flex justify-content-between align-items-center bg-secondary bg-opacity-25 p-3 rounded-3 border border-secondary">
                            <h4 class="m-0 fw-bold text-white">30.00 %</h4>
                            <i class="fas fa-lock text-muted"></i>
                        </div>
                        <small class="text-muted italic mt-1 d-block">La marge est fixe pour ce type d'article.</small>
                    </div>

                    <!-- RESULTATS -->
                    <div class="p-4 rounded-4 shadow-sm" style="background: rgba(225, 29, 72, 0.1); border: 1px solid rgba(225, 29, 72, 0.3);">
                        <div class="mb-3 d-flex justify-content-between">
                            <span class="small opacity-75">Bénéfice Unitaire :</span>
                            <b class="text-success" id="res_benefice">0 F</b>
                        </div>
                        <hr class="border-secondary">
                        <div class="text-center">
                            <label class="extra-small fw-bold text-danger">PRIX DE VENTE DE REVIENT (TTC)</label>
                            <h2 class="fw-bold m-0 text-white amount-mono" id="res_vente">0 FCFA</h2>
                        </div>
                    </div>

                    <div class="mt-5 pt-3">
                        <button type="submit" class="btn btn-danger w-100 py-3 fw-bold fs-5 shadow-lg rounded-3">
                            CONSERVER EN BDD <i class="fas fa-check-double ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- STYLES INTERNES POUR RÉDUIRE LES VIDES ET LES ICONES -->
<style>
    /* Finesse des labels */
    .form-label-custom {
        font-size: 0.7rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
        display: block;
    }

    /* Style des inputs Sage */
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

    /* Zone Upload Compacte */
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
    .italic { font-style: italic; }
</style>

<!-- MOTEUR D'INTELLIGENCE ATIC (JAVASCRIPT) -->
<script>
/**
 * MISE À JOUR DE LA RÉFÉRENCE EN TEMPS RÉEL (Module 2.2)
 */
function updateLiveRef() {
    const desig = document.getElementById('atic_name').value;
    const initial = (desig.length > 0) ? desig.charAt(0).toUpperCase() : 'X';
    const date = new Date();
    const YMD = date.getFullYear() + String(date.getMonth() + 1).padStart(2, '0') + String(date.getDate()).padStart(2, '0');
    const HM = String(date.getHours()).padStart(2, '0') + String(date.getMinutes()).padStart(2, '0');
    const userId = "<?= $userId ?>";

    const finalRef = `REF-${initial}-${YMD}-${HM}-${userId}`;
    document.getElementById('live_ref_display').innerText = finalRef;
}

/**
 * CALCUL DE MARGE SAGE 100 (30% AUTO)
 */
function calculateMargin() {
    const achat = parseFloat(document.getElementById('p_achat').value) || 0;
    const margePct = 30 / 100;
    
    // Calcul
    const benefice = achat * margePct;
    const vente = achat + benefice;

    // Affichage
    document.getElementById('res_benefice').innerText = Math.round(benefice).toLocaleString('fr-FR') + " F";
    document.getElementById('res_vente').innerText = Math.round(vente).toLocaleString('fr-FR') + " FCFA";
}

/**
 * PRÉVISUALISATION DE LA PHOTO
 */
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('image_preview').src = e.target.result;
            document.getElementById('image_preview').classList.remove('d-none');
            document.getElementById('photo_preview_text').classList.add('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

