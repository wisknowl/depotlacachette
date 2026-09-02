<div class="page-title">
    <i class='bx bx-transfer-alt'></i>
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
        <i class='bx bx-edit'></i> Déclarer une Entrée Manuelle, Casse ou Correction d'Inventaire
    </div>
    <div class="card-body">
        <form action="<?= BASE_URL ?>/stock/save" method="POST" id="stock_adjustment_form">
            
            <!-- PRODUCT SELECTION -->
            <div class="form-group">
                <label class="form-label">Produit Concerné <span style="color: var(--c-danger);">*</span></label>
                <select name="product_id" id="stock_product_select" class="form-control" required onchange="onStockProductChange()">
                    <option value="">-- Choisir un produit --</option>
                    <?php foreach ($products as $p): ?>
                        <?php 
                            $unit = \App\Core\Helper::getPackagingUnit($p['category_name'] ?? '', $p['format_name'] ?? ''); 
                            $stk = floatval($p['current_stock'] ?? 0);
                            $stkFmt = number_format($stk, ($stk == intval($stk) ? 0 : 1), ',', ' ');
                        ?>
                        <option value="<?= $p['id'] ?>" 
                                data-factor="<?= max(1, intval($p['factor'] ?: 24)) ?>"
                                data-purchase-price="<?= floatval($p['purchase_price']) ?>"
                                data-current-stock="<?= $stk ?>"
                                data-format-name="<?= htmlspecialchars($p['format_name'] ?: '') ?>"
                                data-full="<?= htmlspecialchars($unit['full']) ?>" 
                                data-demi="<?= htmlspecialchars($unit['demi']) ?>">
                            <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['format_name']) ?> &bull; Stock : <?= $stkFmt ?> <?= htmlspecialchars($unit['unit_name']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- LIVE CURRENT STOCK BADGE -->
            <div id="stock_current_badge" style="display: none; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px 16px; margin: 12px 0;">
                <div style="font-size: 0.88rem; font-weight: 700; color: #334155; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <span>📦 Stock Physique Disponible : <strong id="badge_current_stock" style="color: #0284C7;">0 casier(s)</strong></span>
                    <span>🏷️ Prix d'Achat Référence : <strong id="badge_purchase_price" style="color: var(--c-navy);">0 FCFA</strong></span>
                </div>
            </div>

            <!-- UNIT MODE TOGGLE (FULL CRATES VS INDIVIDUAL BOTTLES) -->
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px 18px; margin: 18px 0;">
                <label class="form-label" style="margin-bottom: 8px; font-weight: 800; color: var(--c-navy);">Mode de Comptage du Mouvement :</label>
                <div style="display: flex; gap: 25px; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 700; font-size: 0.95rem;">
                        <input type="radio" name="input_mode" value="casier" id="mode_casier" checked onchange="toggleStockInputMode()">
                        📦 Par Emballages Entiers / Casiers / Packs
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 700; font-size: 0.95rem; color: var(--c-danger);">
                        <input type="radio" name="input_mode" value="bouteille" id="mode_bouteille" onchange="toggleStockInputMode()">
                        🍾 Par Bouteilles / Unités Individuelles (Casse précise, perte...)
                    </label>
                </div>
            </div>

            <!-- MODE 1: CASIERS (SAISIE COMBINEE ENTIERS + DEMI) -->
            <div id="container_mode_casier">
                <div class="dashboard-grid">
                    <div class="form-group">
                        <label class="form-label" id="label_casier_full">Nombre de Casiers / Packs Entiers <span style="color: var(--c-danger);">*</span></label>
                        <input type="number" step="1" min="0" name="quantity_casier_full" id="input_quantity_casier_full" class="form-control" placeholder="Ex: 3" value="1" oninput="calculateStockImpact()">
                    </div>
                    <div class="form-group">
                        <label class="form-label" id="label_casier_demi">+ Demi-Casier / Demi-Pack Additionnel</label>
                        <select name="quantity_demi_opt" id="input_quantity_demi_opt" class="form-control" onchange="calculateStockImpact()">
                            <option value="0">Non (+ 0.0 casier)</option>
                            <option value="1">Oui (+ 1 Demi-Casier / +0.5)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- MODE 2: BOUTEILLES INDIVIDUELLES -->
            <div id="container_mode_bouteille" style="display: none;">
                <div class="form-group">
                    <label class="form-label" style="color: var(--c-danger);">Nombre Exact de Bouteilles / Unités Cassées ou Perdues <span style="color: var(--c-danger);">*</span></label>
                    <input type="number" step="1" min="1" name="quantity_bouteille" id="input_quantity_bouteille" class="form-control" placeholder="Ex: 20 bouteilles" value="1" oninput="calculateStockImpact()">
                    <small style="color: #64748B; font-size: 0.82rem; margin-top: 4px; display: block;" id="bottle_factor_hint">
                        Ce produit contient 24 bouteilles par casier.
                    </small>
                </div>
            </div>

            <!-- HIDDEN INPUTS FOR CONTROLLER COMPATIBILITY -->
            <input type="hidden" name="format_type" id="final_stock_format_type" value="casier">
            <input type="hidden" name="quantity" id="final_stock_quantity" value="1">

            <!-- LIVE IMPACT / LOSS CALCULATION BOX -->
            <div id="stock_impact_preview" style="display: none; border-radius: 8px; padding: 14px 18px; margin: 15px 0; transition: all 0.2s ease;">
                <div id="preview_title" style="font-weight: 800; font-size: 0.85rem; text-transform: uppercase;">
                    <i class='bx bx-calculator'></i> Impact sur le Stock :
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 6px; font-size: 0.9rem;">
                    <span id="preview_qty_label">Variation du stock physique :</span>
                    <strong id="preview_stock_qty">1 Casier</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 4px; font-size: 0.95rem;">
                    <span id="preview_val_label">Valorisation financière (Coût d'achat) :</span>
                    <strong style="font-size: 1.1rem;" id="preview_val_amount">0 FCFA</strong>
                </div>
                <!-- DYNAMIC ERROR MSG IF DEDUCTION > CURRENT STOCK -->
                <div id="stock_deduction_error" style="display: none; color: #991B1B; font-weight: 700; margin-top: 10px; font-size: 0.88rem; padding: 8px 12px; background: #FEE2E2; border-radius: 6px; border-left: 4px solid var(--c-danger);">
                    ⛔ Stock insuffisant pour ce déstockage.
                </div>
            </div>

            <!-- MOVEMENT TYPE & DATE -->
            <div class="dashboard-grid" style="margin-top: 10px;">
                <div class="form-group">
                    <label class="form-label">Motif / Type de Mouvement <span style="color: var(--c-danger);">*</span></label>
                    <select name="movement_type_id" id="stock_movement_type_select" class="form-control" required onchange="calculateStockImpact()">
                        <?php foreach ($types as $t): ?>
                            <option value="<?= $t['id'] ?>" data-direction="<?= $t['direction'] ?>" <?= ($t['id'] == 3) ? 'selected' : '' ?>>
                                <?= $t['direction'] === 'IN' ? '🟢' : '🔴' ?> <?= htmlspecialchars($t['name']) ?> (<?= $t['direction'] === 'IN' ? '+ Stock' : '- Stock' ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Date du Mouvement <span style="color: var(--c-danger);">*</span></label>
                    <input type="date" name="movement_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Motif Explicatif & Justification <span style="color: var(--c-danger);">*</span></label>
                <input type="text" name="reference" class="form-control" required placeholder="Ex: Stock initial ouverture, Casse transport camion 14h constaté par M. Jean, Inventaire fin de mois...">
            </div>

            <div style="margin-top: 25px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-accent" id="submit_stock_btn"><i class='bx bx-check-circle'></i> Enregistrer le Mouvement</button>
                <a href="<?= BASE_URL ?>/stock" class="btn btn-primary" style="background-color: var(--c-gray-600);"><i class='bx bx-x'></i> Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
function onStockProductChange() {
    const sel = document.getElementById("stock_product_select");
    const labelFull = document.getElementById("label_casier_full");
    const labelDemi = document.getElementById("label_casier_demi");
    const hint = document.getElementById("bottle_factor_hint");
    const badge = document.getElementById("stock_current_badge");
    const badgeStk = document.getElementById("badge_current_stock");
    const badgePrice = document.getElementById("badge_purchase_price");

    if (sel.selectedIndex <= 0) {
        badge.style.display = 'none';
        calculateStockImpact();
        return;
    }

    const opt = sel.options[sel.selectedIndex];
    const full = opt.dataset.full || 'Casiers / Packs Entiers';
    const demi = opt.dataset.demi || 'Demi-Casier';
    const factor = parseInt(opt.dataset.factor) || 24;
    const currentStock = parseFloat(opt.dataset.currentStock) || 0;
    const purchasePrice = parseFloat(opt.dataset.purchasePrice) || 0;

    labelFull.innerText = `Nombre de ${full} *`;
    labelDemi.innerText = `+ ${demi} Additionnel`;

    hint.innerText = `Ce produit contient ${factor} bouteilles/unités par emballage.`;
    badgeStk.innerText = `${currentStock} casier(s)`;
    badgeStk.style.color = (currentStock <= 0) ? 'var(--c-danger)' : '#0284C7';
    badgePrice.innerText = `${Math.round(purchasePrice).toLocaleString('fr-FR')} FCFA`;
    badge.style.display = 'block';

    calculateStockImpact();
}

function toggleStockInputMode() {
    const isBouteille = document.getElementById("mode_bouteille").checked;
    const cCasier = document.getElementById("container_mode_casier");
    const cBtl = document.getElementById("container_mode_bouteille");

    if (isBouteille) {
        cCasier.style.display = 'none';
        cBtl.style.display = 'block';
    } else {
        cCasier.style.display = 'block';
        cBtl.style.display = 'none';
    }
    calculateStockImpact();
}

function calculateStockImpact() {
    const isBouteille = document.getElementById("mode_bouteille").checked;
    const selProduct = document.getElementById("stock_product_select");
    const selMov = document.getElementById("stock_movement_type_select");
    const previewBox = document.getElementById("stock_impact_preview");
    const titleEl = document.getElementById("preview_title");
    const qtyLabelEl = document.getElementById("preview_qty_label");
    const stockQtyEl = document.getElementById("preview_stock_qty");
    const valLabelEl = document.getElementById("preview_val_label");
    const valAmountEl = document.getElementById("preview_val_amount");
    const errorBox = document.getElementById("stock_deduction_error");
    const submitBtn = document.getElementById("submit_stock_btn");
    const finalQty = document.getElementById("final_stock_quantity");
    const finalFormat = document.getElementById("final_stock_format_type");

    if (selProduct.selectedIndex <= 0) {
        previewBox.style.display = 'none';
        return;
    }

    const optProd = selProduct.options[selProduct.selectedIndex];
    const factor = parseInt(optProd.dataset.factor) || 24;
    const purchasePrice = parseFloat(optProd.dataset.purchasePrice) || 0;
    const currentStock = parseFloat(optProd.dataset.currentStock) || 0;

    const optMov = selMov.options[selMov.selectedIndex];
    const isIncoming = (optMov && optMov.dataset.direction === 'IN');

    // Configure styles and labels according to movement direction
    if (isIncoming) {
        previewBox.style.background = '#F0FDF4';
        previewBox.style.border = '1px solid #BBF7D0';
        titleEl.style.color = '#15803D';
        titleEl.innerHTML = "<i class='bx bx-plus-circle'></i> Impact sur le Stock & Valorisation du Stock Ajouté :";
        qtyLabelEl.innerText = "Augmentation du stock physique :";
        stockQtyEl.style.color = '#15803D';
        valLabelEl.innerText = "Valorisation marchande (Coût d'achat) :";
        valAmountEl.style.color = '#15803D';
    } else {
        previewBox.style.background = '#FEF2F2';
        previewBox.style.border = '1px solid #FECACA';
        titleEl.style.color = '#991B1B';
        titleEl.innerHTML = "<i class='bx bx-minus-circle'></i> Impact sur le Stock & Valeur de la Perte :";
        qtyLabelEl.innerText = "Déduction exacte du stock physique :";
        stockQtyEl.style.color = '#991B1B';
        valLabelEl.innerText = "Valeur financière de la perte (Coût d'achat) :";
        valAmountEl.style.color = '#991B1B';
    }

    let requestedCrates = 0;
    let totalValue = 0;

    if (isBouteille) {
        const btlCount = parseFloat(document.getElementById("input_quantity_bouteille").value) || 0;
        finalQty.value = btlCount;
        finalFormat.value = 'casier';

        requestedCrates = btlCount / factor;
        totalValue = (purchasePrice / factor) * btlCount;

        previewBox.style.display = 'block';
        const sign = isIncoming ? '+' : '-';
        stockQtyEl.innerText = `${sign}${requestedCrates.toFixed(4)} Casier (${btlCount} bouteille(s))`;
        valAmountEl.innerText = `${sign}${Math.round(totalValue).toLocaleString('fr-FR')} FCFA`;
    } else {
        const fullCount = parseFloat(document.getElementById("input_quantity_casier_full").value) || 0;
        const hasDemi = parseInt(document.getElementById("input_quantity_demi_opt").value) === 1;
        
        requestedCrates = fullCount + (hasDemi ? 0.5 : 0.0);
        finalQty.value = requestedCrates;
        finalFormat.value = 'casier';
        totalValue = purchasePrice * requestedCrates;

        if (purchasePrice > 0 || requestedCrates > 0) {
            previewBox.style.display = 'block';
            const sign = isIncoming ? '+' : '-';
            let detailText = `${sign}${requestedCrates} Casier(s)`;
            if (fullCount > 0 && hasDemi) {
                detailText += ` (${fullCount} entiers + 1 demi)`;
            } else if (fullCount === 0 && hasDemi) {
                detailText += ` (1 demi-casier)`;
            }
            stockQtyEl.innerText = detailText;
            valAmountEl.innerText = `${sign}${Math.round(totalValue).toLocaleString('fr-FR')} FCFA`;
        } else {
            previewBox.style.display = 'none';
        }
    }

    // Validation: cannot deduce more than available stock
    if (!isIncoming && requestedCrates > currentStock) {
        errorBox.innerHTML = `⛔ <strong>Stock insuffisant :</strong> Vous tentez de déduire <strong>${requestedCrates.toFixed(2)} casier(s)</strong> alors que le stock physique actuel est de <strong>${currentStock} casier(s)</strong>. Opération bloquée.`;
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

document.addEventListener("DOMContentLoaded", function() {
    if (document.getElementById("stock_product_select").selectedIndex > 0) {
        onStockProductChange();
    }
});
</script>