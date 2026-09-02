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
    <i class='bx bx-cart-add'></i>
    <h1><?= $title ?></h1>
</div>

<?php if (!empty($flash_error)): ?>
    <div style="background: #FEE2E2; border-left: 4px solid var(--c-danger); color: #991B1B; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
        <i class='bx bx-error-circle' style="font-size: 1.4rem;"></i>
        <span><?= htmlspecialchars($flash_error) ?></span>
    </div>
<?php endif; ?>

<form action="<?= BASE_URL ?>/ventes/save" method="POST" id="ventes_multi_form">
    
    <!-- TOP SECTION: CLIENT & DATE -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span><i class='bx bx-user-check'></i> 1. Client & Date de Facturation</span>
            <button type="button" class="btn btn-secondary btn-sm" onclick="openQuickAvoirModal()" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.82rem; font-weight: 700; color: #059669; border-color: #A7F3D0; background: #ECFDF5;">
                <i class='bx bx-wallet-alt'></i> + Enregistrer Reliquat Monnaie (Avoir Client)
            </button>
        </div>
        <div class="card-body">
            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Client <span style="color: var(--c-danger);">*</span></label>
                    <select name="client_id" id="vente_client_select" class="form-control" required onchange="onClientChange()">
                        <option value="">-- Sélectionner le client --</option>
                        <?php foreach ($clients as $c): ?>
                            <?php 
                            $isClientSel = (isset($flash_old['client_id']) && $flash_old['client_id'] == $c['id']);
                            $cDebt = floatval($c['current_debt']);
                            ?>
                            <option value="<?= $c['id'] ?>" 
                                    data-name="<?= htmlspecialchars($c['name']) ?>"
                                    data-debt="<?= $cDebt ?>" 
                                    data-max-credit="<?= floatval($c['max_credit']) ?>"
                                    <?= $isClientSel ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?> 
                                &bull; <?= $cDebt < 0 ? '🟢 Avoir: +' . number_format(abs($cDebt), 0, ',', ' ') . ' FCFA' : 'Dette: ' . number_format($cDebt, 0, ',', ' ') . ' FCFA' ?>
                                (Plafond Crédit: <?= number_format($c['max_credit'], 0, ',', ' ') ?> FCFA)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Date de Vente <span style="color: var(--c-danger);">*</span></label>
                    <input type="date" name="sale_date" id="vente_sale_date" class="form-control" required value="<?= htmlspecialchars($flash_old['sale_date'] ?? date('Y-m-d')) ?>">
                </div>
            </div>

            <!-- LIVE CLIENT CREDIT / AVOIR INDICATOR -->
            <div id="client_financial_box" style="display: none; padding: 12px 16px; border-radius: 8px; background: #F8FAFC; border-left: 4px solid var(--c-navy); margin-top: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; font-size: 0.88rem;">
                    <div>Situation Financière : <strong id="c_info_debt" style="color: var(--c-danger);">0 FCFA</strong></div>
                    <div>Plafond de Crédit Autorisé : <strong id="c_info_max">0 FCFA</strong></div>
                    <div>Capacité de Crédit Restante : <strong id="c_info_available" style="color: var(--c-success);">0 FCFA</strong></div>
                </div>
            </div>

            <!-- DEDICATED GREEN AVOIR BANNER WITH CHECKBOX -->
            <div id="client_avoir_banner" style="display: none; background: #ECFDF5; border: 1.5px solid #A7F3D0; border-radius: 8px; padding: 12px 16px; margin-top: 10px; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <span style="color: #047857; font-weight: 800; font-size: 0.95rem; display: flex; align-items: center; gap: 6px;">
                        <i class='bx bx-wallet'></i> 🟢 Avoir Client Disponible : <strong id="avoir_disp_badge">+0 FCFA</strong>
                    </span>
                    <div style="font-size: 0.8rem; color: #065F46; margin-top: 2px;">
                        Ce client dispose d'un crédit d'avance. Vous pouvez déduire tout ou partie de ce montant pour régler cette vente.
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <label style="display: flex; align-items: center; gap: 6px; font-weight: 700; color: #047857; cursor: pointer; background: #FFFFFF; padding: 6px 12px; border-radius: 6px; border: 1px solid #6EE7B7; margin-bottom: 0;">
                        <input type="checkbox" name="use_avoir" id="use_avoir_check" value="1" checked onchange="toggleAvoirUsage()">
                        <span>Déduire l'Avoir</span>
                    </label>
                    <input type="number" step="any" min="0" name="avoir_amount" id="vente_avoir_amount" class="form-control" style="width: 120px; text-align: right; font-weight: 800; color: #047857; border: 1.5px solid #6EE7B7; background: #F0FDF4;" placeholder="0" oninput="calculateTotals()">
                </div>
            </div>
        </div>
    </div>

    <!-- MIDDLE SECTION: MULTI-PRODUCT ITEMS -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span><i class='bx bx-list-check'></i> 2. Articles & Boissons Vendus</span>
            <button type="button" class="btn btn-accent" onclick="addNewProductRow()" style="padding: 6px 14px; font-size: 0.88rem;">
                <i class='bx bx-plus-circle'></i> Ajouter une boisson
            </button>
        </div>
        <div style="padding: 10px;">
            <div class="table-sticky-container">
                <table class="table" id="items_table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="min-width: 220px;">Produit / Boisson</th>
                            <th style="min-width: 105px;">Emballage</th>
                            <th style="min-width: 120px;">Format</th>
                            <th style="min-width: 90px;">Stock</th>
                            <th style="min-width: 110px;">Prix Unit (FCFA)</th>
                            <th style="min-width: 80px;">Quantité</th>
                            <th style="min-width: 100px;" title="Casiers vides rapportés par le client (Échange 1 pour 1 par défaut)">Casiers Rendus</th>
                            <th style="min-width: 100px;" title="Bouteilles individuelles en vrac rendues par le client">Btl Vrac Rendues</th>
                            <?php if (\App\Core\Helper::isAdmin()): ?>
                                <th style="min-width: 80px; text-align: center;" title="Cocher pour mettre à jour le prix officiel du catalogue pour cette boisson">🏷️ MàJ Cat.</th>
                            <?php endif; ?>
                            <th style="min-width: 130px; text-align: right;">Total Ligne (FCFA)</th>
                            <th style="min-width: 45px; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="items_tbody">
                        <!-- Dynamic rows inserted here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TOTALS & VOLUME BAR WITH REMISE & NET A PAYER -->
        <div style="background: #F1F5F9; border-top: 2px solid #E2E8F0; padding: 18px 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    <span style="font-weight: 600; color: var(--c-gray-700);"><span id="lines_count">0</span> article(s)</span>
                    <span style="color: #CBD5E1;">|</span>
                    <span style="font-weight: 600; color: var(--c-navy);">Volume Total : <strong id="total_casiers_equiv">0</strong> Unité(s)</span>
                    <span style="color: #CBD5E1;">|</span>
                    <span style="font-weight: 700; color: #D97706;" id="packaging_debt_summary"><i class='bx bx-archive'></i> Dettes Emballages : 0 casier(s)</span>
                </div>
                
                <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                    <div style="text-align: right;">
                        <span style="font-size: 0.8rem; color: #64748B; font-weight: 600; display: block;">Sous-Total Brut :</span>
                        <strong id="subtotal_display" style="font-size: 1.05rem; color: #334155;">0 FCFA</strong>
                    </div>

                    <div style="text-align: right;">
                        <label for="vente_discount_amount" style="font-size: 0.8rem; font-weight: 700; color: #DC2626; display: block; margin-bottom: 2px;">
                            <i class='bx bx-gift'></i> Remise Commerciale (Rabais) :
                        </label>
                        <input type="number" step="any" min="0" name="discount_amount" id="vente_discount_amount" class="form-control" style="width: 140px; text-align: right; font-weight: 800; font-size: 1rem; color: #DC2626; border: 1.5px solid #FCA5A5; background: #FEF2F2;" placeholder="0" value="<?= htmlspecialchars($flash_old['discount_amount'] ?? '0') ?>" oninput="calculateTotals()">
                    </div>

                    <div id="avoir_deducted_display_box" style="text-align: right; display: none;">
                        <span style="font-size: 0.8rem; font-weight: 700; color: #047857; display: block; margin-bottom: 2px;">
                            <i class='bx bx-wallet'></i> Avoir Déduit :
                        </span>
                        <strong id="avoir_deducted_display" style="font-size: 1.05rem; color: #047857;">-0 FCFA</strong>
                    </div>

                    <div style="text-align: right; border-left: 2px solid #CBD5E1; padding-left: 20px;">
                        <span style="font-size: 0.88rem; font-weight: 700; color: var(--c-gray-600); display: block;">NET À PAYER :</span>
                        <span id="grand_total_display" style="font-size: 1.6rem; font-weight: 900; color: #047857; letter-spacing: 0.5px;">0 FCFA</span>
                    </div>
                </div>
            </div>

            <?php if (\App\Core\Helper::isAdmin()): ?>
                <div style="margin-top: 14px; padding-top: 12px; border-top: 1px dashed #CBD5E1; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.88rem; color: #334155; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" name="update_catalog_prices" id="master_update_catalog_prices" value="1" onchange="toggleAllCatalogPrices(this.checked)">
                        <span><i class='bx bx-sync' style="color: #0284C7;"></i> Mettre à jour le barème officiel du catalogue pour <strong>TOUTES</strong> les boissons modifiées</span>
                    </label>
                    <span style="font-size: 0.8rem; color: #64748B; font-style: italic;">(Vous pouvez aussi cocher individuellement par ligne dans la colonne « 🏷️ MàJ Cat. »)</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- BOTTOM SECTION: PAYMENT & SETTLEMENT -->
    <div class="card" style="margin-bottom: 25px;">
        <div class="card-header">
            <span><i class='bx bx-credit-card'></i> 3. Modalités de Règlement</span>
        </div>
        <div class="card-body">
            <div style="display: flex; gap: 25px; margin-bottom: 18px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 700; font-size: 1rem;">
                    <input type="radio" name="settlement_type" value="cash" id="settle_cash" checked onchange="toggleSettlement()">
                    🟢 Au Comptant / Acompte (Encaissement Immédiat)
                </label>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 700; font-size: 1rem;">
                    <input type="radio" name="settlement_type" value="credit" id="settle_credit" onchange="toggleSettlement()">
                    🟡 100 % À Crédit (Créance Client Complète)
                </label>
            </div>

            <!-- COMPTE DE TRESORERIE & ACOMPTE -->
            <div id="cash_account_container">
                <div class="dashboard-grid">
                    <div class="form-group">
                        <label class="form-label">Compte de Caisse Récepteur <span style="color: var(--c-danger);">*</span></label>
                        <select name="cash_account_id" id="cash_account_select" class="form-control" required>
                            <?php foreach ($cash_accounts as $acc): ?>
                                <option value="<?= $acc['id'] ?>" data-name="<?= htmlspecialchars($acc['name']) ?>">
                                    <?= htmlspecialchars($acc['name']) ?> (<?= htmlspecialchars($acc['payment_method_name']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 700; color: #166534;">
                            <i class='bx bx-money'></i> Montant Encaissé Immédiatement (Acompte / Cash) <span style="color: var(--c-danger);">*</span>
                        </label>
                        <input type="number" step="any" min="0" name="amount_paid" id="vente_amount_paid" class="form-control" style="font-size: 1.1rem; font-weight: 800; color: #166534;" placeholder="0" oninput="calculateTotals()">
                    </div>
                </div>

                <!-- PARTIAL PAYMENT REMAINING DEBT NOTICE -->
                <div id="partial_payment_notice" style="display: none; padding: 12px 16px; background: #FEF3C7; border-left: 4px solid #D97706; border-radius: 6px; font-size: 0.9rem; color: #92400E; margin-top: 12px;">
                    <i class='bx bx-info-circle' style="font-size: 1.2rem; vertical-align: middle;"></i>
                    <strong>Paiement Partiel :</strong> Le client verse <strong id="partial_paid_disp">0 FCFA</strong> au comptant. 
                    Le solde restant de <strong id="partial_due_disp" style="color: #DC2626; font-size: 1.05rem;">0 FCFA</strong> sera créé en <strong>Dette Client</strong>.
                </div>
            </div>

            <!-- CREDIT DETAILS -->
            <div id="credit_details_container" style="display: none; background: #FFFBEB; border: 1px solid #FCD34D; border-radius: 8px; padding: 15px; margin-bottom: 15px;">
                <div style="font-size: 0.88rem; color: #92400E;">
                    <i class='bx bx-info-circle'></i> Cette vente sera enregistrée à 100 % sous forme de <strong>créance client</strong> et augmentera le solde dû de ce client.
                </div>
            </div>

            <div id="credit_limit_warning" style="display: none; padding: 12px 16px; background: #FEE2E2; border-left: 4px solid var(--c-danger); border-radius: 6px; font-size: 0.88rem; color: #991B1B; margin-top: 12px; font-weight: 700;"></div>

            <div class="dashboard-grid" style="margin-top: 15px;">
                <div class="form-group">
                    <label class="form-label">N° de Référence Manuelle (Optionnel)</label>
                    <input type="text" name="reference" id="vente_ref_input" class="form-control" placeholder="Ex: FACT-<?= date('Ymd') ?>-001">
                </div>
                <div class="form-group">
                    <label class="form-label">Notes / Observations</label>
                    <input type="text" name="notes" id="vente_notes_input" class="form-control" placeholder="Ex: Commande pour événement, livraison dépôt...">
                </div>
            </div>

            <div style="margin-top: 25px; display: flex; gap: 12px;">
                <button type="button" class="btn btn-accent" id="submit_sale_btn" onclick="openVenteConfirmModal()" style="padding: 10px 24px; font-size: 1rem; font-weight: 700;">
                    <i class='bx bx-check-circle'></i> Vérifier & Valider la Facture
                </button>
                <a href="<?= BASE_URL ?>/ventes" class="btn btn-primary" style="background-color: var(--c-gray-600); padding: 10px 20px;">
                    <i class='bx bx-x'></i> Annuler
                </a>
            </div>
        </div>
    </div>
</form>

<!-- QUICK MODAL TO RECORD CLIENT AVOIR / RELIQUAT DE MONNAIE -->
<div id="quick_avoir_modal" class="modal-backdrop-custom">
    <div class="modal-card-custom" style="max-width: 500px;">
        <div style="padding: 16px 20px; background: #059669; color: #FFF; border-top-left-radius: 12px; border-top-right-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                <i class='bx bx-wallet-alt'></i> Enregistrer un Avoir / Reliquat Monnaie
            </h3>
            <button type="button" onclick="closeQuickAvoirModal()" style="background: none; border: none; color: #FFF; font-size: 1.4rem; cursor: pointer;">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/reglements/save" method="POST" id="quick_avoir_form">
            <div style="padding: 20px;">
                <p style="font-size: 0.85rem; color: #475569; margin-top: 0; margin-bottom: 15px;">
                    Utilisez ce formulaire si vous devez de la monnaie physique au client (manque de petites pièces). Le montant sera crédité sur son compte Avoir.
                </p>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label">Client <span style="color: var(--c-danger);">*</span></label>
                    <select name="client_id" id="q_avoir_client_id" class="form-control" required>
                        <option value="">-- Sélectionner le client --</option>
                        <?php foreach ($clients as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label">Montant de la Monnaie / Avoir (FCFA) <span style="color: var(--c-danger);">*</span></label>
                    <input type="number" step="any" min="1" name="amount" id="q_avoir_amount" class="form-control" placeholder="Ex: 200" required style="font-size: 1.1rem; font-weight: 800; color: #059669;">
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label">Compte de Caisse (Encaissement physique conservé)</label>
                    <select name="cash_account_id" class="form-control" required>
                        <?php foreach ($cash_accounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>"><?= htmlspecialchars($acc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Motif / Observation</label>
                    <input type="text" name="notes" class="form-control" value="Reliquat monnaie non rendue (manque de pièces)" placeholder="Ex: Reliquat monnaie non rendue">
                </div>
            </div>
            <div style="padding: 14px 20px; background: #F8FAFC; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #E2E8F0;">
                <button type="button" class="btn btn-secondary" onclick="closeQuickAvoirModal()">Annuler</button>
                <button type="submit" class="btn btn-accent" style="background: #059669; border-color: #059669; font-weight: 700;">
                    <i class='bx bx-check'></i> Valider & Créditer Avoir
                </button>
            </div>
        </form>
    </div>
</div>

<!-- DOUBLE-CHECK CONFIRMATION MODAL FOR VENTES -->
<div id="vente_confirm_modal" class="modal-backdrop-custom">
    <div class="modal-card-custom">
        <div style="padding: 18px 24px; background: var(--c-navy); color: #FFF; border-top-left-radius: 12px; border-top-right-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                <i class='bx bx-shield-quarter' style="font-size: 1.4rem; color: #38BDF8;"></i>
                Confirmation de la Vente (Double-Check)
            </h3>
            <button type="button" onclick="closeVenteConfirmModal()" style="background: none; border: none; color: #FFF; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>

        <div style="padding: 20px 24px; overflow-y: auto;">
            <!-- CLIENT HIGHLIGHT BADGE -->
            <div style="background: #F0FDF4; border: 1.5px solid #BBF7D0; border-radius: 8px; padding: 14px 18px; margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <span style="font-size: 0.8rem; text-transform: uppercase; font-weight: 700; color: #16A34A; display: block;">Client Facturé</span>
                    <strong id="vmodal_client_name" style="font-size: 1.25rem; color: #14532D;">-</strong>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 0.8rem; color: #64748B; display: block;">Date de Vente</span>
                    <strong id="vmodal_sale_date" style="font-size: 1.05rem; color: var(--c-navy);">-</strong>
                </div>
            </div>

            <!-- ITEMS TABLE -->
            <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 8px; color: #334155;">
                <i class='bx bx-list-check'></i> Détail des Articles & Consignes :
            </h4>
            <div style="border: 1px solid #E2E8F0; border-radius: 6px; max-height: 220px; overflow-y: auto; margin-bottom: 18px;">
                <table class="table" style="font-size: 0.85rem; margin-bottom: 0;">
                    <thead>
                        <tr style="background: #F1F5F9;">
                            <th>Produit</th>
                            <th>Format</th>
                            <th>Quantité</th>
                            <th>Prix Unit.</th>
                            <th>Casiers Rendus</th>
                            <th style="text-align: right;">Total Ligne</th>
                        </tr>
                    </thead>
                    <tbody id="vmodal_items_tbody">
                        <!-- Filled by JS -->
                    </tbody>
                </table>
            </div>

            <!-- FINANCIAL & PACKAGING BREAKDOWN -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px; margin-bottom: 15px;">
                <!-- Financials -->
                <div style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 8px; padding: 14px 18px;">
                    <span style="font-size: 0.82rem; font-weight: 700; color: #64748B; text-transform: uppercase; display: block; margin-bottom: 8px;">Règlement Financier</span>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.88rem; color: #64748B;">
                        <span>Sous-Total Brut :</span>
                        <strong id="vmodal_subtotal_amount">0 FCFA</strong>
                    </div>
                    <div id="vmodal_discount_row" style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.88rem; color: #DC2626;">
                        <span>Remise Commerciale (Rabais) :</span>
                        <strong id="vmodal_discount_amount">-0 FCFA</strong>
                    </div>
                    <div id="vmodal_avoir_row" style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.88rem; color: #047857;">
                        <span>Avoir Client Déduit :</span>
                        <strong id="vmodal_avoir_amount">-0 FCFA</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.95rem; font-weight: 800; color: var(--c-navy); border-top: 1px dashed #CBD5E1; padding-top: 5px;">
                        <span>Montant Net Facture :</span>
                        <strong id="vmodal_total_amount" style="color: #047857;">0 FCFA</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.9rem; color: #16A34A;">
                        <span>Encaissé (Caisse) :</span>
                        <strong id="vmodal_amount_paid">0 FCFA</strong>
                    </div>
                    <div style="border-top: 1px solid #CBD5E1; padding-top: 6px; display: flex; justify-content: space-between; font-size: 0.95rem; color: #DC2626;">
                        <span>Reste Dû (Dette Client) :</span>
                        <strong id="vmodal_amount_due">0 FCFA</strong>
                    </div>
                </div>

                <!-- Packaging -->
                <div style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 8px; padding: 14px 18px;">
                    <span style="font-size: 0.82rem; font-weight: 700; color: #64748B; text-transform: uppercase; display: block; margin-bottom: 8px;">Bilan des Emballages</span>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.9rem;">
                        <span>Casiers Emportés :</span>
                        <strong id="vmodal_crates_out">0 casier(s)</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.9rem; color: #16A34A;">
                        <span>Casiers Rendus :</span>
                        <strong id="vmodal_crates_in">0 casier(s)</strong>
                    </div>
                    <div style="border-top: 1px solid #CBD5E1; padding-top: 6px; display: flex; justify-content: space-between; font-size: 0.95rem;">
                        <span>Dette Emballages Nette :</span>
                        <strong id="vmodal_crates_due" style="color: #D97706;">0 casier(s)</strong>
                    </div>
                </div>
            </div>
        </div>

        <div style="padding: 16px 24px; background: #F1F5F9; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; display: flex; justify-content: flex-end; gap: 12px;">
            <button type="button" class="btn btn-primary" onclick="closeVenteConfirmModal()" style="background-color: var(--c-gray-600); padding: 9px 18px;">
                <i class='bx bx-edit'></i> Modifier / Corriger
            </button>
            <button type="button" class="btn btn-accent" onclick="submitFinalSale()" style="padding: 9px 24px; font-weight: 800;">
                <i class='bx bx-check-double'></i> Confirmer et Valider Définitivement
            </button>
        </div>
    </div>
</div>

<!-- MODAL: QUICK CLIENT AVOIR / RELIQUAT MONNAIE -->
<div id="quick_avoir_modal" class="modal-backdrop-custom">
    <div class="modal-card-custom" style="max-width: 550px;">
        <div style="padding: 18px 24px; background: var(--c-navy); color: #FFFFFF; border-top-left-radius: 12px; border-top-right-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px; font-size: 1.1rem; font-weight: 700;">
                <i class='bx bx-wallet-alt' style="font-size: 1.3rem; color: #34D399;"></i>
                <span>Enregistrer un Avoir / Reliquat Monnaie</span>
            </div>
            <button type="button" onclick="closeQuickAvoirModal()" style="background: transparent; border: none; color: #FFFFFF; font-size: 1.4rem; cursor: pointer;">
                <i class='bx bx-x'></i>
            </button>
        </div>

        <div style="padding: 24px;">
            <p style="font-size: 0.88rem; color: #64748B; margin-bottom: 18px;">
                Créditez le compte d'un client lorsqu'il manque des pièces de monnaie physique en caisse ou pour enregistrer un acompte. Le solde créditeur sera automatiquement disponible pour déduction.
            </p>

            <div id="q_avoir_error" style="display: none; background: #FEE2E2; border-left: 4px solid var(--c-danger); color: #991B1B; padding: 10px 14px; border-radius: 6px; margin-bottom: 15px; font-size: 0.85rem; font-weight: 600;"></div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label" style="font-size: 0.85rem;">Client Bénéficiaire <span style="color: var(--c-danger);">*</span></label>
                <select id="q_avoir_client_id" class="form-control" style="font-size: 0.9rem;">
                    <option value="">-- Choisir le client --</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label" style="font-size: 0.85rem;">Montant du Reliquat / Avoir (FCFA) <span style="color: var(--c-danger);">*</span></label>
                <input type="number" step="any" min="1" id="q_avoir_amount" class="form-control" placeholder="Ex: 200" style="font-size: 1.1rem; font-weight: 800; color: #059669; text-align: right;">
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label" style="font-size: 0.85rem;">Compte de Caisse Récepteur <span style="color: var(--c-danger);">*</span></label>
                <select id="q_avoir_cash_account_id" class="form-control" style="font-size: 0.88rem;">
                    <?php foreach ($cash_accounts as $ca): ?>
                        <option value="<?= $ca['id'] ?>"><?= htmlspecialchars($ca['name']) ?> (<?= htmlspecialchars($ca['payment_method_name']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 0.85rem;">Motif / Observation</label>
                <input type="text" id="q_avoir_notes" class="form-control" value="Monnaie non rendue / Reliquat en attente" style="font-size: 0.85rem;">
            </div>
        </div>

        <div style="padding: 14px 24px; background: #F8FAFC; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #E2E8F0;">
            <button type="button" class="btn btn-primary" onclick="closeQuickAvoirModal()" style="background-color: var(--c-gray-600); font-size: 0.88rem;">
                <i class='bx bx-x'></i> Annuler
            </button>
            <button type="button" class="btn btn-accent" id="q_avoir_submit_btn" onclick="submitQuickAvoir()" style="background: #059669; border-color: #059669; font-weight: 700; font-size: 0.88rem;">
                <i class='bx bx-check-circle'></i> Enregistrer & Appliquer à la Vente
            </button>
        </div>
    </div>
</div>

<!-- DATA PASSING TO JAVASCRIPT -->
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
        'factor' => intval($p['factor'] ?: 12),
        'unit_name' => 'emb.'
    ];
}, $products)) ?>;

const isAdminUser = <?= \App\Core\Helper::isAdmin() ? 'true' : 'false' ?>;
let rowCounter = 0;
let globalSaleSubTotal = 0;
let globalSaleDiscount = 0;
let globalSaleTotal = 0;

function addNewProductRow() {
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
        optionsHtml += `<option value="${p.id}" ${outOfStock ? 'disabled' : ''}>
            ${retIcon} ${codePrefix}${p.name} (${p.format_name}) ${outOfStock ? '⛔ [RUPTURE 0]' : `[Stock: ${p.stock}]`}
        </option>`;
    });

    const isMasterChecked = document.getElementById("master_update_catalog_prices")?.checked || false;

    let adminCatCell = '';
    if (isAdminUser) {
        adminCatCell = `
            <td style="text-align: center;">
                <input type="checkbox" name="items[${rowCounter}][update_catalog_price]" value="1" class="item-cat-price-chk" ${isMasterChecked ? 'checked' : ''} onchange="onItemCatPriceChange()" title="Mettre à jour le barème officiel du catalogue pour cette boisson">
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
        <td>
            <select name="items[${rowCounter}][format_type]" class="item-format form-control" onchange="onFormatChange(${rowCounter})">
                <option value="casier">Entier (1.0)</option>
                <option value="demi">Demi (0.5)</option>
            </select>
        </td>
        <td>
            <span class="stock-badge" id="stock_badge_${rowCounter}" style="display: inline-block; padding: 4px 6px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; background: #E2E8F0; color: #475569;">
                -
            </span>
        </td>
        <td>
            <input type="number" step="any" min="0" name="items[${rowCounter}][unit_price]" class="item-price form-control" required placeholder="0" oninput="calculateTotals()">
        </td>
        <td>
            <input type="number" step="1" min="1" name="items[${rowCounter}][quantity]" class="item-qty form-control" required value="1" oninput="onQtyChange(${rowCounter})">
        </td>
        <td>
            <input type="number" step="1" min="0" name="items[${rowCounter}][crates_returned]" class="item-crates-ret form-control" value="1" placeholder="0" oninput="calculateTotals()" title="Casiers vides rapportés (1 pour 1 par défaut)">
        </td>
        <td>
            <input type="number" step="1" min="0" name="items[${rowCounter}][bottles_returned]" class="item-bottles-ret form-control" value="0" placeholder="0" oninput="calculateTotals()" title="Bouteilles en vrac rapportées">
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
    calculateTotals();
}

function toggleAllCatalogPrices(isChecked) {
    const chks = document.querySelectorAll(".item-cat-price-chk");
    chks.forEach(chk => {
        chk.checked = isChecked;
    });
}

function onItemCatPriceChange() {
    const chks = document.querySelectorAll(".item-cat-price-chk");
    const master = document.getElementById("master_update_catalog_prices");
    if (!master || chks.length === 0) return;
    
    const allChecked = Array.from(chks).every(c => c.checked);
    master.checked = allChecked;
}

function removeRow(id) {
    const row = document.getElementById(`row_${id}`);
    if (row) {
        row.remove();
        refreshFormatOptionsAcrossRows();
        calculateTotals();
    }
}

function onClientChange() {
    const clientSel = document.getElementById("vente_client_select");
    const opt = clientSel?.options[clientSel.selectedIndex];
    const finBox = document.getElementById("client_financial_box");
    if (!opt || !opt.value) {
        if (finBox) finBox.style.display = "none";
        return;
    }

    const debt = parseFloat(opt.dataset.debt || 0);
    const maxCredit = parseFloat(opt.dataset.maxCredit || 0);
    const availCredit = Math.max(0, maxCredit - Math.max(0, debt));

    if (finBox) {
        finBox.style.display = "block";
        const debtEl = document.getElementById("c_info_debt");
        const maxEl = document.getElementById("c_info_max");
        const availEl = document.getElementById("c_info_available");

        if (debt < 0) {
            // Client has an Avoir (Credit Balance / Monnaie en attente)
            const avoir = Math.abs(debt);
            debtEl.innerHTML = `<span style="color: #16A34A; font-weight: 800;"><i class='bx bx-gift'></i> Avoir Disponible : +${new Intl.NumberFormat('fr-FR').format(avoir)} FCFA (Monnaie en attente)</span>
                <button type="button" class="btn btn-sm btn-accent" style="padding: 2px 8px; font-size: 0.75rem; margin-left: 8px; background: #059669; border-color: #059669; color: white;" onclick="applyClientAvoir(${avoir})">
                    <i class='bx bx-check'></i> Appliquer cet Avoir
                </button>`;
        } else {
            debtEl.innerHTML = `<strong style="color: ${debt > 0 ? '#DC2626' : '#16A34A'};">${new Intl.NumberFormat('fr-FR').format(debt)} FCFA</strong>`;
        }

        if (maxEl) maxEl.innerText = new Intl.NumberFormat('fr-FR').format(maxCredit) + " FCFA";
        if (availEl) availEl.innerText = new Intl.NumberFormat('fr-FR').format(availCredit) + " FCFA";
    }

    calculateTotals();
}

function applyClientAvoir(amount) {
    const discInput = document.getElementById("vente_discount_amount");
    if (discInput) {
        discInput.value = amount;
        calculateTotals();
    }
}

function openQuickAvoirModal() {
    const clientSel = document.getElementById("vente_client_select");
    const selectedClientId = clientSel ? clientSel.value : '';
    const qClientSel = document.getElementById("q_avoir_client_id");
    if (qClientSel && selectedClientId) {
        qClientSel.value = selectedClientId;
    }
    const errBox = document.getElementById("q_avoir_error");
    if (errBox) { errBox.style.display = "none"; errBox.innerText = ""; }
    document.getElementById("quick_avoir_modal").style.display = "flex";
}

function closeQuickAvoirModal() {
    document.getElementById("quick_avoir_modal").style.display = "none";
}

async function submitQuickAvoir() {
    const clientSel = document.getElementById("q_avoir_client_id");
    const amountInput = document.getElementById("q_avoir_amount");
    const accountSel = document.getElementById("q_avoir_cash_account_id");
    const notesInput = document.getElementById("q_avoir_notes");
    const submitBtn = document.getElementById("q_avoir_submit_btn");
    const errBox = document.getElementById("q_avoir_error");

    const clientId = clientSel?.value;
    const amount = parseFloat(amountInput?.value || 0);
    const accountId = accountSel?.value;
    const notes = notesInput?.value || '';

    if (!clientId) {
        if (errBox) { errBox.style.display = "block"; errBox.innerText = "Veuillez choisir le client bénéficiaire."; }
        return;
    }
    if (amount <= 0 || isNaN(amount)) {
        if (errBox) { errBox.style.display = "block"; errBox.innerText = "Veuillez saisir un montant d'avoir valide (> 0 FCFA)."; }
        return;
    }
    if (!accountId) {
        if (errBox) { errBox.style.display = "block"; errBox.innerText = "Veuillez choisir le compte de caisse récepteur."; }
        return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Enregistrement...";

    try {
        const formData = new FormData();
        formData.append("client_id", clientId);
        formData.append("amount", amount);
        formData.append("cash_account_id", accountId);
        formData.append("notes", notes);
        formData.append("payment_date", document.getElementById("vente_sale_date")?.value || new Date().toISOString().slice(0, 10));

        const response = await fetch("<?= BASE_URL ?>/reglements/quickAvoir", {
            method: "POST",
            body: formData,
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        });

        const data = await response.json();
        if (data.success) {
            // Update client select option in the main sale form
            const vClientSel = document.getElementById("vente_client_select");
            if (vClientSel) {
                for (let i = 0; i < vClientSel.options.length; i++) {
                    const opt = vClientSel.options[i];
                    if (opt.value == clientId) {
                        opt.dataset.debt = data.new_debt;
                        const clientName = opt.dataset.name || opt.text.split('•')[0].trim();
                        const avoirText = data.new_debt < 0 
                            ? `🟢 Avoir: +${new Intl.NumberFormat('fr-FR').format(Math.abs(data.new_debt))} FCFA`
                            : `Dette: ${new Intl.NumberFormat('fr-FR').format(data.new_debt)} FCFA`;
                        opt.text = `${clientName} • ${avoirText} (Plafond: ${new Intl.NumberFormat('fr-FR').format(opt.dataset.maxCredit || 0)} FCFA)`;
                        break;
                    }
                }
                vClientSel.value = clientId;
                onClientChange();
            }

            // Apply newly created avoir to discount field
            const discInput = document.getElementById("vente_discount_amount");
            if (discInput) {
                const currentDisc = parseFloat(discInput.value) || 0;
                discInput.value = currentDisc + amount;
                calculateTotals();
            }

            closeQuickAvoirModal();
            amountInput.value = "";
            alert("✓ " + data.message + "\nLe montant a été automatiquement déduit de votre commande en cours !");
        } else {
            if (errBox) {
                errBox.style.display = "block";
                errBox.innerText = data.message || "Erreur lors de l'enregistrement de l'avoir.";
            }
        }
    } catch (e) {
        if (errBox) {
            errBox.style.display = "block";
            errBox.innerText = "Erreur réseau ou serveur : " + e.message;
        }
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = "<i class='bx bx-check-circle'></i> Enregistrer & Appliquer à la Vente";
    }
}

function onProductChange(id) {
    const row = document.getElementById(`row_${id}`);
    const prodSelect = row.querySelector('.item-product');
    const formatSelect = row.querySelector('.item-format');
    const priceInput = row.querySelector('.item-price');
    const embBadge = document.getElementById(`item_emb_badge_${id}`);
    const stockBadge = document.getElementById(`stock_badge_${id}`);
    const cratesRetInput = row.querySelector('.item-crates-ret');
    const bottlesRetInput = row.querySelector('.item-bottles-ret');

    const prodId = parseInt(prodSelect.value) || 0;
    const prod = availableProducts.find(p => p.id === prodId);

    if (!prod) {
        stockBadge.innerText = "-";
        stockBadge.style.background = "#E2E8F0";
        stockBadge.style.color = "#475569";
        embBadge.innerText = "-";
        priceInput.value = "";
        cratesRetInput.value = 0;
        bottlesRetInput.value = 0;
        refreshFormatOptionsAcrossRows();
        calculateTotals();
        return;
    }

    // Check existing format rows for this product to prevent duplicate format usage
    const otherRows = Array.from(document.querySelectorAll("#items_tbody tr")).filter(r => r.id !== `row_${id}`);
    const existingFormats = [];
    otherRows.forEach(r => {
        const pSel = r.querySelector('.item-product');
        const fSel = r.querySelector('.item-format');
        if (parseInt(pSel?.value) === prodId) {
            existingFormats.push(fSel?.value || 'casier');
        }
    });

    if (existingFormats.includes('casier') && existingFormats.includes('demi')) {
        alert(`La boisson "${prod.name}" est déjà présente dans votre panier en format Entier et en format Demi.`);
        prodSelect.value = "";
        onProductChange(id);
        return;
    } else if (existingFormats.includes('casier')) {
        formatSelect.value = 'demi';
    } else if (existingFormats.includes('demi')) {
        formatSelect.value = 'casier';
    }

    stockBadge.innerText = `${prod.stock} emb.`;
    if (prod.stock <= 0) {
        stockBadge.style.background = "#FEE2E2";
        stockBadge.style.color = "#DC2626";
    } else {
        stockBadge.style.background = "#DCFCE7";
        stockBadge.style.color = "#16A34A";
    }

    if (prod.is_returnable) {
        embBadge.innerHTML = `<span style="color: #16A34A; font-weight: 700;">🟢 Consigné</span>`;
    } else {
        embBadge.innerHTML = `<span style="color: #64748B;">⚪ Perdu</span>`;
    }

    onFormatChange(id);
}

function onQtyChange(id) {
    const row = document.getElementById(`row_${id}`);
    const prodSelect = row.querySelector('.item-product');
    const formatSelect = row.querySelector('.item-format');
    const qtyInput = row.querySelector('.item-qty');
    const cratesRetInput = row.querySelector('.item-crates-ret');
    const bottlesRetInput = row.querySelector('.item-bottles-ret');

    const prodId = parseInt(prodSelect.value) || 0;
    const prod = availableProducts.find(p => p.id === prodId);
    const format = formatSelect.value;

    if (format === 'demi') {
        qtyInput.value = 1;
    }

    const qty = parseFloat(qtyInput.value) || 1;

    if (prod && prod.is_returnable) {
        const factor = prod.factor || 12;
        if (format === 'casier' && !cratesRetInput.disabled) {
            cratesRetInput.value = qty;
        } else if (format === 'demi') {
            bottlesRetInput.value = (factor / 2);
        }
    }

    calculateTotals();
}

function onFormatChange(id) {
    const row = document.getElementById(`row_${id}`);
    const prodSelect = row.querySelector('.item-product');
    const formatSelect = row.querySelector('.item-format');
    const priceInput = row.querySelector('.item-price');
    const qtyInput = row.querySelector('.item-qty');
    const cratesRetInput = row.querySelector('.item-crates-ret');
    const bottlesRetInput = row.querySelector('.item-bottles-ret');

    const prodId = parseInt(prodSelect.value) || 0;
    const prod = availableProducts.find(p => p.id === prodId);
    if (!prod) return;

    const format = formatSelect.value;
    const factor = prod.factor || 12;

    if (format === "demi") {
        qtyInput.value = 1;
        qtyInput.readOnly = true;
        qtyInput.style.backgroundColor = "#F1F5F9";
        qtyInput.style.cursor = "not-allowed";
        qtyInput.title = "En demi-casier, la quantité est strictement fixée à 1.";
        
        if (prod.price_demi > 0) priceInput.value = prod.price_demi;
        cratesRetInput.value = 0;
        cratesRetInput.disabled = true;
        if (prod.is_returnable) {
            bottlesRetInput.disabled = false;
            bottlesRetInput.value = (factor / 2);
        }
    } else {
        qtyInput.readOnly = false;
        qtyInput.style.backgroundColor = "";
        qtyInput.style.cursor = "";
        qtyInput.title = "";
        if (parseFloat(qtyInput.value) <= 0) qtyInput.value = 1;

        priceInput.value = prod.price_casier;
        if (prod.is_returnable) {
            cratesRetInput.disabled = false;
            cratesRetInput.value = parseFloat(qtyInput.value) || 1;
            bottlesRetInput.value = 0;
        }
    }

    refreshFormatOptionsAcrossRows();
    calculateTotals();
}

function refreshFormatOptionsAcrossRows() {
    const allRows = document.querySelectorAll("#items_tbody tr");
    allRows.forEach(row => {
        const prodSelect = row.querySelector('.item-product');
        const formatSelect = row.querySelector('.item-format');
        const prodId = parseInt(prodSelect?.value) || 0;
        if (!prodId || !formatSelect) return;

        const optCasier = formatSelect.querySelector("option[value='casier']");
        const optDemi = formatSelect.querySelector("option[value='demi']");
        if (!optCasier || !optDemi) return;

        let hasOtherCasier = false;
        let hasOtherDemi = false;

        allRows.forEach(otherRow => {
            if (otherRow === row) return;
            const otherProd = parseInt(otherRow.querySelector('.item-product')?.value) || 0;
            const otherFormat = otherRow.querySelector('.item-format')?.value;
            if (otherProd === prodId) {
                if (otherFormat === 'casier') hasOtherCasier = true;
                if (otherFormat === 'demi') hasOtherDemi = true;
            }
        });

        optCasier.disabled = hasOtherCasier;
        optDemi.disabled = hasOtherDemi;
    });
}

let isAmountPaidManuallySet = false;
document.addEventListener("DOMContentLoaded", function() {
    const amtPaidInput = document.getElementById("vente_amount_paid");
    if (amtPaidInput) {
        amtPaidInput.addEventListener("input", function() {
            isAmountPaidManuallySet = true;
            calculateTotals();
        });
    }
});

function calculateTotals() {
    let subTotal = 0;
    let totalCasiersEquiv = 0;
    let validLines = 0;
    let hasStockError = false;

    const rows = document.querySelectorAll("#items_tbody tr");
    rows.forEach(tr => {
        const prodSelect = tr.querySelector('.item-product');
        const formatSelect = tr.querySelector('.item-format');
        const priceInput = tr.querySelector('.item-price');
        const qtyInput = tr.querySelector('.item-qty');
        const rowId = tr.id.replace('row_', '');
        const rowTotalDisplay = document.getElementById(`row_total_${rowId}`);
        const stockBadge = document.getElementById(`stock_badge_${rowId}`);

        const prodId = parseInt(prodSelect?.value) || 0;
        const prod = availableProducts.find(p => p.id === prodId);
        const qty = parseFloat(qtyInput?.value) || 0;
        const price = parseFloat(priceInput?.value) || 0;
        const format = formatSelect?.value || 'casier';

        const lineTotal = qty * price;
        subTotal += lineTotal;

        if (rowTotalDisplay) {
            rowTotalDisplay.innerText = new Intl.NumberFormat('fr-FR').format(lineTotal) + " FCFA";
        }

        if (prod && qty > 0) {
            validLines++;
            const equiv = (format === "demi") ? (qty * 0.5) : qty;
            totalCasiersEquiv += equiv;

            // Stock check guard
            if (equiv > prod.stock) {
                hasStockError = true;
                if (stockBadge) {
                    stockBadge.style.background = "#FEE2E2";
                    stockBadge.style.color = "#DC2626";
                    stockBadge.innerText = `⛔ Dispo: ${prod.stock}`;
                }
            } else {
                if (stockBadge) {
                    stockBadge.style.background = "#DCFCE7";
                    stockBadge.style.color = "#16A34A";
                    stockBadge.innerText = `${prod.stock} emb.`;
                }
            }
        }
    });

    const discInput = document.getElementById("vente_discount_amount");
    const discountAmount = Math.max(0, parseFloat(discInput?.value || 0) || 0);
    const grossNet = Math.max(0, subTotal - discountAmount);

    // Calculate Avoir used
    const clientSel = document.getElementById("vente_client_select");
    const opt = clientSel?.options[clientSel.selectedIndex];
    const curDebt = parseFloat(opt?.dataset?.debt || 0);
    const maxCredit = parseFloat(opt?.dataset?.maxCredit || 0);
    const maxAvoir = Math.abs(curDebt < 0 ? curDebt : 0);

    const useAvoirCheck = document.getElementById("use_avoir_check");
    const avoirInput = document.getElementById("vente_avoir_amount");
    let avoirUsed = 0;

    if (useAvoirCheck && useAvoirCheck.checked && maxAvoir > 0) {
        const reqAvoir = Math.max(0, parseFloat(avoirInput?.value || 0) || 0);
        avoirUsed = Math.min(reqAvoir, maxAvoir, grossNet);
        if (avoirInput && (parseFloat(avoirInput.value) > maxAvoir)) {
            avoirInput.value = maxAvoir;
        }
    }

    const netPayable = Math.max(0, grossNet - avoirUsed);

    globalSaleSubTotal = subTotal;
    globalSaleDiscount = discountAmount;
    globalSaleAvoir = avoirUsed;
    globalSaleTotal = netPayable;

    const linesCountEl = document.getElementById("lines_count");
    if (linesCountEl) linesCountEl.innerText = validLines;

    const volEl = document.getElementById("total_casiers_equiv");
    if (volEl) volEl.innerText = totalCasiersEquiv.toFixed(1);

    const subTotDisp = document.getElementById("subtotal_display");
    if (subTotDisp) subTotDisp.innerText = new Intl.NumberFormat('fr-FR').format(subTotal) + " FCFA";

    const avoirDispBox = document.getElementById("avoir_deducted_display_box");
    const avoirDisp = document.getElementById("avoir_deducted_display");
    if (avoirDispBox && avoirDisp) {
        if (avoirUsed > 0) {
            avoirDispBox.style.display = "block";
            avoirDisp.innerText = "-" + new Intl.NumberFormat('fr-FR').format(avoirUsed) + " FCFA";
        } else {
            avoirDispBox.style.display = "none";
        }
    }

    const grandTotDisp = document.getElementById("grand_total_display");
    if (grandTotDisp) grandTotDisp.innerText = new Intl.NumberFormat('fr-FR').format(netPayable) + " FCFA";

    // Accurate packaging debt summary
    const pkgData = getPackagingDebts();
    const embSummary = document.getElementById("packaging_debt_summary");
    if (embSummary) {
        if (pkgData.debts.length === 0) {
            embSummary.innerHTML = `<span style="color: #16A34A; font-weight: 700;"><i class='bx bx-check-circle'></i> Dettes Emballages : 0 (Échange 1 pour 1 🟢)</span>`;
        } else {
            const debtBadges = pkgData.debts.map(d => {
                let str = '';
                if (d.missingCrates > 0) {
                    str += `${d.missingCrates} c. [${d.code}]`;
                    if (d.missingLoose > 0) {
                        str += ` + ${d.missingLoose} btl(s)`;
                    }
                } else {
                    str += `+${d.missingLoose} btl(s) [${d.code}]`;
                }
                return `<span style="background: #FEF3C7; color: #92400E; padding: 2px 7px; border-radius: 4px; border: 1px solid #FCD34D; font-size: 0.82rem; font-weight: 700;">${str}</span>`;
            });
            embSummary.innerHTML = `<span style="color: #D97706; font-weight: 700;"><i class='bx bx-archive'></i> Dettes Emballages : </span> ` + debtBadges.join(' ');
        }
    }

    // Handle Amount Paid & Partial Payment
    const isCredit = document.getElementById("settle_credit")?.checked || false;
    const amtPaidInput = document.getElementById("vente_amount_paid");
    const partialNotice = document.getElementById("partial_payment_notice");

    let amountPaid = netPayable;
    if (isCredit) {
        amountPaid = 0;
        if (partialNotice) partialNotice.style.display = "none";
    } else {
        if (!isAmountPaidManuallySet && amtPaidInput) {
            amtPaidInput.value = netPayable;
            amountPaid = netPayable;
        } else if (amtPaidInput) {
            amountPaid = parseFloat(amtPaidInput.value) || 0;
        }

        const remainingDebt = Math.max(0, netPayable - amountPaid);
        if (remainingDebt > 0 && partialNotice) {
            partialNotice.style.display = "block";
            const paidDisp = document.getElementById("partial_paid_disp");
            const dueDisp = document.getElementById("partial_due_disp");
            if (paidDisp) paidDisp.innerText = new Intl.NumberFormat('fr-FR').format(amountPaid) + " FCFA";
            if (dueDisp) dueDisp.innerText = new Intl.NumberFormat('fr-FR').format(remainingDebt) + " FCFA";
        } else if (partialNotice) {
            partialNotice.style.display = "none";
        }
    }

    // Live Credit limit check
    const creditWarn = document.getElementById("credit_limit_warning");
    const futureDebt = isCredit ? (Math.max(0, curDebt) + netPayable) : (Math.max(0, curDebt) + Math.max(0, netPayable - amountPaid));
    if (creditWarn) {
        if (maxCredit > 0 && futureDebt > maxCredit) {
            creditWarn.style.display = "block";
            creditWarn.innerHTML = `<i class='bx bx-error-circle'></i> Attention : Cette vente portera la dette du client à <strong>${new Intl.NumberFormat('fr-FR').format(futureDebt)} FCFA</strong>, ce qui dépasse son plafond autorisé de <strong>${new Intl.NumberFormat('fr-FR').format(maxCredit)} FCFA</strong>.`;
        } else {
            creditWarn.style.display = "none";
        }
    }
}

function onClientChange() {
    const clientSel = document.getElementById("vente_client_select");
    const opt = clientSel?.options[clientSel.selectedIndex];
    const cDebt = parseFloat(opt?.dataset?.debt || 0);
    const maxCredit = parseFloat(opt?.dataset?.maxCredit || 0);

    const finBox = document.getElementById("client_financial_box");
    const avoirBanner = document.getElementById("client_avoir_banner");
    const avoirInput = document.getElementById("vente_avoir_amount");
    const useAvoirCheck = document.getElementById("use_avoir_check");
    const avoirDispBadge = document.getElementById("avoir_disp_badge");

    if (opt && opt.value) {
        if (cDebt < 0) {
            // Client has an Avoir
            const avAmt = Math.abs(cDebt);
            if (avoirBanner) avoirBanner.style.display = "flex";
            if (finBox) finBox.style.display = "none";
            if (avoirDispBadge) avoirDispBadge.innerText = "+" + new Intl.NumberFormat('fr-FR').format(avAmt) + " FCFA";
            if (avoirInput) {
                avoirInput.max = avAmt;
                avoirInput.value = avAmt;
                avoirInput.disabled = false;
            }
            if (useAvoirCheck) useAvoirCheck.checked = true;
        } else {
            // Standard or indebted client
            if (avoirBanner) avoirBanner.style.display = "none";
            if (useAvoirCheck) useAvoirCheck.checked = false;
            if (avoirInput) avoirInput.value = 0;

            if (cDebt > 0 || maxCredit > 0) {
                if (finBox) finBox.style.display = "block";
                const cDebtEl = document.getElementById("c_info_debt");
                const cMaxEl = document.getElementById("c_info_max");
                const cAvailEl = document.getElementById("c_info_available");

                if (cDebtEl) cDebtEl.innerText = new Intl.NumberFormat('fr-FR').format(cDebt) + " FCFA";
                if (cMaxEl) cMaxEl.innerText = new Intl.NumberFormat('fr-FR').format(maxCredit) + " FCFA";
                if (cAvailEl) {
                    const avail = Math.max(0, maxCredit - cDebt);
                    cAvailEl.innerText = new Intl.NumberFormat('fr-FR').format(avail) + " FCFA";
                }
            } else {
                if (finBox) finBox.style.display = "none";
            }
        }
    } else {
        if (avoirBanner) avoirBanner.style.display = "none";
        if (finBox) finBox.style.display = "none";
        if (useAvoirCheck) useAvoirCheck.checked = false;
        if (avoirInput) avoirInput.value = 0;
    }

    calculateTotals();
}

function toggleAvoirUsage() {
    const useAvoirCheck = document.getElementById("use_avoir_check");
    const avoirInput = document.getElementById("vente_avoir_amount");
    const clientSel = document.getElementById("vente_client_select");
    const opt = clientSel?.options[clientSel.selectedIndex];
    const cDebt = parseFloat(opt?.dataset?.debt || 0);
    const avAmt = Math.abs(cDebt < 0 ? cDebt : 0);

    if (useAvoirCheck?.checked) {
        if (avoirInput) {
            avoirInput.disabled = false;
            if (parseFloat(avoirInput.value || 0) <= 0) {
                avoirInput.value = avAmt;
            }
        }
    } else {
        if (avoirInput) {
            avoirInput.value = 0;
            avoirInput.disabled = true;
        }
    }
    calculateTotals();
}

function getPackagingDebts() {
    const debts = [];
    let totalCratesOut = 0;
    let totalCratesIn = 0;
    let totalBottlesOut = 0;
    let totalBottlesIn = 0;

    const rows = document.querySelectorAll("#items_tbody tr");
    rows.forEach(tr => {
        const prodSelect = tr.querySelector('.item-product');
        const formatSelect = tr.querySelector('.item-format');
        const qtyInput = tr.querySelector('.item-qty');
        const cratesRetInput = tr.querySelector('.item-crates-ret');
        const bottlesRetInput = tr.querySelector('.item-bottles-ret');

        const prodId = parseInt(prodSelect?.value) || 0;
        const prod = availableProducts.find(p => p.id === prodId);
        if (!prod || !prod.is_returnable) return;

        const qty = parseFloat(qtyInput?.value) || 0;
        const format = formatSelect?.value || 'casier';
        const factor = prod.factor || 12;

        let cOut = 0, bOut = 0, cIn = 0, bIn = 0;
        if (format === 'casier') {
            cOut = qty;
            bOut = qty * factor;
            cIn = parseFloat(cratesRetInput?.value) || 0;
            bIn = parseFloat(bottlesRetInput?.value) || 0;
        } else {
            bOut = qty * (factor / 2);
            bIn = parseFloat(bottlesRetInput?.value) || 0;
        }

        totalCratesOut += cOut;
        totalCratesIn += cIn;
        totalBottlesOut += bOut;
        totalBottlesIn += (cIn * factor + bIn);

        const totalRestitutedBtls = (cIn * factor) + bIn;
        const missingBtls = Math.max(0, bOut - totalRestitutedBtls);
        if (missingBtls > 0) {
            const mCrates = Math.floor(missingBtls / factor);
            const mLoose = missingBtls % factor;
            debts.push({
                product_id: prod.id,
                name: prod.name,
                code: prod.short_code || prod.name,
                factor: factor,
                missingCrates: mCrates,
                missingLoose: mLoose,
                totalMissingBottles: missingBtls
            });
        }
    });

    return {
        debts: debts,
        totalCratesOut: totalCratesOut,
        totalCratesIn: totalCratesIn,
        totalBottlesOut: totalBottlesOut,
        totalBottlesIn: totalBottlesIn
    };
}

function toggleSettlement() {
    const isCredit = document.getElementById("settle_credit").checked;
    const cashAccContainer = document.getElementById("cash_account_container");
    const creditContainer = document.getElementById("credit_details_container");
    const cashAccSelect = document.getElementById("cash_account_select");

    if (isCredit) {
        cashAccContainer.style.display = "none";
        creditContainer.style.display = "block";
        cashAccSelect.removeAttribute("required");
    } else {
        cashAccContainer.style.display = "block";
        creditContainer.style.display = "none";
        cashAccSelect.setAttribute("required", "required");
    }

    calculateTotals();
}

function openVenteConfirmModal() {
    const form = document.getElementById("ventes_multi_form");
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const clientSel = document.getElementById("vente_client_select");
    const clientName = clientSel.options[clientSel.selectedIndex]?.dataset?.name || clientSel.options[clientSel.selectedIndex]?.text || "Client Comptoir";
    const sDate = document.getElementById("vente_sale_date").value;
    const isCredit = document.getElementById("settle_credit").checked;
    const amtPaidInput = document.getElementById("vente_amount_paid");
    const cashSelect = document.getElementById("cash_account_select");

    const amountPaid = isCredit ? 0 : (parseFloat(amtPaidInput?.value || 0) || 0);
    const amountDue = Math.max(0, globalSaleTotal - amountPaid);

    document.getElementById("vmodal_client_name").innerText = clientName;
    document.getElementById("vmodal_sale_date").innerText = sDate;
    
    document.getElementById("vmodal_subtotal_amount").innerText = new Intl.NumberFormat('fr-FR').format(globalSaleSubTotal) + " FCFA";
    const discRow = document.getElementById("vmodal_discount_row");
    if (globalSaleDiscount > 0) {
        discRow.style.display = "flex";
        document.getElementById("vmodal_discount_amount").innerText = "-" + new Intl.NumberFormat('fr-FR').format(globalSaleDiscount) + " FCFA";
    } else {
        discRow.style.display = "none";
    }

    const avoirRow = document.getElementById("vmodal_avoir_row");
    if (globalSaleAvoir > 0) {
        if (avoirRow) {
            avoirRow.style.display = "flex";
            document.getElementById("vmodal_avoir_amount").innerText = "-" + new Intl.NumberFormat('fr-FR').format(globalSaleAvoir) + " FCFA";
        }
    } else {
        if (avoirRow) avoirRow.style.display = "none";
    }

    document.getElementById("vmodal_total_amount").innerText = new Intl.NumberFormat('fr-FR').format(globalSaleTotal) + " FCFA";
    document.getElementById("vmodal_amount_paid").innerText = new Intl.NumberFormat('fr-FR').format(amountPaid) + " FCFA" + (!isCredit && cashSelect.selectedIndex >= 0 ? ` (${cashSelect.options[cashSelect.selectedIndex].text.split('(')[0].trim()})` : '');
    document.getElementById("vmodal_amount_due").innerText = new Intl.NumberFormat('fr-FR').format(amountDue) + " FCFA";

    // Populate Items
    const modalTbody = document.getElementById("vmodal_items_tbody");
    modalTbody.innerHTML = "";

    const rows = document.querySelectorAll("#items_tbody tr");
    rows.forEach(tr => {
        const prodSelect = tr.querySelector('.item-product');
        const formatSelect = tr.querySelector('.item-format');
        const priceInput = tr.querySelector('.item-price');
        const qtyInput = tr.querySelector('.item-qty');
        const cratesRetInput = tr.querySelector('.item-crates-ret');
        const bottlesRetInput = tr.querySelector('.item-bottles-ret');

        const prodId = parseInt(prodSelect?.value) || 0;
        const prod = availableProducts.find(p => p.id === prodId);
        if (!prod) return;

        const qty = parseFloat(qtyInput?.value) || 0;
        const price = parseFloat(priceInput?.value) || 0;
        const format = formatSelect?.value === 'demi' ? 'Demi (0.5)' : 'Entier (1.0)';
        const lineTot = qty * price;
        const code = prod.short_code ? `[${prod.short_code}] ` : '';

        let retText = '<span style="color: #94A3B8;">Perdu</span>';
        if (prod.is_returnable) {
            const factor = prod.factor || 12;
            if (formatSelect?.value === 'casier') {
                const cratesRet = parseFloat(cratesRetInput?.value) || 0;
                const bottlesRet = parseFloat(bottlesRetInput?.value) || 0;
                const missingBtls = Math.max(0, (qty * factor) - (cratesRet * factor + bottlesRet));
                if (missingBtls === 0) {
                    retText = `${cratesRet} rendu(s) 🟢`;
                } else {
                    const cDue = Math.floor(missingBtls / factor);
                    const bDue = missingBtls % factor;
                    let dueStr = '';
                    if (cDue > 0) dueStr += `+${cDue} c.`;
                    if (bDue > 0) dueStr += ` +${bDue} btl(s)`;
                    retText = `${cratesRet} rendu(s) <span style="color: #DC2626; font-size: 0.78rem; font-weight: 700;">(${dueStr.trim()} dû)</span>`;
                }
            } else {
                const btlsRet = parseFloat(bottlesRetInput?.value) || 0;
                const missingBtls = Math.max(0, (qty * (factor / 2)) - btlsRet);
                retText = `${btlsRet} btls rendues ${missingBtls > 0 ? `<span style="color: #DC2626; font-size: 0.78rem; font-weight: 700;">(+${missingBtls} btl due)</span>` : '🟢'}`;
            }
        }

        const mtr = document.createElement("tr");
        mtr.innerHTML = `
            <td><strong>${code}${prod.name}</strong></td>
            <td><span style="padding: 2px 6px; border-radius: 4px; background: #E2E8F0; font-size: 0.78rem;">${format}</span></td>
            <td><strong>${qty}</strong></td>
            <td>${new Intl.NumberFormat('fr-FR').format(price)} F</td>
            <td>${retText}</td>
            <td style="text-align: right; font-weight: 700;">${new Intl.NumberFormat('fr-FR').format(lineTot)} FCFA</td>
        `;
        modalTbody.appendChild(mtr);
    });

    const pkgData = getPackagingDebts();
    document.getElementById("vmodal_crates_out").innerText = `${pkgData.totalCratesOut} casier(s)`;
    document.getElementById("vmodal_crates_in").innerText = `${pkgData.totalCratesIn} casier(s)`;

    if (pkgData.debts.length === 0) {
        document.getElementById("vmodal_crates_due").innerHTML = `<span style="color: #16A34A; font-weight: 700;">0 casier (Échange 1 pour 1 🟢)</span>`;
    } else {
        const modalDebtList = pkgData.debts.map(d => {
            if (d.missingCrates > 0) {
                return `${d.missingCrates} c. [${d.code}]` + (d.missingLoose > 0 ? ` + ${d.missingLoose} btl(s)` : '');
            } else {
                return `+${d.missingLoose} btl(s) [${d.code}]`;
            }
        });
        document.getElementById("vmodal_crates_due").innerHTML = `<span style="color: #DC2626; font-weight: 700;">⚠️ ${modalDebtList.join(' | ')}</span>`;
    }

    document.getElementById("vente_confirm_modal").style.display = "flex";
}

function closeVenteConfirmModal() {
    document.getElementById("vente_confirm_modal").style.display = "none";
}

function submitFinalSale() {
    document.getElementById("ventes_multi_form").submit();
}

document.addEventListener("DOMContentLoaded", function() {
    addNewProductRow();
    onClientChange();
});
</script>
