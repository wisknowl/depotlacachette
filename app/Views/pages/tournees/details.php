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
            $totalCashFromSales += floatval($s['amount_paid'] ?: $s['total_amount']) + floatval($s['excess_amount'] ?? 0);
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
                    $hasDemi = !empty($it['has_demi']) || ($it['format_type'] === 'demi');
                    $qtyCrates = intval($it['qty_loaded']);
                    $equiv = floatval($it['stock_equivalent'] ?? ($qtyCrates + ($hasDemi ? 0.5 : 0.0)));
                    $sumLoadedQty += $equiv;
                    $lineVal = ($qtyCrates * $it['unit_price']) + ($hasDemi ? floatval($it['demi_unit_price'] ?: ($it['unit_price'] / 2)) : 0);
                    $sumLoadedVal += $lineVal;
                ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($it['product_name']) ?></strong>
                        <?php if (!empty($it['short_code'])): ?>
                            <small style="color: #64748B;">[<?= htmlspecialchars($it['short_code']) ?>]</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($qtyCrates > 0 && $hasDemi): ?>
                            <span class="badge" style="background: #E0F2FE; color: #0369A1; font-weight: 700;">Casier + Demi</span>
                        <?php elseif ($hasDemi): ?>
                            <span class="badge" style="background: #FEF3C7; color: #B45309; font-weight: 700;">Demi (0.5)</span>
                        <?php else: ?>
                            <span class="badge" style="background: #F1F5F9; color: #475569; font-weight: 700;">Casier (Entier)</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($it['packaging_name'] ?: 'Perdu / Pack') ?></td>
                    <td style="text-align: center; font-weight: 800; font-size: 1rem; color: #0284C7;">
                        <?php if ($hasDemi && $qtyCrates > 0): ?>
                            <?= $qtyCrates ?> c. + Demi <span style="font-size: 0.8rem; color: #64748B; font-weight: normal;">(<?= $equiv ?> eq.)</span>
                        <?php elseif ($hasDemi): ?>
                            0 c. + Demi <span style="font-size: 0.8rem; color: #64748B; font-weight: normal;">(0.5 eq.)</span>
                        <?php else: ?>
                            <?= $qtyCrates ?> c.
                        <?php endif; ?>
                    </td>
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
                    <?php foreach ($sales as $s): 
                        $isCancelled = ($s['status'] === 'Cancelled');
                    ?>
                    <tr style="<?= $isCancelled ? 'background: #FEF2F2; opacity: 0.8;' : '' ?>">
                        <td>
                            <strong style="color: <?= $isCancelled ? '#94A3B8' : '#0284C7' ?>; <?= $isCancelled ? 'text-decoration: line-through;' : '' ?>"><?= htmlspecialchars($s['id']) ?></strong>
                            <?php if ($isCancelled): ?>
                                <span class="badge" style="background: #FEE2E2; color: #DC2626; font-size: 0.72rem; margin-left: 4px; font-weight: 700;">Annulée</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($s['client_name'] ?: 'Client Comptoir') ?></strong>
                        </td>
                        <td>
                            <small style="color: #475569;"><?= htmlspecialchars($s['items_summary']) ?></small>
                        </td>
                        <td style="text-align: right; font-weight: 700; <?= $isCancelled ? 'text-decoration: line-through; color: #94A3B8;' : '' ?>">
                            <?= number_format($s['total_amount'], 0, ',', ' ') ?> F
                        </td>
                        <td style="text-align: right; font-weight: 700; color: <?= $isCancelled ? '#94A3B8' : '#16A34A' ?>; <?= $isCancelled ? 'text-decoration: line-through;' : '' ?>">
                            <?= number_format($s['payment_method_id'] == 5 ? 0 : ($s['amount_paid'] ?: $s['total_amount']), 0, ',', ' ') ?> F
                            <?php if (floatval($s['excess_amount'] ?? 0) > 0): ?>
                                <div style="font-size: 0.72rem; color: #D97706; font-weight: 700;">(+<?= number_format($s['excess_amount'], 0, ',', ' ') ?> F Avoir)</div>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right; font-weight: 700; color: <?= $isCancelled ? '#94A3B8' : ($s['amount_due'] > 0 ? '#DC2626' : '#64748B') ?>; <?= $isCancelled ? 'text-decoration: line-through;' : '' ?>">
                            <?= number_format($s['amount_due'] ?: ($s['payment_method_id'] == 5 ? $s['total_amount'] : 0), 0, ',', ' ') ?> F
                        </td>
                        <td style="text-align: center;">
                            <?php if ($isCancelled): ?>
                                <span style="background: #FEE2E2; color: #DC2626; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">Annulée</span>
                            <?php elseif ($s['payment_method_id'] == 5): ?>
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
                            <th>Emballage</th>
                            <th style="text-align: center;">Chargé Matin</th>
                            <th style="text-align: center;">Vendu sur Factures</th>
                            <th style="text-align: center;">Attendu en Camion</th>
                            <th style="text-align: center; min-width: 220px; background: #E0F2FE; color: #000000">Invendus Physiques au Magasin <span style="color: #DC2626;">*</span></th>
                            <th style="text-align: center;">Écart / Manquant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it): 
                            $hasDemiLoaded = !empty($it['has_demi']) || ($it['format_type'] === 'demi');
                            $loadedEquiv = floatval($it['stock_equivalent'] ?? ($it['qty_loaded'] + ($hasDemiLoaded ? 0.5 : 0.0)));
                            $actualSold = floatval($it['calculated_qty_sold'] ?? 0);
                            $theoreticalReturn = max(0, $loadedEquiv - $actualSold);
                            
                            // Toujours pré-remplir avec la quantité exacte attendue en camion (Attendu en Camion)
                            $defaultReturnCrates = floor($theoreticalReturn);
                            $defaultReturnHasDemi = ($theoreticalReturn - $defaultReturnCrates) >= 0.49 ? 1 : 0;
                        ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($it['product_name']) ?></strong>
                            </td>
                            <td><small style="color: #64748B;"><?= htmlspecialchars($it['packaging_name'] ?: 'Perdu / Pack') ?></small></td>
                            <td style="text-align: center; font-weight: 700;">
                                <?= $loadedEquiv ?> c.
                                <?php if ($hasDemiLoaded): ?>
                                    <br><small style="color: #64748B; font-weight: normal;"><?= intval($it['qty_loaded']) ?> c. + Demi</small>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; font-weight: 700; color: #0284C7;">
                                <?= $actualSold ?> c.
                            </td>
                            <td style="text-align: center; font-weight: 800; font-size: 1.05rem; color: #1E293B;">
                                <span id="theo_return_<?= $it['id'] ?>"><?= $theoreticalReturn ?> c.</span>
                            </td>
                            <td style="text-align: center; background: #F0F9FF; padding: 8px;">
                                <div style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: wrap;">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <input type="number" min="0" step="1" name="returns[<?= $it['id'] ?>][crates]" 
                                               id="input_return_crates_<?= $it['id'] ?>"
                                               class="form-control decharge-return-crates" 
                                               value="<?= $defaultReturnCrates ?>" 
                                               data-item-id="<?= $it['id'] ?>"
                                               data-loaded="<?= $loadedEquiv ?>"
                                               data-sold="<?= $actualSold ?>"
                                               style="text-align: center; font-weight: 800; font-size: 1rem; width: 75px; border: 2px solid #0284C7;"
                                               oninput="calculateDechargeShortages()">
                                        <span style="font-size: 0.8rem; font-weight: 600; color: #475569;">casier(s)</span>
                                    </div>
                                    <label style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.82rem; font-weight: 700; cursor: pointer; background: #E0F2FE; padding: 4px 8px; border-radius: 4px; border: 1px solid #BAE6FD; margin: 0;">
                                        <input type="checkbox" name="returns[<?= $it['id'] ?>][has_demi]" value="1"
                                               id="input_return_demi_<?= $it['id'] ?>"
                                               class="decharge-return-demi"
                                               data-item-id="<?= $it['id'] ?>"
                                               <?= $defaultReturnHasDemi ? 'checked' : '' ?>
                                               onchange="calculateDechargeShortages()">
                                        <span>+ 1 Demi</span>
                                    </label>
                                </div>
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
    <?php
    $returnedSummaryByPkg = [];
    if (!empty($emballagesReturnedSummary)) {
        foreach ($emballagesReturnedSummary as $es) {
            $returnedSummaryByPkg[$es['packaging_type_id']] = $es;
        }
    }
    ?>
    <div class="card" style="margin-bottom: 25px;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span><i class='bx bx-archive'></i> 4. Réintégration des Casiers Vides & Bouteilles Rapportés par le Camion</span>
            <small style="color: #64748B; font-weight: 600;">Stock dépôt (`emballage_stock`)</small>
        </div>
        <div class="card-body">
            <p style="color: #64748B; font-size: 0.85rem; margin-bottom: 15px;">
                Indiquez les <strong>casiers complets</strong> et <strong>bouteilles en vrac</strong> physiquement déchargés du camion pour alimenter le parc de vides du dépôt. Les quantités sont pré-remplies d'après les factures du carnet saisies.
            </p>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 15px;">
                <?php foreach ($packagingTypes as $pt): 
                    $summary = $returnedSummaryByPkg[$pt['id']] ?? null;
                    $prefillCrates = $summary ? intval($summary['total_crates_returned']) : 0;
                    $prefillBottles = $summary ? intval($summary['total_bottles_returned']) : 0;
                ?>
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                        <strong style="font-size: 0.92rem; color: #1E293B;"><?= htmlspecialchars($pt['name']) ?></strong>
                        <span style="font-size: 0.75rem; background: #F1F5F9; color: #475569; padding: 2px 6px; border-radius: 4px;"><?= htmlspecialchars($pt['company'] ?: 'Dépôt') ?></span>
                    </div>
                    <div style="font-size: 0.78rem; color: #64748B; margin-bottom: 10px;">
                        <?= htmlspecialchars($pt['color']) ?> &bull; Actuel en dépôt : <strong><?= $pt['empty_crates'] ?> c. & <?= $pt['loose_bottles'] ?> btls</strong>
                    </div>

                    <?php if ($prefillCrates > 0 || $prefillBottles > 0): ?>
                        <div style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 4px 8px; border-radius: 4px; font-size: 0.78rem; font-weight: 600; margin-bottom: 10px; display: flex; align-items: center; gap: 4px;">
                            <i class='bx bx-check-circle'></i> Sur factures carnet : <strong><?= $prefillCrates ?> casier(s) & <?= $prefillBottles ?> btl(s)</strong>
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <label style="font-size: 0.8rem; font-weight: 700; color: #475569;">Casiers :</label>
                            <input type="number" min="0" step="1" name="empty_crates_returned[<?= $pt['id'] ?>]" class="form-control" value="<?= $prefillCrates ?>" style="font-weight: 800; text-align: center; width: 75px; border: 1px solid #CBD5E1;">
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <label style="font-size: 0.8rem; font-weight: 700; color: #475569;">Bouteilles vrac :</label>
                            <input type="number" min="0" step="1" name="loose_bottles_returned[<?= $pt['id'] ?>]" class="form-control" value="<?= $prefillBottles ?>" style="font-weight: 800; text-align: center; width: 75px; border: 1px solid #CBD5E1;">
                        </div>
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
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; flex-wrap: wrap; gap: 8px;">
                            <label class="form-label" style="font-weight: 800; font-size: 1rem; color: #16A34A; margin-bottom: 0;">
                                Montant Réellement Remis par <?= htmlspecialchars($tournee['driver_name']) ?> (FCFA) <span style="color: var(--c-danger);">*</span>
                            </label>
                            <button type="button" onclick="resetCashDepositedToNetExpected()" style="background: none; border: none; color: #0284C7; font-size: 0.8rem; font-weight: 700; cursor: pointer; text-decoration: underline; padding: 0;" title="Réinitialiser pour correspondre exactement au Net Cash Attendu">
                                ↺ Aligner sur le net attendu
                            </button>
                        </div>
                        <input type="number" step="any" min="0" name="cash_deposited" id="input_cash_deposited" 
                               class="form-control" required 
                               value="<?= max(0, $totalCashFromSales) ?>" 
                               style="font-size: 1.3rem; font-weight: 900; color: #16A34A; padding: 10px 14px; border: 2px solid #16A34A;"
                               oninput="onCashDepositedInput()">
                        <div id="cash_shortage_alert" style="display: none; margin-top: 8px; padding: 8px 12px; border-radius: 6px; font-weight: 700; font-size: 0.85rem;">
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

<script>
let totalCashSales = <?= $totalCashFromSales ?>;
let expenseRowCounter = 1;
let isCashDepositedManuallyModified = false;

function onCashDepositedInput() {
    isCashDepositedManuallyModified = true;
    calculateFinancialReconciliation();
}

function resetCashDepositedToNetExpected() {
    isCashDepositedManuallyModified = false;
    calculateFinancialReconciliation();
}

function calculateDechargeShortages() {
    const crateInputs = document.querySelectorAll(".decharge-return-crates");
    crateInputs.forEach(inp => {
        const itemId = inp.dataset.itemId;
        const loaded = parseFloat(inp.dataset.loaded) || 0;
        const sold = parseFloat(inp.dataset.sold) || 0;
        const crates = parseFloat(inp.value) || 0;
        const demiCheckbox = document.getElementById(`input_return_demi_${itemId}`);
        const hasDemi = (demiCheckbox && demiCheckbox.checked) ? 0.5 : 0.0;
        const returned = crates + hasDemi;
        const theoretical = Math.max(0, loaded - sold);
        const shortage = Math.round((theoretical - returned) * 10) / 10;

        const badge = document.getElementById(`shortage_badge_${itemId}`);
        if (!badge) return;
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
    const dispTotalExp = document.getElementById("disp_total_expenses");
    if (dispTotalExp) dispTotalExp.innerText = new Intl.NumberFormat('fr-FR').format(totalExpenses) + " FCFA";
    const dispNetExp = document.getElementById("disp_net_expected_cash");
    if (dispNetExp) dispNetExp.innerText = new Intl.NumberFormat('fr-FR').format(netExpected) + " FCFA";

    const cashInput = document.getElementById("input_cash_deposited");
    if (!cashInput) return;

    if (!isCashDepositedManuallyModified) {
        cashInput.value = netExpected;
    }

    const cashDeposited = parseFloat(cashInput.value) || 0;
    const shortage = netExpected - cashDeposited;
    const shortageAlert = document.getElementById("cash_shortage_alert");

    if (shortageAlert) {
        if (shortage > 0) {
            shortageAlert.style.display = "block";
            shortageAlert.style.background = "#FEE2E2";
            shortageAlert.style.border = "1px solid #FCA5A5";
            shortageAlert.style.color = "#991B1B";
            shortageAlert.innerHTML = `⚠️ Attention : Manquant de caisse de <strong>${new Intl.NumberFormat('fr-FR').format(shortage)} FCFA</strong> !`;
        } else if (shortage < 0) {
            shortageAlert.style.display = "block";
            shortageAlert.style.background = "#E0F2FE";
            shortageAlert.style.border = "1px solid #BAE6FD";
            shortageAlert.style.color = "#0369A1";
            shortageAlert.innerHTML = `ℹ️ Excédent / Versement supérieur au net attendu de <strong>+${new Intl.NumberFormat('fr-FR').format(Math.abs(shortage))} FCFA</strong> !`;
        } else {
            if (isCashDepositedManuallyModified) {
                shortageAlert.style.display = "block";
                shortageAlert.style.background = "#DCFCE7";
                shortageAlert.style.border = "1px solid #86EFAC";
                shortageAlert.style.color = "#166534";
                shortageAlert.innerHTML = `✓ Solde exact : aucun manquant de caisse.`;
            } else {
                shortageAlert.style.display = "none";
            }
        }
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

document.addEventListener("DOMContentLoaded", function() {
    calculateDechargeShortages();
    calculateFinancialReconciliation();
});
</script>

<?php elseif ($tournee['status'] === 'Cloturee'): ?>
<!-- IF ALREADY CLOSED -->
<?php if (!empty($_SESSION['user']) && strtolower($_SESSION['user']['role'] ?? '') === 'admin'): ?>
<div style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 16px 20px; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class='bx bx-shield-quarter' style="font-size: 1.35rem; color: #D97706;"></i>
                <strong style="color: #92400E; font-size: 1rem;">Zone d'Administration & Régularisation</strong>
            </div>
            <p style="margin: 4px 0 0 0; color: #B45309; font-size: 0.85rem;">
                Une erreur sur les invendus, le versement ou les frais de route ? Vous pouvez rouvrir la décharge pour corriger les chiffres, ou annuler intégralement la tournée.
            </p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button type="button" class="btn btn-primary" onclick="openReopenModal()" style="background: #D97706; padding: 9px 18px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                <i class='bx bx-reset'></i> Rouvrir la Décharge
            </button>
            <button type="button" class="btn btn-primary" onclick="openCancelModal()" style="background: #DC2626; padding: 9px 18px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                <i class='bx bx-x-circle'></i> Annuler la Tournée
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card" style="background: #F8FAFC; border: 2px solid #31C48D; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
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

<div class="card" style="margin-bottom: 25px;">
    <div class="card-header">
        <span><i class='bx bx-clipboard'></i> Bilan Final du Déchargement des Boissons</span>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Boisson</th>
                    <th style="text-align: center;">Chargé au Matin</th>
                    <th style="text-align: center;">Vendu sur Factures</th>
                    <th style="text-align: center;">Rapporté au Dépôt</th>
                    <th style="text-align: center;">Manquant / Écart</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): 
                    $hasDemiLoaded = !empty($it['has_demi']) || ($it['format_type'] === 'demi');
                    $loadedEquiv = floatval($it['stock_equivalent'] ?? ($it['qty_loaded'] + ($hasDemiLoaded ? 0.5 : 0.0)));
                    $sold = floatval($it['qty_sold'] ?? $it['calculated_qty_sold'] ?? 0);
                    $returned = floatval($it['qty_returned'] ?? 0);
                    $shortage = floatval($it['qty_shortage'] ?? 0);
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($it['product_name']) ?></strong></td>
                    <td style="text-align: center; font-weight: 700;"><?= $loadedEquiv ?> c.</td>
                    <td style="text-align: center; font-weight: 700; color: #0284C7;"><?= $sold ?> c.</td>
                    <td style="text-align: center; font-weight: 700; color: #16A34A;"><?= $returned ?> c.</td>
                    <td style="text-align: center;">
                        <?php if ($shortage == 0): ?>
                            <span style="color: #16A34A; font-weight: 700;">0 🟢</span>
                        <?php else: ?>
                            <span style="color: #DC2626; font-weight: 800;">-<?= $shortage ?> manquant 🔴</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php else: ?>
<!-- IF ANNULEE -->
<div class="card" style="background: #FEF2F2; border: 2px solid #EF4444; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
    <div style="display: flex; align-items: center; gap: 12px;">
        <i class='bx bx-x-circle' style="font-size: 2.5rem; color: #DC2626;"></i>
        <div>
            <h3 style="margin: 0; color: #DC2626;">Cette tournée a été annulée</h3>
            <p style="margin: 4px 0 0 0; color: #7F1D1D; font-size: 0.88rem;">
                Le chargement initial du matin a été intégralement restitué au stock magasin. Toutes les factures carnet associées ont été annulées.
                <?php if (!empty($tournee['notes'])): ?>
                    <br><strong>Historique / Notes :</strong> <?= nl2br(htmlspecialchars($tournee['notes'])) ?>
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- MODAL RÉOUVERTURE DÉCHARGE (ACTION A) -->
<div id="reopen_modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
    <div style="background: white; border-radius: 10px; max-width: 520px; width: 100%; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
            <i class='bx bx-reset' style="font-size: 1.8rem; color: #D97706;"></i>
            <h3 style="margin: 0; color: #92400E; font-size: 1.2rem;">Rouvrir la Décharge (Dé-clôturer)</h3>
        </div>
        <p style="color: #475569; font-size: 0.88rem; line-height: 1.5; margin-bottom: 15px;">
            Cette action va <strong>défaire les écritures du soir</strong> (versement caisse, réintégration des invendus et emballages) et replacer la tournée en <strong>En Route</strong>.<br>
            Les factures carnet et créances clients <strong>restent intactes</strong>. Vous pourrez modifier les chiffres et re-clôturer.
        </p>
        <form action="<?= BASE_URL ?>/tournees/rouvrir/<?= $tournee['id'] ?>" method="POST">
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label" style="font-weight: 700;">Motif obligatoire de réouverture <span style="color: var(--c-danger);">*</span></label>
                <input type="text" name="reopen_reason" class="form-control" required minlength="5" placeholder="Ex: Erreur de comptage sur les invendus, faute de frappe carburant...">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-primary" onclick="closeReopenModal()" style="background: #64748B;">Retour</button>
                <button type="submit" class="btn btn-primary" style="background: #D97706; font-weight: 700;">Confirmer la réouverture</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL ANNULATION TOTALE (ACTION B) -->
<div id="cancel_modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
    <div style="background: white; border-radius: 10px; max-width: 520px; width: 100%; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
            <i class='bx bx-error' style="font-size: 1.8rem; color: #DC2626;"></i>
            <h3 style="margin: 0; color: #DC2626; font-size: 1.2rem;">Confirmer l'annulation intégrale</h3>
        </div>
        <p style="color: #475569; font-size: 0.88rem; line-height: 1.5; margin-bottom: 15px;">
            L'annulation intégrale va <strong>annuler toutes les factures carnet rattachées</strong>, soulager les dettes créées et réintégrer <strong>la totalité du chargement initial du matin</strong> dans le stock magasin.<br>
            Le statut final sera <strong>Annulée</strong> avec un solde net nul.
        </p>
        <form action="<?= BASE_URL ?>/tournees/annuler/<?= $tournee['id'] ?>" method="POST">
            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label" style="font-weight: 700;">Motif obligatoire de l'annulation <span style="color: var(--c-danger);">*</span></label>
                <input type="text" name="cancel_reason" class="form-control" required minlength="5" placeholder="Ex: Véhicule tombé en panne, tournée avortée, doublon...">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-primary" onclick="closeCancelModal()" style="background: #64748B;">Retour</button>
                <button type="submit" class="btn btn-primary" style="background: #DC2626; font-weight: 700;">Confirmer l'annulation intégrale</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCancelModal() {
    const modal = document.getElementById("cancel_modal");
    if (modal) modal.style.display = "flex";
}
function closeCancelModal() {
    const modal = document.getElementById("cancel_modal");
    if (modal) modal.style.display = "none";
}
function openReopenModal() {
    const modal = document.getElementById("reopen_modal");
    if (modal) modal.style.display = "flex";
}
function closeReopenModal() {
    const modal = document.getElementById("reopen_modal");
    if (modal) modal.style.display = "none";
}
</script>
