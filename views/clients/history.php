<?php
/**
 * WINTECH ERP V2.5 - HISTORIQUE DYNAMIQUE CLIENT
 */
// Données fictives pour la structure
$stats = ['total_ca' => 12850000, 'impayes' => 450000, 'nb_factures' => 24];
?>

<div class="container-fluid p-4 animate-up">
    
    <!-- HEADER INFOS CLIENT -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-800 text-dark m-0">HISTORIQUE : <span class="text-danger"><?= strtoupper($client['nom_prenom']) ?></span></h2>
            <p class="text-muted small">Zone Géographique : <b><?= $client['localisation_magasin'] ?? 'Abidjan / Plateau' ?></b></p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-dark fw-bold rounded-pill px-4" onclick="window.print()"><i class="fas fa-print me-2"></i> IMPRIMER RELEVÉ</button>
        </div>
    </div>

    <!-- WIDGETS ANALYTIQUES -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="hub-card bg-white p-4 shadow-sm border-0 border-start border-5 border-primary">
                <small class="text-muted fw-bold">TOTAL ACHATS HT</small>
                <h3 class="fw-800 amount-mono m-0"><?= number_format($stats['total_ca'], 0, '.', ' ') ?> F</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="hub-card bg-white p-4 shadow-sm border-0 border-start border-5 border-danger">
                <small class="text-muted fw-bold">SOLDE DÛ (IMPAYÉS)</small>
                <h3 class="fw-800 amount-mono m-0 text-danger"><?= number_format($stats['impayes'], 0, '.', ' ') ?> F</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="hub-card bg-dark text-white p-4 shadow-lg border-0">
                <small class="text-white-50 fw-bold">FRÉQUENCE D'ACHAT</small>
                <h3 class="fw-800 m-0 text-warning"><?= $stats['nb_factures'] ?> FACTURES</h3>
            </div>
        </div>
    </div>

    <!-- TABLEAU DYNAMIQUE DES OPÉRATIONS -->
    <div class="hub-card bg-white shadow-pro border-0 overflow-hidden rounded-4">
        <div class="p-3 bg-light border-bottom d-flex justify-content-between">
            <span class="fw-bold small uppercase text-muted">Journal des opérations tiers</span>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-white border">TOUT</button>
                <button class="btn btn-white border text-primary">FACTURES</button>
                <button class="btn btn-white border text-success">RÈGLEMENTS</button>
            </div>
        </div>
        <table class="table table-hover align-middle m-0">
            <thead class="bg-black-matte text-white small">
                <tr>
                    <th class="ps-4 py-3">Date</th>
                    <th>N° Pièce</th>
                    <th>Libellé de l'opération</th>
                    <th class="text-end">Débit (Achat)</th>
                    <th class="text-end">Crédit (Paiement)</th>
                    <th class="text-center">Statut</th>
                </tr>
            </thead>
            <tbody class="amount-mono" style="font-size: 0.85rem;">
                <!-- Exemple de ligne -->
                <tr>
                    <td class="ps-4">12/01/2026</td>
                    <td class="fw-bold">FAC-2026-0045</td>
                    <td>Achat Matériel Informatique (LDF)</td>
                    <td class="text-end fw-bold">1 500 000</td>
                    <td class="text-end">0</td>
                    <td class="text-center"><span class="badge bg-success">LIVRÉ</span></td>
                </tr>
                <tr class="table-light">
                    <td class="ps-4">15/01/2026</td>
                    <td class="fw-bold">REG-2026-0012</td>
                    <td>Règlement Virement SIB</td>
                    <td class="text-end">0</td>
                    <td class="text-end fw-bold text-success">1 500 000</td>
                    <td class="text-center"><span class="badge bg-primary">LETTRÉ</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>