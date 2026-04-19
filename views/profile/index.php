<div class="p-4 animate-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark m-0"><i class="fas fa-user-circle me-2 text-danger"></i> PARAMÈTRES DU PROFIL</h4>
    </div>

    <div class="row g-4">
        <!-- Carte de prévisualisation -->
        <div class="col-md-4">
            <div class="hub-card bg-dark text-white text-center p-5 border-0 shadow-lg">
                <div class="position-relative d-inline-block mb-4">
                    <img src="<?= $base_url ?>/uploads/<?= $user['photo_path'] ?>" 
                         class="rounded-circle border border-4 border-danger shadow" 
                         width="150" height="150" style="object-fit: cover;">
                </div>
                <h5 class="fw-bold m-0"><?= $user['nom_complet'] ?></h5>
                <p class="text-danger small fw-bold text-uppercase"><?= $user['role'] ?></p>
                <hr class="border-secondary">
                <p class="small text-muted italic">Matricule : <?= $user['matricule'] ?></p>
            </div>
        </div>

        <!-- Formulaire de modification -->
        <div class="col-md-8">
            <div class="hub-card bg-white p-4 shadow-sm border-0">
                <h6 class="fw-bold mb-4 text-uppercase small text-muted border-bottom pb-2">Informations Personnelles</h6>
                
                <form action="<?= $base_url ?>/profile/update" method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small fw-bold">Nom complet</label>
                            <input type="text" name="nom_complet" class="form-control bg-light border-0" value="<?= $user['nom_complet'] ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold">Adresse Email</label>
                            <input type="email" name="email" class="form-control bg-light border-0" value="<?= $user['email'] ?>" placeholder="exemple@yaocoms.ci">
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold">Contact Téléphonique</label>
                            <input type="text" name="telephone" class="form-control bg-light border-0" value="<?= $user['telephone'] ?>" placeholder="+225 ...">
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold">Année de naissance</label>
                            <input type="number" name="annee_naissance" class="form-control bg-light border-0" value="<?= $user['annee_naissance'] ?>" placeholder="1990">
                        </div>
                        <div class="col-md-12">
                            <label class="small fw-bold">Changer la photo de profil</label>
                            <input type="file" name="photo" class="form-control bg-light border-0">
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-danger px-5 py-2 fw-bold rounded-pill shadow">
                                <i class="fas fa-save me-2"></i> ENREGISTRER LES MODIFICATIONS
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

