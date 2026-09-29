<style>
.form-card {
    background: white;
    border-radius: 8px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 24px;
}
.items-table th {
    background-color: var(--c-navy);
    color: white;
    font-size: 0.85rem;
    padding: 10px 8px;
}
.items-table td {
    padding: 8px 6px;
    vertical-align: middle;
}
.stock-badge {
    font-size: 0.75rem;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 600;
    display: inline-block;
}
.badge-ok { background-color: #D1FAE5; color: #065F46; }
.badge-warning { background-color: #FEF3C7; color: #92400E; }
.badge-danger { background-color: #FEE2E2; color: #991B1B; }

.modal-backdrop-custom {
    position: fixed;
    top: 0; left: 0; width: 100vw; height: 100vh;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(3px);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 99999;
}
.modal-card-custom {
    background: #FFFFFF;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
    width: 95%;
    max-width: 820px;
    max-height: 90vh;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}
</style>

<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div class="page-title" style="margin-bottom: 0;">
        <i class='bx bx-edit-alt'></i>
        <h1><?= $title ?></h1>
    </div>
    <div style="display: flex; gap: 10px;">
        <span style="padding: 6px 14px; border-radius: 20px; font-size: 0.88rem; font-weight: 800; background: #FEF3C7; color: #92400E; border: 1.5px solid #FCD34D; display: inline-flex; align-items: center; gap: 6px;">
            <i class='bx bx-time-five'></i> COMMANDE EN ATTENTE
        </span>
        <a href="<?= BASE_URL ?>/ventes/invoice/<?= $sale['id'] ?>" class="btn btn-primary" style="background-color: var(--c-gray-600);">
            <i class='bx bx-arrow-back'></i> Voir Bon de Commande
        </a>
    </div>
</div>

<?php if (!empty($flash_error)): ?>
    <div style="background: #FEE2E2; border-left: 4px solid var(--c-danger); color: #991B1B; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
        <i class='bx bx-error-circle' style="font-size: 1.4rem;"></i>
        <span><?= htmlspecialchars($flash_error) ?></span>
    </div>
<?php endif; ?>

<div style="background: #FFFBEB; border: 1.5px solid #FCD34D; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px; color: #92400E; display: flex; align-items: center; gap: 12px;">
    <i class='bx bx-info-circle' style="font-size: 1.8rem; color: #D97706; flex-shrink: 0;"></i>
    <div style="font-size: 0.9rem; line-height: 1.4;">
        <strong>Mode Ajustement de Commande :</strong> Modifiez les boissons, quantités, prix ou casiers consignés restitués à quai.
        Pour enregistrer les changements sans impacter les stocks, cliquez sur <strong>« Enregistrer les Modifications »</strong>. Pour acter la sortie réelle de stock et encaisser les fonds, cliquez sur <strong>« Valider & Encaisser »</strong>.
    </div>
</div>

<form action="<?= BASE_URL ?>/ventes/update/<?= $sale['id'] ?>" method="POST" id="ventes_edit_form">
    <input type="hidden" name="action_type" id="sale_action_type" value="update">
    
    <!-- TOP SECTION: CLIENT & DATE -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span><i class='bx bx-user-check'></i> 1. Client & Date de Facturation</span>
        </div>
        <div class="card-body">
            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Client <span style="color: var(--c-danger);">*</span></label>
                    <select name="client_id" id="vente_client_select" class="form-control" required onchange="onClientChange()">
                        <option value="">-- Sélectionner le client --</option>
                        <?php foreach ($clients as $c): ?>
                            <?php 
                                $solde = floatval($c['solde_du'] ?? 0);
                                $maxCredit = floatval($c['max_credit'] ?? 0);
                            ?>
                            <option value="<?= $c['id'] ?>" 
                                    data-debt="<?= $solde ?>" 
                                    data-max-credit="<?= $maxCredit ?>"
                                    <?= ($sale['client_id'] == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?> 
                                <?php if ($solde > 0): ?>
                                    (Dette: <?= number_format($solde, 0, ',', ' ') ?> F)
                                <?php elseif ($solde < 0): ?>
                                    (Avoir: <?= number_format(abs($solde), 0, ',', ' ') ?> F)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Date de Commande / Vente <span style="color: var(--c-danger);">*</span></label>
                    <input type="date" name="sale_date" id="vente_sale_date" class="form-control" value="<?= htmlspecialchars($sale['sale_date']) ?>" required>
                </div>
            </div>

            <!-- Client Financial Info Box -->
            <div id="client_financial_box" style="display: none; margin-top: 15px; padding: 14px 18px; border-radius: 8px; border: 1px solid #E2E8F0; background: #F8FAFC;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <span style="font-size: 0.8rem; text-transform: uppercase; font-weight: 700; color: #64748B;">Solde Financier Actuel :</span>
                        <div id="client_debt_display" style="font-size: 1.1rem; font-weight: 800;">0 FCFA</div>
                    </div>
                    <div id="client_avoir_section" style="display: none; background: #ECFDF5; border: 1.5px solid #A7F3D0; padding: 8px 14px; border-radius: 6px;">
                        <span style="font-size: 0.8rem; font-weight: 700; color: #047857; display: block;">Avoir Disponible :</span>
                        <strong id="client_avoir_val" style="font-size: 1.05rem; color: #065F46;">0 FCFA</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MIDDLE SECTION: ITEMS TABLE -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span><i class='bx bx-list-plus'></i> 2. Articles & Consignes d'Emballages</span>
            <button type="button" class="btn btn-accent btn-sm" onclick="addNewProductRow()" style="display: flex; align-items: center; gap: 5px; font-weight: 700;">
                <i class='bx bx-plus'></i> Ajouter une Boisson
            </button>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table items-table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="min-width: 260px;">Article & Format</th>
                            <th style="width: 110px;">Emballage</th>
                            <th style="width: 100px; text-align: center;">Stock Dépôt</th>
                            <th style="width: 120px;">Prix Casier</th>
                            <th style="width: 100px; text-align: center;">Qté Casiers</th>
                            <th style="width: 130px; text-align: center;">+ Demi ?</th>
                            <th style="width: 110px; text-align: center;">Casiers Rendus</th>
                            <th style="width: 110px; text-align: center;">Btls Rendues</th>
                            <?php if (\App\Core\Helper::isAdmin()): ?>
                                <th style="width: 90px; text-align: center;">
                                    <label style="font-size: 0.72rem; display: flex; flex-direction: column; align-items: center; gap: 2px; cursor: pointer; color: #E0E7FF;" title="Mettre à jour le barème de prix du catalogue officiel">
                                        <span>Maj Prix Cat.</span>
                                        <input type="checkbox" id="master_update_catalog_prices" onchange="toggleAllCatalogPrices(this.checked)">
                                    </label>
                                </th>
                            <?php endif; ?>
                            <th style="width: 130px; text-align: right;">Total Ligne</th>
                            <th style="width: 50px; text-align: center;"></th>
                        </tr>
                    </thead>
                    <tbody id="items_tbody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- BOTTOM SECTION: FINANCIAL SUMMARY & SETTLEMENT -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header">
            <span><i class='bx bx-money'></i> 3. Règlement & Mode de Paiement</span>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                <!-- Left: Remises & Avoirs -->
                <div>
                    <div class="form-group">
                        <label class="form-label">Remise / Rabais Global (FCFA)</label>
                        <input type="number" step="any" min="0" name="discount_amount" id="vente_discount" class="form-control" value="<?= floatval($sale['discount_amount'] ?? 0) ?>" oninput="calculateTotals()">
                    </div>

                    <div id="avoir_deduction_box" style="display: none; background: #ECFDF5; border: 1.5px dashed #10B981; padding: 12px 16px; border-radius: 8px; margin-top: 15px;">
                        <label class="form-label" style="color: #065F46; font-weight: 700; margin-bottom: 6px;">Déduire un Avoir Client Disponible :</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="number" step="any" min="0" name="avoir_used" id="vente_avoir_input" class="form-control" value="<?= floatval($sale['avoir_used'] ?? 0) ?>" oninput="calculateTotals()" style="font-weight: 800; color: #047857;">
                            <button type="button" class="btn btn-accent btn-sm" onclick="applyMaxAvoir()" style="white-space: nowrap; background: #059669; border-color: #059669; font-weight: 700;">
                                Max Avoir
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right: Mode de Règlement -->
                <div>
                    <label class="form-label">Type de Règlement <span style="color: var(--c-danger);">*</span></label>
                    <div style="display: flex; gap: 20px; margin-bottom: 15px; margin-top: 5px;">
                        <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                            <input type="radio" name="settlement_type" value="cash" id="settle_cash" <?= ($sale['payment_method_id'] != 5) ? 'checked' : '' ?> onchange="onSettlementChange()">
                            <span>🟢 Au Comptant (Caisse)</span>
                        </label>
                        <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                            <input type="radio" name="settlement_type" value="credit" id="settle_credit" <?= ($sale['payment_method_id'] == 5) ? 'checked' : '' ?> onchange="onSettlementChange()">
                            <span>🟡 À Crédit (Dette Client)</span>
                        </label>
                    </div>

                    <div id="cash_settlement_fields">
                        <div class="form-group">
                            <label class="form-label">Compte de Caisse (Encaissement)</label>
                            <select name="cash_account_id" id="cash_account_select" class="form-control">
                                <?php foreach ($cash_accounts as $ca): ?>
                                    <option value="<?= $ca['id'] ?>" <?= ($sale['cash_account_id'] == $ca['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ca['name']) ?> (<?= htmlspecialchars($ca['payment_method_name']) ?>) - Solde : <?= number_format($ca['current_balance'], 0, ',', ' ') ?> F
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Montant Versé / Encaissé (FCFA)</label>
                            <input type="number" step="any" min="0" name="amount_paid" id="vente_amount_paid" class="form-control" value="<?= floatval($sale['amount_paid'] ?? 0) ?>" oninput="calculateTotals()" style="font-size: 1.1rem; font-weight: 800; color: #047857;">
                        </div>
                    </div>

                    <div id="credit_settlement_fields" style="display: none; background: #FFFBEB; border-left: 4px solid #D97706; padding: 12px 16px; border-radius: 6px;">
                        <div style="font-size: 0.88rem; color: #92400E;">
                            <i class='bx bx-info-circle'></i> Cette vente sera enregistrée sous forme de <strong>créance client</strong> et augmentera le solde dû de ce client.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Global Totals Display -->
            <div style="margin-top: 25px; padding-top: 20px; border-top: 2px dashed #E2E8F0; display: flex; justify-content: flex-end;">
                <div style="width: 100%; max-width: 420px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.95rem; color: #64748B;">
                        <span>Sous-Total Brut :</span>
                        <strong id="display_subtotal">0 FCFA</strong>
                    </div>
                    <div id="display_discount_row" style="display: none; justify-content: space-between; margin-bottom: 6px; font-size: 0.95rem; color: #DC2626;">
                        <span>Remise Déduite :</span>
                        <strong id="display_discount">-0 FCFA</strong>
                    </div>
                    <div id="display_avoir_row" style="display: none; justify-content: space-between; margin-bottom: 6px; font-size: 0.95rem; color: #047857;">
                        <span>Avoir Client Déduit :</span>
                        <strong id="display_avoir">-0 FCFA</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 2px solid var(--c-navy); font-size: 1.35rem; font-weight: 900; color: #047857;">
                        <span>NET À RECOUVRER :</span>
                        <strong id="display_total">0 FCFA</strong>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid" style="margin-top: 20px;">
                <div class="form-group">
                    <label class="form-label">N° de Référence Manuelle (Optionnel)</label>
                    <input type="text" name="reference" class="form-control" value="<?= htmlspecialchars($sale['reference'] ?? '') ?>" placeholder="Ex: FACT-PROFORMA-001">
                </div>
                <div class="form-group">
                    <label class="form-label">Notes / Observations</label>
                    <input type="text" name="notes" class="form-control" value="<?= htmlspecialchars($sale['notes'] ?? '') ?>" placeholder="Ex: Commande prête pour chargement quai...">
                </div>
            </div>

            <!-- BUTTONS -->
            <div style="margin-top: 25px; display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
                <button type="submit" class="btn btn-primary" onclick="document.getElementById('sale_action_type').value='update'" style="padding: 10px 22px; font-size: 0.95rem; font-weight: 700; background: #D97706; color: #FFFFFF; border: none; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class='bx bx-save'></i> Enregistrer Modifications (Garder en attente)
                </button>
                <button type="button" class="btn btn-accent" onclick="submitValidateSale()" style="padding: 10px 24px; font-size: 1rem; font-weight: 800; background: #16A34A; border-color: #16A34A; display: inline-flex; align-items: center; gap: 6px;">
                    <i class='bx bx-check-double'></i> Valider & Encaisser Définitivement
                </button>
                <a href="<?= BASE_URL ?>/ventes/invoice/<?= $sale['id'] ?>" class="btn btn-primary" style="background-color: var(--c-gray-600); padding: 10px 18px;">
                    <i class='bx bx-x'></i> Annuler
                </a>
            </div>
        </div>
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
        'cout_emballage' => floatval($p['cout_emballage'] ?? 3600),
        'packaging_name' => $p['packaging_name'] ?? 'Casier consigné',
        'price_casier' => floatval($p['price_casier']),
        'price_demi' => floatval($p['price_demi']),
        'stock' => floatval($p['current_stock']),
        'factor' => intval($p['factor'] ?: 12)
    ];
}, $products)) ?>;

const existingSaleItems = <?= json_encode($sale['items']) ?>;
const isAdminUser = <?= \App\Core\Helper::isAdmin() ? 'true' : 'false' ?>;

let rowCounter = 0;
let globalSaleSubTotal = 0;
let globalSaleDiscount = 0;
let globalSaleTotal = 0;
let globalSaleAvoir = 0;

function addNewProductRow(prefill = null) {
    rowCounter++;
    const tbody = document.getElementById("items_tbody");
    const tr = document.createElement("tr");
    tr.id = `row_${rowCounter}`;
    tr.style.verticalAlign = "middle";

    let optionsHtml = `<option value="">-- Choisir une boisson --</option>`;
    availableProducts.forEach(p => {
        const outOfStock = (p.stock <= 0);
        const retIcon = p.is_returnable ? '🟢' : '⚪';
        const codePrefix = p.short_code ? `[${p.short_code}] ` : '';
        const stockLabel = outOfStock ? '⛔ [RUPTURE 0]' : `[Stock: ${p.stock}]`;
        const isSelected = prefill && (parseInt(prefill.product_id) === p.id);
        optionsHtml += `<option value="${p.id}" ${isSelected ? 'selected' : ''}>
            ${retIcon} ${codePrefix}${p.name} (${p.format_name}) ${stockLabel}
        </option>`;
    });

    const isMasterChecked = document.getElementById("master_update_catalog_prices")?.checked || false;
    let adminCatCell = '';
    if (isAdminUser) {
        adminCatCell = `
            <td style="text-align: center;">
                <input type="checkbox" name="items[${rowCounter}][update_catalog_price]" value="1" class="item-cat-price-chk" ${isMasterChecked ? 'checked' : ''} onchange="onItemCatPriceChange()">
            </td>
        `;
    }

    tr.innerHTML = `
        <td>
            <select name="items[${rowCounter}][product_id]" class="item-product form-control" required onchange="onProductChange(${rowCounter})">
                ${optionsHtml}
            </select>
        </td>
        <td>
            <span id="item_emb_badge_${rowCounter}" style="display: inline-block; padding: 3px 6px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; background: #F1F5F9; color: #475569;">
                -
            </span>
        </td>
        <td style="text-align: center;">
            <span class="stock-badge" id="stock_badge_${rowCounter}" style="display: inline-block; padding: 4px 6px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; background: #E2E8F0; color: #475569;">
                -
            </span>
        </td>
        <td>
            <input type="number" step="any" min="0" name="items[${rowCounter}][unit_price]" class="item-price form-control" required placeholder="0" oninput="calculateTotals()" style="min-width: 105px; font-weight: 700; text-align: right; padding: 6px 8px;">
        </td>
        <td style="text-align: center;">
            <input type="number" step="1" min="0" name="items[${rowCounter}][quantity]" class="item-qty form-control" required value="1" oninput="onQtyChange(${rowCounter})" style="min-width: 85px; font-weight: 800; font-size: 1.05rem; text-align: center; padding: 6px 8px; color: var(--c-navy-dark);">
        </td>
        <td style="text-align: center;">
            <div id="demi_container_${rowCounter}" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px;">
                <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer; font-size: 0.84rem; font-weight: 700; user-select: none; margin-bottom: 0;">
                    <input type="checkbox" name="items[${rowCounter}][has_demi]" value="1" class="item-has-demi" id="has_demi_${rowCounter}" onchange="onDemiToggle(${rowCounter})">
                    <span id="demi_label_${rowCounter}" style="color: #92400E;">+ 1 Demi</span>
                </label>
                <span id="demi_price_tag_${rowCounter}" style="font-size: 0.72rem; color: #64748B; font-weight: 600;">(0 F)</span>
                <input type="hidden" name="items[${rowCounter}][demi_unit_price]" id="demi_price_${rowCounter}" value="0">
            </div>
        </td>
        <td style="text-align: center;">
            <input type="number" step="1" min="0" name="items[${rowCounter}][crates_returned]" class="item-crates-ret form-control" value="0" placeholder="0" oninput="calculateTotals()" style="min-width: 85px; font-weight: 700; font-size: 1rem; text-align: center; padding: 6px 8px;">
        </td>
        <td style="text-align: center;">
            <input type="number" step="1" min="0" name="items[${rowCounter}][bottles_returned]" class="item-bottles-ret form-control" value="0" placeholder="0" oninput="calculateTotals()" style="min-width: 85px; font-weight: 700; font-size: 1rem; text-align: center; padding: 6px 8px;">
        </td>
        ${adminCatCell}
        <td style="text-align: right; font-weight: 800; font-size: 1.05rem; color: var(--c-navy-dark);" id="row_total_${rowCounter}">
            0 FCFA
        </td>
        <td style="text-align: center;">
            <button type="button" class="btn btn-primary" onclick="removeRow(${rowCounter})" style="background: #EF4444; padding: 4px 8px; border-radius: 6px;" title="Supprimer la ligne">
                <i class='bx bx-trash'></i>
            </button>
        </td>
    `;

    tbody.appendChild(tr);

    // Apply prefill if present
    if (prefill) {
        onProductChange(rowCounter);
        const qtyInput = tr.querySelector('.item-qty');
        if (qtyInput) qtyInput.value = prefill.quantity;

        const priceInput = tr.querySelector('.item-price');
        if (priceInput && parseFloat(prefill.unit_price) > 0) priceInput.value = prefill.unit_price;

        const hasDemiChk = document.getElementById(`has_demi_${rowCounter}`);
        if (hasDemiChk && (prefill.has_demi == 1 || prefill.format_type === 'demi')) {
            hasDemiChk.checked = true;
            onDemiToggle(rowCounter);
            if (parseFloat(prefill.demi_unit_price) > 0) {
                const demiPriceInput = document.getElementById(`demi_price_${rowCounter}`);
                if (demiPriceInput) demiPriceInput.value = prefill.demi_unit_price;
            }
        }

        const cratesRet = tr.querySelector('.item-crates-ret');
        if (cratesRet) cratesRet.value = prefill.crates_returned;

        const bottlesRet = tr.querySelector('.item-bottles-ret');
        if (bottlesRet) bottlesRet.value = prefill.bottles_returned;
    }

    calculateTotals();
}

function removeRow(id) {
    const row = document.getElementById(`row_${id}`);
    if (row) {
        row.remove();
        calculateTotals();
    }
}

function onProductChange(rowId) {
    const tr = document.getElementById(`row_${rowId}`);
    if (!tr) return;
    const prodSelect = tr.querySelector('.item-product');
    const priceInput = tr.querySelector('.item-price');
    const demiPriceInput = document.getElementById(`demi_price_${rowId}`);
    const demiTag = document.getElementById(`demi_price_tag_${rowId}`);
    const demiContainer = document.getElementById(`demi_container_${rowId}`);
    const embBadge = document.getElementById(`item_emb_badge_${rowId}`);
    const stockBadge = document.getElementById(`stock_badge_${rowId}`);

    const prodId = parseInt(prodSelect.value) || 0;
    const product = availableProducts.find(p => p.id === prodId);

    if (!product) {
        if (embBadge) embBadge.innerText = "-";
        if (stockBadge) stockBadge.innerText = "-";
        if (priceInput) priceInput.value = "";
        return;
    }

    if (priceInput) priceInput.value = product.price_casier;
    if (demiPriceInput) demiPriceInput.value = product.price_demi;
    if (demiTag) demiTag.innerText = `(${new Intl.NumberFormat('fr-FR').format(product.price_demi)} F)`;

    if (embBadge) {
        embBadge.innerText = product.is_returnable ? "🟢 Consigné" : "⚪ Perdu";
        embBadge.style.color = product.is_returnable ? "#065F46" : "#475569";
        embBadge.style.background = product.is_returnable ? "#D1FAE5" : "#F1F5F9";
    }

    if (stockBadge) {
        stockBadge.innerText = `${product.stock} cas.`;
        if (product.stock > 10) {
            stockBadge.className = "stock-badge badge-ok";
        } else if (product.stock > 0) {
            stockBadge.className = "stock-badge badge-warning";
        } else {
            stockBadge.className = "stock-badge badge-danger";
        }
    }

    calculateTotals();
}

function onQtyChange(rowId) {
    calculateTotals();
}

function onDemiToggle(rowId) {
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    const rows = document.querySelectorAll("#items_tbody tr");

    rows.forEach(tr => {
        const prodSelect = tr.querySelector('.item-product');
        const priceInput = tr.querySelector('.item-price');
        const qtyInput = tr.querySelector('.item-qty');
        const rowId = tr.id.replace('row_', '');
        const hasDemiChk = document.getElementById(`has_demi_${rowId}`);
        const demiPriceInput = document.getElementById(`demi_price_${rowId}`);
        const totalCell = document.getElementById(`row_total_${rowId}`);

        const prodId = parseInt(prodSelect?.value) || 0;
        const qty = parseFloat(qtyInput?.value) || 0;
        const price = parseFloat(priceInput?.value) || 0;
        const hasDemi = (hasDemiChk && hasDemiChk.checked) ? 1 : 0;
        const demiPrice = parseFloat(demiPriceInput?.value) || (price / 2);

        const lineTotal = (qty * price) + (hasDemi ? demiPrice : 0);
        if (totalCell) {
            totalCell.innerText = new Intl.NumberFormat('fr-FR').format(lineTotal) + " FCFA";
        }
        subtotal += lineTotal;
    });

    globalSaleSubTotal = subtotal;
    const discountInput = document.getElementById("vente_discount");
    globalSaleDiscount = Math.max(0, parseFloat(discountInput?.value) || 0);

    const grossNet = Math.max(0, subtotal - globalSaleDiscount);
    const avoirInput = document.getElementById("vente_avoir_input");
    globalSaleAvoir = Math.max(0, parseFloat(avoirInput?.value) || 0);
    globalSaleTotal = Math.max(0, grossNet - globalSaleAvoir);

    document.getElementById("display_subtotal").innerText = new Intl.NumberFormat('fr-FR').format(subtotal) + " FCFA";

    const discRow = document.getElementById("display_discount_row");
    if (globalSaleDiscount > 0) {
        discRow.style.display = "flex";
        document.getElementById("display_discount").innerText = "-" + new Intl.NumberFormat('fr-FR').format(globalSaleDiscount) + " FCFA";
    } else {
        discRow.style.display = "none";
    }

    const avoirRow = document.getElementById("display_avoir_row");
    if (globalSaleAvoir > 0) {
        avoirRow.style.display = "flex";
        document.getElementById("display_avoir").innerText = "-" + new Intl.NumberFormat('fr-FR').format(globalSaleAvoir) + " FCFA";
    } else {
        avoirRow.style.display = "none";
    }

    document.getElementById("display_total").innerText = new Intl.NumberFormat('fr-FR').format(globalSaleTotal) + " FCFA";

    // Auto-update amount paid if cash
    const isCredit = document.getElementById("settle_credit")?.checked;
    const amtPaidInput = document.getElementById("vente_amount_paid");
    if (!isCredit && amtPaidInput && (parseFloat(amtPaidInput.value) === 0 || amtPaidInput.value === '')) {
        amtPaidInput.value = globalSaleTotal;
    }
}

function onSettlementChange() {
    const isCredit = document.getElementById("settle_credit").checked;
    document.getElementById("cash_settlement_fields").style.display = isCredit ? "none" : "block";
    document.getElementById("credit_settlement_fields").style.display = isCredit ? "block" : "none";
    calculateTotals();
}

function onClientChange() {
    const select = document.getElementById("vente_client_select");
    const opt = select.options[select.selectedIndex];
    const box = document.getElementById("client_financial_box");
    const avoirBox = document.getElementById("avoir_deduction_box");
    const avoirSection = document.getElementById("client_avoir_section");

    if (!opt || !opt.value) {
        box.style.display = "none";
        avoirBox.style.display = "none";
        return;
    }

    box.style.display = "block";
    const debt = parseFloat(opt.dataset.debt || 0);
    const debtDisplay = document.getElementById("client_debt_display");

    if (debt > 0) {
        debtDisplay.innerText = `${new Intl.NumberFormat('fr-FR').format(debt)} FCFA (Dette)`;
        debtDisplay.style.color = "#DC2626";
        avoirSection.style.display = "none";
        avoirBox.style.display = "none";
    } else if (debt < 0) {
        const avoir = Math.abs(debt);
        debtDisplay.innerText = `${new Intl.NumberFormat('fr-FR').format(avoir)} FCFA (Avoir)`;
        debtDisplay.style.color = "#059669";
        avoirSection.style.display = "block";
        document.getElementById("client_avoir_val").innerText = `${new Intl.NumberFormat('fr-FR').format(avoir)} FCFA`;
        avoirBox.style.display = "block";
    } else {
        debtDisplay.innerText = "0 FCFA (Compte équilibré)";
        debtDisplay.style.color = "#16A34A";
        avoirSection.style.display = "none";
        avoirBox.style.display = "none";
    }
}

function applyMaxAvoir() {
    const select = document.getElementById("vente_client_select");
    const opt = select.options[select.selectedIndex];
    if (!opt) return;
    const debt = parseFloat(opt.dataset.debt || 0);
    if (debt < 0) {
        const avail = Math.abs(debt);
        const grossNet = Math.max(0, globalSaleSubTotal - globalSaleDiscount);
        document.getElementById("vente_avoir_input").value = Math.min(avail, grossNet);
        calculateTotals();
    }
}

function submitValidateSale() {
    if (confirm("Valider et encaisser définitivement cette commande ?\n\n- Les sorties de stock seront décomptées du dépôt.\n- La caisse ou le crédit sera enregistré.\n- La facture passera au statut Validée.")) {
        document.getElementById("sale_action_type").value = "validate";
        document.getElementById("ventes_edit_form").submit();
    }
}

document.addEventListener("DOMContentLoaded", function() {
    if (existingSaleItems && existingSaleItems.length > 0) {
        existingSaleItems.forEach(it => {
            addNewProductRow(it);
        });
    } else {
        addNewProductRow();
    }
    onClientChange();
    onSettlementChange();
    calculateTotals();
});
</script>
