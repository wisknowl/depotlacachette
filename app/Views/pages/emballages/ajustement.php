<div class="page-title">
    <i class='bx bx-slider-alt'></i>
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
        <i class='bx bx-package'></i> Déclaration de Stock Initial, Casse ou Correction d'Inventaire d'Emballages
    </div>
    <div class="card-body">
        <form action="<?= BASE_URL ?>/emballages/saveAjustement" method="POST" id="emballage_adjustment_form">
            
            <!-- 1. PACKAGING TYPE SELECTION -->
            <div class="form-group">
                <label class="form-label">Modèle d'Emballage / Casier <span style="color: var(--c-danger);">*</span></label>
                <select name="packaging_type_id" id="pkg_type_select" class="form-control" required onchange="onPackagingTypeChange()">
                    <option value="">-- Sélectionner un modèle d'emballage --</option>
                    <?php foreach ($packagingTypes as $pt): ?>
                        <?php 
                            $isSel = (isset($flash_old['packaging_type_id']) && $flash_old['packaging_type_id'] == $pt['id']);
                        ?>
                        <option value="<?= $pt['id'] ?>" 
                                data-company="<?= htmlspecialchars($pt['company']) ?>"
                                data-color="<?= htmlspecialchars($pt['color']) ?>"
                                data-bottles-per-crate="<?= intval($pt['bottles_per_crate'] ?: 24) ?>"
                                data-default-cost="<?= floatval($pt['default_cost'] ?: 3600) ?>"
                                data-empty-crates="<?= intval($pt['empty_crates'] ?? 0) ?>"
                                data-loose-bottles="<?= intval($pt['loose_bottles'] ?? 0) ?>"
                                <?= $isSel ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pt['company']) ?> &bull; <?= htmlspecialchars($pt['name']) ?> (<?= htmlspecialchars($pt['color']) ?> &bull; <?= $pt['bottles_per_crate'] ?: 24 ?> btls) &mdash; Actuel au dépôt : <?= intval($pt['empty_crates'] ?? 0) ?> casier(s), <?= intval($pt['loose_bottles'] ?? 0) ?> btl(s)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- CURRENT INVENTORY BADGE -->
            <div id="pkg_current_info" style="display: none; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px 16px; margin: 15px 0;">
                <div style="font-size: 0.85rem; font-weight: 700; color: #475569; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <span>📦 Stock actuel de vides au dépôt : <strong id="info_empty_crates" style="color: #0284C7;">0 casiers</strong> (<strong id="info_loose_bottles" style="color: #64748B;">0 bouteilles vrac</strong>)</span>
                    <span>💰 Valeur de référence : <strong id="info_default_cost" style="color: var(--c-navy);">0 FCFA / casier</strong></span>
                </div>
            </div>

            <!-- 2. MOVEMENT TYPE SELECTOR -->
            <div class="form-group" style="margin-top: 15px;">
                <label class="form-label">Nature de l'Ajustement / Motif <span style="color: var(--c-danger);">*</span></label>
                <select name="movement_type" id="pkg_movement_type" class="form-control" required onchange="calculatePackagingImpact()">
                    <option value="Initial_Stock" data-direction="IN" <?= (!isset($flash_old['movement_type']) || $flash_old['movement_type'] === 'Initial_Stock') ? 'selected' : '' ?>>
                        🟢 Stock Initial de Démarrage / Solde Ancien Système (+ Augmentation du Stock)
                    </option>
                    <option value="Adjustment_Plus" data-direction="IN" <?= (isset($flash_old['movement_type']) && $flash_old['movement_type'] === 'Adjustment_Plus') ? 'selected' : '' ?>>
                        🟢 Régularisation Positive / Casiers ou Bouteilles Retrouvés (+ Augmentation du Stock)
                    </option>
                    <option value="Adjustment_Minus" data-direction="OUT" <?= (isset($flash_old['movement_type']) && $flash_old['movement_type'] === 'Adjustment_Minus') ? 'selected' : '' ?>>
                        🔴 Régularisation Négative / Casiers Manquants au Comptage (- Déduction du Stock)
                    </option>
                    <option value="Breakage_Empty" data-direction="OUT" <?= (isset($flash_old['movement_type']) && $flash_old['movement_type'] === 'Breakage_Empty') ? 'selected' : '' ?>>
                        🔴 Casse / Rebut de Casiers ou Bouteilles Vides au Dépôt (- Déduction du Stock)
                    </option>
                </select>
            </div>

            <!-- 3. QUANTITIES: CRATES & LOOSE BOTTLES -->
            <div class="dashboard-grid" style="margin-top: 15px;">
                <div class="form-group">
                    <label class="form-label">Nombre de Casiers Entiers</label>
                    <input type="number" min="0" step="1" name="crates_quantity" id="input_crates_qty" class="form-control" 
                           placeholder="Ex: 50" 
                           value="<?= htmlspecialchars($flash_old['crates_quantity'] ?? '0') ?>" 
                           oninput="calculatePackagingImpact()">
                    <small style="color: #64748B; font-size: 0.8rem; margin-top: 4px; display: block;">Casiers complets (bacs + bouteilles associées)</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Bouteilles Vides Individuelles (Vrac)</label>
                    <input type="number" min="0" step="1" name="bottles_quantity" id="input_bottles_qty" class="form-control" 
                           placeholder="Ex: 12" 
                           value="<?= htmlspecialchars($flash_old['bottles_quantity'] ?? '0') ?>" 
                           oninput="calculatePackagingImpact()">
                    <small style="color: #64748B; font-size: 0.8rem; margin-top: 4px; display: block;">Bouteilles en verre individuelles non en casier</small>
                </div>
            </div>

            <!-- 4. LIVE IMPACT / VALUATION PREVIEW BOX -->
            <div id="pkg_impact_preview" style="display: none; border-radius: 8px; padding: 14px 18px; margin: 18px 0; transition: all 0.2s ease;">
                <div id="pkg_preview_title" style="font-weight: 800; font-size: 0.85rem; text-transform: uppercase;">
                    <i class='bx bx-calculator'></i> Impact sur le Parc d'Emballages :
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 6px; font-size: 0.9rem;">
                    <span id="pkg_preview_qty_label">Variation du stock physique de vides :</span>
                    <strong id="pkg_preview_stock_qty">+0 Casier</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 4px; font-size: 0.95rem;">
                    <span id="pkg_preview_val_label">Valorisation estimée du capital emballages :</span>
                    <strong style="font-size: 1.1rem;" id="pkg_preview_val_amount">0 FCFA</strong>
                </div>
                <!-- DYNAMIC ERROR MSG IF DEDUCTION > CURRENT EMPTY STOCK -->
                <div id="pkg_deduction_error" style="display: none; color: #991B1B; font-weight: 700; margin-top: 10px; font-size: 0.88rem; padding: 8px 12px; background: #FEE2E2; border-radius: 6px; border-left: 4px solid var(--c-danger);">
                    ⛔ Stock de vides insuffisant pour ce déstockage.
                </div>
            </div>

            <!-- 5. DATE & REASON -->
            <div class="form-group" style="margin-top: 10px;">
                <label class="form-label">Date du Mouvement <span style="color: var(--c-danger);">*</span></label>
                <input type="date" name="movement_date" class="form-control" required value="<?= htmlspecialchars($flash_old['movement_date'] ?? date('Y-m-d')) ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Motif Explicatif & Justification <span style="color: var(--c-danger);">*</span></label>
                <input type="text" name="notes" class="form-control" required 
                       placeholder="Ex: Stock initial au démarrage, Casse manipulation manutentionnaire, Inventaire physique fin de semaine..."
                       value="<?= htmlspecialchars($flash_old['notes'] ?? '') ?>">
            </div>

            <div style="margin-top: 25px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-accent" id="submit_pkg_btn"><i class='bx bx-check-circle'></i> Enregistrer l'Ajustement</button>
                <a href="<?= BASE_URL ?>/emballages" class="btn btn-primary" style="background-color: var(--c-gray-600);"><i class='bx bx-x'></i> Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
function onPackagingTypeChange() {
    const sel = document.getElementById("pkg_type_select");
    const infoBox = document.getElementById("pkg_current_info");
    if (sel.selectedIndex <= 0) {
        infoBox.style.display = 'none';
        calculatePackagingImpact();
        return;
    }

    const opt = sel.options[sel.selectedIndex];
    const emptyCrates = parseInt(opt.dataset.emptyCrates) || 0;
    const looseBottles = parseInt(opt.dataset.looseBottles) || 0;
    const defaultCost = parseFloat(opt.dataset.defaultCost) || 0;

    document.getElementById("info_empty_crates").innerText = `${emptyCrates} casier(s)`;
    document.getElementById("info_loose_bottles").innerText = `${looseBottles} btl(s) vrac`;
    document.getElementById("info_default_cost").innerText = `${Math.round(defaultCost).toLocaleString('fr-FR')} FCFA / casier`;
    infoBox.style.display = 'block';

    calculatePackagingImpact();
}

function calculatePackagingImpact() {
    const selPkg = document.getElementById("pkg_type_select");
    const selMov = document.getElementById("pkg_movement_type");
    const previewBox = document.getElementById("pkg_impact_preview");
    const titleEl = document.getElementById("pkg_preview_title");
    const qtyLabelEl = document.getElementById("pkg_preview_qty_label");
    const stockQtyEl = document.getElementById("pkg_preview_stock_qty");
    const valLabelEl = document.getElementById("pkg_preview_val_label");
    const valAmountEl = document.getElementById("pkg_preview_val_amount");
    const errorBox = document.getElementById("pkg_deduction_error");
    const submitBtn = document.getElementById("submit_pkg_btn");

    if (selPkg.selectedIndex <= 0) {
        previewBox.style.display = 'none';
        return;
    }

    const optPkg = selPkg.options[selPkg.selectedIndex];
    const btlsPerCrate = parseInt(optPkg.dataset.bottlesPerCrate) || 24;
    const defaultCost = parseFloat(optPkg.dataset.defaultCost) || 3600;
    const availEmptyCrates = parseInt(optPkg.dataset.emptyCrates) || 0;
    const availLooseBottles = parseInt(optPkg.dataset.looseBottles) || 0;

    const optMov = selMov.options[selMov.selectedIndex];
    const isIncoming = (optMov && optMov.dataset.direction === 'IN');

    const cratesQty = parseInt(document.getElementById("input_crates_qty").value) || 0;
    const bottlesQty = parseInt(document.getElementById("input_bottles_qty").value) || 0;

    if (cratesQty === 0 && bottlesQty === 0) {
        previewBox.style.display = 'none';
        return;
    }

    const totalCrateEquiv = cratesQty + (bottlesQty / btlsPerCrate);
    const totalValuation = totalCrateEquiv * defaultCost;

    if (isIncoming) {
        previewBox.style.background = '#F0FDF4';
        previewBox.style.border = '1px solid #BBF7D0';
        titleEl.style.color = '#15803D';
        titleEl.innerHTML = "<i class='bx bx-plus-circle'></i> Impact sur le Parc d'Emballages (Augmentation) :";
        qtyLabelEl.innerText = "Entrée dans le stock de vides du dépôt :";
        stockQtyEl.style.color = '#15803D';
        valLabelEl.innerText = "Capital emballage ajouté à la valeur du parc :";
        valAmountEl.style.color = '#15803D';
    } else {
        previewBox.style.background = '#FEF2F2';
        previewBox.style.border = '1px solid #FECACA';
        titleEl.style.color = '#991B1B';
        titleEl.innerHTML = "<i class='bx bx-minus-circle'></i> Impact sur le Parc d'Emballages (Diminution / Perte) :";
        qtyLabelEl.innerText = "Déduction du stock de vides du dépôt :";
        stockQtyEl.style.color = '#991B1B';
        valLabelEl.innerText = "Perte de capital emballages :";
        valAmountEl.style.color = '#991B1B';
    }

    previewBox.style.display = 'block';
    const sign = isIncoming ? '+' : '-';
    let qtyDesc = [];
    if (cratesQty > 0) qtyDesc.push(`${sign}${cratesQty} Casier(s)`);
    if (bottlesQty > 0) qtyDesc.push(`${sign}${bottlesQty} Bouteille(s)`);
    stockQtyEl.innerText = qtyDesc.join(' et ');
    valAmountEl.innerText = `${sign}${Math.round(totalValuation).toLocaleString('fr-FR')} FCFA`;

    // Validation for OUT movements
    if (!isIncoming && (cratesQty > availEmptyCrates || bottlesQty > availLooseBottles)) {
        let reasons = [];
        if (cratesQty > availEmptyCrates) {
            reasons.push(`${cratesQty} casier(s) demandé(s) > ${availEmptyCrates} disponible(s)`);
        }
        if (bottlesQty > availLooseBottles) {
            reasons.push(`${bottlesQty} bouteille(s) demandée(s) > ${availLooseBottles} disponible(s)`);
        }
        errorBox.innerHTML = `⛔ <strong>Stock de vides insuffisant :</strong> ${reasons.join(', ')}. Opération bloquée.`;
        errorBox.style.display = 'block';
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';
        submitBtn.style.cursor = 'not-allowed';
    } else {
        errorBox.style.display = 'none';
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
    }
}

// Initial trigger if previous old data
document.addEventListener("DOMContentLoaded", function() {
    if (document.getElementById("pkg_type_select").selectedIndex > 0) {
        onPackagingTypeChange();
    }
});
</script>
