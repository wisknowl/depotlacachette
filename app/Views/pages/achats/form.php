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
.modal-backdrop-custom {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(3px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
}
.modal-card-custom {
    background: #FFFFFF;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
    width: 100%;
    max-width: 850px;
    max-height: 90vh;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}
</style>

<div class="page-title">
    <i class='bx bx-cart-download'></i>
    <h1><?= $title ?></h1>
</div>

<?php if (!empty($flash_error)): ?>
    <div style="background: #FEE2E2; border-left: 4px solid var(--c-danger); color: #991B1B; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
        <i class='bx bx-error-circle' style="font-size: 1.4rem;"></i>
        <span><?= htmlspecialchars($flash_error) ?></span>
    </div>
<?php endif; ?>

<form action="<?= BASE_URL ?>/achats/save" method="POST" id="achats_multi_form">
    
    <!-- TOP SECTION: SUPPLIER, INVOICE REF & DATES -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header">
            <span><i class='bx bx-buildings'></i> 1. Fournisseur, Facture Usine & Dates</span>
        </div>
        <div class="card-body">
            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Fournisseur / Brasserie <span style="color: var(--c-danger);">*</span></label>
                    <select name="supplier_id" id="supplier_select" class="form-control" required>
                        <option value="">-- Sélectionner le fournisseur (ex: Boissons du Cameroun, UCB...) --</option>
                        <?php foreach ($suppliers as $s): ?>
                            <?php $isSuppSel = (isset($flash_old['supplier_id']) && $flash_old['supplier_id'] == $s['id']); ?>
                            <option value="<?= $s['id'] ?>" data-name="<?= htmlspecialchars($s['name']) ?>" <?= $isSuppSel ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['name']) ?> <?= !empty($s['phone']) ? '(' . htmlspecialchars($s['phone']) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">N° Facture / BL Fournisseur <span style="color: var(--c-danger);">*</span></label>
                    <input type="text" name="reference" id="invoice_ref_input" class="form-control" required 
                           placeholder="Ex: DV-L4L24005/48149/26" 
                           value="<?= htmlspecialchars($flash_old['reference'] ?? '') ?>">
                    <small style="color: #64748B; font-size: 0.8rem; margin-top: 3px; display: block;">
                        Numéro officiel imprimé sur le bon de livraison ou la facture de l'usine.
                    </small>
                </div>
            </div>

            <div class="dashboard-grid" style="margin-top: 15px;">
                <div class="form-group">
                    <label class="form-label">Date d'Émission Facture Fournisseur <span style="color: var(--c-danger);">*</span></label>
                    <input type="date" name="supplier_invoice_date" id="supplier_invoice_date" class="form-control" required 
                           value="<?= htmlspecialchars($flash_old['supplier_invoice_date'] ?? date('Y-m-d')) ?>">
                    <small style="color: #64748B; font-size: 0.8rem; margin-top: 3px; display: block;">
                        Date d'édition de la facture à l'usine / centre de distribution.
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label">Date de Réception au Dépôt <span style="color: var(--c-danger);">*</span></label>
                    <input type="date" name="purchase_date" id="purchase_date" class="form-control" required 
                           value="<?= htmlspecialchars($flash_old['purchase_date'] ?? date('Y-m-d')) ?>">
                    <small style="color: #64748B; font-size: 0.8rem; margin-top: 3px; display: block;">
                        Date d'arrivée effective du camion et déchargement en magasin.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- MIDDLE SECTION: MULTI-PRODUCT ITEMS -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span><i class='bx bx-package'></i> 2. Articles Réceptionnés en Stock</span>
            <button type="button" class="btn btn-accent" onclick="addNewPurchaseRow()" style="padding: 6px 14px; font-size: 0.88rem;">
                <i class='bx bx-plus-circle'></i> Ajouter une boisson
            </button>
        </div>
        <div style="padding: 10px;">
            <div class="table-sticky-container">
                <table class="table" id="purchase_table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="min-width: 220px;">Produit / Boisson</th>
                            <th style="min-width: 120px;">Emballage & Vides Dispo</th>
                            <th style="min-width: 110px;">Format</th>
                            <th style="min-width: 80px;">Stock</th>
                            <th style="min-width: 110px;">Prix Liquide (FCFA)</th>
                            <th style="min-width: 80px;">Qté Livrée</th>
                            <th style="min-width: 110px;" title="Nombre de casiers vides remis immédiatement au camion de livraison">Vides Remis</th>
                            <th style="min-width: 210px;" title="Mode de règlement en cas de déficit de casiers vides (Consigne facturée vs Dette fournisseur)">Règlement Déficit Vides</th>
                            <th style="min-width: 90px;" title="Ristourne unitaire attendue">Ristourne/U</th>
                            <th style="min-width: 130px; text-align: right;">Total Ligne (FCFA)</th>
                            <th style="min-width: 45px; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="purchase_tbody">
                        <!-- Dynamic rows will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TOTALS BAR WITH ADDITIONAL FEES / CHARGE SUR FACTURE -->
        <div style="background: #F1F5F9; border-top: 2px solid #E2E8F0; padding: 18px 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    <span style="font-weight: 600; color: var(--c-gray-700);"><span id="purchase_lines_count">0</span> article(s)</span>
                    <span style="color: #CBD5E1;">|</span>
                    <span style="font-weight: 600; color: var(--c-navy);">Entrée Stock : <strong id="purchase_casiers_equiv">0</strong> Unité(s)</span>
                    <span style="color: #CBD5E1;">|</span>
                    <span style="font-weight: 700; color: #059669;" title="Casiers vides rendus au camion"><i class='bx bx-export'></i> Vides Remis : <strong id="purchase_total_empties">0</strong> casier(s)</span>
                    <span style="color: #CBD5E1;">|</span>
                    <span style="font-weight: 700; color: #0284C7;" title="Consignes facturées sur ce bon"><i class='bx bx-archive'></i> Consignes Facturées : <strong id="purchase_total_emballage">0 FCFA</strong></span>
                    <span style="color: #CBD5E1;">|</span>
                    <span style="font-weight: 700; color: #D97706;" title="Casiers avancés par le fournisseur en dette"><i class='bx bx-time'></i> Dettes Emballages : <strong id="purchase_total_debt_crates">0</strong> casier(s)</span>
                    <span style="color: #CBD5E1;">|</span>
                    <span style="font-weight: 700; color: #6366F1;"><i class='bx bx-gift'></i> Ristournes : <strong id="purchase_total_ristourne">0 FCFA</strong></span>
                </div>
                
                <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label for="purchase_additional_fees" style="font-weight: 700; font-size: 0.88rem; color: #475569; white-space: nowrap;" title="Arrondi centimes, timbres fiscaux, frais de manutention (+/-)">
                            <i class='bx bx-plus-minus'></i> Frais / Écart Facture (+/-) :
                        </label>
                        <input type="number" step="any" name="additional_fees" id="purchase_additional_fees" class="form-control" style="width: 110px; font-weight: 700; text-align: right;" value="0" placeholder="0" oninput="calculatePurchaseTotals()">
                    </div>

                    <div style="text-align: right;">
                        <span style="font-size: 0.92rem; font-weight: 700; color: var(--c-gray-600); margin-right: 8px;">TOTAL NET FACTURE :</span>
                        <span id="purchase_grand_total" style="font-size: 1.55rem; font-weight: 900; color: var(--c-navy-dark); letter-spacing: 0.5px;">0 FCFA</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BOTTOM SECTION: SETTLEMENT -->
    <div class="card" style="margin-bottom: 25px;">
        <div class="card-header">
            <span><i class='bx bx-credit-card'></i> 3. Règlement Fournisseur</span>
        </div>
        <div class="card-body">
            <div style="display: flex; gap: 25px; margin-bottom: 18px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 700; font-size: 1rem;">
                    <input type="radio" name="settlement_type" value="cash" id="purchase_settle_cash" checked onchange="togglePurchaseSettlement()">
                    🟢 Au Comptant (Paiement Immédiat)
                </label>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 700; font-size: 1rem;">
                    <input type="radio" name="settlement_type" value="credit" id="purchase_settle_credit" onchange="togglePurchaseSettlement()">
                    🟡 À Crédit (Dette Fournisseur)
                </label>
            </div>

            <!-- COMPTE DE DECAISSEMENT -->
            <div id="purchase_cash_container">
                <div class="form-group">
                    <label class="form-label">Compte de Décaissement (Caisse Débitrice) <span style="color: var(--c-danger);">*</span></label>
                    <select name="cash_account_id" id="purchase_cash_account" class="form-control" required onchange="calculatePurchaseTotals()">
                        <option value="">-- Choisir le compte qui paye la facture --</option>
                        <?php foreach ($cash_accounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>" data-name="<?= htmlspecialchars($acc['name']) ?>" data-balance="<?= $acc['current_balance'] ?>">
                                <?= htmlspecialchars($acc['name']) ?> (<?= htmlspecialchars($acc['payment_method_name']) ?>) &mdash; Solde disponible : <?= number_format($acc['current_balance'], 0, ',', ' ') ?> FCFA
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="cash_overdraft_warning" style="display: none; padding: 12px 16px; background: #FEE2E2; border-left: 4px solid var(--c-danger); border-radius: 6px; font-size: 0.88rem; color: #991B1B; margin-top: 10px; font-weight: 700;"></div>
            </div>

            <!-- CREDIT NOTICE -->
            <div id="purchase_credit_notice" style="display: none; padding: 14px 18px; background: #FEF3C7; border-left: 4px solid var(--c-amber); border-radius: 8px; font-size: 0.9rem; color: #92400E;">
                <i class='bx bx-info-circle' style="font-size: 1.25rem; vertical-align: middle;"></i>
                <strong>Achat à Crédit :</strong> Aucun décaissement immédiat. Ce montant sera enregistré dans les <strong>Dettes Fournisseurs</strong>.
            </div>

            <div class="form-group" style="margin-top: 15px;">
                <label class="form-label">Notes / Observations Générales</label>
                <input type="text" name="notes" id="purchase_notes_input" class="form-control" placeholder="Ex: Chauffeur SABC M. Pierre, camion N° LT-..., emballages intacts..." value="<?= htmlspecialchars($flash_old['notes'] ?? '') ?>">
            </div>

            <div style="margin-top: 25px; display: flex; gap: 12px;">
                <button type="button" class="btn btn-accent" id="submit_purchase_btn" onclick="openPurchaseConfirmModal()" style="padding: 10px 24px; font-size: 1rem; font-weight: 700;">
                    <i class='bx bx-check-circle'></i> Vérifier & Enregistrer la Réception
                </button>
                <a href="<?= BASE_URL ?>/achats" class="btn btn-primary" style="background-color: var(--c-gray-600); padding: 10px 20px;">
                    <i class='bx bx-x'></i> Annuler
                </a>
            </div>
        </div>
    </div>
</form>

<!-- DOUBLE-CHECK CONFIRMATION MODAL -->
<div id="purchase_confirm_modal" class="modal-backdrop-custom">
    <div class="modal-card-custom">
        <div style="padding: 18px 24px; background: var(--c-navy); color: #FFF; border-top-left-radius: 12px; border-top-right-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                <i class='bx bx-shield-quarter' style="font-size: 1.4rem; color: #38BDF8;"></i>
                Confirmation de la Réception d'Achat (Double-Check)
            </h3>
            <button type="button" onclick="closePurchaseConfirmModal()" style="background: none; border: none; color: #FFF; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>

        <div style="padding: 20px 24px; overflow-y: auto;">
            <!-- SUPPLIER HIGHLIGHT BADGE -->
            <div style="background: #F0F9FF; border: 1.5px solid #BAE6FD; border-radius: 8px; padding: 14px 18px; margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <span style="font-size: 0.8rem; text-transform: uppercase; font-weight: 700; color: #0284C7; display: block;">Fournisseur Destinataire</span>
                    <strong id="modal_supp_name" style="font-size: 1.25rem; color: #0C4A6E;">-</strong>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 0.8rem; color: #64748B; display: block;">N° Facture / BL Fournisseur</span>
                    <strong id="modal_invoice_ref" style="font-size: 1.05rem; color: var(--c-navy);">-</strong>
                </div>
            </div>

            <!-- DATES & SETTLEMENT SUMMARY -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 18px;">
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 10px 14px;">
                    <span style="font-size: 0.75rem; color: #64748B;">Date Réception :</span>
                    <strong id="modal_purchase_date" style="display: block; color: #1E293B;">-</strong>
                </div>
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 10px 14px;">
                    <span style="font-size: 0.75rem; color: #64748B;">Mode de Règlement :</span>
                    <strong id="modal_settle_type" style="display: block; color: #1E293B;">-</strong>
                </div>
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 10px 14px;">
                    <span style="font-size: 0.75rem; color: #64748B;">Compte / Caisse :</span>
                    <strong id="modal_cash_acc" style="display: block; color: #1E293B;">-</strong>
                </div>
            </div>

            <!-- ITEMS TABLE -->
            <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 8px; color: #334155;">
                <i class='bx bx-list-check'></i> Détail des Boissons & Emballages Réceptionnés :
            </h4>
            <div style="border: 1px solid #E2E8F0; border-radius: 6px; max-height: 220px; overflow-y: auto; margin-bottom: 18px;">
                <table class="table" style="font-size: 0.85rem; margin-bottom: 0;">
                    <thead>
                        <tr style="background: #F1F5F9;">
                            <th>Produit</th>
                            <th>Format</th>
                            <th>Quantité</th>
                            <th>Prix Unit.</th>
                            <th>Vides Remis</th>
                            <th>Consigne / Dette</th>
                            <th style="text-align: right;">Total Ligne</th>
                        </tr>
                    </thead>
                    <tbody id="modal_items_tbody">
                        <!-- Filled by JS -->
                    </tbody>
                </table>
            </div>

            <!-- FINANCIAL SUMMARY -->
            <div style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 8px; padding: 14px 18px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.9rem;">
                    <span>Sous-total Boissons :</span>
                    <strong id="modal_drinks_subtotal">0 FCFA</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.9rem; color: #0284C7;">
                    <span>Consignes Emballages Facturées :</span>
                    <strong id="modal_emb_total">0 FCFA</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.9rem; color: #D97706;">
                    <span>Dette Emballages Fournisseur :</span>
                    <strong id="modal_debt_crates_total">0 casier(s)</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem; color: #64748B;" id="modal_fees_row">
                    <span>Frais Annexes / Écart de Facturation :</span>
                    <strong id="modal_fees_total">0 FCFA</strong>
                </div>
                <div style="border-top: 2px solid #CBD5E1; padding-top: 8px; display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: 900; color: var(--c-navy);">
                    <span>TOTAL FACTURE FOURNISSEUR :</span>
                    <span id="modal_grand_total" style="color: #0284C7;">0 FCFA</span>
                </div>
            </div>
        </div>

        <div style="padding: 16px 24px; background: #F1F5F9; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; display: flex; justify-content: flex-end; gap: 12px;">
            <button type="button" class="btn btn-primary" onclick="closePurchaseConfirmModal()" style="background-color: var(--c-gray-600); padding: 9px 18px;">
                <i class='bx bx-edit'></i> Modifier / Corriger
            </button>
            <button type="button" class="btn btn-accent" onclick="submitFinalPurchase()" style="padding: 9px 24px; font-weight: 800;">
                <i class='bx bx-check-double'></i> Confirmer et Enregistrer Définitivement
            </button>
        </div>
    </div>
</div>

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
        'packaging_type_id' => intval($p['packaging_type_id'] ?? 0),
        'available_empty_crates' => intval($p['available_empty_crates'] ?? 0),
        'purchase_price' => floatval($p['purchase_price']),
        'stock' => floatval($p['current_stock']),
        'factor' => intval($p['factor']),
        'unit_name' => 'emb.'
    ];
}, $products)) ?>;

let purchaseRowCounter = 0;
let globalPurchaseTotal = 0;

function addNewPurchaseRow() {
    purchaseRowCounter++;
    const tbody = document.getElementById("purchase_tbody");
    const tr = document.createElement("tr");
    tr.id = `prow_${purchaseRowCounter}`;
    tr.style.verticalAlign = "middle";

    let optionsHtml = `<option value="">-- Choisir une boisson --</option>`;
    availableProducts.forEach(p => {
        const retIcon = p.is_returnable ? '🟢' : '⚪';
        const codePrefix = p.short_code ? `[${p.short_code}] ` : '';
        const emptyBadge = p.is_returnable ? ` (Vides: ${p.available_empty_crates} c.)` : '';
        optionsHtml += `<option value="${p.id}">
            ${retIcon} ${codePrefix}${p.name} [Stock: ${p.stock}]${emptyBadge}
        </option>`;
    });

    tr.innerHTML = `
        <td>
            <select name="items[${purchaseRowCounter}][product_id]" class="form-control pitem-product" required onchange="onPurchaseProductChange(${purchaseRowCounter})">
                ${optionsHtml}
            </select>
        </td>
        <td>
            <div id="pitem_emb_container_${purchaseRowCounter}" style="display: flex; flex-direction: column; gap: 3px;">
                <span id="pitem_emb_badge_${purchaseRowCounter}" style="display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; background: #F1F5F9; color: #475569;">
                    -
                </span>
                <span id="pitem_empty_stock_${purchaseRowCounter}" style="display: none; font-size: 0.72rem; font-weight: 700;">
                    -
                </span>
            </div>
        </td>
        <td>
            <select name="items[${purchaseRowCounter}][format_type]" class="form-control pitem-format" onchange="calculatePurchaseTotals()">
                <option value="casier">Entier (1.0)</option>
                <option value="demi">Demi (0.5)</option>
            </select>
        </td>
        <td>
            <span id="pstock_badge_${purchaseRowCounter}" style="display: inline-block; padding: 4px 6px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; background: #E2E8F0; color: #475569;">
                -
            </span>
        </td>
        <td>
            <input type="number" step="any" min="0" name="items[${purchaseRowCounter}][unit_price]" class="form-control pitem-price" required placeholder="0" oninput="calculatePurchaseTotals()">
        </td>
        <td>
            <input type="number" step="1" min="1" name="items[${purchaseRowCounter}][quantity]" class="form-control pitem-qty" required value="1" oninput="onPurchaseQtyChange(${purchaseRowCounter})">
        </td>
        <td>
            <div style="display: flex; flex-direction: column; gap: 2px;">
                <input type="number" step="1" min="0" name="items[${purchaseRowCounter}][empties_returned]" class="form-control pitem-empties" value="0" oninput="onEmptiesReturnedChange(${purchaseRowCounter})">
                <small id="pitem_empty_warn_${purchaseRowCounter}" style="display: none; color: #DC2626; font-size: 0.7rem; font-weight: 700;"></small>
            </div>
        </td>
        <td>
            <div id="pitem_mode_container_${purchaseRowCounter}" style="display: flex; flex-direction: column; gap: 3px;">
                <select name="items[${purchaseRowCounter}][packaging_mode]" class="form-control pitem-mode" style="font-size: 0.78rem; padding: 3px 6px;" onchange="calculatePurchaseTotals()">
                    <option value="charge">🔵 Facturer Consigne (+3 600 F/c.)</option>
                    <option value="debt">🔴 Dette Fournisseur (+casiers dus)</option>
                </select>
                <input type="hidden" name="items[${purchaseRowCounter}][emballage_cost]" class="pitem-embcost" value="3600">
                <small id="pitem_mode_desc_${purchaseRowCounter}" style="font-size: 0.72rem; color: #64748B;">Échange 1:1 équilibré</small>
            </div>
        </td>
        <td>
            <input type="number" step="any" min="0" name="items[${purchaseRowCounter}][ristourne_unit]" class="form-control pitem-ristourne" placeholder="0" value="0" oninput="calculatePurchaseTotals()" title="Ristourne unitaire accordée" disabled>
        </td>
        <td style="text-align: right; font-weight: 800; font-size: 0.95rem; color: var(--c-navy-dark);" id="prow_total_${purchaseRowCounter}">
            0 FCFA
        </td>
        <td style="text-align: center;">
            <button type="button" class="btn btn-primary" onclick="removePurchaseRow(${purchaseRowCounter})" style="background: #EF4444; padding: 4px 8px; border-radius: 6px;" title="Supprimer la ligne">
                <i class='bx bx-trash'></i>
            </button>
        </td>
    `;

    tbody.appendChild(tr);
    calculatePurchaseTotals();
}

function removePurchaseRow(id) {
    const row = document.getElementById(`prow_${id}`);
    if (row) {
        row.remove();
        refreshPurchaseFormatOptionsAcrossRows();
        calculatePurchaseTotals();
    }
}

function onPurchaseProductChange(id) {
    const row = document.getElementById(`prow_${id}`);
    const prodSelect = row.querySelector('.pitem-product');
    const formatSelect = row.querySelector('.pitem-format');
    const priceInput = row.querySelector('.pitem-price');
    const qtyInput = row.querySelector('.pitem-qty');
    const emptiesInput = row.querySelector('.pitem-empties');
    const embCostInput = row.querySelector('.pitem-embcost');
    const modeSelect = row.querySelector('.pitem-mode');
    const embBadge = document.getElementById(`pitem_emb_badge_${id}`);
    const emptyStockSpan = document.getElementById(`pitem_empty_stock_${id}`);
    const stockBadge = document.getElementById(`pstock_badge_${id}`);

    const prodId = parseInt(prodSelect.value) || 0;
    const prod = availableProducts.find(p => p.id === prodId);

    if (!prod) {
        stockBadge.innerText = "-";
        embBadge.innerText = "-";
        emptyStockSpan.style.display = "none";
        priceInput.value = "";
        refreshPurchaseFormatOptionsAcrossRows();
        calculatePurchaseTotals();
        return;
    }

    // Check existing format rows for this product
    const otherRows = Array.from(document.querySelectorAll("#purchase_tbody tr")).filter(r => r.id !== `prow_${id}`);
    const existingFormats = [];
    otherRows.forEach(r => {
        const pSel = r.querySelector('.pitem-product');
        const fSel = r.querySelector('.pitem-format');
        if (parseInt(pSel?.value) === prodId) {
            existingFormats.push(fSel?.value || 'casier');
        }
    });

    if (existingFormats.includes('casier') && existingFormats.includes('demi')) {
        alert(`La boisson "${prod.name}" est déjà présente dans votre bon en format Entier et en format Demi.`);
        prodSelect.value = "";
        onPurchaseProductChange(id);
        return;
    } else if (existingFormats.includes('casier')) {
        formatSelect.value = 'demi';
    } else if (existingFormats.includes('demi')) {
        formatSelect.value = 'casier';
    }

    stockBadge.innerText = `${prod.stock} emb.`;
    priceInput.value = prod.purchase_price || 0;

    if (prod.is_returnable) {
        const avail = prod.available_empty_crates || 0;
        embBadge.innerHTML = `<span style="color: #16A34A; font-weight: 700;">🟢 Consigné</span>`;
        emptyStockSpan.innerHTML = (avail > 0) ? `<span style="color: #16A34A;">🟢 ${avail} v. dispo</span>` : `<span style="color: #DC2626;">🔴 0 v. dispo</span>`;
        emptyStockSpan.style.display = "inline-block";

        const curQty = parseFloat(qtyInput.value) || 1;
        emptiesInput.value = Math.min(curQty, avail);
        emptiesInput.disabled = false;
        embCostInput.value = prod.cout_emballage || 3600;
        modeSelect.disabled = false;
    } else {
        embBadge.innerHTML = `<span style="color: #64748B;">⚪ Perdu</span>`;
        emptyStockSpan.style.display = "none";
        emptiesInput.value = 0;
        emptiesInput.disabled = true;
        embCostInput.value = 0;
        modeSelect.disabled = true;
    }

    onEmptiesReturnedChange(id);
    onPurchaseFormatChange(id);
}

function onPurchaseFormatChange(id) {
    const row = document.getElementById(`prow_${id}`);
    const prodSelect = row.querySelector('.pitem-product');
    const formatSelect = row.querySelector('.pitem-format');
    const qtyInput = row.querySelector('.pitem-qty');

    const format = formatSelect.value;
    if (format === 'demi') {
        qtyInput.value = 1;
        qtyInput.readOnly = true;
        qtyInput.style.backgroundColor = "#F1F5F9";
        qtyInput.style.cursor = "not-allowed";
        qtyInput.title = "En demi-casier, la quantité est strictement fixée à 1.";
    } else {
        qtyInput.readOnly = false;
        qtyInput.style.backgroundColor = "";
        qtyInput.style.cursor = "";
        qtyInput.title = "";
        if (parseFloat(qtyInput.value) <= 0) qtyInput.value = 1;
    }

    refreshPurchaseFormatOptionsAcrossRows();
    onPurchaseQtyChange(id);
}

function refreshPurchaseFormatOptionsAcrossRows() {
    const allRows = document.querySelectorAll("#purchase_tbody tr");
    allRows.forEach(row => {
        const prodSelect = row.querySelector('.pitem-product');
        const formatSelect = row.querySelector('.pitem-format');
        const prodId = parseInt(prodSelect?.value) || 0;
        if (!prodId || !formatSelect) return;

        const optCasier = formatSelect.querySelector("option[value='casier']");
        const optDemi = formatSelect.querySelector("option[value='demi']");
        if (!optCasier || !optDemi) return;

        let hasOtherCasier = false;
        let hasOtherDemi = false;

        allRows.forEach(otherRow => {
            if (otherRow === row) return;
            const otherProd = parseInt(otherRow.querySelector('.pitem-product')?.value) || 0;
            const otherFormat = otherRow.querySelector('.pitem-format')?.value;
            if (otherProd === prodId) {
                if (otherFormat === 'casier') hasOtherCasier = true;
                if (otherFormat === 'demi') hasOtherDemi = true;
            }
        });

        optCasier.disabled = hasOtherCasier;
        optDemi.disabled = hasOtherDemi;
    });
}

function onPurchaseQtyChange(id) {
    const row = document.getElementById(`prow_${id}`);
    const prodSelect = row.querySelector('.pitem-product');
    const qtyInput = row.querySelector('.pitem-qty');
    const emptiesInput = row.querySelector('.pitem-empties');

    const prodId = parseInt(prodSelect.value) || 0;
    const prod = availableProducts.find(p => p.id === prodId);
    const qty = parseFloat(qtyInput.value) || 0;

    if (prod && prod.is_returnable && !emptiesInput.disabled) {
        const avail = prod.available_empty_crates || 0;
        // Smart default: min(qty, available)
        emptiesInput.value = Math.min(qty, avail);
    }

    onEmptiesReturnedChange(id);
}

function onEmptiesReturnedChange(id) {
    const row = document.getElementById(`prow_${id}`);
    const prodSelect = row.querySelector('.pitem-product');
    const qtyInput = row.querySelector('.pitem-qty');
    const emptiesInput = row.querySelector('.pitem-empties');
    const warn = document.getElementById(`pitem_empty_warn_${id}`);

    const prodId = parseInt(prodSelect.value) || 0;
    const prod = availableProducts.find(p => p.id === prodId);
    const qty = parseFloat(qtyInput.value) || 0;
    const empties = parseFloat(emptiesInput.value) || 0;
    const avail = prod ? (prod.available_empty_crates || 0) : 0;

    if (prod && prod.is_returnable) {
        if (empties > avail) {
            emptiesInput.style.borderColor = "#DC2626";
            emptiesInput.style.backgroundColor = "#FEF2F2";
            warn.innerText = `⛔ Stock max : ${avail} c.`;
            warn.style.display = "block";
        } else if (empties > qty) {
            emptiesInput.style.borderColor = "#DC2626";
            emptiesInput.style.backgroundColor = "#FEF2F2";
            warn.innerText = `⛔ Max livrés : ${qty} c.`;
            warn.style.display = "block";
        } else {
            emptiesInput.style.borderColor = "";
            emptiesInput.style.backgroundColor = "";
            warn.style.display = "none";
        }
    } else {
        emptiesInput.style.borderColor = "";
        emptiesInput.style.backgroundColor = "";
        warn.style.display = "none";
    }

    calculatePurchaseTotals();
}

function calculatePurchaseTotals() {
    let grandTotal = 0;
    let drinksSubtotal = 0;
    let totalEmballageSurcharge = 0;
    let totalDebtCrates = 0;
    let totalEmptiesReturned = 0;
    let totalCasiersEquiv = 0;
    let totalExpectedRistourne = 0;
    let validLines = 0;
    let hasEmptiesOverdraft = false;

    // Track total empties returned per packaging type across all rows
    const emptiesByPkg = {};

    const rows = document.querySelectorAll("#purchase_tbody tr");
    rows.forEach(tr => {
        const prodSelect = tr.querySelector('.pitem-product');
        const formatSelect = tr.querySelector('.pitem-format');
        const priceInput = tr.querySelector('.pitem-price');
        const ristourneInput = tr.querySelector('.pitem-ristourne');
        const qtyInput = tr.querySelector('.pitem-qty');
        const emptiesInput = tr.querySelector('.pitem-empties');
        const embCostInput = tr.querySelector('.pitem-embcost');
        const modeSelect = tr.querySelector('.pitem-mode');
        const rowId = tr.id.replace('prow_', '');
        const rowTotalDisplay = document.getElementById(`prow_total_${rowId}`);
        const modeDesc = document.getElementById(`pitem_mode_desc_${rowId}`);

        const prodId = parseInt(prodSelect.value) || 0;
        const prod = availableProducts.find(p => p.id === prodId);
        const qty = parseFloat(qtyInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        const ristourne = parseFloat(ristourneInput ? ristourneInput.value : 0) || 0;
        const format = formatSelect.value;

        const empties = prod && prod.is_returnable ? (parseFloat(emptiesInput.value) || 0) : 0;
        const embCost = prod && prod.is_returnable ? (parseFloat(embCostInput.value) || 0) : 0;
        const deficit = Math.max(0, qty - empties);
        const mode = modeSelect ? modeSelect.value : 'charge';

        let lineEmbCost = 0;
        let lineDebtCrates = 0;

        if (prod && prod.is_returnable) {
            const pkgId = prod.packaging_type_id || prod.id;
            emptiesByPkg[pkgId] = (emptiesByPkg[pkgId] || 0) + empties;
            if (emptiesByPkg[pkgId] > (prod.available_empty_crates || 0)) {
                hasEmptiesOverdraft = true;
            }

            if (deficit === 0) {
                if (modeDesc) {
                    modeDesc.innerText = "Échange 1:1 équilibré";
                    modeDesc.style.color = "#16A34A";
                }
            } else if (mode === 'charge') {
                lineEmbCost = deficit * embCost;
                if (modeDesc) {
                    modeDesc.innerText = `+${new Intl.NumberFormat('fr-FR').format(lineEmbCost)} F consigne facturée`;
                    modeDesc.style.color = "#0284C7";
                }
            } else {
                lineDebtCrates = deficit;
                if (modeDesc) {
                    modeDesc.innerText = `+${deficit} c. en dette fournisseur`;
                    modeDesc.style.color = "#D97706";
                }
            }
        } else {
            if (modeDesc) {
                modeDesc.innerText = "-";
                modeDesc.style.color = "#64748B";
            }
        }

        const drinkTotal = qty * price;
        const lineTotal = drinkTotal + lineEmbCost;

        drinksSubtotal += drinkTotal;
        grandTotal += lineTotal;
        totalEmballageSurcharge += lineEmbCost;
        totalDebtCrates += lineDebtCrates;
        totalEmptiesReturned += empties;
        totalExpectedRistourne += (qty * ristourne);

        if (rowTotalDisplay) {
            let displayHtml = `<strong>${new Intl.NumberFormat('fr-FR').format(lineTotal)} F</strong>`;
            if (lineEmbCost > 0) {
                displayHtml += `<br><small style="font-size: 0.72rem; color: #0284C7; font-weight: 700;">+${new Intl.NumberFormat('fr-FR').format(lineEmbCost)} F consigne</small>`;
            } else if (lineDebtCrates > 0) {
                displayHtml += `<br><small style="font-size: 0.72rem; color: #D97706; font-weight: 700;">+${lineDebtCrates} c. dette</small>`;
            }
            rowTotalDisplay.innerHTML = displayHtml;
        }

        if (prod && qty > 0) {
            validLines++;
            const equiv = (format === "demi") ? (qty * 0.5) : qty;
            totalCasiersEquiv += equiv;
        }
    });

    const addFeesInput = document.getElementById("purchase_additional_fees");
    const additionalFees = parseFloat(addFeesInput ? addFeesInput.value : 0) || 0;
    grandTotal += additionalFees;

    globalPurchaseTotal = grandTotal;
    document.getElementById("purchase_lines_count").innerText = validLines;
    document.getElementById("purchase_casiers_equiv").innerText = totalCasiersEquiv.toFixed(1);
    document.getElementById("purchase_total_empties").innerText = totalEmptiesReturned;
    document.getElementById("purchase_total_emballage").innerText = new Intl.NumberFormat('fr-FR').format(totalEmballageSurcharge) + " FCFA";
    document.getElementById("purchase_total_debt_crates").innerText = totalDebtCrates;
    document.getElementById("purchase_total_ristourne").innerText = new Intl.NumberFormat('fr-FR').format(totalExpectedRistourne) + " FCFA";
    document.getElementById("purchase_grand_total").innerText = new Intl.NumberFormat('fr-FR').format(grandTotal) + " FCFA";

    validatePurchaseGuards(validLines, hasEmptiesOverdraft);
}

function togglePurchaseSettlement() {
    const isCredit = document.getElementById("purchase_settle_credit").checked;
    const cashContainer = document.getElementById("purchase_cash_container");
    const cashSelect = document.getElementById("purchase_cash_account");
    const creditBox = document.getElementById("purchase_credit_notice");

    if (isCredit) {
        cashContainer.style.display = "none";
        cashSelect.removeAttribute("required");
        creditBox.style.display = "block";
    } else {
        cashContainer.style.display = "block";
        cashSelect.setAttribute("required", "required");
        creditBox.style.display = "none";
    }
    calculatePurchaseTotals();
}

function validatePurchaseGuards(validLines, hasEmptiesOverdraft) {
    const submitBtn = document.getElementById("submit_purchase_btn");
    const isCredit = document.getElementById("purchase_settle_credit").checked;
    const cashSelect = document.getElementById("purchase_cash_account");
    const overdraftWarning = document.getElementById("cash_overdraft_warning");

    overdraftWarning.style.display = "none";
    overdraftWarning.innerText = "";
    submitBtn.disabled = false;

    if (validLines === 0) {
        submitBtn.disabled = true;
        return;
    }

    if (hasEmptiesOverdraft) {
        overdraftWarning.innerText = `⛔ Stock d'emballages insuffisant : Vous avez saisi plus de casiers vides à rendre au camion que le dépôt n'en possède en stock physique.`;
        overdraftWarning.style.display = "block";
        submitBtn.disabled = true;
        return;
    }

    if (!isCredit && cashSelect.selectedIndex > 0) {
        const opt = cashSelect.options[cashSelect.selectedIndex];
        const balance = parseFloat(opt.dataset.balance) || 0;

        if (globalPurchaseTotal > balance) {
            overdraftWarning.innerText = `⛔ Solde insuffisant : Le compte sélectionné dispose de ${new Intl.NumberFormat('fr-FR').format(balance)} FCFA (Montant total net achat : ${new Intl.NumberFormat('fr-FR').format(globalPurchaseTotal)} FCFA).`;
            overdraftWarning.style.display = "block";
            submitBtn.disabled = true;
            return;
        }
    }
}

function openPurchaseConfirmModal() {
    const form = document.getElementById("achats_multi_form");
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const suppSelect = document.getElementById("supplier_select");
    const suppName = suppSelect.options[suppSelect.selectedIndex]?.dataset?.name || suppSelect.options[suppSelect.selectedIndex]?.text || "Non défini";
    const invoiceRef = document.getElementById("invoice_ref_input").value.trim() || "Sans référence";
    const pDate = document.getElementById("purchase_date").value;
    const isCredit = document.getElementById("purchase_settle_credit").checked;
    const cashSelect = document.getElementById("purchase_cash_account");
    const cashAccName = isCredit ? "Aucun (Achat à crédit)" : (cashSelect.options[cashSelect.selectedIndex]?.dataset?.name || "Caisse non sélectionnée");

    document.getElementById("modal_supp_name").innerText = suppName;
    document.getElementById("modal_invoice_ref").innerText = invoiceRef;
    document.getElementById("modal_purchase_date").innerText = pDate;
    document.getElementById("modal_settle_type").innerText = isCredit ? "🟡 À Crédit (Dette Fournisseur)" : "🟢 Au Comptant (Paiement Immédiat)";
    document.getElementById("modal_cash_acc").innerText = cashAccName;

    // Populate Items
    const modalTbody = document.getElementById("modal_items_tbody");
    modalTbody.innerHTML = "";

    let drinksSubtotal = 0;
    let totalEmb = 0;
    let totalDebtCrates = 0;
    const rows = document.querySelectorAll("#purchase_tbody tr");

    rows.forEach(tr => {
        const prodSelect = tr.querySelector('.pitem-product');
        const formatSelect = tr.querySelector('.pitem-format');
        const priceInput = tr.querySelector('.pitem-price');
        const qtyInput = tr.querySelector('.pitem-qty');
        const emptiesInput = tr.querySelector('.pitem-empties');
        const embCostInput = tr.querySelector('.pitem-embcost');
        const modeSelect = tr.querySelector('.pitem-mode');

        const prodId = parseInt(prodSelect.value) || 0;
        const prod = availableProducts.find(p => p.id === prodId);
        if (!prod) return;

        const qty = parseFloat(qtyInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        const format = formatSelect.value === 'demi' ? 'Demi (0.5)' : 'Entier (1.0)';
        const empties = prod.is_returnable ? (parseFloat(emptiesInput.value) || 0) : 0;
        const embCost = prod.is_returnable ? (parseFloat(embCostInput.value) || 0) : 0;
        const deficit = Math.max(0, qty - empties);
        const mode = modeSelect ? modeSelect.value : 'charge';

        let lineEmbCost = 0;
        let lineStatusHtml = '';

        if (prod.is_returnable) {
            if (deficit === 0) {
                lineStatusHtml = `<span style="color: #16A34A; font-weight: 700;">🟢 Échange 1:1</span>`;
            } else if (mode === 'charge') {
                lineEmbCost = deficit * embCost;
                lineStatusHtml = `<span style="color: #0284C7; font-weight: 700;">🔵 Consigne (+${new Intl.NumberFormat('fr-FR').format(lineEmbCost)} F)</span>`;
            } else {
                totalDebtCrates += deficit;
                lineStatusHtml = `<span style="color: #D97706; font-weight: 700;">🔴 Dette (+${deficit} c.)</span>`;
            }
        } else {
            lineStatusHtml = `<span style="color: #64748B;">Perdu</span>`;
        }

        const lineDrink = qty * price;
        const lineTot = lineDrink + lineEmbCost;

        drinksSubtotal += lineDrink;
        totalEmb += lineEmbCost;

        const code = prod.short_code ? `[${prod.short_code}] ` : '';
        const mtr = document.createElement("tr");
        mtr.innerHTML = `
            <td><strong>${code}${prod.name}</strong></td>
            <td><span style="padding: 2px 6px; border-radius: 4px; background: #E2E8F0; font-size: 0.78rem;">${format}</span></td>
            <td><strong>${qty}</strong></td>
            <td>${new Intl.NumberFormat('fr-FR').format(price)} F</td>
            <td>${prod.is_returnable ? `<strong>${empties}</strong> remis` : '-'}</td>
            <td>${lineStatusHtml}</td>
            <td style="text-align: right; font-weight: 700;">${new Intl.NumberFormat('fr-FR').format(lineTot)} FCFA</td>
        `;
        modalTbody.appendChild(mtr);
    });

    const addFees = parseFloat(document.getElementById("purchase_additional_fees")?.value || 0) || 0;
    const finalTotal = drinksSubtotal + totalEmb + addFees;

    document.getElementById("modal_drinks_subtotal").innerText = new Intl.NumberFormat('fr-FR').format(drinksSubtotal) + " FCFA";
    document.getElementById("modal_emb_total").innerText = new Intl.NumberFormat('fr-FR').format(totalEmb) + " FCFA";
    document.getElementById("modal_debt_crates_total").innerText = totalDebtCrates + " casier(s)";
    document.getElementById("modal_fees_total").innerText = (addFees >= 0 ? "+" : "") + new Intl.NumberFormat('fr-FR').format(addFees) + " FCFA";
    document.getElementById("modal_grand_total").innerText = new Intl.NumberFormat('fr-FR').format(finalTotal) + " FCFA";

    document.getElementById("purchase_confirm_modal").style.display = "flex";
}

function closePurchaseConfirmModal() {
    document.getElementById("purchase_confirm_modal").style.display = "none";
}

function submitFinalPurchase() {
    document.getElementById("achats_multi_form").submit();
}

document.addEventListener("DOMContentLoaded", function() {
    addNewPurchaseRow();
});
</script>
