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
        <span><i class='bx bx-edit'></i> Formulaire d'Engagement & Décaissement de Dépense</span>
    </div>
    <div class="card-body">
        <form action="<?= BASE_URL ?>/depenses/save" method="POST" id="expense_form">
            
            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Catégorie de Dépense <span style="color: var(--c-danger);">*</span></label>
                    <select name="category_id" class="form-control" required>
                        <option value="">-- Sélectionner une catégorie --</option>
                        <?php foreach ($categories as $cat): ?>
                            <?php $isCatSel = (isset($flash_old['category_id']) && $flash_old['category_id'] == $cat['id']); ?>
                            <option value="<?= $cat['id'] ?>" <?= $isCatSel ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Date de la Dépense <span style="color: var(--c-danger);">*</span></label>
                    <input type="date" name="expense_date" class="form-control" required value="<?= htmlspecialchars($flash_old['expense_date'] ?? date('Y-m-d')) ?>">
                </div>
            </div>

            <!-- COMPTE SOURCE / CAISSE WITH LIVE BALANCE -->
            <div class="form-group">
                <label class="form-label">Compte de Décaissement (Caisse Débitrice) <span style="color: var(--c-danger);">*</span></label>
                <select name="cash_account_id" id="expense_cash_account" class="form-control" required onchange="validateExpenseBalance()">
                    <option value="">-- Choisir la caisse qui finance la dépense --</option>
                    <?php foreach ($cash_accounts as $acc): ?>
                        <?php 
                            $bal = floatval($acc['current_balance']);
                            $isAccSel = (isset($flash_old['cash_account_id']) && $flash_old['cash_account_id'] == $acc['id']);
                            $isEmpty = ($bal <= 0);
                        ?>
                        <option value="<?= $acc['id'] ?>" 
                                data-balance="<?= $bal ?>"
                                data-method="<?= $acc['payment_method_id'] ?>"
                                <?= $isAccSel ? 'selected' : '' ?>>
                            <?= htmlspecialchars($acc['name']) ?> (<?= htmlspecialchars($acc['payment_method_name']) ?>) &mdash; <?= $isEmpty ? '⛔ SOLDE VIDE (0 FCFA)' : 'Solde dispo : ' . number_format($bal, 0, ',', ' ') . ' FCFA' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="payment_method_id" id="expense_payment_method_id" value="1">
            </div>

            <!-- LIVE ACCOUNT STATUS BOX -->
            <div id="account_status_box" style="padding: 12px 16px; border-radius: 8px; background: #F8FAFC; border-left: 4px solid #CBD5E1; margin-bottom: 20px; transition: all 0.3s ease;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.85rem; font-weight: 700; color: #64748B;">Solde disponible dans ce compte :</span>
                    <span id="account_balance_display" style="font-size: 1.25rem; font-weight: 900; color: var(--c-navy);">-</span>
                </div>
                <div id="expense_overdraft_warning" style="display: none; margin-top: 8px; font-weight: 700; color: var(--c-danger); font-size: 0.88rem;"></div>
            </div>

            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Montant de la Dépense (FCFA) <span style="color: var(--c-danger);">*</span></label>
                    <input type="number" step="1" min="1" name="amount" id="expense_amount" class="form-control" required placeholder="Ex: 15000" value="<?= htmlspecialchars($flash_old['amount'] ?? '') ?>" oninput="validateExpenseBalance()">
                </div>
                <div class="form-group">
                    <label class="form-label">Bénéficiaire / Destinataire</label>
                    <input type="text" name="beneficiary" class="form-control" placeholder="Ex: Eneo, Bailleur, Chauffeur..." value="<?= htmlspecialchars($flash_old['beneficiary'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Description / Motif Détaillé <span style="color: var(--c-danger);">*</span></label>
                <textarea name="description" class="form-control" rows="3" required placeholder="Ex: Facture électricité du mois de février, recharge carburant camion livraison..."><?= htmlspecialchars($flash_old['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Référence Reçu / Pièce Justificative (Optionnel)</label>
                <input type="text" name="reference" class="form-control" placeholder="Ex: REC-ENEO-8924, FACT-TOTAL-001" value="<?= htmlspecialchars($flash_old['reference'] ?? '') ?>">
            </div>

            <div style="margin-top: 25px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-accent" id="submit_expense_btn" style="padding: 10px 22px; font-weight: 700;">
                    <i class='bx bx-check-circle'></i> Valider & Décaisser
                </button>
                <a href="<?= BASE_URL ?>/depenses" class="btn btn-primary" style="background-color: var(--c-gray-600); padding: 10px 18px;">
                    <i class='bx bx-x'></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function validateExpenseBalance() {
    const accSelect = document.getElementById("expense_cash_account");
    const amountInput = document.getElementById("expense_amount");
    const submitBtn = document.getElementById("submit_expense_btn");
    const statusBox = document.getElementById("account_status_box");
    const balDisplay = document.getElementById("account_balance_display");
    const warningDiv = document.getElementById("expense_overdraft_warning");
    const methodInput = document.getElementById("expense_payment_method_id");

    warningDiv.style.display = "none";
    warningDiv.innerText = "";
    submitBtn.disabled = false;

    if (accSelect.selectedIndex <= 0) {
        balDisplay.innerText = "-";
        statusBox.style.background = "#F8FAFC";
        statusBox.style.borderLeftColor = "#CBD5E1";
        submitBtn.disabled = true;
        return;
    }

    const opt = accSelect.options[accSelect.selectedIndex];
    const balance = parseFloat(opt.dataset.balance) || 0;
    const amount = parseFloat(amountInput.value) || 0;
    methodInput.value = opt.dataset.method || 1;

    balDisplay.innerText = new Intl.NumberFormat('fr-FR').format(balance) + " FCFA";

    // Guard 1: Empty Account
    if (balance <= 0) {
        statusBox.style.background = "#FEE2E2";
        statusBox.style.borderLeftColor = "var(--c-danger)";
        warningDiv.innerText = `⛔ Décaissement impossible : Ce compte est totalement vide (Solde disponible : 0 FCFA). Veuillez choisir un autre compte approvisionné.`;
        warningDiv.style.display = "block";
        submitBtn.disabled = true;
        return;
    }

    // Guard 2: Amount exceeds balance
    if (amount > balance) {
        statusBox.style.background = "#FEE2E2";
        statusBox.style.borderLeftColor = "var(--c-danger)";
        warningDiv.innerText = `⛔ Solde insuffisant : Le montant de la dépense (${new Intl.NumberFormat('fr-FR').format(amount)} FCFA) dépasse le solde disponible (${new Intl.NumberFormat('fr-FR').format(balance)} FCFA).`;
        warningDiv.style.display = "block";
        submitBtn.disabled = true;
        return;
    }

    // Guard 3: Amount <= 0
    if (amount <= 0 && amountInput.value !== "") {
        warningDiv.innerText = `Le montant de la dépense doit être supérieur à 0 FCFA.`;
        warningDiv.style.display = "block";
        submitBtn.disabled = true;
        return;
    }

    // Valid State
    statusBox.style.background = "#DCFCE7";
    statusBox.style.borderLeftColor = "var(--c-success)";
}

document.addEventListener("DOMContentLoaded", function() {
    validateExpenseBalance();
});
</script>