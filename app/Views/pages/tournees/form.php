<style>
.table-sticky-container {
    max-height: 480px;
    overflow-y: auto;
    overflow-x: auto;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
}
.table-sticky-container table thead th {
    position: sticky;
    top: 0;
    z-index: 5;
    background: var(--c-navy) !important;
    color: #FFFFFF !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.12);
}
.titem-qty {
    min-width: 90px !important;
    font-weight: 800 !important;
    font-size: 1.05rem !important;
    text-align: center !important;
    padding: 6px 8px !important;
}
.titem-price {
    min-width: 110px !important;
    font-weight: 700 !important;
    text-align: right !important;
    padding: 6px 8px !important;
}
</style>

<div class="page-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
    <div>
        <h1 style="margin: 0; font-size: 1.4rem; font-weight: 800; color: #1E293B;"><?= $title ?></h1>
        <p style="margin: 0; font-size: 0.85rem; color: #64748B;">Saisie des marchandises sortant du magasin pour la tournée du chauffeur.</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/tournees" class="btn btn-primary" style="background-color: var(--c-gray-600); padding: 8px 16px;">
            <i class='bx bx-arrow-back'></i> Retour à la liste
        </a>
    </div>
</div>

<?php if (!empty($flash_error)): ?>
    <div style="background: #FEE2E2; border-left: 4px solid var(--c-danger); color: #991B1B; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
        <i class='bx bx-error-circle' style="font-size: 1.4rem;"></i>
        <span><?= htmlspecialchars($flash_error) ?></span>
    </div>
<?php endif; ?>

<form action="<?= BASE_URL ?>/tournees/saveNouveau" method="POST" id="tournee_loading_form">
    
    <!-- SECTION 1: EN-TÊTE CHAUFFEUR & VÉHICULE -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header">
            <span><i class='bx bx-id-card'></i> 1. Informations de Départ Tournée</span>
        </div>
        <div class="card-body">
            <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
                
                <!-- CHAUFFEUR -->
                <div class="form-group">
                    <label class="form-label">Chauffeur / Vendeur Responsable <span style="color: var(--c-danger);">*</span></label>
                    <select name="driver_id" id="tournee_driver_select" class="form-control" required style="font-weight: 700;">
                        <option value="">-- Choisir un livreur --</option>
                        <?php foreach ($drivers as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= (isset($flash_old['driver_id']) && $flash_old['driver_id'] == $d['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['name']) ?> &bull; <?= htmlspecialchars($d['role'] ?: 'Livreur') ?> <?= !empty($d['phone']) ? '(' . htmlspecialchars($d['phone']) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- DATE TOURNÉE -->
                <div class="form-group">
                    <label class="form-label">Date de Départ <span style="color: var(--c-danger);">*</span></label>
                    <input type="date" name="tournee_date" class="form-control" required value="<?= htmlspecialchars($flash_old['tournee_date'] ?? date('Y-m-d')) ?>" style="font-weight: 600;">
                </div>

                <!-- VÉHICULE -->
                <div class="form-group">
                    <label class="form-label">Véhicule / Engin Assigné</label>
                    <input type="text" name="vehicle_name" class="form-control" placeholder="Ex: Tricycle #01, Camionnette Dyna..." value="<?= htmlspecialchars($flash_old['vehicle_name'] ?? '') ?>" style="font-weight: 600;">
                </div>

                <!-- NOTES -->
                <div class="form-group">
                    <label class="form-label">Zone de Vente / Itinéraire / Notes</label>
                    <input type="text" name="notes" class="form-control" placeholder="Ex: Axe Bonabéri, Zone Marché..." value="<?= htmlspecialchars($flash_old['notes'] ?? '') ?>">
                </div>

            </div>
        </div>
    </div>

    <!-- SECTION 2: CHARGEMENT DES MARCHANDISES -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span><i class='bx bx-package'></i> 2. Boissons & Marchandises Chargées sur le Véhicule</span>
            <button type="button" class="btn btn-accent" onclick="addNewTourneeItemRow()" style="padding: 6px 14px; font-size: 0.88rem;">
                <i class='bx bx-plus-circle'></i> Ajouter une boisson
            </button>
        </div>
        <div style="padding: 10px;">
            <div class="table-sticky-container">
                <table class="table" id="tournee_items_table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="min-width: 240px;">Produit / Boisson</th>
                            <th style="min-width: 100px; text-align: center;">Stock Magasin</th>
                            <th style="min-width: 115px; text-align: right;">Prix Casier (FCFA)</th>
                            <th style="min-width: 110px; text-align: center;">Casiers Entiers</th>
                            <th style="min-width: 135px; text-align: center;">+ 1 Demi (0.5)</th>
                            <th style="min-width: 140px; text-align: right;">Valeur Ligne (FCFA)</th>
                            <th style="min-width: 50px; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="tournee_tbody">
                        <!-- Dynamic rows -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TOTALS SUMMARY BAR -->
        <div style="background: #F1F5F9; border-top: 2px solid #E2E8F0; padding: 18px 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    <span style="font-weight: 600; color: var(--c-gray-700);"><span id="tournee_lines_count">0</span> article(s)</span>
                    <span style="color: #CBD5E1;">|</span>
                    <span style="font-weight: 600; color: var(--c-navy);">Total Chargé : <strong id="tournee_total_qty">0</strong> Unité(s)</span>
                </div>
                
                <div style="text-align: right;">
                    <span style="font-size: 0.92rem; font-weight: 700; color: var(--c-gray-600); margin-right: 8px;">VALEUR TOTALE CHARGÉE :</span>
                    <span id="tournee_grand_total" style="font-size: 1.55rem; font-weight: 900; color: var(--c-navy-dark); letter-spacing: 0.5px;">0 FCFA</span>
                </div>
            </div>
        </div>
    </div>

    <!-- SUBMIT BUTTONS -->
    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-bottom: 40px;">
        <a href="<?= BASE_URL ?>/tournees" class="btn btn-primary" style="background: #64748B; padding: 10px 20px;">
            Annuler
        </a>
        <button type="submit" id="submit_tournee_btn" class="btn btn-accent" style="padding: 10px 28px; font-weight: 800; font-size: 1rem; box-shadow: 0 4px 6px -1px rgba(244, 164, 35, 0.3);">
            <i class='bx bx-check-double'></i> Valider le Chargement & Départ en Tournée
        </button>
    </div>

</form>

<script>
const availableProducts = <?= json_encode(array_map(function($p) {
    return [
        'id' => intval($p['id']),
        'name' => $p['name'],
        'short_code' => $p['short_code'] ?? '',
        'category_name' => $p['category_name'] ?? '',
        'format_name' => $p['format_name'] ?? 'Format standard',
        'is_returnable' => intval($p['is_returnable'] ?? 1),
        'price_casier' => floatval($p['price_casier'] ?? 0),
        'price_demi' => floatval($p['price_demi'] ?: ($p['price_casier'] / 2)),
        'price_unite' => floatval($p['calculated_price_unite'] ?? 0),
        'stock' => floatval($p['current_stock']),
        'factor' => intval($p['factor'] ?: 12),
        'unit_name' => 'emb.'
    ];
}, $products)) ?>;

let tourneeRowCounter = 0;

function addNewTourneeItemRow() {
    tourneeRowCounter++;
    const tbody = document.getElementById("tournee_tbody");
    const tr = document.createElement("tr");
    tr.id = `trow_${tourneeRowCounter}`;
    tr.style.verticalAlign = "middle";

    let optionsHtml = `<option value="">-- Sélectionner une boisson --</option>`;
    availableProducts.forEach(p => {
        const retIcon = p.is_returnable ? '🟢' : '⚪';
        const codePrefix = p.short_code ? `[${p.short_code}] ` : '';
        optionsHtml += `<option value="${p.id}">
            ${retIcon} ${codePrefix}${p.name} [Stock dispo: ${p.stock}]
        </option>`;
    });

    tr.innerHTML = `
        <td>
            <select name="items[${tourneeRowCounter}][product_id]" class="form-control titem-product" required onchange="onTourneeProductChange(${tourneeRowCounter})">
                ${optionsHtml}
            </select>
        </td>
        <td style="text-align: center;">
            <span id="titem_stock_badge_${tourneeRowCounter}" style="display: inline-block; padding: 4px 8px; border-radius: 6px; font-size: 0.82rem; font-weight: 700; background: #E2E8F0; color: #475569;">
                -
            </span>
        </td>
        <td>
            <input type="number" step="any" min="0" name="items[${tourneeRowCounter}][unit_price]" class="form-control titem-price" required placeholder="0" oninput="calculateTourneeTotals()" style="min-width: 105px; font-weight: 700; text-align: right; padding: 6px 8px;">
        </td>
        <td style="text-align: center;">
            <input type="number" step="1" min="0" name="items[${tourneeRowCounter}][quantity]" class="form-control titem-qty" required value="1" oninput="onTourneeQtyChange(${tourneeRowCounter})" style="min-width: 85px; font-weight: 800; font-size: 1.05rem; text-align: center; padding: 6px 8px; color: var(--c-navy-dark);" title="Nombre de casiers entiers (mettre 0 si chargement d'un demi seul)">
        </td>
        <td style="text-align: center;">
            <div id="tdemi_container_${tourneeRowCounter}" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px;">
                <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer; font-size: 0.84rem; font-weight: 700; user-select: none; margin-bottom: 0;">
                    <input type="checkbox" name="items[${tourneeRowCounter}][has_demi]" value="1" class="titem-has-demi" id="thas_demi_${tourneeRowCounter}" onchange="onTourneeDemiToggle(${tourneeRowCounter})">
                    <span id="tdemi_label_${tourneeRowCounter}" style="color: #92400E;">+ 1 Demi</span>
                </label>
                <span id="tdemi_price_tag_${tourneeRowCounter}" style="font-size: 0.72rem; color: #64748B; font-weight: 600;">(0 F)</span>
                <input type="hidden" name="items[${tourneeRowCounter}][demi_unit_price]" id="tdemi_price_${tourneeRowCounter}" value="0">
            </div>
        </td>
        <td style="text-align: right; font-weight: 800; font-size: 0.95rem; color: var(--c-navy-dark);" id="titem_line_total_${tourneeRowCounter}">
            0 FCFA
        </td>
        <td style="text-align: center;">
            <button type="button" class="btn btn-primary" onclick="removeTourneeItemRow(${tourneeRowCounter})" style="background: #EF4444; padding: 4px 8px; border-radius: 6px;" title="Supprimer la ligne">
                <i class='bx bx-trash'></i>
            </button>
        </td>
    `;

    tbody.appendChild(tr);
    calculateTourneeTotals();
}

function removeTourneeItemRow(id) {
    const row = document.getElementById(`trow_${id}`);
    if (row) {
        row.remove();
        calculateTourneeTotals();
    }
}

function onTourneeProductChange(id) {
    const row = document.getElementById(`trow_${id}`);
    const prodSelect = row.querySelector('.titem-product');
    const priceInput = row.querySelector('.titem-price');
    const qtyInput = row.querySelector('.titem-qty');
    const hasDemiChk = document.getElementById(`thas_demi_${id}`);
    const demiPriceHidden = document.getElementById(`tdemi_price_${id}`);
    const demiLabel = document.getElementById(`tdemi_label_${id}`);
    const demiPriceTag = document.getElementById(`tdemi_price_tag_${id}`);
    const stockBadge = document.getElementById(`titem_stock_badge_${id}`);

    const prodId = parseInt(prodSelect.value) || 0;
    const prod = availableProducts.find(p => p.id === prodId);

    if (!prod) {
        stockBadge.innerText = "-";
        stockBadge.style.background = "#E2E8F0";
        stockBadge.style.color = "#475569";
        priceInput.value = "";
        if (hasDemiChk) hasDemiChk.checked = false;
        if (demiPriceHidden) demiPriceHidden.value = 0;
        if (demiLabel) demiLabel.innerText = "+ 1 Demi";
        if (demiPriceTag) demiPriceTag.innerText = "(0 F)";
        calculateTourneeTotals();
        return;
    }

    // Check if this product is already selected in another row to avoid duplicate rows
    const otherRows = Array.from(document.querySelectorAll("#tournee_tbody tr")).filter(r => r.id !== `trow_${id}`);
    let alreadySelected = false;
    otherRows.forEach(r => {
        const pSel = r.querySelector('.titem-product');
        if (parseInt(pSel?.value) === prodId) {
            alreadySelected = true;
        }
    });

    if (alreadySelected) {
        alert(`La boisson "${prod.name}" est déjà présente dans votre chargement.\nVous pouvez saisir les casiers entiers et cocher "+ 1 Demi" sur sa ligne existante.`);
        prodSelect.value = "";
        onTourneeProductChange(id);
        return;
    }

    stockBadge.innerText = `${prod.stock} dispo`;
    if (prod.stock <= 0) {
        stockBadge.style.background = "#FEE2E2";
        stockBadge.style.color = "#DC2626";
    } else {
        stockBadge.style.background = "#DCFCE7";
        stockBadge.style.color = "#16A34A";
    }

    priceInput.value = prod.price_casier || 0;
    const factor = prod.factor || 12;
    const demiBottles = Math.round(factor / 2);
    const demiPrice = prod.price_demi > 0 ? prod.price_demi : Math.round((prod.price_casier || 0) / 2);

    if (demiPriceHidden) demiPriceHidden.value = demiPrice;
    if (demiLabel) demiLabel.innerText = `+ 1 Demi (${demiBottles} btls)`;
    if (demiPriceTag) demiPriceTag.innerText = `(+${new Intl.NumberFormat('fr-FR').format(demiPrice)} F)`;

    if (hasDemiChk) hasDemiChk.checked = false;
    if (parseFloat(qtyInput.value) <= 0) qtyInput.value = 1;

    calculateTourneeTotals();
}

function onTourneeDemiToggle(id) {
    const row = document.getElementById(`trow_${id}`);
    const qtyInput = row.querySelector('.titem-qty');
    const hasDemiChk = document.getElementById(`thas_demi_${id}`);

    const isChecked = hasDemiChk?.checked || false;
    if (isChecked) {
        qtyInput.min = 0;
    } else {
        qtyInput.min = 1;
        if (parseFloat(qtyInput.value) <= 0) {
            qtyInput.value = 1;
        }
    }
    calculateTourneeTotals();
}

function onTourneeQtyChange(id) {
    const row = document.getElementById(`trow_${id}`);
    const qtyInput = row?.querySelector('.titem-qty');
    const hasDemiChk = document.getElementById(`thas_demi_${id}`);
    const isChecked = hasDemiChk?.checked || false;

    let qty = parseFloat(qtyInput?.value);
    if (isNaN(qty) || qty < 0) qty = 0;
    if (!isChecked && qty < 1) qty = 1;
    if (qtyInput) qtyInput.value = qty;

    calculateTourneeTotals();
}

function calculateTourneeTotals() {
    let grandTotal = 0;
    let totalCasiersEquiv = 0;
    let validLines = 0;
    let hasOverdraft = false;

    // 1. Calculate aggregated stock equivalent requested per product
    const requestedStockByProd = {};
    const rows = document.querySelectorAll("#tournee_tbody tr");
    rows.forEach(tr => {
        const prodSelect = tr.querySelector('.titem-product');
        const qtyInput = tr.querySelector('.titem-qty');
        const rowId = tr.id.replace('trow_', '');
        const hasDemiChk = document.getElementById(`thas_demi_${rowId}`);
        const prodId = parseInt(prodSelect?.value) || 0;
        const qty = parseFloat(qtyInput?.value) || 0;
        const hasDemi = (hasDemiChk && hasDemiChk.checked) ? 1 : 0;
        const equiv = qty + (hasDemi ? 0.5 : 0.0);

        if (prodId > 0 && equiv > 0) {
            requestedStockByProd[prodId] = (requestedStockByProd[prodId] || 0) + equiv;
        }
    });

    // 2. Validate rows against aggregated total and compute totals
    rows.forEach(tr => {
        const prodSelect = tr.querySelector('.titem-product');
        const priceInput = tr.querySelector('.titem-price');
        const qtyInput = tr.querySelector('.titem-qty');
        const rowId = tr.id.replace('trow_', '');
        const hasDemiChk = document.getElementById(`thas_demi_${rowId}`);
        const demiPriceHidden = document.getElementById(`tdemi_price_${rowId}`);
        const lineTotalDisplay = document.getElementById(`titem_line_total_${rowId}`);
        const stockBadge = document.getElementById(`titem_stock_badge_${rowId}`);

        const prodId = parseInt(prodSelect?.value) || 0;
        const prod = availableProducts.find(p => p.id === prodId);
        const qty = parseFloat(qtyInput?.value) || 0;
        const hasDemi = (hasDemiChk && hasDemiChk.checked) ? 1 : 0;
        const priceCasier = parseFloat(priceInput?.value) || 0;

        // Dynamic demi price calculation based on the entered casier price
        let priceDemi = 0;
        if (prod) {
            if (priceCasier === prod.price_casier && prod.price_demi > 0) {
                priceDemi = prod.price_demi;
            } else {
                priceDemi = Math.round(priceCasier / 2);
            }
            if (demiPriceHidden) demiPriceHidden.value = priceDemi;
            const demiPriceTag = document.getElementById(`tdemi_price_tag_${rowId}`);
            if (demiPriceTag) {
                demiPriceTag.innerText = `(+${new Intl.NumberFormat('fr-FR').format(priceDemi)} F)`;
            }
        } else {
            priceDemi = parseFloat(demiPriceHidden?.value) || 0;
        }

        const lineTotal = (qty * priceCasier) + (hasDemi ? priceDemi : 0.0);
        grandTotal += lineTotal;

        const equiv = qty + (hasDemi ? 0.5 : 0.0);

        if (prod && equiv > 0) {
            validLines++;
            totalCasiersEquiv += equiv;
            const totalRequested = requestedStockByProd[prodId] || 0;
            if (totalRequested > prod.stock) {
                qtyInput.style.borderColor = "#DC2626";
                qtyInput.style.background = "#FEF2F2";
                if (stockBadge) {
                    stockBadge.style.background = "#FEE2E2";
                    stockBadge.style.color = "#DC2626";
                    stockBadge.innerText = `⛔ Dispo: ${prod.stock} (Demandé: ${totalRequested})`;
                }
                hasOverdraft = true;
            } else {
                qtyInput.style.borderColor = "";
                qtyInput.style.background = "";
                if (stockBadge) {
                    stockBadge.style.background = "#DCFCE7";
                    stockBadge.style.color = "#16A34A";
                    stockBadge.innerText = `${prod.stock} dispo`;
                }
            }
        }

        if (lineTotalDisplay) {
            lineTotalDisplay.innerText = new Intl.NumberFormat('fr-FR').format(lineTotal) + " FCFA";
        }
    });

    document.getElementById("tournee_lines_count").innerText = validLines;
    document.getElementById("tournee_total_qty").innerText = totalCasiersEquiv.toFixed(1);
    document.getElementById("tournee_grand_total").innerText = new Intl.NumberFormat('fr-FR').format(grandTotal) + " FCFA";

    const submitBtn = document.getElementById("submit_tournee_btn");
    submitBtn.disabled = (validLines === 0 || hasOverdraft);
}

document.addEventListener("DOMContentLoaded", function() {
    addNewTourneeItemRow();
});
</script>
