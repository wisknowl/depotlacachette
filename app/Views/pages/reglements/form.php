<div class="page-title">
    <i class='bx bx-check-double'></i>
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
        <i class='bx bx-edit'></i> Formulaire d'Encaissement de Dette Client
    </div>
    <div class="card-body">
        <form action="<?= BASE_URL ?>/reglements/save" method="POST" id="reg_payment_form">
            
            <!-- 1. CLIENT SELECTION -->
            <div class="form-group">
                <label class="form-label">Client Débiteur <span style="color: var(--c-danger);">*</span></label>
                <select name="client_id" id="reg_client_select" class="form-control" required onchange="updateClientDebtInfo()">
                    <option value="">-- Sélectionner le client qui règle sa dette --</option>
                    <?php foreach ($clients as $c): ?>
                        <?php 
                        $isSel = ($selectedClientId == $c['id']) || (isset($flash_old['client_id']) && $flash_old['client_id'] == $c['id']);
                        ?>
                        <option value="<?= $c['id'] ?>" 
                                data-debt="<?= $c['solde_du'] ?>" 
                                <?= $isSel ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?> &bull; Dette Actuelle: <?= number_format($c['solde_du'], 0, ',', ' ') ?> FCFA
                        </option>
                    <?php endforeach; ?>
                </select>
                <!-- FIELD VALIDATION MESSAGE UNDER CLIENT -->
                <small id="reg_client_warning" style="display: none; color: var(--c-danger); font-size: 0.82rem; margin-top: 5px; font-weight: 700;"></small>
            </div>

            <!-- LIVE DEBT INDICATOR BOX -->
            <div id="debt_indicator_box" style="padding: 15px; border-radius: 8px; background: #e8f4fd; border-left: 4px solid var(--c-navy); margin-bottom: 20px; transition: all 0.3s ease;">
                <div style="font-size: 0.85rem; color: var(--c-gray-600); font-weight: 600;">Solde restant dû par ce client :</div>
                <div id="client_debt_display" style="font-size: 1.4rem; font-weight: 800; color: var(--c-navy);">0 FCFA</div>
                <div id="client_debt_status" style="font-size: 0.82rem; margin-top: 4px; color: var(--c-gray-600);">Sélectionnez un client pour voir sa situation.</div>
                <div id="advance_notice" style="display: none; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #A7F3D0; font-size: 0.85rem; color: #047857; font-weight: 700;">
                    <i class='bx bx-gift'></i> Ce versement sera crédité en tant qu'<strong>Avoir / Monnaie en attente</strong> sur le compte du client.
                </div>
            </div>

            <!-- OPTION AVOIR / ACOMPTE -->
            <div style="margin-bottom: 18px; padding: 10px 14px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.88rem; font-weight: 700; color: #334155;">
                    <input type="checkbox" name="is_advance" id="reg_is_advance" value="1" <?= (isset($_GET['type']) && $_GET['type'] === 'avoir') ? 'checked' : '' ?> onchange="validateReglementsLive()">
                    <span><i class='bx bx-wallet-alt' style="color: #059669;"></i> Enregistrer comme Avoir / Reliquat Monnaie (Acompte d'avance pour futurs achats)</span>
                </label>
            </div>

            <!-- 2. MONTANT & DATE -->
            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Montant du Versement (FCFA) <span style="color: var(--c-danger);">*</span></label>
                    <input type="number" step="1" min="1" name="amount" id="reg_amount" class="form-control" required 
                           placeholder="Ex: 50000" 
                           value="<?= htmlspecialchars($flash_old['amount'] ?? '') ?>"
                           oninput="validateReglementsLive()">
                    <!-- FIELD VALIDATION MESSAGE UNDER AMOUNT -->
                    <small id="reg_amount_warning" style="display: none; color: var(--c-danger); font-size: 0.82rem; margin-top: 5px; font-weight: 700;"></small>
                </div>
                <div class="form-group">
                    <label class="form-label">Date du Paiement <span style="color: var(--c-danger);">*</span></label>
                    <input type="date" name="payment_date" class="form-control" required value="<?= htmlspecialchars($flash_old['payment_date'] ?? date('Y-m-d')) ?>">
                </div>
            </div>

            <!-- 3. COMPTE D'ENCAISSEMENT -->
            <div class="form-group">
                <label class="form-label">Compte d'Encaissement (Caisse / Trésorerie de Destination) <span style="color: var(--c-danger);">*</span></label>
                <select name="cash_account_id" id="reg_cash_account" class="form-control" required onchange="validateReglementsLive()">
                    <option value="">-- Choisir la caisse réceptrice --</option>
                    <?php foreach ($cash_accounts as $acc): ?>
                        <?php
                        $isAccSel = (isset($flash_old['cash_account_id']) && $flash_old['cash_account_id'] == $acc['id']);
                        ?>
                        <option value="<?= $acc['id'] ?>" 
                                data-method="<?= htmlspecialchars($acc['payment_method_name']) ?>"
                                <?= $isAccSel ? 'selected' : '' ?>>
                            <?= htmlspecialchars($acc['name']) ?> (<?= htmlspecialchars($acc['payment_method_name']) ?>) &mdash; Solde actuel : <?= number_format($acc['current_balance'], 0, ',', ' ') ?> FCFA
                        </option>
                    <?php endforeach; ?>
                </select>
                <!-- FIELD VALIDATION MESSAGE UNDER ACCOUNT -->
                <small id="reg_account_warning" style="display: none; color: var(--c-danger); font-size: 0.82rem; margin-top: 5px; font-weight: 700;"></small>
                <small style="color: var(--c-gray-600); font-size: 0.8rem; margin-top: 4px; display: block;">
                    <i class='bx bx-info-circle'></i> L'argent sera crédité (+Entrée) dans ce compte de caisse, et le mode de paiement est automatiquement déduit.
                </small>
            </div>

            <!-- 4. REFERENCE & NOTES -->
            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">N° Reçu / Référence Transaction (Optionnel)</label>
                    <input type="text" name="reference" class="form-control" placeholder="Ex: RECU-CLI-0045" value="<?= htmlspecialchars($flash_old['reference'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Observation / Notes</label>
                    <input type="text" name="notes" id="reg_notes" class="form-control" placeholder="Ex: Règlement facture ou reliquat monnaie..." value="<?= htmlspecialchars($flash_old['notes'] ?? '') ?>">
                </div>
            </div>

            <div style="margin-top: 25px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-accent" id="submit_reg_btn"><i class='bx bx-check'></i> Valider & Encaisser en Caisse</button>
                <a href="<?= BASE_URL ?>/reglements" class="btn btn-primary" style="background-color: var(--c-gray-600);"><i class='bx bx-x'></i> Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
let currentClientDebt = 0;

function updateClientDebtInfo() {
    const sel = document.getElementById("reg_client_select");
    const debtBox = document.getElementById("debt_indicator_box");
    const debtDisplay = document.getElementById("client_debt_display");
    const debtStatus = document.getElementById("client_debt_status");
    const amountInput = document.getElementById("reg_amount");
    const isAdvanceChk = document.getElementById("reg_is_advance");

    if (sel.selectedIndex <= 0) {
        currentClientDebt = 0;
        debtDisplay.innerText = "0 FCFA";
        debtStatus.innerText = "Sélectionnez un client pour voir sa situation.";
        debtBox.style.background = "#F3F4F6";
        debtBox.style.borderLeftColor = "#9CA3AF";
        validateReglementsLive();
        return;
    }

    const opt = sel.options[sel.selectedIndex];
    currentClientDebt = parseFloat(opt.dataset.debt) || 0;
    
    if (currentClientDebt < 0) {
        debtDisplay.innerText = "Avoir: +" + new Intl.NumberFormat('fr-FR').format(Math.abs(currentClientDebt)) + " FCFA";
        debtDisplay.style.color = "#047857";
        debtStatus.innerText = "🟢 Ce client a déjà un solde créditeur (Avoir / Monnaie en attente).";
        debtBox.style.background = "#ECFDF5";
        debtBox.style.borderLeftColor = "#10B981";
        if (isAdvanceChk) isAdvanceChk.checked = true;
    } else if (currentClientDebt === 0) {
        debtDisplay.innerText = "0 FCFA";
        debtDisplay.style.color = "#334155";
        debtStatus.innerText = "✓ Ce client n'a aucune dette en cours (Totalement à jour).";
        debtBox.style.background = "#F0FDF4";
        debtBox.style.borderLeftColor = "#10B981";
        if (isAdvanceChk) isAdvanceChk.checked = true;
    } else {
        debtDisplay.innerText = new Intl.NumberFormat('fr-FR').format(currentClientDebt) + " FCFA";
        debtDisplay.style.color = "#DC2626";
        debtStatus.innerText = "⚠️ Dette exigible. Saisissez le montant versé par le client.";
        debtBox.style.background = "#FEF3C7";
        debtBox.style.borderLeftColor = "#F59E0B";
        if (!amountInput.value || parseFloat(amountInput.value) <= 0) {
            amountInput.value = currentClientDebt;
        }
    }
    validateReglementsLive();
}

function validateReglementsLive() {
    const clientSel = document.getElementById("reg_client_select");
    const clientWarning = document.getElementById("reg_client_warning");
    const amountInput = document.getElementById("reg_amount");
    const amountWarning = document.getElementById("reg_amount_warning");
    const accountSel = document.getElementById("reg_cash_account");
    const accountWarning = document.getElementById("reg_account_warning");
    const isAdvanceChk = document.getElementById("reg_is_advance");
    const advanceNotice = document.getElementById("advance_notice");
    const submitBtn = document.getElementById("submit_reg_btn");

    // Reset warnings and borders
    clientWarning.style.display = "none";
    clientWarning.innerText = "";
    clientSel.style.borderColor = "";

    amountWarning.style.display = "none";
    amountWarning.innerText = "";
    amountInput.style.borderColor = "";

    accountWarning.style.display = "none";
    accountWarning.innerText = "";
    accountSel.style.borderColor = "";

    submitBtn.disabled = false;

    const isAdvance = isAdvanceChk ? isAdvanceChk.checked : false;
    if (advanceNotice) {
        advanceNotice.style.display = (isAdvance || currentClientDebt <= 0) ? "block" : "none";
    }

    if (isAdvance || currentClientDebt <= 0) {
        submitBtn.innerHTML = "<i class='bx bx-check-circle'></i> Valider & Créditer l'Avoir Client";
    } else {
        submitBtn.innerHTML = "<i class='bx bx-check'></i> Valider & Encaisser en Caisse";
    }

    // Check 1: Client selection & 0 debt without advance checked
    if (clientSel.selectedIndex > 0 && currentClientDebt <= 0 && !isAdvance) {
        if (isAdvanceChk) isAdvanceChk.checked = true;
    }

    // Check 2: Amount validation
    const amount = parseFloat(amountInput.value) || 0;
    if (amountInput.value !== "" && amount <= 0 && clientSel.selectedIndex > 0) {
        amountWarning.innerText = "Le montant du versement doit être supérieur à 0 FCFA.";
        amountWarning.style.display = "block";
        amountInput.style.borderColor = "var(--c-danger)";
        submitBtn.disabled = true;
        return;
    }

    if (currentClientDebt > 0 && amount > currentClientDebt && !isAdvance) {
        amountWarning.innerText = `💡 Le montant versé (${new Intl.NumberFormat('fr-FR').format(amount)} FCFA) dépasse la dette (${new Intl.NumberFormat('fr-FR').format(currentClientDebt)} FCFA). Le surplus de ${new Intl.NumberFormat('fr-FR').format(amount - currentClientDebt)} FCFA sera crédité en Avoir.`;
        amountWarning.style.color = "#047857";
        amountWarning.style.display = "block";
        if (isAdvanceChk) isAdvanceChk.checked = true;
    }

    // Check 3: Account selection
    if (clientSel.selectedIndex <= 0 || amount <= 0 || accountSel.selectedIndex <= 0) {
        submitBtn.disabled = true;
    }
}

document.addEventListener("DOMContentLoaded", function() {
    updateClientDebtInfo();
});
</script>
