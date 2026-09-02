<div class="page-title">
    <i class='bx bx-cart-add'></i>
    <h1>Acheter des Emballages Vides (Casiers & Bouteilles)</h1>
</div>

<?php if (!empty($flash_error)): ?>
    <div style="background: #FEE2E2; border-left: 4px solid var(--c-danger); color: #991B1B; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
        <i class='bx bx-error-circle' style="font-size: 1.4rem;"></i>
        <span><?= htmlspecialchars($flash_error) ?></span>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 800px;">
    <div class="card-header">
        <i class='bx bx-package'></i> Formulaire d'Acquisition d'Emballages Vides (Investissement / Capital Emballage)
    </div>
    <div class="card-body">
        <form action="<?= BASE_URL ?>/emballages/saveAchat" method="POST" id="form_achat_emballage">
            
            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Fournisseur / Vendeur</label>
                    <select name="supplier_id" class="form-control">
                        <option value="">-- Brasserie / Vendeur Particulier --</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Modèle d'Emballage (Casier) <span style="color: var(--c-danger);">*</span></label>
                    <select name="packaging_type_id" id="select_pkg_type" class="form-control" required onchange="onPkgTypeChange()">
                        <option value="">-- Sélectionner le modèle --</option>
                        <?php foreach ($packagingTypes as $pt): ?>
                            <option value="<?= $pt['id'] ?>" data-cost="<?= $pt['default_cost'] ?>">
                                <?= htmlspecialchars($pt['company']) ?> - <?= htmlspecialchars($pt['name']) ?> (<?= htmlspecialchars($pt['color']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Quantité de Casiers Vides <span style="color: var(--c-danger);">*</span></label>
                    <input type="number" name="crates_quantity" id="input_qty" class="form-control" required min="1" placeholder="Ex: 50" value="10" oninput="calculateTotal()">
                </div>

                <div class="form-group">
                    <label class="form-label">Prix Unitaire / Casier (FCFA) <span style="color: var(--c-danger);">*</span></label>
                    <input type="number" step="1" name="unit_cost" id="input_unit_cost" class="form-control" required placeholder="Ex: 3600" value="3600" oninput="calculateTotal()">
                </div>
            </div>

            <!-- TOTAL COMPUTATION BANNER -->
            <div style="background: #F0FDF4; border: 1px solid #DCFCE7; border-radius: 8px; padding: 14px 18px; margin: 15px 0; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: 600; color: #166534;">Montant Total à Décaisser :</span>
                <span id="display_total" style="font-size: 1.25rem; font-weight: 800; color: #15803D;">36 000 FCFA</span>
            </div>

            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Mode de Règlement <span style="color: var(--c-danger);">*</span></label>
                    <select name="payment_method_id" id="select_payment_method" class="form-control" required onchange="onPaymentMethodChange()">
                        <?php foreach ($paymentMethods as $pm): ?>
                            <option value="<?= $pm['id'] ?>" <?= ($pm['id'] == 1) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($pm['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" id="group_cash_account">
                    <label class="form-label">Compte de Décaissement <span style="color: var(--c-danger);">*</span></label>
                    <select name="cash_account_id" class="form-control">
                        <?php foreach ($cashAccounts as $ca): ?>
                            <option value="<?= $ca['id'] ?>">
                                <?= htmlspecialchars($ca['name']) ?> (Solde: <?= number_format($ca['current_balance'], 0, ',', ' ') ?> FCFA)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Notes / Justification</label>
                <input type="text" name="notes" class="form-control" placeholder="Ex: Achat 50 casiers jaunes SABC pour extension dépôt...">
            </div>

            <div style="margin-top: 25px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-accent"><i class='bx bx-save'></i> Enregistrer l'Achat d'Emballages</button>
                <a href="<?= BASE_URL ?>/emballages" class="btn btn-primary" style="background-color: var(--c-gray-600);"><i class='bx bx-x'></i> Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
function onPkgTypeChange() {
    const select = document.getElementById('select_pkg_type');
    const opt = select.options[select.selectedIndex];
    if (opt && opt.dataset.cost) {
        document.getElementById('input_unit_cost').value = opt.dataset.cost;
    }
    calculateTotal();
}

function calculateTotal() {
    const qty = parseInt(document.getElementById('input_qty').value) || 0;
    const cost = parseFloat(document.getElementById('input_unit_cost').value) || 0;
    const total = qty * cost;
    document.getElementById('display_total').innerText = total.toLocaleString('fr-FR') + ' FCFA';
}

function onPaymentMethodChange() {
    const pmId = document.getElementById('select_payment_method').value;
    const groupCash = document.getElementById('group_cash_account');
    if (pmId === '5') { // À crédit
        groupCash.style.display = 'none';
    } else {
        groupCash.style.display = 'block';
    }
}

document.addEventListener("DOMContentLoaded", function() {
    calculateTotal();
});
</script>
