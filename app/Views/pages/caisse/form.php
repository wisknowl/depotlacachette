<div class="page-title">
    <i class='bx bx-wallet'></i>
    <h1><?= $title ?></h1>
</div>

<?php if (!empty($flash_error)): ?>
    <div style="background: #FEE2E2; border-left: 4px solid var(--c-danger); color: #991B1B; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
        <i class='bx bx-error-circle' style="font-size: 1.4rem;"></i>
        <span><?= htmlspecialchars($flash_error) ?></span>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 750px;">
    <div class="card-header">
        <i class='bx bx-transfer'></i> Enregistrement d'une Opération de Trésorerie
    </div>
    <div class="card-body">
        <form action="<?= BASE_URL ?>/caisse/save" method="POST" id="caisse_op_form">
            
            <!-- OPERATION TYPE SELECTOR -->
            <div class="form-group">
                <label class="form-label">Nature de l'Opération de Trésorerie <span style="color: var(--c-danger);">*</span></label>
                <select name="operation_type" id="op_type_select" class="form-control" required onchange="toggleOperationFields()">
                    <option value="deposit" <?= (!isset($flash_old['operation_type']) || $flash_old['operation_type'] === 'deposit') ? 'selected' : '' ?>>
                        🟢 Apport de Fonds / Alimentation de Caisse (Capital initial, Fond de roulement, Dépôt)
                    </option>
                    <option value="transfer" <?= (isset($flash_old['operation_type']) && $flash_old['operation_type'] === 'transfer') ? 'selected' : '' ?>>
                        🔄 Transfert Inter-Comptes (Ex: Espèces vers Banque / Orange Money / MoMo)
                    </option>
                    <option value="withdrawal" <?= (isset($flash_old['operation_type']) && $flash_old['operation_type'] === 'withdrawal') ? 'selected' : '' ?>>
                        🔴 Retrait de Caisse Exceptionnel / Prélèvement Exploitant
                    </option>
                </select>
            </div>

            <!-- 1. DEPOSIT (APPORT DE FONDS) FIELDS -->
            <div id="deposit_fields">
                <div class="dashboard-grid">
                    <div class="form-group">
                        <label class="form-label">Type d'Apport / Origine des Fonds <span style="color: var(--c-danger);">*</span></label>
                        <select name="deposit_type" id="deposit_type" class="form-control">
                            <option value="Capital Initial / Fond de Roulement (Démarrage)">Capital Initial / Fond de Roulement (Démarrage)</option>
                            <option value="Apport Personnel de l'Exploitant">Apport Personnel de l'Exploitant</option>
                            <option value="Alimentation de Caisse (Réapprovisionnement)">Alimentation de Caisse (Réapprovisionnement)</option>
                            <option value="Autre Dépôt Exceptionnel">Autre Dépôt Exceptionnel</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Compte Récepteur (Caisse à Créditer) <span style="color: var(--c-danger);">*</span></label>
                        <select name="deposit_account_id" id="deposit_account" class="form-control">
                            <?php foreach ($accounts as $acc): ?>
                                <?php $isDepSel = (isset($flash_old['deposit_account_id']) && $flash_old['deposit_account_id'] == $acc['id']); ?>
                                <option value="<?= $acc['id'] ?>" <?= $isDepSel ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($acc['name']) ?> (<?= htmlspecialchars($acc['payment_method_name']) ?>) &mdash; Solde actuel : <?= number_format($acc['current_balance'], 0, ',', ' ') ?> FCFA
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 2. TRANSFER FIELDS -->
            <div id="transfer_fields" style="display: none;">
                <div class="dashboard-grid">
                    <div class="form-group">
                        <label class="form-label">Compte Source (Caisse de Départ) <span style="color: var(--c-danger);">*</span></label>
                        <select name="from_account_id" id="from_account" class="form-control" onchange="validateForm()">
                            <option value="">-- Choisir compte émetteur --</option>
                            <?php foreach ($accounts as $acc): ?>
                                <?php 
                                    $bal = floatval($acc['current_balance']);
                                    $isFromSel = (isset($flash_old['from_account_id']) && $flash_old['from_account_id'] == $acc['id']);
                                ?>
                                <option value="<?= $acc['id'] ?>" data-balance="<?= $bal ?>" <?= $isFromSel ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($acc['name']) ?> (Solde : <?= number_format($bal, 0, ',', ' ') ?> FCFA)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small id="from_account_warning" style="display: none; color: var(--c-danger); font-size: 0.82rem; font-weight: 700; margin-top: 5px;"></small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Compte Destinataire (Caisse d'Arrivée) <span style="color: var(--c-danger);">*</span></label>
                        <select name="to_account_id" id="to_account" class="form-control" onchange="validateForm()">
                            <option value="">-- Choisir compte récepteur --</option>
                            <?php foreach ($accounts as $acc): ?>
                                <?php $isToSel = (isset($flash_old['to_account_id']) && $flash_old['to_account_id'] == $acc['id']); ?>
                                <option value="<?= $acc['id'] ?>" <?= $isToSel ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($acc['name']) ?> (Solde : <?= number_format($acc['current_balance'], 0, ',', ' ') ?> FCFA)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small id="to_account_warning" style="display: none; color: var(--c-danger); font-size: 0.82rem; font-weight: 700; margin-top: 5px;"></small>
                    </div>
                </div>
            </div>

            <!-- 3. WITHDRAWAL FIELDS -->
            <div id="withdrawal_fields" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Compte Source du Retrait <span style="color: var(--c-danger);">*</span></label>
                    <select name="withdrawal_account_id" id="withdrawal_account" class="form-control" onchange="validateForm()">
                        <option value="">-- Choisir la caisse à débiter --</option>
                        <?php foreach ($accounts as $acc): ?>
                            <?php 
                                $bal = floatval($acc['current_balance']);
                                $isWthSel = (isset($flash_old['withdrawal_account_id']) && $flash_old['withdrawal_account_id'] == $acc['id']);
                            ?>
                            <option value="<?= $acc['id'] ?>" data-balance="<?= $bal ?>" <?= $isWthSel ? 'selected' : '' ?>>
                                <?= htmlspecialchars($acc['name']) ?> (Solde : <?= number_format($bal, 0, ',', ' ') ?> FCFA)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small id="withdrawal_account_warning" style="display: none; color: var(--c-danger); font-size: 0.82rem; font-weight: 700; margin-top: 5px;"></small>
                </div>
            </div>

            <!-- COMMON AMOUNT & DATE -->
            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Montant de l'Opération (FCFA) <span style="color: var(--c-danger);">*</span></label>
                    <input type="number" step="1" min="1" name="amount" id="op_amount" class="form-control" required 
                           placeholder="Ex: 1000000" 
                           value="<?= htmlspecialchars($flash_old['amount'] ?? '') ?>" 
                           oninput="validateForm()">
                    <!-- VALIDATION MESSAGE DIRECTLY UNDER AMOUNT -->
                    <small id="op_amount_warning" style="display: none; color: var(--c-danger); font-size: 0.82rem; font-weight: 700; margin-top: 5px;"></small>
                </div>
                <div class="form-group">
                    <label class="form-label">Date de l'Opération <span style="color: var(--c-danger);">*</span></label>
                    <input type="date" name="transaction_date" class="form-control" required value="<?= htmlspecialchars($flash_old['transaction_date'] ?? date('Y-m-d')) ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Motif / Justification (Optionnel)</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Ex: Fond de roulement initial pour achat du premier camion de boissons..."><?= htmlspecialchars($flash_old['description'] ?? '') ?></textarea>
            </div>

            <div style="margin-top: 25px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-accent" id="submit_caisse_btn"><i class='bx bx-check'></i> Exécuter l'Opération</button>
                <a href="<?= BASE_URL ?>/caisse" class="btn btn-primary" style="background-color: var(--c-gray-600);"><i class='bx bx-x'></i> Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleOperationFields() {
    const val = document.getElementById("op_type_select").value;
    const depFields = document.getElementById("deposit_fields");
    const trfFields = document.getElementById("transfer_fields");
    const wthFields = document.getElementById("withdrawal_fields");

    const depAccount = document.getElementById("deposit_account");
    const fromAccount = document.getElementById("from_account");
    const toAccount = document.getElementById("to_account");
    const wthAccount = document.getElementById("withdrawal_account");

    if (val === "deposit") {
        depFields.style.display = "block";
        trfFields.style.display = "none";
        wthFields.style.display = "none";

        depAccount.removeAttribute("disabled");
        fromAccount.setAttribute("disabled", "disabled");
        toAccount.setAttribute("disabled", "disabled");
        wthAccount.setAttribute("disabled", "disabled");
    } else if (val === "transfer") {
        depFields.style.display = "none";
        trfFields.style.display = "block";
        wthFields.style.display = "none";

        depAccount.setAttribute("disabled", "disabled");
        fromAccount.removeAttribute("disabled");
        toAccount.removeAttribute("disabled");
        wthAccount.setAttribute("disabled", "disabled");
    } else if (val === "withdrawal") {
        depFields.style.display = "none";
        trfFields.style.display = "none";
        wthFields.style.display = "block";

        depAccount.setAttribute("disabled", "disabled");
        fromAccount.setAttribute("disabled", "disabled");
        toAccount.setAttribute("disabled", "disabled");
        wthAccount.removeAttribute("disabled");
    }

    validateForm();
}

function validateForm() {
    const val = document.getElementById("op_type_select").value;
    const amountInput = document.getElementById("op_amount");
    const amountWarning = document.getElementById("op_amount_warning");
    const submitBtn = document.getElementById("submit_caisse_btn");

    const fromWarning = document.getElementById("from_account_warning");
    const toWarning = document.getElementById("to_account_warning");
    const wthWarning = document.getElementById("withdrawal_account_warning");

    const fromSel = document.getElementById("from_account");
    const toSel = document.getElementById("to_account");
    const wthSel = document.getElementById("withdrawal_account");

    // Reset all warnings and borders
    amountWarning.style.display = "none";
    amountWarning.innerText = "";
    amountInput.style.borderColor = "";

    fromWarning.style.display = "none";
    fromWarning.innerText = "";
    fromSel.style.borderColor = "";

    toWarning.style.display = "none";
    toWarning.innerText = "";
    toSel.style.borderColor = "";

    wthWarning.style.display = "none";
    wthWarning.innerText = "";
    wthSel.style.borderColor = "";

    submitBtn.disabled = false;

    const amount = parseFloat(amountInput.value) || 0;

    if (amountInput.value !== "" && amount <= 0) {
        amountWarning.innerText = "Le montant de l'opération doit être supérieur à 0 FCFA.";
        amountWarning.style.display = "block";
        amountInput.style.borderColor = "var(--c-danger)";
        submitBtn.disabled = true;
        return;
    }

    if (val === "transfer") {
        if (fromSel.selectedIndex > 0 && toSel.selectedIndex > 0 && fromSel.value === toSel.value) {
            toWarning.innerText = "⛔ Le compte de départ et le compte d'arrivée doivent être différents.";
            toWarning.style.display = "block";
            toSel.style.borderColor = "var(--c-danger)";
            submitBtn.disabled = true;
            return;
        }

        if (fromSel.selectedIndex > 0) {
            const balance = parseFloat(fromSel.options[fromSel.selectedIndex].dataset.balance) || 0;
            if (balance <= 0) {
                fromWarning.innerText = `⛔ Ce compte de départ est totalement vide (0 FCFA). Transfert impossible.`;
                fromWarning.style.display = "block";
                fromSel.style.borderColor = "var(--c-danger)";
                submitBtn.disabled = true;
                return;
            }
            if (amount > balance) {
                fromWarning.innerText = `⛔ Solde insuffisant : ${new Intl.NumberFormat('fr-FR').format(balance)} FCFA disponible (Montant requis : ${new Intl.NumberFormat('fr-FR').format(amount)} FCFA).`;
                fromWarning.style.display = "block";
                fromSel.style.borderColor = "var(--c-danger)";
                submitBtn.disabled = true;
                return;
            }
        }
        if (fromSel.selectedIndex <= 0 || toSel.selectedIndex <= 0 || amount <= 0) {
            submitBtn.disabled = true;
        }
    } else if (val === "withdrawal") {
        if (wthSel.selectedIndex > 0) {
            const balance = parseFloat(wthSel.options[wthSel.selectedIndex].dataset.balance) || 0;
            if (balance <= 0) {
                wthWarning.innerText = `⛔ Ce compte est totalement vide (0 FCFA). Retrait impossible.`;
                wthWarning.style.display = "block";
                wthSel.style.borderColor = "var(--c-danger)";
                submitBtn.disabled = true;
                return;
            }
            if (amount > balance) {
                wthWarning.innerText = `⛔ Solde insuffisant : ${new Intl.NumberFormat('fr-FR').format(balance)} FCFA disponible (Montant requis : ${new Intl.NumberFormat('fr-FR').format(amount)} FCFA).`;
                wthWarning.style.display = "block";
                wthSel.style.borderColor = "var(--c-danger)";
                submitBtn.disabled = true;
                return;
            }
        }
        if (wthSel.selectedIndex <= 0 || amount <= 0) {
            submitBtn.disabled = true;
        }
    } else if (val === "deposit") {
        if (amount <= 0) {
            submitBtn.disabled = true;
        }
    }
}

document.addEventListener("DOMContentLoaded", function() {
    toggleOperationFields();
});
</script>
