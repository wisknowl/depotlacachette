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
                            <th style="min-width: 110px;">Format</th>
                            <th style="min-width: 100px; text-align: center;">Stock Magasin</th>
                            <th style="min-width: 115px; text-align: right;">Prix Vente (FCFA)</th>
                            <th style="min-width: 110px; text-align: center;">Qté à Charger</th>
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
        <td>
            <select name="items[${tourneeRowCounter}][format_type]" class="form-control titem-format" onchange="onTourneeFormatChange(${tourneeRowCounter})">
                <option value="casier">Entier (1.0)</option>
                <option value="demi">Demi (0.5)</option>
            </select>
        </td>
        <td>
            <span id="titem_stock_badge_${tourneeRowCounter}" style="display: inline-block; padding: 4px 8px; border-radius: 6px; font-size: 0.82rem; font-weight: 700; background: #E2E8F0; color: #475569;">
                -
            </span>
        </td>
        <td>
            <input type="number" step="any" min="0" name="items[${tourneeRowCounter}][unit_price]" class="form-control titem-price" required placeholder="0" oninput="calculateTourneeTotals()" style="min-width: 105px; font-weight: 700; text-align: right; padding: 6px 8px;">
        </td>
        <td>
            <input type="number" step="1" min="1" name="items[${tourneeRowCounter}][quantity]" class="form-control titem-qty" required value="1" oninput="onTourneeQtyChange(${tourneeRowCounter})" style="min-width: 90px; font-weight: 800; font-size: 1.05rem; text-align: center; padding: 6px 8px; color: var(--c-navy-dark);">
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
        refreshTourneeFormatOptionsAcrossRows();
        calculateTourneeTotals();
    }
}

function onTourneeProductChange(id) {
    const row = document.getElementById(`trow_${id}`);
    const prodSelect = row.querySelector('.titem-product');
    const formatSelect = row.querySelector('.titem-format');
    const priceInput = row.querySelector('.titem-price');
    const stockBadge = document.getElementById(`titem_stock_badge_${id}`);

    const prodId = parseInt(prodSelect.value) || 0;
    const prod = availableProducts.find(p => p.id === prodId);

    if (!prod) {
        stockBadge.innerText = "-";
        stockBadge.style.background = "#E2E8F0";
        stockBadge.style.color = "#475569";
        priceInput.value = "";
        refreshTourneeFormatOptionsAcrossRows();
        calculateTourneeTotals();
        return;
    }

    // Check existing format rows for this product to prevent duplicate format usage
    const otherRows = Array.from(document.querySelectorAll("#tournee_tbody tr")).filter(r => r.id !== `trow_${id}`);
    const existingFormats = [];
    otherRows.forEach(r => {
        const pSel = r.querySelector('.titem-product');
        const fSel = r.querySelector('.titem-format');
        if (parseInt(pSel?.value) === prodId) {
            existingFormats.push(fSel?.value || 'casier');
        }
    });

    if (existingFormats.includes('casier') && existingFormats.includes('demi')) {
        alert(`La boisson "${prod.name}" est déjà présente dans votre chargement en format Entier et en format Demi.`);
        prodSelect.value = "";
        onTourneeProductChange(id);
        return;
    } else if (existingFormats.includes('casier')) {
        formatSelect.value = 'demi';
    } else if (existingFormats.includes('demi')) {
        formatSelect.value = 'casier';
    }

    stockBadge.innerText = `${prod.stock} dispo`;
    if (prod.stock <= 0) {
        stockBadge.style.background = "#FEE2E2";
        stockBadge.style.color = "#DC2626";
    } else {
        stockBadge.style.background = "#DCFCE7";
        stockBadge.style.color = "#16A34A";
    }

    onTourneeFormatChange(id);
}

function onTourneeFormatChange(id) {
    const row = document.getElementById(`trow_${id}`);
    const prodSelect = row.querySelector('.titem-product');
    const formatSelect = row.querySelector('.titem-format');
    const priceInput = row.querySelector('.titem-price');
    const qtyInput = row.querySelector('.titem-qty');

    const prodId = parseInt(prodSelect.value) || 0;
    const prod = availableProducts.find(p => p.id === prodId);
    if (!prod) return;

    const format = formatSelect.value;
    if (format === 'demi') {
        qtyInput.value = 1;
        qtyInput.readOnly = true;
        qtyInput.style.backgroundColor = "#F1F5F9";
        qtyInput.style.cursor = "not-allowed";
        qtyInput.title = "En demi-casier, la quantité est strictement fixée à 1.";
        priceInput.value = prod.price_demi || (prod.price_casier / 2);
    } else {
        qtyInput.readOnly = false;
        qtyInput.style.backgroundColor = "";
        qtyInput.style.cursor = "";
        qtyInput.title = "";
        if (parseFloat(qtyInput.value) <= 0) qtyInput.value = 1;
        priceInput.value = prod.price_casier || 0;
    }

    refreshTourneeFormatOptionsAcrossRows();
    calculateTourneeTotals();
}

function refreshTourneeFormatOptionsAcrossRows() {
    const allRows = document.querySelectorAll("#tournee_tbody tr");
    allRows.forEach(row => {
        const prodSelect = row.querySelector('.titem-product');
        const formatSelect = row.querySelector('.titem-format');
        const prodId = parseInt(prodSelect?.value) || 0;
        if (!prodId || !formatSelect) return;

        const optCasier = formatSelect.querySelector("option[value='casier']");
        const optDemi = formatSelect.querySelector("option[value='demi']");
        if (!optCasier || !optDemi) return;

        let hasOtherCasier = false;
        let hasOtherDemi = false;

        allRows.forEach(otherRow => {
            if (otherRow === row) return;
            const otherProd = parseInt(otherRow.querySelector('.titem-product')?.value) || 0;
            const otherFormat = otherRow.querySelector('.titem-format')?.value;
            if (otherProd === prodId) {
                if (otherFormat === 'casier') hasOtherCasier = true;
                if (otherFormat === 'demi') hasOtherDemi = true;
            }
        });

        optCasier.disabled = hasOtherCasier;
        optDemi.disabled = hasOtherDemi;
    });
}

function onTourneeQtyChange(id) {
    const row = document.getElementById(`trow_${id}`);
    const formatSelect = row?.querySelector('.titem-format');
    const qtyInput = row?.querySelector('.titem-qty');
    if (formatSelect?.value === 'demi' && qtyInput) {
        qtyInput.value = 1;
    }
    calculateTourneeTotals();
}

function calculateTourneeTotals() {
    let grandTotal = 0;
    let totalQty = 0;
    let validLines = 0;
    let hasOverdraft = false;

    // 1. Calculate aggregated stock equivalent requested per product
    const requestedStockByProd = {};
    const rows = document.querySelectorAll("#tournee_tbody tr");
    rows.forEach(tr => {
        const prodSelect = tr.querySelector('.titem-product');
        const formatSelect = tr.querySelector('.titem-format');
        const qtyInput = tr.querySelector('.titem-qty');
        const prodId = parseInt(prodSelect?.value) || 0;
        const qty = parseFloat(qtyInput?.value) || 0;
        const format = formatSelect?.value || 'casier';
        const equiv = (format === 'demi') ? (qty * 0.5) : qty;

        if (prodId > 0 && qty > 0) {
            requestedStockByProd[prodId] = (requestedStockByProd[prodId] || 0) + equiv;
        }
    });

    // 2. Validate rows against aggregated total and compute totals
    rows.forEach(tr => {
        const prodSelect = tr.querySelector('.titem-product');
        const formatSelect = tr.querySelector('.titem-format');
        const priceInput = tr.querySelector('.titem-price');
        const qtyInput = tr.querySelector('.titem-qty');
        const rowId = tr.id.replace('trow_', '');
        const lineTotalDisplay = document.getElementById(`titem_line_total_${rowId}`);
        const stockBadge = document.getElementById(`titem_stock_badge_${rowId}`);

        const prodId = parseInt(prodSelect?.value) || 0;
        const prod = availableProducts.find(p => p.id === prodId);
        const qty = parseFloat(qtyInput?.value) || 0;
        const price = parseFloat(priceInput?.value) || 0;
        const format = formatSelect?.value || 'casier';

        const lineTotal = qty * price;
        grandTotal += lineTotal;
        totalQty += qty;

        if (prod && qty > 0) {
            validLines++;
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
    document.getElementById("tournee_total_qty").innerText = totalQty;
    document.getElementById("tournee_grand_total").innerText = new Intl.NumberFormat('fr-FR').format(grandTotal) + " FCFA";

    const submitBtn = document.getElementById("submit_tournee_btn");
    submitBtn.disabled = (validLines === 0 || hasOverdraft);
}

document.addEventListener("DOMContentLoaded", function() {
    addNewTourneeItemRow();
});
</script>
