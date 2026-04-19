<div class="p-4 animate-up">
    <!-- TITRE DE SESSION -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0">ENRÔLEMENT PARTENAIRE</h2>
            <p class="text-muted small">Standard SYSCOHADA - Gestion des tiers</p>
        </div>
        <a href="<?= $base_url ?>/fournisseurs" class="btn btn-outline-dark px-4 py-2 fw-bold shadow-sm">
            <i class="fas fa-times me-2"></i> ANNULER
        </a>
    </div>

    <!-- NAVIGATION PAR ONGLETS (STYLE SAGE 100 - Fidèle à votre image) -->
    <div class="compta-tabs-container mb-0" style="background: transparent; box-shadow: none;">
        <div class="compta-tabs">
            <a href="javascript:void(0)" class="compta-tab active" onclick="switchStep(1, this)">Identification</a>
            <a href="javascript:void(0)" class="compta-tab" onclick="switchStep(2, this)">Coordonnées</a>
            <a href="javascript:void(0)" class="compta-tab" onclick="switchStep(3, this)">Contacts</a>
            <a href="javascript:void(0)" class="compta-tab" onclick="switchStep(4, this)">Offres/Tarifs</a>
            <a href="javascript:void(0)" class="compta-tab" onclick="switchStep(5, this)">Logistique</a>
            <a href="javascript:void(0)" class="compta-tab" onclick="switchStep(6, this)">Banques</a>
        </div>
    </div>

    <!-- CONTENEUR DU FORMULAIRE -->
    <div class="hub-card bg-white shadow-lg border-0 p-5" style="border-radius: 0 20px 20px 20px;">
        <form action="<?= $base_url ?>/fournisseurs/save" method="POST" enctype="multipart/form-data" id="supplierForm">
            
            <!-- ÉTAPE 1 : IDENTIFICATION GÉNÉRALE -->
            <div class="step-content" id="step1">
                <h5 class="text-danger fw-bold mb-4 text-uppercase small letter-spacing-1">Identification Générale</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label-custom">COMPTE TIERS (AUTO-GÉNÉRÉ)</label>
                        <input type="text" name="supplier_account_code" class="form-control-pro amount-mono" value="<?= $next_code ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">INTITULÉ / NOM *</label>
                        <input type="text" name="supplier_name" class="form-control-pro border-danger shadow-sm" placeholder="Nom officiel du fournisseur" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">ABRÉGÉ / LIBELLÉ</label>
                        <input type="text" name="supplier_abbreviation" class="form-control-pro" placeholder="Ex: SISCO">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">CATÉGORIE DE PRODUITS</label>
                        <select name="product_category" class="form-select form-control-pro">
                            <option value="INFORMATIQUE">MATÉRIEL INFORMATIQUE</option>
                            <option value="BUREAUTIQUE">BUREAUTIQUE</option>
                            <option value="SERVICES">SERVICES & MAINTENANCE</option>
                            <option value="AUTRES">AUTRES</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ÉTAPE 2 : COORDONNÉES & MÉDIAS -->
            <div class="step-content d-none" id="step2">
                <h5 class="text-danger fw-bold mb-4 text-uppercase small letter-spacing-1">Coordonnées & Télécommunication</h5>
                <div class="row g-4">
                    <div class="col-md-8">
                        <label class="form-label-custom">ADRESSE COMPLÈTE</label>
                        <textarea name="address" class="form-control-pro" rows="2" placeholder="N°, Rue, Boulevard..."></textarea>
                        <div class="row mt-3">
                            <div class="col-md-6"><input type="text" name="postal_code" class="form-control-pro" placeholder="Code Postal"></div>
                            <div class="col-md-6"><input type="text" name="city" class="form-control-pro" placeholder="Ville"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">LOGO FOURNISSEUR</label>
                        <div class="border border-dashed rounded-4 p-3 text-center bg-light">
                            <input type="file" name="logo_file" class="form-control form-control-sm border-0 bg-transparent">
                            <small class="text-muted d-block mt-2">PNG, JPG ou SVG</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom"><i class="fas fa-phone"></i> TÉLÉPHONE</label>
                        <input type="text" name="phone_number" class="form-control-pro" placeholder="+225 ...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom"><i class="fas fa-envelope"></i> E-MAIL</label>
                        <input type="email" name="email" class="form-control-pro" placeholder="contact@fournisseur.com">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom"><i class="fab fa-whatsapp"></i> WHATSAPP</label>
                        <input type="text" name="whatsapp_number" class="form-control-pro" placeholder="Numéro Pro">
                    </div>
                </div>
            </div>

            <!-- ÉTAPE 6 : BANQUES (Module final du PDF) -->
            <div class="step-content d-none" id="step6">
                <h5 class="text-danger fw-bold mb-4 text-uppercase small letter-spacing-1">Informations Bancaires & Sensibilité</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label-custom">NOM DE LA BANQUE</label>
                        <input type="text" name="bank_name" class="form-control-pro" placeholder="Ex: Société Générale / BOA">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">IBAN / NUMÉRO DE COMPTE</label>
                        <input type="text" name="iban" class="form-control-pro amount-mono" placeholder="CI00 0000 0000...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">CODE SWIFT</label>
                        <input type="text" name="swift_code" class="form-control-pro amount-mono" placeholder="BIC/SWIFT">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">NIVEAU DE SENSIBILITÉ</label>
                        <select name="sensitivity_level" class="form-select form-control-pro">
                            <option value="FAIBLE">FAIBLE (Standard)</option>
                            <option value="MOYENNE">MOYENNE (Contrôle accru)</option>
                            <option value="ÉLEVÉE">ÉLEVÉE (Validation SuperAdmin)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- BARRE D'ACTIONS DU FORMULAIRE -->
            <div class="mt-5 pt-4 border-top d-flex justify-content-between">
                <button type="button" id="btnPrev" class="btn btn-light px-4 fw-bold d-none" onclick="prevStep()">PRÉCÉDENT</button>
                <div class="ms-auto">
                    <button type="button" id="btnNext" class="btn btn-dark px-5 fw-bold shadow" onclick="nextStep()">SUIVANT</button>
                    <button type="submit" id="btnSubmit" class="btn btn-danger px-5 fw-bold shadow d-none">VALIDER L'ENRÔLEMENT FINAL</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
let currentStep = 1;

function switchStep(step, btn) {
    // UI Tabs
    document.querySelectorAll('.compta-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    
    // UI Content
    document.querySelectorAll('.step-content').forEach(s => s.classList.add('d-none'));
    document.getElementById('step' + step).classList.remove('d-none');
    currentStep = step;
    updateButtons();
}

function nextStep() {
    if(currentStep < 6) {
        currentStep++;
        const targetTab = document.querySelectorAll('.compta-tab')[currentStep - 1];
        switchStep(currentStep, targetTab);
    }
}

function prevStep() {
    if(currentStep > 1) {
        currentStep--;
        const targetTab = document.querySelectorAll('.compta-tab')[currentStep - 1];
        switchStep(currentStep, targetTab);
    }
}

function updateButtons() {
    document.getElementById('btnPrev').classList.toggle('d-none', currentStep === 1);
    document.getElementById('btnNext').classList.toggle('d-none', currentStep === 6);
    document.getElementById('btnSubmit').classList.toggle('d-none', currentStep !== 6);
}
</script>

<style>
/* CSS SPECIAL ENRÔLEMENT EXPERT */
.compta-tab { 
    background: #f1f5f9; 
    border: 1px solid #e2e8f0; 
    margin-right: 2px; 
    border-bottom: none;
    transition: 0.2s;
}
.compta-tab.active { 
    background: #ffffff !important; 
    color: var(--primary-red) !important; 
    border-top: 3px solid var(--primary-red);
    box-shadow: 0 -5px 10px rgba(0,0,0,0.05);
}
.form-control-pro { 
    background: #f8fafc; 
    border: 1px solid #e2e8f0; 
    padding: 12px 15px; 
    border-radius: 8px; 
    font-weight: 600;
}
.form-control-pro:focus { 
    background: white; 
    border-color: var(--primary-red); 
    box-shadow: 0 0 0 4px rgba(225, 29, 72, 0.05);
    outline: none;
}
.letter-spacing-1 { letter-spacing: 1px; }
</style>

