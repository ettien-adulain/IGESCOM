<div class="p-4 animate-up">
    <!-- BARRE DE SYNTHÈSE (KPIs) -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="hub-card bg-white border-start border-5 border-success p-3 shadow-sm">
                <small class="text-muted fw-bold">SOLDE TRÉSORERIE GLOBAL</small>
                <h3 class="amount-mono text-dark m-0"><?= number_format($stats['solde_tresor'], 0, '.', ' ') ?> F</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="hub-card bg-dark text-white p-3 shadow-lg">
                <small class="text-danger fw-bold">RÉSULTAT PROVISOIRE</small>
                <h3 class="amount-mono text-white m-0"><?= number_format($stats['resultat_prov'], 0, '.', ' ') ?> F</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div id="balance-alert" class="hub-card bg-danger text-white p-3 shadow-sm d-flex align-items-center justify-content-center">
                <h5 class="fw-bold m-0"><i class="fas fa-exclamation-circle me-2"></i> DÉSÉQUILIBRÉ</h5>
            </div>
        </div>
    </div>

    <!-- FORMULAIRE DE SAISIE -->
    <div class="hub-card bg-white shadow-xl border-0 overflow-hidden" style="border-radius: 20px;">
        <div class="bg-dark p-3 text-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold"><i class="fas fa-pen-nib me-2 text-danger"></i> NOUVELLE ÉCRITURE EN PARTIE DOUBLE</h6>
            <span class="badge bg-secondary">RÉFÉRENTIEL SYSCOHADA</span>
        </div>

        <div class="p-4">
            <!-- Entête de la pièce -->
            <div class="row g-3 mb-4 bg-light p-3 rounded-3">
                <div class="col-md-3">
                    <label class="extra-small-sage">JOURNAL</label>
                    <select id="id_journal" class="form-select-sage">
                        <?php foreach($journaux as $j): ?>
                            <option value="<?= $j['id'] ?>">[<?= $j['code'] ?>] <?= $j['libelle'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="extra-small-sage">DATE OPÉRATION</label>
                    <input type="date" id="date_op" class="form-control-sage" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-2">
                    <label class="extra-small-sage">N° PIÈCE / RÉF.</label>
                    <input type="text" id="ref_piece" class="form-control-sage" placeholder="ex: CHQ-402">
                </div>
                <div class="col-md-4">
                    <label class="extra-small-sage">LIBELLÉ GÉNÉRAL</label>
                    <input type="text" id="libelle_gen" class="form-control-sage" placeholder="Libellé de l'opération...">
                </div>
            </div>

            <!-- Grille de saisie -->
            <table class="table table-finance" id="journalTable">
                <thead>
                    <tr>
                        <th width="20%">COMPTE</th>
                        <th width="40%">LIBELLÉ LIGNE</th>
                        <th width="18%">DÉBIT</th>
                        <th width="18%">CRÉDIT</th>
                        <th width="4%"></th>
                    </tr>
                </thead>
                <tbody id="journalBody">
                    <!-- Lignes injectées par JS -->
                </tbody>
                <tfoot class="bg-dark text-white">
                    <tr class="amount-mono">
                        <td colspan="2" class="text-end py-3">TOTALISATION ÉCRITURE</td>
                        <td id="total-debit" class="text-success text-end pe-3">0</td>
                        <td id="total-credit" class="text-danger text-end pe-3">0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            <div class="mt-4 d-flex justify-content-between">
                <button class="btn btn-outline-dark fw-bold px-4 rounded-pill" onclick="addNewLine()">
                    <i class="fas fa-plus me-2"></i> AJOUTER LIGNE
                </button>
                <button class="btn btn-danger btn-lg px-5 fw-bold shadow rounded-pill" onclick="validateAndSave()">
                    VALIDER ET ENREGISTRER AU GRAND LIVRE <i class="fas fa-check-double ms-2"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let lineCount = 0;
const comptes = <?= json_encode($comptes) ?>;

function addNewLine() {
    lineCount++;
    let options = comptes.map(c => `<option value="${c.numero}">${c.numero} - ${c.libelle}</option>`).join('');
    
    const row = `
        <tr id="row-${lineCount}" class="animate-up">
            <td><select class="form-select-sage c-num border-0 bg-transparent">${options}</select></td>
            <td><input type="text" class="form-control-sage c-lib border-0 bg-transparent" placeholder="Détail..."></td>
            <td><input type="number" class="form-control-sage text-end amount-mono debit-val border-0 bg-transparent" value="0" oninput="recalc()"></td>
            <td><input type="number" class="form-control-sage text-end amount-mono credit-val border-0 bg-transparent" value="0" oninput="recalc()"></td>
            <td class="text-center"><i class="fas fa-times text-muted cursor-pointer" onclick="this.closest('tr').remove();recalc()"></i></td>
        </tr>`;
    document.getElementById('journalBody').insertAdjacentHTML('beforeend', row);
}

function recalc() {
    let d = 0; let c = 0;
    document.querySelectorAll('.debit-val').forEach(i => d += parseFloat(i.value || 0));
    document.querySelectorAll('.credit-val').forEach(i => c += parseFloat(i.value || 0));

    document.getElementById('total-debit').innerText = d.toLocaleString('fr-FR');
    document.getElementById('total-credit').innerText = c.toLocaleString('fr-FR');

    const alertBox = document.getElementById('balance-alert');
    if (d === c && d > 0) {
        alertBox.className = "hub-card bg-success text-white p-3 shadow-sm d-flex align-items-center justify-content-center";
        alertBox.innerHTML = '<h5 class="fw-bold m-0"><i class="fas fa-check-circle me-2"></i> ÉQUILIBRÉ</h5>';
    } else {
        alertBox.className = "hub-card bg-danger text-white p-3 shadow-sm d-flex align-items-center justify-content-center";
        alertBox.innerHTML = '<h5 class="fw-bold m-0"><i class="fas fa-exclamation-circle me-2"></i> DÉSÉQUILIBRÉ</h5>';
    }
}

function validateAndSave() {
    const lines = [];
    document.querySelectorAll('#journalBody tr').forEach(tr => {
        lines.push({
            compte_num: tr.querySelector('.c-num').value,
            debit: tr.querySelector('.debit-val').value,
            credit: tr.querySelector('.credit-val').value
        });
    });

    const payload = {
        header: {
            id_journal: document.getElementById('id_journal').value,
            date_operation: document.getElementById('date_op').value,
            reference_piece: document.getElementById('ref_piece').value,
            libelle_operation: document.getElementById('libelle_gen').value
        },
        lines: lines
    };

    fetch('<?= $base_url ?>/compta/save-entry', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    }).then(r => r.json()).then(res => {
        if(res.status === 'success') window.location.href = '<?= $base_url ?>/compta/analytics?success=1';
        else alert(res.message);
    });
}

window.onload = () => { addNewLine(); addNewLine(); };
</script>

<style>
    .amount-mono { font-family: 'JetBrains Mono', monospace; font-weight: 700; }
    .extra-small-sage { font-size: 0.65rem; font-weight: 800; color: #64748b; margin-bottom: 5px; display: block; }
    .form-control-sage, .form-select-sage { width: 100%; padding: 8px 12px; font-size: 0.9rem; border: 1px solid #e2e8f0; border-radius: 8px; }
    .table-finance thead th { background: #1e293b; color: white; font-size: 0.7rem; padding: 12px; text-transform: uppercase; }
</style>

