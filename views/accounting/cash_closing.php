<div class="p-4 animate-up">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="hub-card bg-white shadow-lg border-0 overflow-hidden" style="border-radius: 25px;">
                <div class="bg-dark p-4 text-white text-center">
                    <h5 class="fw-bold m-0 text-uppercase">Clôture de Caisse Journalière</h5>
                    <small class="text-white-50">Date : <?= date('d F Y') ?></small>
                </div>

                <form action="<?= $base_url ?>/compta/cloture/save" method="POST" class="p-5">
                    <!-- Résumé Automatique -->
                    <div class="row g-3 mb-5">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-4 text-center border-start border-5 border-success">
                                <small class="text-muted fw-bold">ENCAISSEMENTS</small>
                                <div class="fw-bold fs-5"><?= number_format($flux['encaissements'], 0, '.', ' ') ?> F</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-4 text-center border-start border-5 border-danger">
                                <small class="text-muted fw-bold">DÉCAISSEMENTS</small>
                                <div class="fw-bold fs-5"><?= number_format($flux['decaissements'], 0, '.', ' ') ?> F</div>
                            </div>
                        </div>
                    </div>

                    <!-- Saisie du Comptage Physique -->
                    <div class="mb-4 text-center">
                        <label class="fw-bold text-dark mb-2">MONTANT RÉEL EN CAISSE (PHYSIQUE)</label>
                        <input type="number" name="solde_physique" class="form-control form-control-lg text-center fw-bold fs-2 border-danger" 
                               style="background: #fff5f5; border-radius: 15px;" placeholder="0" required>
                        <input type="hidden" name="solde_theorique" value="<?= $flux['solde'] ?>">
                    </div>

                    <div class="alert alert-info border-0 small shadow-sm mb-4">
                        <i class="fas fa-info-circle me-2"></i> 
                        Le solde théorique calculé par le système est de <b><?= number_format($flux['solde'], 0, '.', ' ') ?> FCFA</b>.
                    </div>

                    <button type="submit" class="btn btn-danger w-100 py-3 fw-bold shadow-lg rounded-pill">
                        VALIDER LA CLÔTURE ET VERROUILLER LE JOURNAL
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

