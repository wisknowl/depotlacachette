<div class="page-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <h1 style="margin: 0; font-size: 1.45rem; font-weight: 800; color: #1E293B;">
                Tournée <?= htmlspecialchars($tournee['reference']) ?>
            </h1>
            <?php if ($tournee['status'] === 'En_Route'): ?>
                <span style="background: #E0F2FE; color: #0284C7; padding: 4px 10px; border-radius: 6px; font-size: 0.82rem; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #0284C7; display: inline-block;"></span> En Route
                </span>
            <?php elseif ($tournee['status'] === 'Cloturee'): ?>
                <span style="background: #DCFCE7; color: #16A34A; padding: 4px 10px; border-radius: 6px; font-size: 0.82rem; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">
                    <i class='bx bx-check-shield'></i> Clôturée & Déchargée
                </span>
            <?php else: ?>
                <span style="background: #FEE2E2; color: #DC2626; padding: 4px 10px; border-radius: 6px; font-size: 0.82rem; font-weight: 700;">
                    Annulée
                </span>
            <?php endif; ?>
        </div>
        <p style="margin: 4px 0 0 0; font-size: 0.85rem; color: #64748B;">
            Chauffeur : <strong><?= htmlspecialchars($tournee['driver_name'] ?: 'Non assigné') ?></strong> &bull; 
            Date : <strong><?= date('d/m/Y', strtotime($tournee['tournee_date'])) ?></strong> &bull; 
            Véhicule : <strong><?= htmlspecialchars($tournee['vehicle_name'] ?: 'Standard') ?></strong>
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="<?= BASE_URL ?>/tournees/imprimerChargement/<?= $tournee['id'] ?>" target="_blank" class="btn btn-primary" style="background: #0284C7; padding: 8px 16px; font-weight: 600;">
            <i class='bx bx-printer'></i> Imprimer Bon de Chargement
        </a>
        <a href="<?= BASE_URL ?>/tournees" class="btn btn-primary" style="background-color: var(--c-gray-600); padding: 8px 16px;">
            <i class='bx bx-arrow-back'></i> Retour à la liste
        </a>
    </div>
</div>

<?php if (!empty($flash_success)): ?>
    <div style="background: #DEF7EC; border-left: 4px solid #31C48D; color: #03543F; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <i class='bx bx-check-circle' style="font-size: 1.25rem;"></i>
        <span><?= htmlspecialchars($flash_success) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($flash_error)): ?>
    <div style="background: #FDE8E8; border-left: 4px solid #F98080; color: #9B1C1C; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <i class='bx bx-error-circle' style="font-size: 1.25rem;"></i>
        <span><?= htmlspecialchars($flash_error) ?></span>
    </div>
<?php endif; ?>

<!-- TOP STATS SUMMARY -->
<?php
$totalSoldFromSales = 0;
$totalCashFromSales = 0;
$totalCreditFromSales = 0;
$soldItemsMap = [];

foreach ($sales as $s) {
    if ($s['status'] === 'Valid') {
        $totalSoldFromSales += floatval($s['total_amount']);
        if ($s['payment_method_id'] == 5) {
            $totalCreditFromSales += floatval($s['total_amount']);
        } else {
            $totalCashFromSales += floatval($s['amount_paid'] ?: $s['total_amount']);
        }
    }
}
?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 22px;">
    <div style="background: white; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px 18px;">
        <span style="font-size: 0.78rem; font-weight: 700; color: #64748B; text-transform: uppercase;">1. Valeur Chargée</span>
        <div style="font-size: 1.3rem; font-weight: 900; color: #1E293B; margin-top: 2px;">
            <?= number_format($tournee['total_loaded_amount'], 0, ',', ' ') ?> FCFA
        </div>
    </div>
    <div style="background: white; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px 18px;">
        <span style="font-size: 0.78rem; font-weight: 700; color: #64748B; text-transform: uppercase;">2. Ventes Saisies</span>
        <div style="font-size: 1.3rem; font-weight: 900; color: #0284C7; margin-top: 2px;">
            <?= number_format($totalSoldFromSales, 0, ',', ' ') ?> FCFA
        </div>
        <span style="font-size: 0.72rem; color: #64748B;"><?= count($sales) ?> facture(s)</span>
    </div>
    <div style="background: white; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px 18px;">
        <span style="font-size: 0.78rem; font-weight: 700; color: #64748B; text-transform: uppercase;">3. Cash Encaissé Ventes</span>
        <div style="font-size: 1.3rem; font-weight: 900; color: #16A34A; margin-top: 2px;">
            <?= number_format($totalCashFromSales, 0, ',', ' ') ?> FCFA
        </div>
    </div>
    <div style="background: white; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px 18px;">
        <span style="font-size: 0.78rem; font-weight: 700; color: #64748B; text-transform: uppercase;">4. Créances / Crédit</span>
        <div style="font-size: 1.3rem; font-weight: 900; color: #D97706; margin-top: 2px;">
            <?= number_format($totalCreditFromSales, 0, ',', ' ') ?> FCFA
        </div>
    </div>
</div>

<!-- SECTION 1: MARCHANDISES CHARGÉES AU DÉPART -->
<div class="card" style="margin-bottom: 25px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <span><i class='bx bx-package'></i> 1. Marchandises Chargées au Départ (Matin)</span>
        <span style="font-size: 0.8rem; font-weight: 700; color: #475569; background: #F1F5F9; padding: 3px 8px; border-radius: 4px;">
            <?= count($items) ?> référence(s)
        </span>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Boisson</th>
                    <th>Format</th>
                    <th>Emballage</th>
                    <th style="text-align: center;">Qté Chargée</th>
                    <th style="text-align: right;">Prix Unitaire</th>
                    <th style="text-align: right;">Valeur Totale</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $sumLoadedQty = 0;
                $sumLoadedVal = 0;
                foreach ($items as $it): 
                    $sumLoadedQty += $it['qty_loaded'];
                    $lineVal = $it['qty_loaded'] * $it['unit_price'];
                    $sumLoadedVal += $lineVal;
                ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($it['product_name']) ?></strong>
                        <?php if (!empty($it['short_code'])): ?>
                            <small style="color: #64748B;">[<?= htmlspecialchars($it['short_code']) ?>]</small>
                        <?php endif; ?>
                    </td>
                    <td><?= ($it['format_type'] === 'demi') ? 'Demi (0.5)' : 'Entier (1.0)' ?></td>
                    <td><?= htmlspecialchars($it['packaging_name'] ?: 'Perdu / Pack') ?></td>
                    <td style="text-align: center; font-weight: 800; font-size: 1rem; color: #0284C7;"><?= $it['qty_loaded'] ?></td>
                    <td style="text-align: right;"><?= number_format($it['unit_price'], 0, ',', ' ') ?> F</td>
                    <td style="text-align: right; font-weight: 700;"><?= number_format($lineVal, 0, ',', ' ') ?> FCFA</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- SECTION 2: FACTURES DU CARNET PAPIER -->
<div class="card" style="margin-bottom: 25px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div>
            <span><i class='bx bx-receipt'></i> 2. Factures de Vente du Carnet Papier Saisies</span>
            <small style="display: block; color: #64748B; font-size: 0.78rem;">Factures rédigées par le chauffeur sur le terrain et saisies dans le logiciel.</small>
        </div>
        <?php if ($tournee['status'] === 'En_Route'): ?>
            <a href="<?= BASE_URL ?>/ventes/form?tournee_id=<?= $tournee['id'] ?>" class="btn btn-accent" style="padding: 7px 16px; font-weight: 700; font-size: 0.88rem; display: flex; align-items: center; gap: 6px;">
                <i class='bx bx-plus-circle'></i> Saisir une Facture du Carnet
            </a>
        <?php endif; ?>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>N° Facture</th>
                    <th>Client</th>
                    <th>Articles Vendus</th>
                    <th style="text-align: right;">Total Facture</th>
                    <th style="text-align: right;">Montant Encaissé</th>
                    <th style="text-align: right;">Reste Dû (Crédit)</th>
                    <th style="text-align: center;">Mode</th>
                    <th style="text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sales)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 30px 20px; color: #64748B;">
                        <i class='bx bx-book-open' style="font-size: 2.5rem; color: #CBD5E1; display: block; margin-bottom: 8px;"></i>
                        Aucune facture du carnet n'a encore été saisie pour cette tournée.<br>
                        <?php if ($tournee['status'] === 'En_Route'): ?>
                            <a href="<?= BASE_URL ?>/ventes/form?tournee_id=<?= $tournee['id'] ?>" style="color: #0284C7; font-weight: 700; text-decoration: underline; margin-top: 6px; display: inline-block;">
                                Commencer la saisie des factures papier de <?= htmlspecialchars($tournee['driver_name']) ?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($sales as $s): ?>
                    <tr>
                        <td>
                            <strong style="color: #0284C7;"><?= htmlspecialchars($s['id']) ?></strong>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($s['client_name'] ?: 'Client Comptoir') ?></strong>
                        </td>
                        <td>
                            <small style="color: #475569;"><?= htmlspecialchars($s['items_summary']) ?></small>
                        </td>
                        <td style="text-align: right; font-weight: 700;">
                            <?= number_format($s['total_amount'], 0, ',', ' ') ?> F
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #16A34A;">
                            <?= number_format($s['payment_method_id'] == 5 ? 0 : ($s['amount_paid'] ?: $s['total_amount']), 0, ',', ' ') ?> F
                        </td>
                        <td style="text-align: right; font-weight: 700; color: <?= $s['amount_due'] > 0 ? '#DC2626' : '#64748B' ?>;">
                            <?= number_format($s['amount_due'] ?: ($s['payment_method_id'] == 5 ? $s['total_amount'] : 0), 0, ',', ' ') ?> F
                        </td>
                        <td style="text-align: center;">
                            <?php if ($s['payment_method_id'] == 5): ?>
                                <span style="background: #FEF3C7; color: #D97706; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">Crédit</span>
                            <?php else: ?>
                                <span style="background: #DCFCE7; color: #16A34A; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">Comptant</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <a href="<?= BASE_URL ?>/ventes/invoice/<?= $s['id'] ?>" target="_blank" class="btn btn-primary" style="padding: 3px 8px; font-size: 0.78rem; background: #64748B;" title="Voir la facture">
                                <i class='bx bx-file'></i> Facture
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- SECTION 3: CLÔTURE & DÉCHARGE (IF EN ROUTE) -->
<?php if ($tournee['status'] === 'En_Route'): ?>
<form action="<?= BASE_URL ?>/tournees/saveDecharge" method="POST" id="decharge_form">
    <input type="hidden" name="tournee_id" value="<?= $tournee['id'] ?>">

    <!-- COMPTAGE PHYSIQUE DES RETOURS BOISSONS -->
    <div class="card" style="margin-bottom: 25px; border-top: 3px solid #0284C7;">
        <div class="card-header">
            <span><i class='bx bx-check-double'></i> 3. Déchargement & Comptage Physique des Boissons au Dépôt</span>
        </div>
        <div class="card-body" style="padding: 10px;">
            <p style="color: #64748B; font-size: 0.85rem; margin: 5px 10px 15px 10px;">
                Indiquez les quantités physiques de <strong>boissons pleines invendues</strong> redescendues du camion pour être réintégrées dans le stock du magasin.
            </p>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr style="background: #F1F5F9;">
                            <th>Boisson</th>
                            <th>Format</th>
                            <th style="text-align: center;">Chargé le Matin</th>
                            <th style="text-align: center;">Vendu sur Factures</th>
                            <th style="text-align: center;">Attendu en Camion</th>
                            <th style="text-align: center; width: 170px; background: #E0F2FE;">Invendus au Magasin <span style="color: #DC2626;">*</span></th>
                            <th style="text-align: center;">Écart / Manquant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it): 
                            $actualSold = floatval($it['calculated_qty_sold'] ?? 0);
                            $theoreticalReturn = max(0, floatval($it['qty_loaded']) - $actualSold);
                        ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($it['product_name']) ?></strong>
                            </td>
                            <td><?= ($it['format_type'] === 'demi') ? 'Demi (0.5)' : 'Entier (1.0)' ?></td>
                            <td style="text-align: center; font-weight: 700;"><?= $it['qty_loaded'] ?></td>
                            <td style="text-align: center; font-weight: 700; color: #0284C7;"><?= $actualSold ?></td>
                            <td style="text-align: center; font-weight: 800; font-size: 1.05rem; color: #1E293B;">
                                <span id="theo_return_<?= $it['id'] ?>"><?= $theoreticalReturn ?></span>
                            </td>
                            <td style="text-align: center; background: #F0F9FF;">
                                <input type="number" step="any" min="0" name="returns[<?= $it['id'] ?>]" 
                                       id="input_return_<?= $it['id'] ?>"
                                       class="form-control decharge-return-input" 
                                       value="<?= $theoreticalReturn ?>" 
                                       data-item-id="<?= $it['id'] ?>"
                                       data-loaded="<?= $it['qty_loaded'] ?>"
                                       data-sold="<?= $actualSold ?>"
                                       style="text-align: center; font-weight: 800; font-size: 1rem; border: 2px solid #0284C7;"
                                       oninput="calculateDechargeShortages()">
                            </td>
                            <td style="text-align: center;">
                                <span id="shortage_badge_<?= $it['id'] ?>" style="font-weight: 700; font-size: 0.88rem; color: #16A34A;">
                                    0 🟢
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- COMPTAGE CASIERS VIDES RAPPORTÉS -->
    <div class="card" style="margin-bottom: 25px;">
        <div class="card-header">
            <span><i class='bx bx-archive'></i> 4. Réintégration des Casiers Vides Rapportés par le Camion</span>
        </div>
        <div class="card-body">
            <p style="color: #64748B; font-size: 0.85rem; margin-bottom: 15px;">
                Indiquez le nombre de <strong>casiers vides physiques</strong> déchargés du camion pour alimenter le parc de vides du dépôt (`emballage_stock`).
            </p>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px;">
                <?php foreach ($packagingTypes as $pt): ?>
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px 16px;">
                    <strong style="font-size: 0.9rem; color: #1E293B; display: block;"><?= htmlspecialchars($pt['name']) ?></strong>
                    <span style="font-size: 0.78rem; color: #64748B;"><?= htmlspecialchars($pt['color']) ?> &bull; Actuel en dépôt : <?= $pt['empty_crates'] ?> c.</span>
                    <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
                        <label style="font-size: 0.82rem; font-weight: 700; color: #475569; white-space: nowrap;">Vides ramenés :</label>
                        <input type="number" min="0" step="1" name="empty_crates_returned[<?= $pt['id'] ?>]" class="form-control" value="0" style="font-weight: 700; text-align: center; width: 100px;">
                        <span style="font-size: 0.82rem; color: #64748B;">casier(s)</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- FRAIS DE ROUTE & JUSTIFICATIFS -->
    <div class="card" style="margin-bottom: 25px;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span><i class='bx bx-gas-pump'></i> 5. Frais de Route Déductibles (Carburant, Ration, Péage)</span>
            <button type="button" class="btn btn-primary" onclick="addExpenseRow()" style="padding: 5px 12px; font-size: 0.82rem; background: #64748B;">
                <i class='bx bx-plus'></i> Ajouter un reçu
            </button>
        </div>
        <div class="card-body">
            <div id="expenses_container" style="display: flex; flex-direction: column; gap: 10px;">
                <!-- Initial row -->
                <div class="expense-row" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <div style="flex: 1.5; min-width: 180px;">
                        <select name="expenses[0][category_id]" class="form-control" style="font-size: 0.85rem;">
                            <?php foreach ($expenseCategories as $ec): ?>
                                <option value="<?= $ec['id'] ?>"><?= htmlspecialchars($ec['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="flex: 1; min-width: 130px;">
                        <input type="number" step="any" min="0" name="expenses[0][amount]" class="form-control expense-amount-input" placeholder="Montant F" value="0" oninput="calculateFinancialReconciliation()" style="font-weight: 700; text-align: right;">
                    </div>
                    <div style="flex: 2; min-width: 200px;">
                        <input type="text" name="expenses[0][notes]" class="form-control" placeholder="Justificatif (ex: Carburant 5 000 F reçu #12)" style="font-size: 0.85rem;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BILAN FINANCIER & VERSEMENT EN CAISSE -->
    <div class="card" style="margin-bottom: 30px; border: 2px solid #0284C7;">
        <div class="card-header" style="background: #0284C7; color: white;">
            <span style="font-weight: 800; font-size: 1rem;"><i class='bx bx-wallet'></i> 6. Rapprochement Financier & Versement dans la Caisse du Dépôt</span>
        </div>
        <div class="card-body" style="background: #F8FAFC; padding: 22px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; align-items: center;">
                
                <!-- Financial Breakdown -->
                <div style="background: white; border: 1px solid #E2E8F0; border-radius: 8px; padding: 16px 20px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.95rem;">
                        <span>Total Ventes Espèces / Cash :</span>
                        <strong id="disp_total_cash_sales"><?= number_format($totalCashFromSales, 0, ',', ' ') ?> FCFA</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.95rem; color: #DC2626;">
                        <span>Moins Frais de Route (Carburant...) :</span>
                        <strong id="disp_total_expenses">0 FCFA</strong>
                    </div>
                    <div style="border-top: 2px solid #E2E8F0; padding-top: 10px; display: flex; justify-content: space-between; font-size: 1.15rem; font-weight: 900; color: #0284C7;">
                        <span>NET CASH ATTENDU :</span>
                        <span id="disp_net_expected_cash"><?= number_format($totalCashFromSales, 0, ',', ' ') ?> FCFA</span>
                    </div>
                </div>

                <!-- Cash Account Selection & Real Deposit -->
                <div>
                    <div class="form-group" style="margin-bottom: 14px;">
                        <label class="form-label" style="font-weight: 700;">Caisse de Destination du Versement <span style="color: var(--c-danger);">*</span></label>
                        <select name="cash_account_id" id="cash_account_select" class="form-control" required style="font-weight: 700; font-size: 0.95rem;">
                            <option value="">-- Sélectionner le compte d'encaissement --</option>
                            <?php foreach ($cashAccounts as $ca): ?>
                                <option value="<?= $ca['id'] ?>" <?= ($ca['payment_method_id'] == 1) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ca['name']) ?> (Solde actuel : <?= number_format($ca['current_balance'], 0, ',', ' ') ?> F)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 800; font-size: 1rem; color: #16A34A;">
                            Montant Réellement Remis par <?= htmlspecialchars($tournee['driver_name']) ?> (FCFA) <span style="color: var(--c-danger);">*</span>
                        </label>
                        <input type="number" step="any" min="0" name="cash_deposited" id="input_cash_deposited" 
                               class="form-control" required 
                               value="<?= $totalCashFromSales ?>" 
                               style="font-size: 1.3rem; font-weight: 900; color: #16A34A; padding: 10px 14px; border: 2px solid #16A34A;"
                               oninput="calculateFinancialReconciliation()">
                        <div id="cash_shortage_alert" style="display: none; margin-top: 8px; font-weight: 700; color: #DC2626; font-size: 0.85rem;">
                            ⚠️ Attention : Manquant de caisse de <span id="disp_shortage_amount">0</span> FCFA !
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- SUBMIT ACTIONS -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 40px;">
        <button type="button" class="btn btn-primary" onclick="openCancelModal()" style="background: #EF4444; padding: 10px 18px;">
            <i class='bx bx-x-circle'></i> Annuler la Tournée
        </button>
        <button type="submit" class="btn btn-accent" style="padding: 12px 32px; font-size: 1.1rem; font-weight: 900; box-shadow: 0 4px 6px -1px rgba(244, 164, 35, 0.3);">
            <i class='bx bx-check-shield'></i> Valider la Décharge et Clôturer Définitivement
        </button>
    </div>
</form>

<!-- MODAL ANNULATION -->
<div id="cancel_modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
    <div style="background: white; border-radius: 10px; max-width: 500px; width: 100%; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <h3 style="margin-top: 0; color: #DC2626; font-size: 1.2rem;"><i class='bx bx-error'></i> Confirmer l'annulation de la tournée</h3>
        <p style="color: #64748B; font-size: 0.9rem;">
            L'annulation de la tournée réintégrera immédiatement <strong>toutes les marchandises chargées</strong> dans le stock du magasin.
        </p>
        <form action="<?= BASE_URL ?>/tournees/annuler/<?= $tournee['id'] ?>" method="POST">
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Motif de l'annulation <span style="color: var(--c-danger);">*</span></label>
                <input type="text" name="cancel_reason" class="form-control" required placeholder="Ex: Chauffeur indisponible, Panne véhicule...">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-primary" onclick="closeCancelModal()" style="background: #64748B;">Retour</button>
                <button type="submit" class="btn btn-primary" style="background: #DC2626; font-weight: 700;">Confirmer l'annulation</button>
            </div>
        </form>
    </div>
</div>

<script>
let totalCashSales = <?= $totalCashFromSales ?>;
let expenseRowCounter = 1;

function calculateDechargeShortages() {
    const returnInputs = document.querySelectorAll(".decharge-return-input");
    returnInputs.forEach(inp => {
        const itemId = inp.dataset.itemId;
        const loaded = parseFloat(inp.dataset.loaded) || 0;
        const sold = parseFloat(inp.dataset.sold) || 0;
        const returned = parseFloat(inp.value) || 0;
        const theoretical = Math.max(0, loaded - sold);
        const shortage = theoretical - returned;

        const badge = document.getElementById(`shortage_badge_${itemId}`);
        if (shortage === 0) {
            badge.innerHTML = `<span style="color: #16A34A; font-weight: 700;">0 🟢</span>`;
            inp.style.borderColor = "#0284C7";
        } else if (shortage > 0) {
            badge.innerHTML = `<span style="color: #DC2626; font-weight: 800;">-${shortage} manquant 🔴</span>`;
            inp.style.borderColor = "#DC2626";
        } else {
            badge.innerHTML = `<span style="color: #0284C7; font-weight: 800;">+${Math.abs(shortage)} excédent 🔵</span>`;
            inp.style.borderColor = "#0284C7";
        }
    });
}

function calculateFinancialReconciliation() {
    const expInputs = document.querySelectorAll(".expense-amount-input");
    let totalExpenses = 0;
    expInputs.forEach(inp => {
        totalExpenses += parseFloat(inp.value) || 0;
    });

    const netExpected = Math.max(0, totalCashSales - totalExpenses);
    document.getElementById("disp_total_expenses").innerText = new Intl.NumberFormat('fr-FR').format(totalExpenses) + " FCFA";
    document.getElementById("disp_net_expected_cash").innerText = new Intl.NumberFormat('fr-FR').format(netExpected) + " FCFA";

    const cashDeposited = parseFloat(document.getElementById("input_cash_deposited").value) || 0;
    const shortage = netExpected - cashDeposited;
    const shortageAlert = document.getElementById("cash_shortage_alert");

    if (shortage > 0) {
        document.getElementById("disp_shortage_amount").innerText = new Intl.NumberFormat('fr-FR').format(shortage);
        shortageAlert.style.display = "block";
    } else {
        shortageAlert.style.display = "none";
    }
}

function addExpenseRow() {
    const container = document.getElementById("expenses_container");
    const div = document.createElement("div");
    div.className = "expense-row";
    div.style = "display: flex; gap: 12px; align-items: center; flex-wrap: wrap;";
    div.innerHTML = `
        <div style="flex: 1.5; min-width: 180px;">
            <select name="expenses[${expenseRowCounter}][category_id]" class="form-control" style="font-size: 0.85rem;">
                <?php foreach ($expenseCategories as $ec): ?>
                    <option value="<?= $ec['id'] ?>"><?= htmlspecialchars($ec['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="flex: 1; min-width: 130px;">
            <input type="number" step="any" min="0" name="expenses[${expenseRowCounter}][amount]" class="form-control expense-amount-input" placeholder="Montant F" value="0" oninput="calculateFinancialReconciliation()" style="font-weight: 700; text-align: right;">
        </div>
        <div style="flex: 2; min-width: 200px;">
            <input type="text" name="expenses[${expenseRowCounter}][notes]" class="form-control" placeholder="Justificatif (ex: Péage, Ration)" style="font-size: 0.85rem;">
        </div>
        <button type="button" class="btn btn-primary" onclick="this.parentElement.remove(); calculateFinancialReconciliation();" style="background: #EF4444; padding: 6px 10px;">
            <i class='bx bx-trash'></i>
        </button>
    `;
    container.appendChild(div);
    expenseRowCounter++;
}

function openCancelModal() {
    document.getElementById("cancel_modal").style.display = "flex";
}
function closeCancelModal() {
    document.getElementById("cancel_modal").style.display = "none";
}

document.addEventListener("DOMContentLoaded", function() {
    calculateDechargeShortages();
    calculateFinancialReconciliation();
});
</script>

<?php else: ?>
<!-- IF ALREADY CLOSED -->
<div class="card" style="background: #F8FAFC; border: 2px solid #31C48D; padding: 20px; border-radius: 8px;">
    <div style="display: flex; align-items: center; gap: 12px;">
        <i class='bx bx-check-shield' style="font-size: 2.5rem; color: #16A34A;"></i>
        <div>
            <h3 style="margin: 0; color: #16A34A;">Cette tournée a été clôturée avec succès</h3>
            <p style="margin: 4px 0 0 0; color: #64748B; font-size: 0.88rem;">
                Date de clôture : <strong><?= date('d/m/Y H:i', strtotime($tournee['closed_at'] ?: $tournee['created_at'])) ?></strong> &bull; 
                Versement Caisse : <strong><?= number_format($tournee['cash_deposited'], 0, ',', ' ') ?> FCFA</strong> (Compte : <?= htmlspecialchars($tournee['cash_account_name'] ?: 'Caisse') ?>)
                <?php if ($tournee['cash_shortage'] > 0): ?>
                    &bull; <span style="color: #DC2626; font-weight: 700;">Manquant de caisse : <?= number_format($tournee['cash_shortage'], 0, ',', ' ') ?> FCFA</span>
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>
<?php endif; ?>
