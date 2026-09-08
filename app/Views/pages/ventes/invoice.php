<div class="page-title no-print">
    <i class='bx bx-receipt'></i>
    <h1>Facture de Vente N° <?= htmlspecialchars($sale['id']) ?></h1>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="no-print" style="background: #D1FAE5; border-left: 4px solid var(--c-success); color: #065F46; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
        <i class='bx bx-check-circle' style="font-size: 1.4rem;"></i>
        <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<!-- ACTION TOOLBAR -->
<div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 10px; flex-wrap: wrap;">
    <a href="<?= BASE_URL ?>/ventes" class="btn btn-primary" style="background: var(--c-gray-600);">
        <i class='bx bx-arrow-back'></i> Retour au Journal des Ventes
    </a>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button onclick="window.print()" class="btn btn-accent" style="padding: 8px 18px; font-weight: 700;">
            <i class='bx bx-printer'></i> Imprimer la Facture / Reçu
        </button>
        <a href="<?= BASE_URL ?>/ventes/en-detail" class="btn btn-primary" style="background: #059669;">
            <i class='bx bx-wine'></i> Vente Détail
        </a>
        <a href="<?= BASE_URL ?>/ventes/form" class="btn btn-primary">
            <i class='bx bx-package'></i> Vente Gros
        </a>
    </div>
</div>

<!-- INVOICE SHEET -->
<div class="card" style="max-width: 850px; margin: 0 auto; background: #FFFFFF; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); padding: 35px;">
    
    <!-- HEADER -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #E2E8F0; padding-bottom: 25px; margin-bottom: 25px;">
        <div>
            <h2 style="font-size: 1.6rem; font-weight: 900; color: var(--c-navy); margin: 0 0 5px 0; letter-spacing: -0.5px;">
                DÉPOT LA CACHETTE
            </h2>
            <div style="font-size: 0.88rem; color: var(--c-gray-600); line-height: 1.4;">
                Vente en Gros & Demi-Gros de Boissons<br>
                Yaoundé, Cameroun &bull; Tél: (+237) 6XX XX XX XX
            </div>
        </div>
        <div style="text-align: right;">
            <div style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 800; background: <?= $sale['status'] === 'Valid' ? '#DCFCE7; color: #166534;' : '#FEE2E2; color: #991B1B;' ?> margin-bottom: 8px;">
                <?= $sale['status'] === 'Valid' ? '✓ FACTURE VALIDÉE' : '⛔ FACTURE ANNULÉE' ?>
            </div>
            <div style="font-size: 1.3rem; font-weight: 800; color: var(--c-navy-dark);">
                <?= htmlspecialchars($sale['id']) ?>
            </div>
            <div style="font-size: 0.85rem; color: var(--c-gray-600);">
                Date : <strong><?= date('d/m/Y', strtotime($sale['sale_date'])) ?></strong>
            </div>
        </div>
    </div>

    <!-- CLIENT & PAYMENT INFO -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background: #F8FAFC; border-radius: 8px; padding: 18px; margin-bottom: 25px;">
        <div>
            <div style="font-size: 0.78rem; text-transform: uppercase; font-weight: 800; color: #64748B; margin-bottom: 4px;">Facturé à :</div>
            <div style="font-size: 1.1rem; font-weight: 800; color: var(--c-navy);"><?= htmlspecialchars($sale['client_name']) ?></div>
            <?php if (!empty($sale['client_phone'])): ?>
                <div style="font-size: 0.85rem; color: var(--c-gray-600);">Tél : <?= htmlspecialchars($sale['client_phone']) ?></div>
            <?php endif; ?>
            <?php if (!empty($sale['client_address'])): ?>
                <div style="font-size: 0.85rem; color: var(--c-gray-600);">Adresse : <?= htmlspecialchars($sale['client_address']) ?></div>
            <?php endif; ?>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 0.78rem; text-transform: uppercase; font-weight: 800; color: #64748B; margin-bottom: 4px;">Mode de Règlement :</div>
            <div style="font-size: 1rem; font-weight: 800; color: <?= $sale['payment_method_id'] == 5 ? '#D97706' : '#059669' ?>;">
                <?= $sale['payment_method_id'] == 5 ? '🟡 À Crédit (Compte Débiteur)' : '🟢 Au Comptant (' . htmlspecialchars($sale['cash_account_name'] ?: 'Caisse') . ')' ?>
            </div>
            <div style="font-size: 0.85rem; color: var(--c-gray-600); margin-top: 4px;">
                Établie par : <strong><?= htmlspecialchars($sale['username'] ?: 'Admin') ?></strong>
            </div>
            <?php if (!empty($sale['reference'])): ?>
                <div style="font-size: 0.82rem; color: var(--c-gray-600);">Réf : <?= htmlspecialchars($sale['reference']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ITEMS TABLE -->
    <div class="table-responsive" style="margin-bottom: 25px;">
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #F1F5F9; border-bottom: 2px solid #CBD5E1;">
                    <th style="padding: 10px 12px; text-align: left;">Désignation Boisson</th>
                    <th style="padding: 10px 12px; text-align: center;">Format</th>
                    <th style="padding: 10px 12px; text-align: center;">Quantité</th>
                    <th style="padding: 10px 12px; text-align: right;">Prix Unitaire</th>
                    <th style="padding: 10px 12px; text-align: right;">Montant Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalCasiers = 0;
                $debtsByProduct = [];

                foreach ($sale['items'] as $item): 
                    $totalCasiers += floatval($item['stock_equivalent']);
                    $pkgLabel = \App\Core\Helper::formatPackagingLabel($item['category_name'] ?? '', $item['format_name'] ?? '', $item['format_type'] ?? 'casier');
                    $isDemi = ($item['format_type'] === 'demi');
                    $isRet = !empty($item['is_returnable']);
                    $cratesOut = intval($item['crates_out'] ?? 0);
                    $bottlesOut = intval($item['bottles_out'] ?? 0);
                    $cratesRet = intval($item['crates_returned'] ?? 0);
                    $bottlesRet = intval($item['bottles_returned'] ?? 0);
                    $factor = max(1, intval($item['factor'] ?: 12));
                    $bottlesDue = max(0, $bottlesOut - ($cratesRet * $factor + $bottlesRet));
                    $cratesDue = floor($bottlesDue / $factor);
                    $looseDue = $bottlesDue % $factor;

                    if ($isRet && $bottlesDue > 0) {
                        $code = !empty($item['short_code']) ? $item['short_code'] : $item['product_name'];
                        if (!isset($debtsByProduct[$item['product_id']])) {
                            $debtsByProduct[$item['product_id']] = [
                                'code' => $code,
                                'name' => $item['product_name'],
                                'factor' => $factor,
                                'missingBottles' => 0
                            ];
                        }
                        $debtsByProduct[$item['product_id']]['missingBottles'] += $bottlesDue;
                    }
                ?>
                <tr style="border-bottom: 1px solid #E2E8F0;">
                    <td style="padding: 12px;">
                        <?php if (!empty($item['short_code'])): ?>
                            <span style="padding: 1px 6px; background: #EFF6FF; color: #0284C7; border: 1px solid #BFDBFE; border-radius: 4px; font-size: 0.72rem; font-weight: 800; margin-right: 4px;">
                                <?= htmlspecialchars($item['short_code']) ?>
                            </span>
                        <?php endif; ?>
                        <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                        <div style="font-size: 0.78rem; color: #64748B;"><?= htmlspecialchars($item['category_name'] ?? '') ?> &bull; <?= htmlspecialchars($item['format_name'] ?? '') ?></div>
                        <?php if ($isRet && $bottlesDue > 0): ?>
                            <?php 
                                $dueText = '';
                                if ($cratesDue > 0) $dueText .= $cratesDue . ' casier(s)';
                                if ($looseDue > 0) $dueText .= ($cratesDue > 0 ? ' + ' : '') . $looseDue . ' btl(s)';
                            ?>
                            <div style="font-size: 0.75rem; color: #D97706; font-weight: 700; margin-top: 3px;">
                                ⚠️ Dette Emballage : <?= $dueText ?>
                            </div>
                        <?php elseif ($isRet && ($cratesRet > 0 || $bottlesRet > 0)): ?>
                            <div style="font-size: 0.75rem; color: #16A34A; font-weight: 600; margin-top: 3px;">
                                ✓ <?= $cratesRet ?> casier(s)<?= $bottlesRet > 0 ? ' & ' . $bottlesRet . ' btl(s)' : '' ?> vide(s) restitué(s) au comptoir 🟢
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        <?php 
                            $badgeStyle = '#E0E7FF; color: #3730A3;';
                            if ($item['format_type'] === 'demi') {
                                $badgeStyle = '#FEF3C7; color: #92400E;';
                            } elseif ($item['format_type'] === 'unite' || $item['format_type'] === 'bouteille') {
                                $badgeStyle = '#DCFCE7; color: #166534;';
                            }
                        ?>
                        <span style="display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; background: <?= $badgeStyle ?>">
                            <?= htmlspecialchars($pkgLabel) ?>
                        </span>
                    </td>
                    <td style="padding: 12px; text-align: center; font-weight: 800; font-size: 1rem; color: var(--c-navy);">
                        <?= number_format($item['quantity'], 0) ?>
                    </td>
                    <td style="padding: 12px; text-align: right; color: var(--c-gray-700);">
                        <?= number_format($item['unit_price'], 0, ',', ' ') ?> FCFA
                    </td>
                    <td style="padding: 12px; text-align: right; font-weight: 800; color: var(--c-navy-dark);">
                        <?= number_format($item['total_price'], 0, ',', ' ') ?> FCFA
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- SUMMARY -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-top: 2px solid #E2E8F0; padding-top: 20px;">
        <div>
            <?php if (!empty($sale['notes'])): ?>
                <div style="font-size: 0.85rem; color: var(--c-gray-600);">
                    <strong>Notes :</strong> <?= htmlspecialchars($sale['notes']) ?>
                </div>
            <?php endif; ?>
            <div style="font-size: 0.85rem; color: #64748B; margin-top: 5px;">
                Total Volume Boissons : <strong><?= number_format($totalCasiers, 1) ?> Unité(s) Grossiste Équivalentes</strong>
            </div>
            <?php if (!empty($debtsByProduct)): ?>
                <div style="font-size: 0.85rem; color: #D97706; font-weight: 700; margin-top: 6px; background: #FFFBEB; padding: 8px 12px; border-radius: 6px; border: 1px solid #FEF3C7; display: inline-block;">
                    <i class='bx bx-archive'></i> Dettes Emballages sur cette Facture : 
                    <div style="margin-top: 5px; display: flex; flex-wrap: wrap; gap: 6px;">
                        <?php foreach ($debtsByProduct as $d): ?>
                            <?php 
                                $cDue = floor($d['missingBottles'] / $d['factor']);
                                $bDue = $d['missingBottles'] % $d['factor'];
                                $text = '';
                                if ($cDue > 0) $text .= $cDue . ' c.';
                                if ($bDue > 0) $text .= ($cDue > 0 ? ' + ' : '') . $bDue . ' btl(s)';
                            ?>
                            <span style="padding: 2px 8px; background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; border-radius: 4px; font-size: 0.78rem; font-weight: 700;">
                                <?= $text ?> [<?= htmlspecialchars($d['code']) ?>]
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div style="text-align: right; min-width: 280px;">
            <?php 
            $subTotal = floatval($sale['total_amount']);
            $discount = floatval($sale['discount_amount'] ?? 0);
            $avoirUsed = floatval($sale['avoir_used'] ?? 0);
            $netPayable = max(0, $subTotal - $discount - $avoirUsed);
            $paid = floatval($sale['amount_paid'] ?? 0);
            $due = floatval($sale['amount_due'] ?? 0);
            $cashDebt = max(0, $netPayable - $paid);
            ?>
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.9rem;">
                <span style="color: var(--c-gray-600); font-weight: 600;">Sous-Total Brut :</span>
                <span style="font-weight: 700; color: #334155;"><?= number_format($subTotal, 0, ',', ' ') ?> FCFA</span>
            </div>
            <?php if ($discount > 0): ?>
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.9rem; color: #DC2626;">
                <span style="font-weight: 700;">Remise Commerciale :</span>
                <span style="font-weight: 800;">-<?= number_format($discount, 0, ',', ' ') ?> FCFA</span>
            </div>
            <?php endif; ?>
            <?php if ($avoirUsed > 0): ?>
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.9rem; color: #047857;">
                <span style="font-weight: 700;">Avoir Client Déduit :</span>
                <span style="font-weight: 800;">-<?= number_format($avoirUsed, 0, ',', ' ') ?> FCFA</span>
            </div>
            <?php endif; ?>
            <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 2px solid #0F172A; font-size: 1.3rem; font-weight: 900; color: #047857; margin-bottom: 8px;">
                <span>NET À PAYER :</span>
                <span><?= number_format($netPayable, 0, ',', ' ') ?> FCFA</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 0.88rem; color: #16A34A;">
                <span>Montant Versé :</span>
                <strong style="font-weight: 800;"><?= number_format($paid, 0, ',', ' ') ?> FCFA</strong>
            </div>
            <?php if ($cashDebt > 0): ?>
            <div style="display: flex; justify-content: space-between; font-size: 0.95rem; color: #DC2626; border-top: 1px dashed #CBD5E1; padding-top: 4px; margin-top: 4px;">
                <span style="font-weight: 700;">Reste Dû (Dette Client) :</span>
                <strong style="font-weight: 900;"><?= number_format($cashDebt, 0, ',', ' ') ?> FCFA</strong>
            </div>
            <?php else: ?>
            <div style="font-size: 0.8rem; color: #16A34A; font-weight: 700; margin-top: 4px;">
                ✓ Facture Intégralement Réglée
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- FOOTER -->
    <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px dashed #CBD5E1; font-size: 0.8rem; color: #94A3B8;">
        Merci de votre fidélité ! Les marchandises vendues ne sont ni reprises ni échangées après contrôle à la livraison.
    </div>
</div>

<style>
@page {
    size: auto;
    margin: 8mm 12mm;
}
@media print {
    .no-print, .sidebar, .top-header, .header, .page-title, .user-dropdown-container, .toggle-btn {
        display: none !important;
    }
    body, .main-content, .page-content {
        background: #FFF !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
    }
    .card {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
    }
}
</style>
