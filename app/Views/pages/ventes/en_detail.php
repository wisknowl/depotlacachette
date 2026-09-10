<div class="page-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px;">
    <div>
        <h1 style="display: flex; align-items: center; gap: 10px; margin: 0; font-size: 1.5rem;">
            <i class='bx bx-wine' style="color: #059669;"></i> Vente au Détail &bull; Service à la Bouteille
        </h1>
        <p style="margin: 4px 0 0 0; color: #64748B; font-size: 0.88rem;">
            Enregistrement rapide des ventes à l'unité, consommation sur place (bar/snack) et emporté au verre.
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="<?= BASE_URL ?>/ventes" class="btn btn-primary" style="background: #64748B;">
            <i class='bx bx-arrow-back'></i> Retour au Journal
        </a>
        <a href="<?= BASE_URL ?>/ventes/form" class="btn btn-primary" style="background: var(--c-navy);">
            <i class='bx bx-package'></i> Passer en Vente en Gros (Casiers)
        </a>
    </div>
</div>

<?php if (!empty($flash_error)): ?>
    <div style="background: #FEE2E2; border-left: 4px solid var(--c-danger); color: #991B1B; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
        <i class='bx bx-error-circle' style="font-size: 1.4rem;"></i>
        <span><?= htmlspecialchars($flash_error) ?></span>
    </div>
<?php endif; ?>

<form action="<?= BASE_URL ?>/ventes/save-en-detail" method="POST" id="ventes_detail_form">
    
    <!-- TOP CONFIGURATION CARD -->
    <div class="card" style="margin-bottom: 20px; padding: 20px; background: white;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
            
            <!-- SALE DATE -->
            <div>
                <label class="form-label" style="font-weight: 700; font-size: 0.85rem; color: #334155;">
                    Date de la Vente <span style="color: var(--c-danger);">*</span>
                </label>
                <input type="date" name="sale_date" id="vente_sale_date" class="form-control" value="<?= date('Y-m-d') ?>" required style="font-size: 0.95rem;">
            </div>

            <!-- CLIENT -->
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <label class="form-label" style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0;">
                        Client Bénéficiaire <span style="color: var(--c-danger);">*</span>
                    </label>
                    <button type="button" class="btn btn-sm btn-accent" style="padding: 1px 8px; font-size: 0.75rem; background: #0284C7; border-color: #0284C7;" onclick="openQuickAvoirModal()">
                        <i class='bx bx-plus-circle'></i> + Créer Avoir
                    </button>
                </div>
                <select name="client_id" id="vente_client_select" class="form-control" required onchange="onClientChange()" style="font-size: 0.95rem;">
                    <?php foreach ($clients as $c): ?>
                        <?php 
                            $isComptoir = (strtolower(trim($c['name'])) === 'client comptoir' || strtolower(trim($c['name'])) === 'comptoir');
                            $debtVal = floatval($c['solde_du']);
                            $avoirText = ($debtVal < 0) 
                                ? '• 🟢 Avoir: +' . number_format(abs($debtVal), 0, ',', ' ') . ' FCFA' 
                                : ($debtVal > 0 ? '• 🔴 Dette: ' . number_format($debtVal, 0, ',', ' ') . ' FCFA' : '');
                        ?>
                        <option value="<?= $c['id'] ?>" 
                                data-debt="<?= $debtVal ?>" 
                                data-max-credit="<?= $c['max_credit'] ?>" 
                                data-name="<?= htmlspecialchars($c['name']) ?>"
                                <?= $isComptoir ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?> <?= $avoirText ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- REFERENCE -->
            <div>
                <label class="form-label" style="font-weight: 700; font-size: 0.85rem; color: #334155;">
                    N° Référence / Bon / Table
                </label>
                <input type="text" name="reference" class="form-control" placeholder="Auto ou ex: Table 4, Serveur 2..." style="font-size: 0.95rem;">
            </div>
        </div>

        <!-- BANNER AVOIR DISPONIBLE (IF CLIENT HAS CREDIT) -->
        <div id="client_avoir_banner" style="display: none; background: #ECFDF5; border: 1px solid #A7F3D0; border-left: 4px solid #059669; padding: 12px 16px; border-radius: 8px; margin-top: 15px; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #D1FAE5; display: flex; align-items: center; justify-content: center; color: #059669; font-size: 1.3rem;">
                    <i class='bx bx-gift'></i>
                </div>
                <div>
                    <div style="font-size: 0.85rem; font-weight: 700; color: #065F46;">
                        Avoir Disponible : <span id="avoir_disp_badge" style="font-size: 1rem; color: #047857;">+0 FCFA</span>
                    </div>
                    <div style="font-size: 0.78rem; color: #059669;">
                        Ce client dispose d'une avance/monnaie en attente.
                    </div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 0.88rem; font-weight: 700; color: #065F46; margin: 0;">
                    <input type="checkbox" id="use_avoir_check" checked onchange="toggleAvoirUsage()" style="width: 18px; height: 18px; accent-color: #059669;">
                    <span>Déduire l'Avoir sur cette vente</span>
                </label>
                <input type="number" step="any" min="0" name="avoir_amount" id="vente_avoir_amount" class="form-control" style="width: 140px; font-weight: 800; color: #065F46; border-color: #A7F3D0; padding: 4px 10px; font-size: 0.95rem; text-align: right;" value="0" oninput="calculateTotals()">
            </div>
        </div>

        <!-- CLIENT FINANCIAL INFO BOX (IF INDEBTED) -->
        <div id="client_financial_box" style="display: none; background: #F8FAFC; border: 1px solid #E2E8F0; padding: 10px 14px; border-radius: 6px; margin-top: 12px; font-size: 0.85rem;">
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div>Dette Financière Actuelle : <strong id="c_info_debt" style="color: #DC2626;">0 FCFA</strong></div>
                <div>Plafond de Crédit : <strong id="c_info_max">0 FCFA</strong></div>
                <div>Crédit Disponible : <strong id="c_info_available" style="color: #059669;">0 FCFA</strong></div>
            </div>
        </div>
    </div>

    <!-- MAIN BOTTLES BASKET CARD -->
    <div class="card" style="margin-bottom: 20px; padding: 0; overflow: hidden; background: white;">
        <div style="padding: 16px 20px; background: #F8FAFC; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div style="font-weight: 700; color: var(--c-navy); font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                <i class='bx bx-cart' style="font-size: 1.3rem; color: #059669;"></i> Panier des Boissons au Détail
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn btn-accent" onclick="addNewBottleRow()" style="background: #059669; border-color: #059669; font-size: 0.88rem; padding: 6px 14px;">
                    <i class='bx bx-plus'></i> + Ajouter une Boisson
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table" style="margin-bottom: 0; font-size: 0.9rem;" id="items_table">
                <thead style="background: #F1F5F9; color: #475569;">
                    <tr>
                        <th style="width: 32%;">Boisson / Produit <span style="color: var(--c-danger);">*</span></th>
                        <th style="width: 14%; text-align: center;">Stock Magasin</th>
                        <th style="width: 22%;">Mode Emballage</th>
                        <th style="width: 10%; text-align: center;">Quantité (Btls) <span style="color: var(--c-danger);">*</span></th>
                        <th style="width: 11%; text-align: right;">Prix Unitaire (Btl) <span style="color: var(--c-danger);">*</span></th>
                        <th style="width: 11%; text-align: right;">Total Ligne</th>
                        <th style="width: 5%; text-align: center;"></th>
                    </tr>
                </thead>
                <tbody id="items_tbody">
                    <!-- Dynamic Rows Inserted by JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- BOTTOM TOTALS & PAYMENT SECTION -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start;">
        
        <!-- LEFT: EMBALLAGES SUMMARY & NOTES -->
        <div class="card" style="padding: 20px; background: white;">
            <div style="font-weight: 700; color: var(--c-navy); font-size: 0.95rem; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                <i class='bx bx-archive' style="color: #0284C7;"></i> Bilan des Bouteilles en Verre sur cette Vente
            </div>
            
            <div id="bottle_packaging_summary" style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 12px 16px; border-radius: 8px; font-size: 0.88rem; min-height: 48px; display: flex; align-items: center;">
                <span style="color: #16A34A; font-weight: 700;"><i class='bx bx-check-circle'></i> Aucune dette de verre générée (Bues sur place / Verre rendu 🟢)</span>
            </div>

            <div style="margin-top: 15px;">
                <label class="form-label" style="font-size: 0.85rem; font-weight: 700; color: #475569;">Notes & Observations</label>
                <input type="text" name="notes" class="form-control" placeholder="Ex: Servi au bar, consommation en salle..." style="font-size: 0.88rem;">
            </div>
        </div>

        <!-- RIGHT: FINANCIAL SETTLEMENT CARD -->
        <div class="card" style="padding: 20px; background: white;">
            <div style="font-weight: 700; color: var(--c-navy); font-size: 0.95rem; margin-bottom: 15px; display: flex; align-items: center; justify-content: space-between;">
                <span><i class='bx bx-wallet' style="color: #059669;"></i> Récapitulatif & Règlement</span>
                <span id="grand_total_badge" style="font-size: 1.25rem; font-weight: 800; color: var(--c-navy-dark);">0 FCFA</span>
            </div>

            <!-- SUB-TOTAL -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-size: 0.9rem;">
                <span style="color: #64748B;">Sous-Total Brut :</span>
                <span id="disp_subtotal" style="font-weight: 700; color: #334155;">0 FCFA</span>
            </div>

            <!-- DISCOUNT -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-size: 0.9rem;">
                <span style="color: #64748B;">Remise Commerciale (Rabais) :</span>
                <div style="width: 140px;">
                    <input type="number" step="any" min="0" name="discount_amount" id="vente_discount_amount" class="form-control" placeholder="0" style="padding: 4px 8px; font-size: 0.88rem; text-align: right;" oninput="calculateTotals()">
                </div>
            </div>

            <!-- AVOIR DEDUCTED -->
            <div id="row_avoir_deducted" style="display: none; justify-content: space-between; align-items: center; margin-bottom: 8px; font-size: 0.9rem; color: #059669;">
                <span style="font-weight: 700;">Avoir Client Déduit :</span>
                <span id="disp_avoir_deducted" style="font-weight: 800;">-0 FCFA</span>
            </div>

            <!-- NET PAYABLE -->
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-top: 1px dashed #CBD5E1; border-bottom: 1px dashed #CBD5E1; margin: 10px 0; font-size: 1rem;">
                <span style="font-weight: 800; color: #1E293B;">Net à Payer (Espèces) :</span>
                <span id="disp_net_payable" style="font-weight: 800; color: #059669; font-size: 1.15rem;">0 FCFA</span>
            </div>

            <!-- PAYMENT MODE RADIOS -->
            <div style="margin-bottom: 12px;">
                <div style="display: flex; gap: 20px; align-items: center;">
                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 0.88rem; font-weight: 700; color: #1E293B; margin: 0;">
                        <input type="radio" name="settlement_type" value="cash" id="settle_cash" checked onchange="toggleSettlement()" style="accent-color: #059669; width: 16px; height: 16px;">
                        <span>🟢 Au Comptant (Caisse)</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 0.88rem; font-weight: 700; color: #DC2626; margin: 0;">
                        <input type="radio" name="settlement_type" value="credit" id="settle_credit" onchange="toggleSettlement()" style="accent-color: #DC2626; width: 16px; height: 16px;">
                        <span>🔴 Vente à Crédit</span>
                    </label>
                </div>
            </div>

            <!-- CASH ACCOUNT -->
            <div id="cash_account_container" style="margin-bottom: 12px;">
                <label class="form-label" style="font-size: 0.82rem; font-weight: 700; color: #475569; margin-bottom: 4px;">Compte de Caisse Récepteur</label>
                <select name="cash_account_id" id="cash_account_select" class="form-control" required style="font-size: 0.88rem;">
                    <?php foreach ($cash_accounts as $ca): ?>
                        <option value="<?= $ca['id'] ?>">
                            <?= htmlspecialchars($ca['name']) ?> (Solde : <?= number_format($ca['current_balance'], 0, ',', ' ') ?> FCFA)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- AMOUNT PAID / PARTIAL CASH -->
            <div id="amount_paid_container" style="margin-bottom: 15px;">
                <label class="form-label" style="font-size: 0.82rem; font-weight: 700; color: #475569; margin-bottom: 4px;">
                    Montant Encaissé Immédiatement (Espèces)
                </label>
                <input type="number" step="any" min="0" name="amount_paid" id="vente_amount_paid" class="form-control" style="font-size: 1.05rem; font-weight: 800; color: #059669;" oninput="onAmountPaidInput()">
                
                <div id="partial_payment_notice" style="display: none; margin-top: 6px; font-size: 0.8rem; color: #D97706; font-weight: 700;">
                    ⚠️ Reliquat non payé : <span id="partial_due_disp">0 FCFA</span> sera porté au compte client.
                </div>

                <!-- EXCESS PAYMENT / AVOIR NOTICE -->
                <div id="excess_payment_notice" style="display: none; margin-top: 8px; padding: 10px 14px; background: #ECFDF5; border-left: 4px solid #10B981; border-radius: 6px; font-size: 0.85rem; color: #065F46;">
                    <div style="display: flex; align-items: flex-start; gap: 6px;">
                        <i class='bx bx-check-circle' style="font-size: 1.25rem; color: #059669; flex-shrink: 0; margin-top: 1px;"></i>
                        <div>
                            <strong>Paiement avec Surplus (Avoir Automatique) :</strong><br>
                            Versé : <strong id="excess_paid_disp">0 FCFA</strong> (Net : <strong id="excess_net_disp">0 FCFA</strong>).<br>
                            Surplus de <strong id="excess_surplus_disp" style="color: #059669; font-size: 0.95rem;">+0 FCFA</strong> crédité en <strong>Avoir Client</strong> et encaissé en caisse.
                        </div>
                    </div>
                </div>
            </div>

            <!-- CREDIT LIMIT WARNING -->
            <div id="credit_limit_warning" style="display: none; background: #FEF2F2; border-left: 4px solid #DC2626; color: #991B1B; padding: 10px; border-radius: 6px; font-size: 0.82rem; margin-bottom: 15px;"></div>

            <!-- SUBMIT BUTTON -->
            <button type="button" class="btn btn-accent" onclick="openDetailConfirmModal()" style="width: 100%; padding: 12px; font-size: 1.05rem; font-weight: 800; background: #059669; border-color: #059669; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i class='bx bx-check-double'></i> Vérifier & Valider la Vente au Détail
            </button>
        </div>
    </div>
</form>

<!-- CONFIRMATION MODAL -->
<div id="vente_detail_confirm_modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
    <div class="card" style="max-width: 650px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; background: #F8FAFC; border-bottom: 1px solid #E2E8F0;">
            <span style="font-weight: 800; color: var(--c-navy); font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                <i class='bx bx-check-shield' style="color: #059669; font-size: 1.4rem;"></i> Confirmation de la Vente au Détail
            </span>
            <button type="button" onclick="closeDetailConfirmModal()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: #64748B;">&times;</button>
        </div>
        <div class="card-body" style="padding: 20px;">
            
            <div style="background: #F1F5F9; padding: 12px 16px; border-radius: 8px; margin-bottom: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.9rem;">
                <div>Client : <strong id="vmodal_client_name" style="color: var(--c-navy);"></strong></div>
                <div>Date : <strong id="vmodal_sale_date"></strong></div>
            </div>

            <!-- ITEMS TABLE -->
            <div class="table-responsive" style="margin-bottom: 15px;">
                <table class="table" style="font-size: 0.85rem; margin-bottom: 0;">
                    <thead style="background: #F8FAFC;">
                        <tr>
                            <th>Boisson</th>
                            <th>Mode Verre</th>
                            <th style="text-align: center;">Qté (Btls)</th>
                            <th style="text-align: right;">Prix Unitaire</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody id="vmodal_items_tbody"></tbody>
                </table>
            </div>

            <!-- FINANCIAL SUMMARY -->
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 12px 16px; border-radius: 8px; margin-bottom: 15px; font-size: 0.9rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span>Sous-Total Brut :</span>
                    <strong id="vmodal_subtotal_amount">0 FCFA</strong>
                </div>
                <div id="vmodal_discount_row" style="display: none; justify-content: space-between; margin-bottom: 4px; color: #DC2626;">
                    <span>Remise Commerciale :</span>
                    <strong id="vmodal_discount_amount">-0 FCFA</strong>
                </div>
                <div id="vmodal_avoir_row" style="display: none; justify-content: space-between; margin-bottom: 4px; color: #059669;">
                    <span>Avoir Client Déduit :</span>
                    <strong id="vmodal_avoir_amount">-0 FCFA</strong>
                </div>
                <div style="display: flex; justify-content: space-between; border-top: 1px solid #E2E8F0; padding-top: 6px; font-size: 1.05rem;">
                    <span style="font-weight: 800;">Total Facturé :</span>
                    <strong id="vmodal_total_amount" style="color: #059669;">0 FCFA</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 4px; font-size: 0.85rem; color: #64748B;">
                    <span>Montant Encaissé (Caisse) :</span>
                    <strong id="vmodal_amount_paid" style="color: #059669;">0 FCFA</strong>
                </div>
                <div id="vmodal_excess_row" style="display: none; justify-content: space-between; margin-top: 4px; font-size: 0.85rem; color: #059669; background: #ECFDF5; padding: 4px 8px; border-radius: 4px; border: 1px dashed #10B981; font-weight: 700;">
                    <span><i class='bx bx-plus-circle'></i> Avoir Généré (Surplus) :</span>
                    <strong id="vmodal_excess_amount">+0 FCFA</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #64748B;">
                    <span>Reste Dû (Dette) :</span>
                    <strong id="vmodal_amount_due" style="color: #DC2626;">0 FCFA</strong>
                </div>
            </div>

            <!-- PACKAGING BILAN -->
            <div style="background: #EFF6FF; border: 1px solid #BFDBFE; padding: 10px 14px; border-radius: 6px; font-size: 0.85rem; margin-bottom: 20px;">
                <div style="color: #1E40AF; font-weight: 700; margin-bottom: 4px;">Bilan des Bouteilles en Verre :</div>
                <div id="vmodal_bottle_debts" style="font-weight: 700; color: #16A34A;">0 dette de verre (Consommation sur place ou échangée 🟢)</div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-primary" onclick="closeDetailConfirmModal()" style="background: #64748B;">Modifier la saisie</button>
                <button type="button" class="btn btn-accent" onclick="submitFinalDetailSale()" style="background: #059669; border-color: #059669; font-weight: 800; padding: 10px 20px;">
                    <i class='bx bx-printer'></i> Confirmer & Enregistrer la Vente
                </button>
            </div>
        </div>
    </div>
</div>

<!-- QUICK AVOIR MODAL (HELPER) -->
<div id="quick_avoir_modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; justify-content: center; align-items: center; padding: 20px;">
    <div class="card" style="max-width: 480px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-weight: 700; color: var(--c-navy); font-size: 1rem;">
                <i class='bx bx-gift' style="color: #16A34A;"></i> Enregistrer un Avoir / Reliquat Monnaie
            </span>
            <button type="button" onclick="closeQuickAvoirModal()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: #64748B;">&times;</button>
        </div>
        <div class="card-body">
            <div id="q_avoir_error" style="display: none; background: #FEE2E2; color: #991B1B; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; margin-bottom: 12px;"></div>
            
            <div class="form-group" style="margin-bottom: 12px;">
                <label class="form-label" style="font-size: 0.82rem; font-weight: 700;">Client Bénéficiaire <span style="color: var(--c-danger);">*</span></label>
                <select id="q_avoir_client_id" class="form-control" style="font-size: 0.88rem;">
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 12px;">
                <label class="form-label" style="font-size: 0.82rem; font-weight: 700;">Montant de l'Avoir (FCFA) <span style="color: var(--c-danger);">*</span></label>
                <input type="number" step="any" min="1" id="q_avoir_amount" class="form-control" placeholder="Ex: 500, 1000..." style="font-size: 1rem; font-weight: 700;">
            </div>

            <div class="form-group" style="margin-bottom: 12px;">
                <label class="form-label" style="font-size: 0.82rem; font-weight: 700;">Compte de Caisse Récepteur <span style="color: var(--c-danger);">*</span></label>
                <select id="q_avoir_cash_account_id" class="form-control" style="font-size: 0.88rem;">
                    <?php foreach ($cash_accounts as $ca): ?>
                        <option value="<?= $ca['id'] ?>"><?= htmlspecialchars($ca['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label" style="font-size: 0.82rem; font-weight: 700;">Notes / Motif</label>
                <input type="text" id="q_avoir_notes" class="form-control" placeholder="Ex: Monnaie non rendue, avance..." style="font-size: 0.85rem;">
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-primary" onclick="closeQuickAvoirModal()" style="background: #64748B;">Annuler</button>
                <button type="button" class="btn btn-accent" id="q_avoir_submit_btn" onclick="submitQuickAvoir()" style="font-weight: 700; background: #059669; border-color: #059669;">
                    <i class='bx bx-check-circle'></i> Enregistrer l'Avoir
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const availableProducts = <?= json_encode($products) ?>;
let rowCounter = 0;
let isAmountPaidManuallySet = false;

let globalSaleSubTotal = 0;
let globalSaleDiscount = 0;
let globalSaleAvoir = 0;
let globalSaleTotal = 0;

function addNewBottleRow() {
    rowCounter++;
    const tbody = document.getElementById("items_tbody");
    const tr = document.createElement("tr");
    tr.id = `row_${rowCounter}`;

    let productOptions = '<option value="">-- Choisir une boisson --</option>';
    availableProducts.forEach(p => {
        const factor = p.factor || 12;
        const totalBtlsInStock = Math.floor((p.current_stock || 0) * factor);
        const code = p.short_code ? `[${p.short_code}] ` : '';
        productOptions += `<option value="${p.id}" data-price="${p.price_unite || 0}" data-factor="${factor}" data-stock="${p.current_stock || 0}" data-returnable="${p.is_returnable}">${code}${p.name} (Stock: ${totalBtlsInStock} btls / ${p.current_stock || 0} casiers)</option>`;
    });

    tr.innerHTML = `
        <td>
            <select name="items[${rowCounter}][product_id]" class="item-product form-control" required onchange="onBottleProductChange(${rowCounter})" style="font-size: 0.88rem;">
                ${productOptions}
            </select>
            <div id="item_emb_badge_${rowCounter}" style="font-size: 0.75rem; margin-top: 3px;"></div>
        </td>
        <td style="text-align: center;">
            <span class="stock-badge" id="stock_badge_${rowCounter}" style="padding: 2px 6px; border-radius: 4px; font-weight: 700; font-size: 0.8rem; background: #E2E8F0; color: #475569;">
                -
            </span>
        </td>
        <td>
            <select name="items[${rowCounter}][emballage_mode]" class="item-emb-mode form-control" onchange="onEmbModeChange(${rowCounter})" style="font-size: 0.85rem;">
                <option value="sur_place">🟢 Sur place (Snack / Bar)</option>
                <option value="echange">🔵 À emporter (Échange verre)</option>
                <option value="dette">🔴 À emporter (Sans verre / Dette)</option>
            </select>
            <div id="ret_input_box_${rowCounter}" style="display: none; margin-top: 4px; font-size: 0.78rem; align-items: center; gap: 4px;">
                <span>Vides rendus :</span>
                <input type="number" step="1" min="0" name="items[${rowCounter}][bottles_returned]" class="item-bottles-ret form-control" value="0" style="width: 70px; padding: 2px 6px; font-size: 0.82rem; text-align: center;" oninput="calculateTotals()">
            </div>
        </td>
        <td>
            <input type="number" step="1" min="1" name="items[${rowCounter}][quantity]" class="item-qty form-control" required value="1" style="font-size: 0.95rem; font-weight: 700; text-align: center;" oninput="onBottleQtyChange(${rowCounter})">
        </td>
        <td>
            <input type="number" step="any" min="0" name="items[${rowCounter}][unit_price]" class="item-price form-control" required placeholder="0" style="font-size: 0.95rem; text-align: right;" oninput="calculateTotals()">
        </td>
        <td style="text-align: right; font-weight: 800; font-size: 1rem; color: var(--c-navy-dark);" id="row_total_${rowCounter}">
            0 FCFA
        </td>
        <td style="text-align: center;">
            <button type="button" class="btn btn-primary" onclick="removeBottleRow(${rowCounter})" style="background: #EF4444; padding: 4px 8px; border-radius: 6px;" title="Supprimer la ligne">
                <i class='bx bx-trash'></i>
            </button>
        </td>
    `;

    tbody.appendChild(tr);
    calculateTotals();
}

function removeBottleRow(id) {
    const row = document.getElementById(`row_${id}`);
    if (row) {
        row.remove();
        calculateTotals();
    }
}

function onBottleProductChange(id) {
    const row = document.getElementById(`row_${id}`);
    const prodSelect = row.querySelector('.item-product');
    const priceInput = row.querySelector('.item-price');
    const stockBadge = document.getElementById(`stock_badge_${id}`);
    const embBadge = document.getElementById(`item_emb_badge_${id}`);
    const embModeSelect = row.querySelector('.item-emb-mode');

    const prodId = parseInt(prodSelect.value) || 0;
    const prod = availableProducts.find(p => p.id === prodId);

    if (!prod) {
        stockBadge.innerText = "-";
        stockBadge.style.background = "#E2E8F0";
        stockBadge.style.color = "#475569";
        embBadge.innerText = "";
        priceInput.value = "";
        calculateTotals();
        return;
    }

    const factor = prod.factor || 12;
    const totalBtlsInStock = Math.floor((prod.current_stock || 0) * factor);
    stockBadge.innerText = `${totalBtlsInStock} btls`;

    if (totalBtlsInStock <= 0) {
        stockBadge.style.background = "#FEE2E2";
        stockBadge.style.color = "#DC2626";
    } else {
        stockBadge.style.background = "#DCFCE7";
        stockBadge.style.color = "#16A34A";
    }

    if (prod.is_returnable) {
        embBadge.innerHTML = `<span style="color: #16A34A; font-weight: 700;">🟢 Verre Consigné</span>`;
        embModeSelect.disabled = false;
    } else {
        embBadge.innerHTML = `<span style="color: #64748B;">⚪ Emballage Perdu (PET/Canette)</span>`;
        embModeSelect.value = "sur_place";
        embModeSelect.disabled = true;
    }

        const unitPr = prod.calculated_price_unite || prod.price_unite || (prod.factor ? Math.round(prod.price_casier / prod.factor) : 0);
        priceInput.value = unitPr;
        onBottleQtyChange(id);
}

function onBottleQtyChange(id) {
    const row = document.getElementById(`row_${id}`);
    const qtyInput = row.querySelector('.item-qty');
    const btlsRetInput = row.querySelector('.item-bottles-ret');
    const embMode = row.querySelector('.item-emb-mode')?.value;

    const qty = parseFloat(qtyInput?.value) || 1;
    if (embMode === 'echange' && btlsRetInput) {
        btlsRetInput.value = qty;
    }
    calculateTotals();
}

function onEmbModeChange(id) {
    const row = document.getElementById(`row_${id}`);
    const embMode = row.querySelector('.item-emb-mode')?.value;
    const retBox = document.getElementById(`ret_input_box_${id}`);
    const btlsRetInput = row.querySelector('.item-bottles-ret');
    const qty = parseFloat(row.querySelector('.item-qty')?.value) || 1;

    if (embMode === 'echange') {
        retBox.style.display = "flex";
        if (btlsRetInput) btlsRetInput.value = qty;
    } else {
        retBox.style.display = "none";
        if (btlsRetInput) btlsRetInput.value = 0;
    }
    calculateTotals();
}

function calculateTotals() {
    let subTotal = 0;
    const bottleDebts = [];

    const rows = document.querySelectorAll("#items_tbody tr");
    rows.forEach(tr => {
        const prodSelect = tr.querySelector('.item-product');
        const qtyInput = tr.querySelector('.item-qty');
        const priceInput = tr.querySelector('.item-price');
        const embModeSelect = tr.querySelector('.item-emb-mode');
        const btlsRetInput = tr.querySelector('.item-bottles-ret');

        const prodId = parseInt(prodSelect?.value) || 0;
        const prod = availableProducts.find(p => p.id === prodId);
        if (!prod) return;

        const qty = parseFloat(qtyInput?.value) || 0;
        const price = parseFloat(priceInput?.value) || 0;
        const lineTot = qty * price;
        subTotal += lineTot;

        const rowTotEl = tr.querySelector(`[id^="row_total_"]`);
        if (rowTotEl) {
            rowTotEl.innerText = new Intl.NumberFormat('fr-FR').format(lineTot) + " FCFA";
        }

        // Packaging Math
        if (prod.is_returnable) {
            const embMode = embModeSelect?.value || 'sur_place';
            let bDue = 0;
            if (embMode === 'dette') {
                bDue = qty;
            } else if (embMode === 'echange') {
                const bRet = parseFloat(btlsRetInput?.value) || 0;
                bDue = Math.max(0, qty - bRet);
            }
            if (bDue > 0) {
                bottleDebts.push({
                    name: prod.name,
                    code: prod.short_code || prod.name,
                    missingBottles: bDue
                });
            }
        }
    });

    globalSaleSubTotal = subTotal;
    const discInput = document.getElementById("vente_discount_amount");
    const discount = Math.min(subTotal, Math.max(0, parseFloat(discInput?.value || 0)));
    globalSaleDiscount = discount;

    const grossNet = Math.max(0, subTotal - discount);

    // Client & Avoir
    const clientSel = document.getElementById("vente_client_select");
    const opt = clientSel?.options[clientSel.selectedIndex];
    const curDebt = parseFloat(opt?.dataset?.debt || 0);
    const maxCredit = parseFloat(opt?.dataset?.maxCredit || 0);

    const useAvoirCheck = document.getElementById("use_avoir_check");
    const avoirInput = document.getElementById("vente_avoir_amount");
    let avoirUsed = 0;
    if (useAvoirCheck?.checked && avoirInput) {
        const reqAvoir = Math.max(0, parseFloat(avoirInput.value) || 0);
        const availAvoir = Math.abs(curDebt < 0 ? curDebt : 0);
        avoirUsed = Math.min(reqAvoir, availAvoir, grossNet);
        avoirInput.value = avoirUsed;
    }
    globalSaleAvoir = avoirUsed;

    const netPayable = Math.max(0, grossNet - avoirUsed);
    globalSaleTotal = netPayable;

    // Render displays
    document.getElementById("disp_subtotal").innerText = new Intl.NumberFormat('fr-FR').format(subTotal) + " FCFA";
    document.getElementById("grand_total_badge").innerText = new Intl.NumberFormat('fr-FR').format(netPayable) + " FCFA";
    document.getElementById("disp_net_payable").innerText = new Intl.NumberFormat('fr-FR').format(netPayable) + " FCFA";

    const avoirRow = document.getElementById("row_avoir_deducted");
    if (avoirUsed > 0 && avoirRow) {
        avoirRow.style.display = "flex";
        document.getElementById("disp_avoir_deducted").innerText = "-" + new Intl.NumberFormat('fr-FR').format(avoirUsed) + " FCFA";
    } else if (avoirRow) {
        avoirRow.style.display = "none";
    }

    // Packaging Summary Box
    const embSummary = document.getElementById("bottle_packaging_summary");
    if (embSummary) {
        if (bottleDebts.length === 0) {
            embSummary.innerHTML = `<span style="color: #16A34A; font-weight: 700;"><i class='bx bx-check-circle'></i> Aucune dette de verre générée (Bues sur place / Verre rendu 🟢)</span>`;
        } else {
            const badges = bottleDebts.map(d => `<span style="background: #FEF2F2; color: #DC2626; padding: 2px 8px; border-radius: 4px; border: 1px solid #FECACA; font-size: 0.82rem; font-weight: 700;">+${d.missingBottles} btl [${d.code}]</span>`);
            embSummary.innerHTML = `<span style="color: #DC2626; font-weight: 700;"><i class='bx bx-archive'></i> Dettes Bouteilles Vrac : </span> &nbsp;` + badges.join(' ');
        }
    }

    // Amount Paid & Partial Cash / Excess Avoir
    const isCredit = document.getElementById("settle_credit")?.checked || false;
    const amtPaidInput = document.getElementById("vente_amount_paid");
    const partialNotice = document.getElementById("partial_payment_notice");
    const excessNotice = document.getElementById("excess_payment_notice");

    let amountPaid = netPayable;
    if (isCredit) {
        amountPaid = 0;
        if (partialNotice) partialNotice.style.display = "none";
        if (excessNotice) excessNotice.style.display = "none";
    } else {
        if (!isAmountPaidManuallySet && amtPaidInput) {
            amtPaidInput.value = netPayable;
            amountPaid = netPayable;
        } else if (amtPaidInput) {
            amountPaid = parseFloat(amtPaidInput.value) || 0;
        }

        const remainingDebt = Math.max(0, netPayable - amountPaid);
        const excessCash = Math.max(0, amountPaid - netPayable);

        if (excessCash > 0 && excessNotice) {
            excessNotice.style.display = "block";
            if (partialNotice) partialNotice.style.display = "none";
            const paidDisp = document.getElementById("excess_paid_disp");
            const netDisp = document.getElementById("excess_net_disp");
            const surplusDisp = document.getElementById("excess_surplus_disp");
            if (paidDisp) paidDisp.innerText = new Intl.NumberFormat('fr-FR').format(amountPaid) + " FCFA";
            if (netDisp) netDisp.innerText = new Intl.NumberFormat('fr-FR').format(netPayable) + " FCFA";
            if (surplusDisp) surplusDisp.innerText = "+" + new Intl.NumberFormat('fr-FR').format(excessCash) + " FCFA";
        } else {
            if (excessNotice) excessNotice.style.display = "none";
            if (remainingDebt > 0 && partialNotice) {
                partialNotice.style.display = "block";
                const dueDisp = document.getElementById("partial_due_disp");
                if (dueDisp) dueDisp.innerText = new Intl.NumberFormat('fr-FR').format(remainingDebt) + " FCFA";
            } else if (partialNotice) {
                partialNotice.style.display = "none";
            }
        }
    }

    // Credit limit warning
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

function onAmountPaidInput() {
    isAmountPaidManuallySet = true;
    calculateTotals();
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

function toggleSettlement() {
    const isCredit = document.getElementById("settle_credit").checked;
    const cashAccContainer = document.getElementById("cash_account_container");
    const amtPaidContainer = document.getElementById("amount_paid_container");
    const cashAccSelect = document.getElementById("cash_account_select");

    if (isCredit) {
        cashAccContainer.style.display = "none";
        amtPaidContainer.style.display = "none";
        cashAccSelect.removeAttribute("required");
    } else {
        cashAccContainer.style.display = "block";
        amtPaidContainer.style.display = "block";
        cashAccSelect.setAttribute("required", "required");
    }

    calculateTotals();
}

function openDetailConfirmModal() {
    const form = document.getElementById("ventes_detail_form");
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
    if (globalSaleAvoir > 0 && avoirRow) {
        avoirRow.style.display = "flex";
        document.getElementById("vmodal_avoir_amount").innerText = "-" + new Intl.NumberFormat('fr-FR').format(globalSaleAvoir) + " FCFA";
    } else if (avoirRow) {
        avoirRow.style.display = "none";
    }

    document.getElementById("vmodal_total_amount").innerText = new Intl.NumberFormat('fr-FR').format(globalSaleTotal) + " FCFA";
    document.getElementById("vmodal_amount_paid").innerText = new Intl.NumberFormat('fr-FR').format(amountPaid) + " FCFA" + (!isCredit && cashSelect.selectedIndex >= 0 ? ` (${cashSelect.options[cashSelect.selectedIndex].text.split('(')[0].trim()})` : '');
    
    const excessRow = document.getElementById("vmodal_excess_row");
    const excessCash = Math.max(0, amountPaid - globalSaleTotal);
    if (excessCash > 0 && excessRow) {
        excessRow.style.display = "flex";
        document.getElementById("vmodal_excess_amount").innerText = "+" + new Intl.NumberFormat('fr-FR').format(excessCash) + " FCFA";
    } else if (excessRow) {
        excessRow.style.display = "none";
    }

    document.getElementById("vmodal_amount_due").innerText = new Intl.NumberFormat('fr-FR').format(amountDue) + " FCFA";

    // Populate Modal Items
    const modalTbody = document.getElementById("vmodal_items_tbody");
    modalTbody.innerHTML = "";

    const bottleDebts = [];
    const rows = document.querySelectorAll("#items_tbody tr");
    rows.forEach(tr => {
        const prodSelect = tr.querySelector('.item-product');
        const qtyInput = tr.querySelector('.item-qty');
        const priceInput = tr.querySelector('.item-price');
        const embModeSelect = tr.querySelector('.item-emb-mode');
        const btlsRetInput = tr.querySelector('.item-bottles-ret');

        const prodId = parseInt(prodSelect?.value) || 0;
        const prod = availableProducts.find(p => p.id === prodId);
        if (!prod) return;

        const qty = parseFloat(qtyInput?.value) || 0;
        const price = parseFloat(priceInput?.value) || 0;
        const lineTot = qty * price;
        const code = prod.short_code ? `[${prod.short_code}] ` : '';

        let modeText = '<span style="color: #64748B;">Perdu</span>';
        if (prod.is_returnable) {
            const embMode = embModeSelect?.value || 'sur_place';
            if (embMode === 'sur_place') {
                modeText = '<span style="color: #16A34A; font-weight: 700;">Sur place 🟢</span>';
            } else if (embMode === 'echange') {
                const bRet = parseFloat(btlsRetInput?.value) || 0;
                const mDue = Math.max(0, qty - bRet);
                modeText = `Échange (${bRet} btl rendue${mDue > 0 ? `, <span style="color: #DC2626; font-weight: 700;">+${mDue} due</span>` : ' 🟢'})`;
                if (mDue > 0) bottleDebts.push(`+${mDue} btl [${prod.short_code || prod.name}]`);
            } else {
                modeText = '<span style="color: #DC2626; font-weight: 700;">Dette de verre</span>';
                bottleDebts.push(`+${qty} btl [${prod.short_code || prod.name}]`);
            }
        }

        const mtr = document.createElement("tr");
        mtr.innerHTML = `
            <td><strong>${code}${prod.name}</strong></td>
            <td>${modeText}</td>
            <td style="text-align: center;"><strong>${qty}</strong></td>
            <td style="text-align: right;">${new Intl.NumberFormat('fr-FR').format(price)} F</td>
            <td style="text-align: right; font-weight: 700;">${new Intl.NumberFormat('fr-FR').format(lineTot)} FCFA</td>
        `;
        modalTbody.appendChild(mtr);
    });

    const vDebtsEl = document.getElementById("vmodal_bottle_debts");
    if (bottleDebts.length === 0) {
        vDebtsEl.innerHTML = `<span style="color: #16A34A; font-weight: 700;">0 dette de verre (Consommation sur place ou échangée 🟢)</span>`;
    } else {
        vDebtsEl.innerHTML = `<span style="color: #DC2626; font-weight: 700;">⚠️ Dette générée : ${bottleDebts.join(' | ')}</span>`;
    }

    document.getElementById("vente_detail_confirm_modal").style.display = "flex";
}

function closeDetailConfirmModal() {
    document.getElementById("vente_detail_confirm_modal").style.display = "none";
}

function submitFinalDetailSale() {
    document.getElementById("ventes_detail_form").submit();
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
        formData.append('client_id', clientId);
        formData.append('amount', amount);
        formData.append('cash_account_id', accountId);
        formData.append('notes', notes);

        const resp = await fetch('<?= BASE_URL ?>/reglements/saveQuickAvoir', {
            method: 'POST',
            body: formData
        });
        const data = await resp.json();

        if (data.success) {
            const vClientSel = document.getElementById("vente_client_select");
            if (vClientSel) {
                for (let i = 0; i < vClientSel.options.length; i++) {
                    const opt = vClientSel.options[i];
                    if (opt.value == clientId) {
                        opt.dataset.debt = data.new_debt;
                        const clientName = opt.dataset.name || opt.text.split('•')[0].trim();
                        const avoirText = (data.new_debt < 0) 
                            ? `• 🟢 Avoir: +${new Intl.NumberFormat('fr-FR').format(Math.abs(data.new_debt))} FCFA` 
                            : `• 🔴 Dette: ${new Intl.NumberFormat('fr-FR').format(data.new_debt)} FCFA`;
                        opt.text = `${clientName} ${avoirText}`;
                        break;
                    }
                }
                vClientSel.value = clientId;
                onClientChange();
            }

            const avoirInput = document.getElementById("vente_avoir_amount");
            const useAvoirCheck = document.getElementById("use_avoir_check");
            if (avoirInput) avoirInput.value = amount;
            if (useAvoirCheck) useAvoirCheck.checked = true;
            calculateTotals();

            closeQuickAvoirModal();
            amountInput.value = "";
            alert("✓ " + data.message + "\nL'avoir a été appliqué à votre vente au détail !");
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
        submitBtn.innerHTML = "<i class='bx bx-check-circle'></i> Enregistrer l'Avoir";
    }
}

document.addEventListener("DOMContentLoaded", function() {
    addNewBottleRow();
    onClientChange();
});
</script>
