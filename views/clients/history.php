<?php
/**
 * WINTECH ERP V2.5 — Historique & dossier tiers
 * @var \App\Models\Client $client
 * @var array $history
 * @var array $stats
 */
use App\Models\Client;

$client = $client instanceof Client ? $client : new Client(is_array($client) ? $client : []);
$history = $history ?? [];
$stats = $stats ?? ['total_ca' => 0.0, 'impayes' => 0.0, 'nb_factures' => 0];

$badgeClass = static function (string $statut): string {
    return match ($statut) {
        'FACTURE_VALIDEE' => 'bg-success',
        'CLIENT_OK' => 'bg-primary',
        'ATTENTE_VAL_CLIENT' => 'bg-warning text-dark',
        default => 'bg-secondary',
    };
};
?>

<div class="container-fluid p-4 animate-up">
    <nav class="small text-muted mb-3">
        <a href="<?= $base_url ?>/clients" class="text-decoration-none text-muted">Clients</a>
        <span class="mx-1">/</span>
        <span class="text-danger fw-bold">Dossier</span>
    </nav>

    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold text-dark m-0">Historique : <span class="text-danger"><?= htmlspecialchars($client->getLabel()) ?></span></h2>
            <p class="text-muted small mb-0">
                Réf. <code class="text-danger"><?= htmlspecialchars($client->id_unique_client) ?></code>
                <?php if (!empty($client->localisation_magasin)): ?>
                    · Zone : <b><?= htmlspecialchars($client->localisation_magasin) ?></b>
                <?php endif; ?>
            </p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="<?= $base_url ?>/clients" class="btn btn-outline-dark btn-sm fw-bold rounded-pill px-3 me-2">
                <i class="fas fa-arrow-left me-1"></i> Retour liste
            </a>
            <button type="button" class="btn btn-dark btn-sm fw-bold rounded-pill px-4" onclick="window.print()">
                <i class="fas fa-print me-2"></i> Imprimer
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="hub-card bg-white p-4 shadow-sm border-0 border-start border-5 border-primary">
                <small class="text-muted fw-bold text-uppercase" style="font-size:0.65rem;">Volume documents (net)</small>
                <h3 class="fw-bold amount-mono m-0 mt-1"><?= number_format((float) $stats['total_ca'], 0, '.', ' ') ?> <small class="fs-6 text-muted">F</small></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="hub-card bg-white p-4 shadow-sm border-0 border-start border-5 border-danger">
                <small class="text-muted fw-bold text-uppercase" style="font-size:0.65rem;">Encours non soldés</small>
                <h3 class="fw-bold amount-mono m-0 mt-1 text-danger"><?= number_format((float) $stats['impayes'], 0, '.', ' ') ?> <small class="fs-6">F</small></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="hub-card bg-dark text-white p-4 shadow-lg border-0">
                <small class="text-white-50 fw-bold text-uppercase" style="font-size:0.65rem;">Pièces enregistrées</small>
                <h3 class="fw-bold m-0 mt-1 text-warning"><?= (int) $stats['nb_factures'] ?></h3>
            </div>
        </div>
    </div>

    <div class="hub-card bg-white shadow-pro border-0 overflow-hidden rounded-4">
        <div class="p-3 bg-light border-bottom">
            <span class="fw-bold small text-uppercase text-muted">Journal des opérations</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead class="bg-dark text-white small">
                    <tr>
                        <th class="ps-4 py-3">Date</th>
                        <th>N° pièce</th>
                        <th>Type / statut</th>
                        <th class="text-end">Montant net</th>
                        <th class="text-center">Statut</th>
                    </tr>
                </thead>
                <tbody class="amount-mono" style="font-size: 0.85rem;">
                    <?php if (!empty($history)): ?>
                        <?php foreach ($history as $doc): ?>
                            <?php
                                $dc = $doc['date_creation'] ?? '';
                                $ts = $dc ? strtotime($dc) : false;
                                $dateAff = $ts ? date('d/m/Y H:i', $ts) : '—';
                                $statut = (string) ($doc['statut'] ?? '');
                                $typeDoc = (string) ($doc['type_doc'] ?? '');
                                $net = (float) ($doc['net_a_payer'] ?? 0);
                            ?>
                            <tr>
                                <td class="ps-4"><?= htmlspecialchars($dateAff) ?></td>
                                <td class="fw-bold"><?= htmlspecialchars((string) ($doc['numero_officiel'] ?? '')) ?></td>
                                <td>
                                    <span class="text-muted small"><?= htmlspecialchars($typeDoc) ?></span>
                                    <?php if ($statut !== ''): ?>
                                        <br><span class="small"><?= htmlspecialchars($statut) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold"><?= number_format($net, 0, '.', ' ') ?> F</td>
                                <td class="text-center">
                                    <span class="badge <?= $badgeClass($statut) ?>"><?= htmlspecialchars($statut ?: '—') ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                                Aucun document lié à ce tiers pour le moment.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .amount-mono { font-family: ui-monospace, monospace; }
    @media print {
        .btn, nav { display: none !important; }
    }
</style>
