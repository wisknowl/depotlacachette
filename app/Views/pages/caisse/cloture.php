<div class="page-title">
    <i class='bx bx-calculator'></i>
    <h1><?= $title ?></h1>
</div>

<?php if (!empty($flash_error)): ?>
    <div style="background: #FEE2E2; border-left: 4px solid var(--c-danger); color: #991B1B; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
        <i class='bx bx-error-circle' style="font-size: 1.4rem;"></i>
        <span><?= htmlspecialchars($flash_error) ?></span>
    </div>
<?php endif; ?>

<!-- DATE & ACCOUNT SELECTOR -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body" style="padding: 15px 20px;">
        <form method="GET" action="<?= BASE_URL ?>/caisse/cloture" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 0.85rem; font-weight: 700;">Date de Clôture</label>
                <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($selectedDate) ?>" onchange="this.form.submit()">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 0.85rem; font-weight: 700;">Compte de Caisse</label>
                <select name="account_id" class="form-control" onchange="this.form.submit()">
                    <?php foreach ($accounts as $acc): ?>
                        <option value="<?= $acc['id'] ?>" <?= ($selectedAccountId == $acc['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($acc['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="background-color: var(--c-navy); padding: 7px 15px;">
                <i class='bx bx-refresh'></i> Actualiser
            </button>
            <a href="<?= BASE_URL ?>/caisse" class="btn btn-secondary" style="padding: 7px 15px;">
                <i class='bx bx-arrow-back'></i> Retour à la Caisse
            </a>
        </form>
    </div>
</div>

<form action="<?= BASE_URL ?>/caisse/saveCloture" method="POST" id="cloture_form">
    <input type="hidden" name="closing_date" value="<?= htmlspecialchars($selectedDate) ?>">
    <input type="hidden" name="cash_account_id" value="<?= $selectedAccountId ?>">
    <input type="hidden" name="opening_balance" value="<?= $summary['opening_balance'] ?>">
    <input type="hidden" name="total_in" value="<?= $summary['total_in'] ?>">
    <input type="hidden" name="total_out" value="<?= $summary['total_out'] ?>">
    <input type="hidden" name="theoretical_balance" id="input_theoretical_balance" value="<?= $summary['theoretical_balance'] ?>">
    <input type="hidden" name="physical_cash" id="input_physical_cash" value="0">

    <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr; gap: 20px; align-items: start;">
        
        <!-- LEFT: THEORETICAL CASH REGISTER SUMMARY -->
        <div class="card">
            <div class="card-header" style="background: var(--c-navy); color: white;">
                <span><i class='bx bx-data'></i> 1. Solde Théorique du Système (<?= date('d/m/Y', strtotime($selectedDate)) ?>)</span>
            </div>
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #E2E8F0;">
                    <span style="color: #64748B;">Solde d'Ouverture (Report Veille) :</span>
                    <strong style="color: var(--c-navy); font-size: 1rem;"><?= number_format($summary['opening_balance'], 0, ',', ' ') ?> FCFA</strong>
                </div>

                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #E2E8F0;">
                    <span style="color: var(--c-success); font-weight: 600;"><i class='bx bx-plus'></i> Total Encaissements du Jour :</span>
                    <strong style="color: var(--c-success); font-size: 1.05rem;">+<?= number_format($summary['total_in'], 0, ',', ' ') ?> FCFA</strong>
                </div>

                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #E2E8F0;">
                    <span style="color: var(--c-danger); font-weight: 600;"><i class='bx bx-minus'></i> Total Décaissements du Jour :</span>
                    <strong style="color: var(--c-danger); font-size: 1.05rem;">-<?= number_format($summary['total_out'], 0, ',', ' ') ?> FCFA</strong>
                </div>

                <!-- BREAKDOWN OF DAY -->
                <?php if (!empty($summary['breakdown'])): ?>
                <div style="margin-top: 15px; background: #F8FAFC; border-radius: 8px; padding: 12px; font-size: 0.82rem;">
                    <div style="font-weight: 700; color: #475569; margin-bottom: 6px; text-transform: uppercase;">Détail des Opérations du Jour :</div>
                    <?php foreach ($summary['breakdown'] as $b): ?>
                        <div style="display: flex; justify-content: space-between; padding: 3px 0;">
                            <span><?= htmlspecialchars($b['transaction_type']) ?> (<?= $b['count_tx'] ?>) :</span>
                            <strong>
                                <?php if ($b['sum_in'] > 0): ?>
                                    <span style="color: var(--c-success);">+<?= number_format($b['sum_in'], 0, ',', ' ') ?> FCFA</span>
                                <?php endif; ?>
                                <?php if ($b['sum_out'] > 0): ?>
                                    <span style="color: var(--c-danger);">-<?= number_format($b['sum_out'], 0, ',', ' ') ?> FCFA</span>
                                <?php endif; ?>
                            </strong>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- THEORETICAL TOTAL BOX -->
                <div style="margin-top: 20px; background: #EFF6FF; border: 2px solid #3B82F6; border-radius: 10px; padding: 16px; text-align: center;">
                    <div style="font-size: 0.85rem; font-weight: 800; color: #1E40AF; text-transform: uppercase;">
                        Solde Théorique Attendu en Caisse :
                    </div>
                    <div style="font-size: 1.8rem; font-weight: 900; color: #1D4ED8; margin-top: 4px;">
                        <?= number_format($summary['theoretical_balance'], 0, ',', ' ') ?> FCFA
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: BILLETAGE & PHYSICAL COUNT -->
        <div class="card">
            <div class="card-header" style="background: #059669; color: white;">
                <span><i class='bx bx-money'></i> 2. Billetage & Comptage Physique des Espèces</span>
            </div>
            <div class="card-body">
                <table class="table" style="font-size: 0.88rem;">
                    <thead>
                        <tr style="background: #F8FAFC;">
                            <th>Coupure</th>
                            <th style="width: 110px; text-align: center;">Nombre</th>
                            <th style="text-align: right;">Sous-Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Billets 10 000 FCFA</strong></td>
                            <td><input type="number" min="0" step="1" name="b10000" id="b10000" class="form-control" style="text-align: center; padding: 4px;" value="0" oninput="calculateBilletage()"></td>
                            <td style="text-align: right; font-weight: 700;" id="sub_10000">0 FCFA</td>
                        </tr>
                        <tr>
                            <td><strong>Billets 5 000 FCFA</strong></td>
                            <td><input type="number" min="0" step="1" name="b5000" id="b5000" class="form-control" style="text-align: center; padding: 4px;" value="0" oninput="calculateBilletage()"></td>
                            <td style="text-align: right; font-weight: 700;" id="sub_5000">0 FCFA</td>
                        </tr>
                        <tr>
                            <td><strong>Billets 2 000 FCFA</strong></td>
                            <td><input type="number" min="0" step="1" name="b2000" id="b2000" class="form-control" style="text-align: center; padding: 4px;" value="0" oninput="calculateBilletage()"></td>
                            <td style="text-align: right; font-weight: 700;" id="sub_2000">0 FCFA</td>
                        </tr>
                        <tr>
                            <td><strong>Billets 1 000 FCFA</strong></td>
                            <td><input type="number" min="0" step="1" name="b1000" id="b1000" class="form-control" style="text-align: center; padding: 4px;" value="0" oninput="calculateBilletage()"></td>
                            <td style="text-align: right; font-weight: 700;" id="sub_1000">0 FCFA</td>
                        </tr>
                        <tr>
                            <td><strong>Billets 500 FCFA</strong></td>
                            <td><input type="number" min="0" step="1" name="b500" id="b500" class="form-control" style="text-align: center; padding: 4px;" value="0" oninput="calculateBilletage()"></td>
                            <td style="text-align: right; font-weight: 700;" id="sub_500">0 FCFA</td>
                        </tr>
                        <tr>
                            <td><strong>Pièces 500 FCFA</strong></td>
                            <td><input type="number" min="0" step="1" name="c500" id="c500" class="form-control" style="text-align: center; padding: 4px;" value="0" oninput="calculateBilletage()"></td>
                            <td style="text-align: right; font-weight: 700;" id="sub_c500">0 FCFA</td>
                        </tr>
                        <tr>
                            <td><strong>Pièces 100 FCFA</strong></td>
                            <td><input type="number" min="0" step="1" name="c100" id="c100" class="form-control" style="text-align: center; padding: 4px;" value="0" oninput="calculateBilletage()"></td>
                            <td style="text-align: right; font-weight: 700;" id="sub_c100">0 FCFA</td>
                        </tr>
                        <tr>
                            <td><strong>Pièces 50 FCFA</strong></td>
                            <td><input type="number" min="0" step="1" name="c50" id="c50" class="form-control" style="text-align: center; padding: 4px;" value="0" oninput="calculateBilletage()"></td>
                            <td style="text-align: right; font-weight: 700;" id="sub_c50">0 FCFA</td>
                        </tr>
                        <tr>
                            <td><strong>Pièces 25 FCFA</strong></td>
                            <td><input type="number" min="0" step="1" name="c25" id="c25" class="form-control" style="text-align: center; padding: 4px;" value="0" oninput="calculateBilletage()"></td>
                            <td style="text-align: right; font-weight: 700;" id="sub_c25">0 FCFA</td>
                        </tr>
                    </tbody>
                </table>

                <!-- TOTAL COUNTED HIGHLIGHT -->
                <div style="margin-top: 15px; background: #ECFDF5; border: 2px solid #059669; border-radius: 10px; padding: 14px; text-align: center;">
                    <div style="font-size: 0.85rem; font-weight: 800; color: #065F46; text-transform: uppercase;">
                        Total Espèces Physiques Comptées :
                    </div>
                    <div style="font-size: 1.8rem; font-weight: 900; color: #047857; margin-top: 4px;" id="total_physical_display">
                        0 FCFA
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- COMPARISON & GAP BOX (ÉCART DE CAISSE) -->
    <div class="card" style="margin-top: 20px;" id="ecart_card">
        <div class="card-body" style="padding: 25px; text-align: center;">
            <div style="font-size: 1rem; font-weight: 800; text-transform: uppercase; color: #475569;" id="ecart_title">
                Résultat du Rapprochement de Caisse
            </div>
            
            <div style="font-size: 2.3rem; font-weight: 900; margin: 10px 0;" id="ecart_value_display">
                0 FCFA
            </div>

            <div style="font-size: 0.95rem; font-weight: 600;" id="ecart_status_text">
                Renseignez le nombre de billets ci-dessus pour calculer l'écart.
            </div>

            <div class="form-group" style="max-width: 600px; margin: 20px auto 0 auto; text-align: left;">
                <label class="form-label">Observations / Justification de l'Écart (le cas échéant) :</label>
                <input type="text" name="notes" class="form-control" placeholder="Ex: Caisse vérifiée conforme, 0 écart constaté...">
            </div>

            <div style="margin-top: 25px; display: flex; justify-content: center; gap: 15px;">
                <button type="submit" class="btn btn-accent" style="padding: 12px 30px; font-size: 1.05rem; font-weight: 800;">
                    <i class='bx bx-check-double'></i> Valider & Clôturer la Journée de Caisse
                </button>
                <a href="<?= BASE_URL ?>/caisse" class="btn btn-primary" style="background-color: var(--c-gray-600); padding: 12px 20px;">
                    <i class='bx bx-x'></i> Annuler
                </a>
            </div>
        </div>
    </div>
</form>

<script>
const theoreticalBal = <?= floatval($summary['theoretical_balance']) ?>;

function calculateBilletage() {
    const b10000 = (parseInt(document.getElementById('b10000').value) || 0) * 10000;
    const b5000  = (parseInt(document.getElementById('b5000').value)  || 0) * 5000;
    const b2000  = (parseInt(document.getElementById('b2000').value)  || 0) * 2000;
    const b1000  = (parseInt(document.getElementById('b1000').value)  || 0) * 1000;
    const b500   = (parseInt(document.getElementById('b500').value)   || 0) * 500;
    const c500   = (parseInt(document.getElementById('c500').value)   || 0) * 500;
    const c100   = (parseInt(document.getElementById('c100').value)   || 0) * 100;
    const c50    = (parseInt(document.getElementById('c50').value)    || 0) * 50;
    const c25    = (parseInt(document.getElementById('c25').value)    || 0) * 25;

    document.getElementById('sub_10000').innerText = b10000.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('sub_5000').innerText  = b5000.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('sub_2000').innerText  = b2000.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('sub_1000').innerText  = b1000.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('sub_500').innerText   = b500.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('sub_c500').innerText  = c500.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('sub_c100').innerText  = c100.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('sub_c50').innerText   = c50.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('sub_c25').innerText   = c25.toLocaleString('fr-FR') + ' FCFA';

    const totalPhysical = b10000 + b5000 + b2000 + b1000 + b500 + c500 + c100 + c50 + c25;

    document.getElementById('total_physical_display').innerText = totalPhysical.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('input_physical_cash').value = totalPhysical;

    // Calculate gap
    const diff = totalPhysical - theoreticalBal;
    const card = document.getElementById('ecart_card');
    const valDisplay = document.getElementById('ecart_value_display');
    const statusText = document.getElementById('ecart_status_text');

    if (diff === 0) {
        valDisplay.innerText = "0 FCFA (Écart Nul)";
        valDisplay.style.color = "#047857";
        statusText.innerHTML = "🟢 <strong>Parfait !</strong> Le comptage physique correspond exactement au solde théorique du système.";
        statusText.style.color = "#065F46";
        card.style.borderTop = "4px solid #059669";
    } else if (diff < 0) {
        valDisplay.innerText = "-" + Math.abs(diff).toLocaleString('fr-FR') + " FCFA";
        valDisplay.style.color = "#DC2626";
        statusText.innerHTML = "🔴 <strong>Attention Déficit / Manquant de Caisse :</strong> Il manque <strong>" + Math.abs(diff).toLocaleString('fr-FR') + " FCFA</strong> dans le tiroir-caisse !";
        statusText.style.color = "#991B1B";
        card.style.borderTop = "4px solid #DC2626";
    } else {
        valDisplay.innerText = "+" + diff.toLocaleString('fr-FR') + " FCFA";
        valDisplay.style.color = "#D97706";
        statusText.innerHTML = "🟡 <strong>Excédent de Caisse :</strong> Vous avez <strong>" + diff.toLocaleString('fr-FR') + " FCFA</strong> de plus que prévu par le système.";
        statusText.style.color = "#92400E";
        card.style.borderTop = "4px solid #D97706";
    }
}

document.addEventListener('DOMContentLoaded', function() {
    calculateBilletage();
});
</script>
