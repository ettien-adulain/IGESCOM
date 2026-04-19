<?php 
// Initialisation des variables de structure
$active = $active ?? 'clients';
$page_title = "Création Fiche Tiers";
?>

<div class="p-4 animate-up">
    <!-- FIL D'ARIANE & TITRE -->
    <nav class="small text-muted mb-2">Gestion / Répertoire Tiers / <span class="text-danger fw-bold">Nouveau Client</span></nav>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark m-0">FICHE SIGNALÉTIQUE TIERS</h2>
        <div class="d-flex gap-2">
            <a href="<?= $base_url ?>/clients" class="btn btn-outline-dark btn-sm fw-bold px-3">
                <i class="fas fa-times me-1"></i> ANNULER
            </a>
            <button type="button" onclick="submitClientForm()" class="btn btn-danger btn-sm fw-bold px-4 shadow rounded-pill">
                <i class="fas fa-check-double me-1"></i> ENREGISTRER LE CLIENT
            </button>
        </div>
    </div>

    <!-- FORMULAIRE PRINCIPAL -->
    <form id="mainClientForm" action="<?= $base_url ?>/clients/save" method="POST" enctype="multipart/form-data">
        <div class="row g-4">
            
            <!-- COLONNE GAUCHE : IDENTITÉ ET LOGO -->
            <div class="col-xl-4 col-lg-5">
                <div class="hub-card bg-white shadow-sm border-0 p-0 overflow-hidden mb-4" style="border-radius: 20px;">
                    <div class="bg-dark p-3 text-white text-center">
                        <h6 class="m-0 fw-bold text-uppercase small">Identité Visuelle</h6>
                    </div>
                    <div class="p-4 text-center">
                        <!-- Preview du Logo -->
                        <div class="mb-3">
                            <img src="<?= $base_url ?>/assets/img/static/default_user.png" id="logoPreview" 
                                 class="img-thumbnail rounded-4 shadow-sm" style="width: 180px; height: 180px; object-fit: cover;">
                        </div>
                        <label class="btn btn-outline-danger btn-sm fw-bold px-4 rounded-pill cursor-pointer">
                            <i class="fas fa-camera me-2"></i> CHARGER LE LOGO
                            <input type="file" name="logo_client" id="logoInput" class="d-none" accept="image/*" onchange="previewImage(this)">
                        </label>
                        <p class="text-muted extra-small mt-2">Format PNG ou JPG, Max 2Mo</p>
                    </div>
                </div>

                <div class="hub-card bg-white shadow-sm border-0 p-4" style="border-radius: 20px;">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 text-uppercase small">Classification Sage 100</h6>
                    
                    <div class="mb-3">
                        <label class="extra-small fw-bold text-muted uppercase">Type de Tiers *</label>
                        <select name="type_client" id="type_client" class="form-select bg-light border-0 py-2" onchange="toggleClientFields()">
                            <option value="PARTICULIER">Particulier (Individuel)</option>
                            <option value="PROFESSIONNEL" selected>Professionnel (Entreprise/Magasin)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="extra-small fw-bold text-muted uppercase">Catégorie Tarifaire</label>
                        <select name="categorie_tarifaire" class="form-select bg-light border-0 py-2">
                            <option value="DETAIL">Tarif Détail (Public)</option>
                            <option value="GROS">Tarif Gros (Revendeur)</option>
                            <option value="SPECIAL">Tarif Spécial (Grands Comptes)</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="extra-small fw-bold text-muted uppercase">Code Client (Génération Auto)</label>
                        <input type="text" name="id_unique_client" value="CLI-<?= date('ymd-His') ?>" class="form-control bg-dark text-warning fw-bold border-0" readonly>
                    </div>
                </div>
            </div>

            <!-- COLONNE DROITE : DATA ET PARAMÈTRES -->
            <div class="col-xl-8 col-lg-7">
                
                <!-- BLOC 1 : INFORMATIONS GÉNÉRALES -->
                <div class="hub-card bg-white shadow-sm border-0 p-4 mb-4" style="border-radius: 20px;">
                    <h6 class="fw-bold text-danger border-bottom border-danger border-opacity-10 pb-2 mb-4 text-uppercase small">
                        <i class="fas fa-info-circle me-2"></i> Informations Générales
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="extra-small fw-bold text-muted uppercase">Raison Sociale / Nom Complet *</label>
                            <input type="text" name="nom_prenom" class="form-control bg-light border-0 py-3 fw-bold fs-5" placeholder="Ex: INDUSTRIE ET PROMOTION DU BOIS" required>
                        </div>
                        
                        <div class="col-md-6 pro-only">
                            <label class="extra-small fw-bold text-muted uppercase">Nom du Magasin / Enseigne</label>
                            <input type="text" name="nom_magasin" class="form-control bg-light border-0" placeholder="Ex: IPB-CI SARL">
                        </div>
                        <div class="col-md-6 pro-only">
                            <label class="extra-small fw-bold text-muted uppercase">Numéro NCC (DGI)</label>
                            <input type="text" name="ncc" class="form-control bg-light border-0" placeholder="Ex: 8204594B">
                        </div>

                        <div class="col-md-6">
                            <label class="extra-small fw-bold text-muted uppercase">Contact Téléphonique *</label>
                            <input type="text" name="telephone" class="form-control bg-light border-0" placeholder="Ex: 07 47 17 61 15" required>
                        </div>
                        <div class="col-md-6">
                            <label class="extra-small fw-bold text-muted uppercase">Adresse E-mail</label>
                            <input type="email" name="email" class="form-control bg-light border-0" placeholder="Ex: achats@inprobois.ci">
                        </div>
                    </div>
                </div>

                <!-- BLOC 2 : LOGISTIQUE ET SOLVABILITÉ -->
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="hub-card bg-white shadow-sm border-0 p-4 h-100" style="border-radius: 20px;">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 text-uppercase small">Logistique & Livraison</h6>
                            <div class="mb-3">
                                <label class="extra-small fw-bold text-muted uppercase">Zone Géographique / Localisation</label>
                                <input type="text" name="localisation_magasin" class="form-control bg-light border-0" placeholder="Ex: San-Pedro, Zone Industrielle">
                            </div>
                            <div class="mb-0">
                                <label class="extra-small fw-bold text-muted uppercase">Adresse Complète (Livraison)</label>
                                <textarea name="adresse_complete" class="form-control bg-light border-0" rows="3" placeholder="Rue, porte, repères précis..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="hub-card bg-white shadow-sm border-0 p-4 h-100" style="border-radius: 20px;">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 text-uppercase small">Gestion des Risques (Sage)</h6>
                            <div class="mb-3">
                                <label class="extra-small fw-bold text-muted uppercase">Plafond de Solvabilité (FCFA)</label>
                                <input type="number" name="solvabilite_max" class="form-control bg-danger bg-opacity-10 border-0 fw-bold text-danger" value="0">
                                <small class="text-muted italic extra-small">Blocage auto si encours > plafond</small>
                            </div>
                            <div class="mb-0">
                                <label class="extra-small fw-bold text-muted uppercase">Conditions de Règlement</label>
                                <select name="condition_reglement" class="form-select bg-light border-0">
                                    <option value="COMPTANT">Paiement Comptant</option>
                                    <option value="30J">30 Jours Fin de mois</option>
                                    <option value="45J">45 Jours Date de facture</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<!-- SCRIPTS D'INTELLIGENCE UI -->
<script>
/**
 * Prévisualisation du logo chargé
 */
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Basculement dynamique Particulier/Pro
 */
function toggleClientFields() {
    const type = document.getElementById('type_client').value;
    const proElements = document.querySelectorAll('.pro-only');
    
    proElements.forEach(el => {
        if (type === 'PARTICULIER') {
            el.style.opacity = '0.3';
            el.querySelectorAll('input').forEach(i => i.disabled = true);
        } else {
            el.style.opacity = '1';
            el.querySelectorAll('input').forEach(i => i.disabled = false);
        }
    });
}

/**
 * Soumission sécurisée
 */
function submitClientForm() {
    const form = document.getElementById('mainClientForm');
    if(form.checkValidity()) {
        form.submit();
    } else {
        alert("Attention : Veuillez remplir tous les champs obligatoires (*)");
        form.reportValidity();
    }
}

// Initialisation au chargement
document.addEventListener('DOMContentLoaded', toggleClientFields);
</script>

<style>
    .form-control:focus, .form-select:focus {
        background-color: #fff !important;
        border: 1px solid var(--primary-red) !important;
        box-shadow: 0 0 0 0.25rem rgba(225, 29, 72, 0.1);
    }
    .extra-small { font-size: 0.65rem; letter-spacing: 0.5px; margin-bottom: 5px; display: block; }
    .uppercase { text-transform: uppercase; }
    .italic { font-style: italic; }
    .cursor-pointer { cursor: pointer; }
    .pro-only { transition: all 0.4s ease; }
</style>

