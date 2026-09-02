<div class="page-title">
    <i class='bx bx-check-shield'></i>
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
        <i class='bx bx-edit'></i> Formulaire de Règlement de Dette Fournisseur
    </div>
    <div class="card-body">
        <form action="<?= BASE_URL ?>/paiementsFournisseurs/save" method="POST" id="pf_payment_form">
            
            <!-- 1. FOURNISSEUR SELECTION -->
            <div class="form-group">
                <label class="form-label">Fournisseur Bénéficiaire <span style="color: var(--c-danger);">*</span></label>
                <select name="supplier_id" id="pf_supplier_select" class="form-control" required onchange="updateSupplierDebtInfo()">
                    <option value="">-- Sélectionner le fournisseur à régler --</option>
                    <?php foreach ($suppliers as $s): ?>
                        <?php 
                        $isSel = ($selectedSupplierId == $s['id']) || (isset($flash_old['supplier_id']) && $flash_old['supplier_id'] == $s['id']);
                        ?>
                        <option value="<?= $s['id'] ?>" 
                                data-debt="<?= $s['solde_du'] ?>" 
                                <?= $isSel ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['name']) ?> &bull; Dette Actuelle: <?= number_format($s['solde_du'], 0, ',', ' ') ?> FCFA
                        </option>
                    <?php endforeach; ?>
                </select>
                <!-- FIELD VALIDATION MESSAGE UNDER SUPPLIER -->
                <small id="pf_supplier_warning" style="display: none; color: var(--c-danger); font-size: 0.82rem; margin-top: 5px; font-weight: 700;"></small>
            </div>

            <!-- LIVE DEBT INDICATOR BOX -->
            <div id="supplier_debt_box" style="padding: 15px; border-radius: 8px; background: #fff3cd; border-left: 4px solid var(--c-amber); margin-bottom: 20px; transition: all 0.3s ease;">
                <div style="font-size: 0.85rem; color: var(--c-gray-600); font-weight: 600;">Montant total dû au fournisseur :</div>
                <div id="supplier_debt_display" style="font-size: 1.4rem; font-weight: 800; color: var(--c-navy-dark);">0 FCFA</div>
                <div id="supplier_debt_status" style="font-size: 0.82rem; margin-top: 4px; color: var(--c-gray-600);">Sélectionnez un fournisseur pour voir sa dette.</div>
            </div>

            <!-- 2. MONTANT & DATE -->
            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Montant à Payer (FCFA) <span style="color: var(--c-danger);">*</span></label>
                    <input type="number" step="1" min="1" name="amount" id="pf_amount" class="form-control" required 
                           placeholder="Ex: 150000" 
                           value="<?= htmlspecialchars($flash_old['amount'] ?? '') ?>"
                           oninput="validatePaiementsLive()">
                    <!-- FIELD VALIDATION MESSAGE UNDER AMOUNT -->
                    <small id="pf_amount_warning" style="display: none; color: var(--c-danger); font-size: 0.82rem; margin-top: 5px; font-weight: 700;"></small>
                </div>
                <div class="form-group">
                    <label class="form-label">Date du Règlement <span style="color: var(--c-danger);">*</span></label>
                    <input type="date" name="payment_date" class="form-control" required value="<?= htmlspecialchars($flash_old['payment_date'] ?? date('Y-m-d')) ?>">
                </div>
            </div>

            <!-- 3. COMPTE DE DECAISSEMENT -->
            <div class="form-group">
                <label class="form-label">Compte de Décaissement (Caisse / Trésorerie Débitrice) <span style="color: var(--c-danger);">*</span></label>
                <select name="cash_account_id" id="pf_cash_account" class="form-control" required onchange="validatePaiementsLive()">
                    <option value="">-- Choisir la caisse source qui paye --</option>
                    <?php foreach ($cash_accounts as $acc): ?>
                        <?php
                        $bal = floatval($acc['current_balance']);
                        $isAccSel = (isset($flash_old['cash_account_id']) && $flash_old['cash_account_id'] == $acc['id']);
                        $isEmpty = ($bal <= 0);
                        ?>
                        <option value="<?= $acc['id'] ?>" 
                                data-balance="<?= $bal ?>" 
                                data-method="<?= htmlspecialchars($acc['payment_method_name']) ?>"
                                <?= $isAccSel ? 'selected' : '' ?>>
                            <?= htmlspecialchars($acc['name']) ?> (<?= htmlspecialchars($acc['payment_method_name']) ?>) &mdash; <?= $isEmpty ? '⛔ SOLDE VIDE (0 FCFA)' : 'Solde disponible : ' . number_format($bal, 0, ',', ' ') . ' FCFA' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <!-- FIELD VALIDATION MESSAGE UNDER CASH ACCOUNT -->
                <small id="pf_account_warning" style="display: none; color: var(--c-danger); font-size: 0.82rem; margin-top: 5px; font-weight: 700;"></small>
                <small style="color: var(--c-gray-600); font-size: 0.8rem; margin-top: 4px; display: block;">
                    <i class='bx bx-info-circle'></i> L'argent sera déduit (-Sortie) de ce compte, et le mode de paiement est automatiquement déduit.
                </small>
            </div>

            <!-- 4. REFERENCE & NOTES -->
            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">N° Reçu / Référence Virement / Chèque (Optionnel)</label>
                    <input type="text" name="reference" class="form-control" placeholder="Ex: VIR-SABC-4892" value="<?= htmlspecialchars($flash_old['reference'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Observation / Notes</label>
                    <input type="text" name="notes" class="form-control" placeholder="Ex: Paiement partiel facture d'approvisionnement..." value="<?= htmlspecialchars($flash_old['notes'] ?? '') ?>">
                </div>
            </div>

            <div style="margin-top: 25px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-accent" id="submit_pf_btn"><i class='bx bx-check'></i> Valider & Décaisser de la Caisse</button>
                <a href="<?= BASE_URL ?>/paiementsFournisseurs" class="btn btn-primary" style="background-color: var(--c-gray-600);"><i class='bx bx-x'></i> Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
let currentSupplierDebt = 0;

function updateSupplierDebtInfo() {
    const sel = document.getElementById("pf_supplier_select");
    const debtBox = document.getElementById("supplier_debt_box");
    const debtDisplay = document.getElementById("supplier_debt_display");
    const debtStatus = document.getElementById("supplier_debt_status");
    const amountInput = document.getElementById("pf_amount");

    if (sel.selectedIndex <= 0) {
        currentSupplierDebt = 0;
        debtDisplay.innerText = "0 FCFA";
        debtStatus.innerText = "Sélectionnez un fournisseur pour voir sa dette.";
        debtBox.style.background = "#F3F4F6";
        debtBox.style.borderLeftColor = "#9CA3AF";
        validatePaiementsLive();
        return;
    }

    const opt = sel.options[sel.selectedIndex];
    currentSupplierDebt = parseFloat(opt.dataset.debt) || 0;
    debtDisplay.innerText = new Intl.NumberFormat('fr-FR').format(currentSupplierDebt) + " FCFA";

    if (currentSupplierDebt <= 0) {
        debtStatus.innerText = "✓ Ce fournisseur n'a aucune dette en cours (Totalement réglé).";
        debtBox.style.background = "#D1FAE5";
        debtBox.style.borderLeftColor = "#10B981";
        amountInput.value = "";
    } else {
        debtStatus.innerText = "⚠️ Montant à solder. Saisissez le montant à décaisser ci-dessous.";
        debtBox.style.background = "#FEF3C7";
        debtBox.style.borderLeftColor = "#F59E0B";
        if (!amountInput.value || parseFloat(amountInput.value) <= 0) {
            amountInput.value = currentSupplierDebt;
        }
    }
    validatePaiementsLive();
}

function validatePaiementsLive() {
    const supplierSel = document.getElementById("pf_supplier_select");
    const supplierWarning = document.getElementById("pf_supplier_warning");
    const amountInput = document.getElementById("pf_amount");
    const amountWarning = document.getElementById("pf_amount_warning");
    const accountSel = document.getElementById("pf_cash_account");
    const accountWarning = document.getElementById("pf_account_warning");
    const submitBtn = document.getElementById("submit_pf_btn");

    // Reset warnings and borders
    supplierWarning.style.display = "none";
    supplierWarning.innerText = "";
    supplierSel.style.borderColor = "";

    amountWarning.style.display = "none";
    amountWarning.innerText = "";
    amountInput.style.borderColor = "";

    accountWarning.style.display = "none";
    accountWarning.innerText = "";
    accountSel.style.borderColor = "";

    submitBtn.disabled = false;

    let hasError = false;

    // Check 1: Supplier Debt Limit
    if (supplierSel.selectedIndex > 0 && currentSupplierDebt <= 0) {
        supplierWarning.innerText = "⛔ Ce fournisseur n'a aucune dette en cours (0 FCFA). Aucun paiement nécessaire.";
        supplierWarning.style.display = "block";
        supplierSel.style.borderColor = "var(--c-danger)";
        hasError = true;
    }

    // Check 2: Amount Limit
    const amount = parseFloat(amountInput.value) || 0;
    if (amountInput.value !== "" && amount <= 0 && supplierSel.selectedIndex > 0) {
        amountWarning.innerText = "Le montant à payer doit être supérieur à 0 FCFA.";
        amountWarning.style.display = "block";
        amountInput.style.borderColor = "var(--c-danger)";
        hasError = true;
    } else if (currentSupplierDebt > 0 && amount > currentSupplierDebt) {
        amountWarning.innerText = `⛔ Montant excessif : Le montant saisi (${new Intl.NumberFormat('fr-FR').format(amount)} FCFA) dépasse la dette due au fournisseur (${new Intl.NumberFormat('fr-FR').format(currentSupplierDebt)} FCFA).`;
        amountWarning.style.display = "block";
        amountInput.style.borderColor = "var(--c-danger)";
        hasError = true;
    }

    // Check 3: Cash Account Balance Limit
    if (accountSel.selectedIndex > 0) {
        const accOpt = accountSel.options[accountSel.selectedIndex];
        const accBal = parseFloat(accOpt.dataset.balance) || 0;

        if (accBal <= 0) {
            accountWarning.innerText = `⛔ Décaissement impossible : Ce compte de caisse est totalement vide (Solde disponible : 0 FCFA).`;
            accountWarning.style.display = "block";
            accountSel.style.borderColor = "var(--c-danger)";
            hasError = true;
        } else if (amount > accBal) {
            accountWarning.innerText = `⛔ Solde insuffisant : Le compte dispose de ${new Intl.NumberFormat('fr-FR').format(accBal)} FCFA (Montant requis : ${new Intl.NumberFormat('fr-FR').format(amount)} FCFA).`;
            accountWarning.style.display = "block";
            accountSel.style.borderColor = "var(--c-danger)";
            hasError = true;
        }
    }

    if (hasError || supplierSel.selectedIndex <= 0 || amount <= 0 || accountSel.selectedIndex <= 0) {
        submitBtn.disabled = true;
    }
}

document.addEventListener("DOMContentLoaded", function() {
    updateSupplierDebtInfo();
});
</script>
