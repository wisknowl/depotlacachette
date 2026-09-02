<div class="page-title">
    <i class='bx bx-money-withdraw'></i>
    <h1><?= $title ?></h1>
</div>

<?php if (!empty($flash_error)): ?>
    <div style="background: #FEE2E2; border-left: 4px solid var(--c-danger); color: #991B1B; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
        <i class='bx bx-error-circle' style="font-size: 1.4rem;"></i>
        <span><?= htmlspecialchars($flash_error) ?></span>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 800px;">
    <div class="card-header">
        <i class='bx bx-wallet'></i> Décaissement de Paie ou Avance sur Salaire
    </div>
    <div class="card-body">
        <form action="<?= BASE_URL ?>/payroll/save" method="POST" id="payroll_form">
            
            <div class="dashboard-grid">
                <!-- EMPLOYEE -->
                <div class="form-group">
                    <label class="form-label">Bénéficiaire / Employé <span style="color: var(--c-danger);">*</span></label>
                    <select name="employee_id" id="payroll_employee" class="form-control" required onchange="onEmployeeSelected()">
                        <option value="">-- Sélectionner un employé --</option>
                        <?php foreach ($employees as $e): ?>
                            <?php $isSel = (isset($flash_old['employee_id']) && $flash_old['employee_id'] == $e['id']); ?>
                            <option value="<?= $e['id'] ?>" 
                                    data-salary="<?= $e['base_salary'] ?>" 
                                    data-role="<?= htmlspecialchars($e['role']) ?>" 
                                    data-phone="<?= htmlspecialchars($e['phone'] ?? '') ?>"
                                    <?= $isSel ? 'selected' : '' ?>>
                                <?= htmlspecialchars($e['name']) ?> (<?= htmlspecialchars($e['role']) ?>) - Base: <?= number_format($e['base_salary'], 0, ',', ' ') ?> FCFA
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- PAYMENT TYPE -->
                <div class="form-group">
                    <label class="form-label">Type de Versement <span style="color: var(--c-danger);">*</span></label>
                    <select name="payment_type" id="payroll_type" class="form-control" required>
                        <option value="Salary" <?= (isset($flash_old['payment_type']) && $flash_old['payment_type'] === 'Salary') ? 'selected' : '' ?>>
                            💵 Salaire Mensuel Régulier
                        </option>
                        <option value="Advance" <?= (isset($flash_old['payment_type']) && $flash_old['payment_type'] === 'Advance') ? 'selected' : '' ?>>
                            ⏱️ Avance sur Salaire
                        </option>
                        <option value="Bonus" <?= (isset($flash_old['payment_type']) && $flash_old['payment_type'] === 'Bonus') ? 'selected' : '' ?>>
                            🎁 Prime / Gratification
                        </option>
                        <option value="Overtime" <?= (isset($flash_old['payment_type']) && $flash_old['payment_type'] === 'Overtime') ? 'selected' : '' ?>>
                            ⚡ Heures Supplémentaires / Manutention
                        </option>
                    </select>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- DATE -->
                <div class="form-group">
                    <label class="form-label">Date du Paiement <span style="color: var(--c-danger);">*</span></label>
                    <input type="date" name="payment_date" class="form-control" required 
                           value="<?= htmlspecialchars($flash_old['payment_date'] ?? date('Y-m-d')) ?>">
                </div>

                <!-- PERIOD -->
                <div class="form-group">
                    <label class="form-label">Période Concernée <span style="color: var(--c-danger);">*</span></label>
                    <input type="text" name="period" class="form-control" required 
                           placeholder="Ex: Août <?= date('Y') ?>, Quinzaine 1..." 
                           value="<?= htmlspecialchars($flash_old['period'] ?? (strftime('%B %Y') ?: date('F Y'))) ?>">
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- AMOUNT -->
                <div class="form-group">
                    <label class="form-label">Montant à Décaisser (FCFA) <span style="color: var(--c-danger);">*</span></label>
                    <input type="number" step="1" min="1" name="amount" id="payroll_amount" class="form-control" required 
                           placeholder="Ex: 85000" 
                           value="<?= htmlspecialchars($flash_old['amount'] ?? '') ?>" 
                           oninput="checkPayrollCashBalance()">
                </div>

                <!-- CASH ACCOUNT -->
                <div class="form-group">
                    <label class="form-label">Compte de Caisse Décaissé <span style="color: var(--c-danger);">*</span></label>
                    <select name="cash_account_id" id="payroll_cash_account" class="form-control" required onchange="checkPayrollCashBalance()">
                        <option value="">-- Choisir la caisse débitée --</option>
                        <?php foreach ($cash_accounts as $ca): ?>
                            <?php $isAccSel = (isset($flash_old['cash_account_id']) && $flash_old['cash_account_id'] == $ca['id']) || ($ca['id'] == 1); ?>
                            <option value="<?= $ca['id'] ?>" data-balance="<?= floatval($ca['current_balance']) ?>" <?= $isAccSel ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ca['name']) ?> (Solde : <?= number_format($ca['current_balance'], 0, ',', ' ') ?> FCFA)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div id="payroll_cash_warning" style="display: none; padding: 12px 16px; background: #FEE2E2; border-left: 4px solid var(--c-danger); border-radius: 6px; font-size: 0.88rem; color: #991B1B; margin-top: 10px; font-weight: 700;">
            </div>

            <div class="dashboard-grid" style="margin-top: 15px;">
                <div class="form-group">
                    <label class="form-label">Référence Interne / N° Pièce</label>
                    <input type="text" name="reference" class="form-control" placeholder="Automatique (ex: PAY00001)" value="<?= htmlspecialchars($flash_old['reference'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Observations / Détails</label>
                    <input type="text" name="notes" class="form-control" placeholder="Ex: Déduction de 10 000 d'avance le mois prochain..." value="<?= htmlspecialchars($flash_old['notes'] ?? '') ?>">
                </div>
            </div>

            <div style="margin-top: 25px; display: flex; gap: 12px;">
                <button type="submit" class="btn btn-accent" id="submit_payroll_btn" style="padding: 10px 24px; font-size: 1rem; font-weight: 700;">
                    <i class='bx bx-check-circle'></i> Valider le Décaissement Salaire
                </button>
                <a href="<?= BASE_URL ?>/payroll" class="btn btn-primary" style="background-color: var(--c-gray-600); padding: 10px 20px;">
                    <i class='bx bx-x'></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function onEmployeeSelected() {
    const sel = document.getElementById('payroll_employee');
    const opt = sel.options[sel.selectedIndex];
    const amountInput = document.getElementById('payroll_amount');
    const typeSel = document.getElementById('payroll_type');

    if (opt && opt.dataset.salary && (!amountInput.value || amountInput.value == '0')) {
        if (typeSel.value === 'Salary') {
            amountInput.value = parseFloat(opt.dataset.salary);
        }
    }
    checkPayrollCashBalance();
}

function checkPayrollCashBalance() {
    const accSel = document.getElementById('payroll_cash_account');
    const amountInput = document.getElementById('payroll_amount');
    const warnBox = document.getElementById('payroll_cash_warning');
    const btn = document.getElementById('submit_payroll_btn');

    if (!accSel || !amountInput || !warnBox) return;

    const opt = accSel.options[accSel.selectedIndex];
    const available = opt ? parseFloat(opt.dataset.balance || 0) : 0;
    const amount = parseFloat(amountInput.value || 0);

    if (amount > available && available >= 0) {
        warnBox.style.display = 'block';
        warnBox.innerHTML = "<i class='bx bx-error-circle'></i> <strong>Attention Solde Insuffisant :</strong> La caisse sélectionnée ne dispose que de <strong>" + available.toLocaleString('fr-FR') + " FCFA</strong>. Décaissement demandé : <strong>" + amount.toLocaleString('fr-FR') + " FCFA</strong>.";
        btn.disabled = true;
        btn.style.opacity = '0.5';
        btn.style.cursor = 'not-allowed';
    } else {
        warnBox.style.display = 'none';
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    checkPayrollCashBalance();
});
</script>
