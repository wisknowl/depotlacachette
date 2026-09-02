<div class="page-title no-print">
    <i class='bx bx-file-blank'></i>
    <h1>Bon d'Approvisionnement N° <?= htmlspecialchars($purchase['id']) ?></h1>
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
    <a href="<?= BASE_URL ?>/achats" class="btn btn-primary" style="background: var(--c-gray-600);">
        <i class='bx bx-arrow-back'></i> Retour au Journal des Achats
    </a>
    <div style="display: flex; gap: 10px;">
        <button onclick="window.print()" class="btn btn-accent" style="padding: 8px 18px; font-weight: 700;">
            <i class='bx bx-printer'></i> Imprimer le Bon de Réception
        </button>
        <a href="<?= BASE_URL ?>/achats/form" class="btn btn-primary">
            <i class='bx bx-plus'></i> Nouvel Achat
        </a>
    </div>
</div>

<!-- ORDER SHEET -->
<div class="card" style="max-width: 850px; margin: 0 auto; background: #FFFFFF; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); padding: 35px;">
    
    <!-- HEADER -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #E2E8F0; padding-bottom: 25px; margin-bottom: 25px;">
        <div>
            <h2 style="font-size: 1.6rem; font-weight: 900; color: var(--c-navy); margin: 0 0 5px 0; letter-spacing: -0.5px;">
                DÉPOT LA CACHETTE
            </h2>
            <div style="font-size: 0.88rem; color: var(--c-gray-600); line-height: 1.4;">
                Réception de Marchandises & Approvisionnements<br>
                Yaoundé, Cameroun
            </div>
        </div>
        <div style="text-align: right;">
            <div style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 800; background: <?= $purchase['status'] === 'Valid' ? '#DCFCE7; color: #166534;' : '#FEE2E2; color: #991B1B;' ?> margin-bottom: 8px;">
                <?= $purchase['status'] === 'Valid' ? '✓ BON VALIDÉ' : '⛔ BON ANNULÉ' ?>
            </div>
            <div style="font-size: 1.3rem; font-weight: 800; color: var(--c-navy-dark);">
                <?= htmlspecialchars($purchase['id']) ?>
            </div>
            <div style="font-size: 0.85rem; color: var(--c-gray-600);">
                Date de Réception : <strong><?= date('d/m/Y', strtotime($purchase['purchase_date'])) ?></strong>
            </div>
        </div>
    </div>

    <!-- SUPPLIER & PAYMENT INFO -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background: #F8FAFC; border-radius: 8px; padding: 18px; margin-bottom: 25px; border: 1px solid #E2E8F0;">
        <div>
            <div style="font-size: 0.78rem; text-transform: uppercase; font-weight: 800; color: #64748B; margin-bottom: 4px;">Fournisseur / Brasserie :</div>
            <div style="font-size: 1.1rem; font-weight: 800; color: var(--c-navy);"><?= htmlspecialchars($purchase['supplier_name']) ?></div>
            <?php if (!empty($purchase['supplier_phone'])): ?>
                <div style="font-size: 0.85rem; color: var(--c-gray-600);"><i class='bx bx-phone'></i> Tél : <?= htmlspecialchars($purchase['supplier_phone']) ?></div>
            <?php endif; ?>
            <?php if (!empty($purchase['supplier_address'])): ?>
                <div style="font-size: 0.85rem; color: var(--c-gray-600);"><i class='bx bx-map'></i> <?= htmlspecialchars($purchase['supplier_address']) ?></div>
            <?php endif; ?>
            <div style="font-size: 0.85rem; color: #1E293B; margin-top: 6px;">
                N° Facture / BL Usine : <strong style="color: #0F172A;"><?= htmlspecialchars($purchase['reference'] ?: '-') ?></strong>
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 0.78rem; text-transform: uppercase; font-weight: 800; color: #64748B; margin-bottom: 4px;">Modalités de Règlement :</div>
            <div style="font-size: 1rem; font-weight: 800; color: <?= $purchase['payment_method_id'] == 5 ? '#D97706' : '#059669' ?>;">
                <?= $purchase['payment_method_id'] == 5 ? '🟡 À Crédit (Dette Fournisseur)' : '🟢 Au Comptant (' . htmlspecialchars($purchase['cash_account_name'] ?: 'Caisse') . ')' ?>
            </div>
            <div style="font-size: 0.85rem; color: var(--c-gray-600); margin-top: 6px;">
                Date d'Émission Facture : <strong><?= !empty($purchase['supplier_invoice_date']) ? date('d/m/Y', strtotime($purchase['supplier_invoice_date'])) : date('d/m/Y', strtotime($purchase['purchase_date'])) ?></strong>
            </div>
            <div style="font-size: 0.85rem; color: var(--c-gray-600); margin-top: 3px;">
                Date Réception Magasin : <strong><?= date('d/m/Y', strtotime($purchase['purchase_date'])) ?></strong>
            </div>
            <div style="font-size: 0.82rem; color: #64748B; margin-top: 3px;">
                Réceptionné par : <strong><?= htmlspecialchars($purchase['username'] ?: 'Admin') ?></strong>
            </div>
        </div>
    </div>

    <!-- ITEMS TABLE -->
    <div class="table-responsive" style="margin-bottom: 25px;">
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #F1F5F9; border-bottom: 2px solid #CBD5E1;">
                    <th style="padding: 10px 12px; text-align: left;">Désignation Boisson</th>
                    <th style="padding: 10px 12px; text-align: center;">Unité</th>
                    <th style="padding: 10px 12px; text-align: center;">Quantité</th>
                    <th style="padding: 10px 12px; text-align: right;">Prix Achat Unit</th>
                    <th style="padding: 10px 12px; text-align: right;">Ristourne/U</th>
                    <th style="padding: 10px 12px; text-align: right;">Montant Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalCasiers = 0;
                $totalRistourneExpected = 0;
                foreach ($purchase['items'] as $item): 
                    $totalCasiers += floatval($item['stock_equivalent']);
                    $totalRistourneExpected += floatval($item['total_ristourne'] ?? 0);
                    $pkgLabel = \App\Core\Helper::formatPackagingLabel($item['category_name'] ?? '', $item['format_name'] ?? '', $item['format_type'] ?? 'casier');
                    $isDemi = ($item['format_type'] === 'demi');
                ?>
                <tr style="border-bottom: 1px solid #E2E8F0;">
                    <td style="padding: 12px;">
                        <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                        <div style="font-size: 0.78rem; color: #64748B;"><?= htmlspecialchars($item['category_name'] ?? '') ?> &bull; <?= htmlspecialchars($item['format_name'] ?? '') ?></div>
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        <span style="display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; background: <?= $isDemi ? '#FEF3C7; color: #92400E;' : '#E0E7FF; color: #3730A3;' ?>">
                            <?= htmlspecialchars($pkgLabel) ?>
                        </span>
                    </td>
                    <td style="padding: 12px; text-align: center; font-weight: 800; font-size: 1rem; color: var(--c-navy);">
                        <?= number_format($item['quantity'], 0) ?>
                    </td>
                    <td style="padding: 12px; text-align: right; color: var(--c-gray-700);">
                        <?= number_format($item['unit_price'], 0, ',', ' ') ?> FCFA
                    </td>
                    <td style="padding: 12px; text-align: right; color: #0284C7; font-weight: 600;">
                        <?= floatval($item['ristourne_unit'] ?? 0) > 0 ? '+' . number_format($item['ristourne_unit'], 0, ',', ' ') . ' FCFA' : '-' ?>
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
            <?php if (!empty($purchase['notes'])): ?>
                <div style="font-size: 0.85rem; color: var(--c-gray-600);">
                    <strong>Notes :</strong> <?= htmlspecialchars($purchase['notes']) ?>
                </div>
            <?php endif; ?>
            <div style="font-size: 0.85rem; color: #64748B; margin-top: 5px;">
                Total Volume Entrant : <strong><?= number_format($totalCasiers, 1) ?> Unité(s) Grossiste Équivalentes</strong>
            </div>
            <?php if ($totalRistourneExpected > 0): ?>
                <div style="font-size: 0.85rem; color: #0284C7; font-weight: 700; margin-top: 4px;">
                    <i class='bx bx-gift'></i> Ristournes Attendues Fournisseur : <strong><?= number_format($totalRistourneExpected, 0, ',', ' ') ?> FCFA</strong>
                </div>
            <?php endif; ?>
        </div>
        <div style="text-align: right; min-width: 280px;">
            <?php if (!empty($purchase['total_emballage_amount']) && floatval($purchase['total_emballage_amount']) > 0): ?>
                <div style="display: flex; justify-content: space-between; font-size: 0.9rem; color: #475569; margin-bottom: 4px;">
                    <span>Montant Boissons :</span>
                    <span><?= number_format(floatval($purchase['total_amount']) - floatval($purchase['total_emballage_amount']), 0, ',', ' ') ?> FCFA</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.9rem; color: #16A34A; font-weight: 700; margin-bottom: 6px;">
                    <span>Consigne Emballages Déficitaires :</span>
                    <span>+<?= number_format($purchase['total_emballage_amount'], 0, ',', ' ') ?> FCFA</span>
                </div>
            <?php endif; ?>
            <div style="display: flex; justify-content: space-between; padding-top: 10px; border-top: 2px solid #0F172A; font-size: 1.35rem; font-weight: 900; color: var(--c-navy);">
                <span>TOTAL FACTURE :</span>
                <span><?= number_format($purchase['total_amount'], 0, ',', ' ') ?> FCFA</span>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print, .sidebar, .header, .page-title {
        display: none !important;
    }
    body, .main-content {
        background: #FFF !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .card {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        max-width: 100% !important;
    }
}
</style>
