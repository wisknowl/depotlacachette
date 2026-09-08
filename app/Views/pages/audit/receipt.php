<div style="margin-bottom: 20px;" class="no-print">
    <a href="<?= BASE_URL ?>/audit" class="btn btn-primary" style="background-color: var(--c-navy);">
        <i class='bx bx-arrow-back'></i> Retour à l'Audit
    </a>
    <button onclick="window.print()" class="btn btn-accent" style="margin-left: 10px;">
        <i class='bx bx-printer'></i> Imprimer le Procès-Verbal de Clôture
    </button>
</div>

<style>
@page {
    size: auto;
    margin: 8mm 12mm;
}
@media print {
    .no-print, .sidebar, .top-header, .topbar, .user-dropdown-container, .toggle-btn, .page-title { display: none !important; }
    .main-content, .page-content { margin-left: 0 !important; padding: 0 !important; width: 100% !important; }
    body { background: white !important; }
    .receipt-container { box-shadow: none !important; border: 1px solid #ccc !important; width: 100% !important; max-width: 100% !important; }
}
</style>

<?php
$diff = floatval($closing['difference']);
$isBalanced = ($diff == 0);
$isShortage = ($diff < 0);
?>

<div class="card receipt-container" style="max-width: 850px; margin: 0 auto; background: #FFFFFF; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); padding: 35px; border: 1px solid #E2E8F0;">
    
    <!-- HEADER -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #E2E8F0; padding-bottom: 20px; margin-bottom: 25px;">
        <div>
            <h2 style="font-size: 1.6rem; font-weight: 900; color: var(--c-navy); margin: 0 0 5px 0; letter-spacing: -0.5px;">
                DÉPOT LA CACHETTE
            </h2>
            <div style="font-size: 0.88rem; color: var(--c-gray-600); line-height: 1.4;">
                Contrôle & Rapprochement de Trésorerie<br>
                Yaoundé, Cameroun
            </div>
        </div>
        <div style="text-align: right;">
            <div style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 800; background: <?= $isBalanced ? '#DCFCE7; color: #166534;' : ($isShortage ? '#FEE2E2; color: #991B1B;' : '#FEF3C7; color: #92400E;') ?> margin-bottom: 8px;">
                <?= $isBalanced ? '✓ CAISSE ÉQUILIBRÉE' : ($isShortage ? '⚠️ DÉFICIT DE CAISSE' : '🟡 EXCÉDENT DE CAISSE') ?>
            </div>
            <div style="font-size: 1.3rem; font-weight: 800; color: var(--c-navy-dark);">
                PV-CLOTURE #<?= htmlspecialchars($closing['id']) ?>
            </div>
            <div style="font-size: 0.85rem; color: var(--c-gray-600);">
                Date Clôturée : <strong><?= date('d/m/Y', strtotime($closing['closing_date'])) ?></strong>
            </div>
        </div>
    </div>

    <!-- DOCUMENT TITLE -->
    <div style="text-align: center; margin-bottom: 25px; padding: 12px; background: #F8FAFC; border-radius: 8px; border-left: 4px solid var(--c-navy);">
        <h3 style="margin: 0; font-size: 1.25rem; color: var(--c-navy); text-transform: uppercase; font-weight: 800;">
            PROCÈS-VERBAL DE CLÔTURE & BILLETAGE DE CAISSE
        </h3>
        <div style="font-size: 0.9rem; color: #64748B; margin-top: 4px;">
            Compte : <strong><?= htmlspecialchars($closing['cash_account_name']) ?></strong> | Opérateur : <strong><?= htmlspecialchars($closing['closed_by_name']) ?></strong>
        </div>
    </div>

    <!-- FINANCIAL RECONCILIATION SUMMARY -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
        <div style="background: #F8FAFC; border-radius: 8px; padding: 16px; border: 1px solid #E2E8F0;">
            <div style="font-size: 0.8rem; text-transform: uppercase; font-weight: 800; color: #64748B; margin-bottom: 8px;">Flux de la Journée (Système) :</div>
            
            <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.9rem;">
                <span>Solde d'Ouverture :</span>
                <strong><?= number_format($closing['opening_balance'], 0, ',', ' ') ?> FCFA</strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.9rem; color: var(--c-success);">
                <span>Total Encaissements (+) :</span>
                <strong>+<?= number_format($closing['total_in'], 0, ',', ' ') ?> FCFA</strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.9rem; color: var(--c-danger);">
                <span>Total Décaissements (-) :</span>
                <strong>-<?= number_format($closing['total_out'], 0, ',', ' ') ?> FCFA</strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 8px 0 0 0; margin-top: 6px; border-top: 2px solid #CBD5E1; font-size: 0.95rem; font-weight: 800; color: var(--c-navy);">
                <span>Solde Théorique Final :</span>
                <span><?= number_format($closing['theoretical_balance'], 0, ',', ' ') ?> FCFA</span>
            </div>
        </div>

        <div style="background: <?= $isBalanced ? '#F0FDF4' : ($isShortage ? '#FEF2F2' : '#FFFBEB') ?>; border-radius: 8px; padding: 16px; border: 1px solid <?= $isBalanced ? '#BBF7D0' : ($isShortage ? '#FECACA' : '#FDE68A') ?>;">
            <div style="font-size: 0.8rem; text-transform: uppercase; font-weight: 800; color: #475569; margin-bottom: 8px;">Comptage Physique Réel :</div>
            
            <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.95rem;">
                <span>Total Espèces Comptées :</span>
                <strong style="font-size: 1.1rem; color: var(--c-navy);"><?= number_format($closing['physical_cash'], 0, ',', ' ') ?> FCFA</strong>
            </div>

            <div style="display: flex; justify-content: space-between; padding: 8px 0 0 0; margin-top: 20px; border-top: 2px solid <?= $isBalanced ? '#86EFAC' : ($isShortage ? '#FCA5A5' : '#FCD34D') ?>; font-size: 1.05rem; font-weight: 900;">
                <span>Écart de Caisse :</span>
                <span style="color: <?= $isBalanced ? '#166534' : ($isShortage ? '#991B1B' : '#92400E') ?>;">
                    <?= ($diff > 0 ? '+' : '') . number_format($diff, 0, ',', ' ') ?> FCFA
                </span>
            </div>
            <div style="font-size: 0.78rem; margin-top: 4px; color: #64748B;">
                <?= $isBalanced ? 'Caisse parfaitement conforme.' : ($isShortage ? 'Manquant à justifier par le caissier.' : 'Excédent constaté.') ?>
            </div>
        </div>
    </div>

    <!-- BILLETAGE DETAILS TABLE -->
    <?php if (!empty($billetage)): ?>
    <div style="margin-bottom: 25px;">
        <div style="font-size: 0.85rem; text-transform: uppercase; font-weight: 800; color: var(--c-navy); margin-bottom: 8px;">
            Détail du Billetage Physique :
        </div>
        <table class="table" style="font-size: 0.85rem;">
            <thead>
                <tr style="background: #F8FAFC;">
                    <th>Coupures (Billets & Pièces)</th>
                    <th style="text-align: center;">Quantité</th>
                    <th style="text-align: right;">Montant Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $cuts = [
                    'b10000' => ['label' => 'Billets de 10 000 FCFA', 'val' => 10000],
                    'b5000'  => ['label' => 'Billets de 5 000 FCFA',  'val' => 5000],
                    'b2000'  => ['label' => 'Billets de 2 000 FCFA',  'val' => 2000],
                    'b1000'  => ['label' => 'Billets de 1 000 FCFA',  'val' => 1000],
                    'b500'   => ['label' => 'Billets de 500 FCFA',    'val' => 500],
                    'c500'   => ['label' => 'Pièces de 500 FCFA',     'val' => 500],
                    'c100'   => ['label' => 'Pièces de 100 FCFA',     'val' => 100],
                    'c50'    => ['label' => 'Pièces de 50 FCFA',      'val' => 50],
                    'c25'    => ['label' => 'Pièces de 25 FCFA',      'val' => 25],
                ];
                foreach ($cuts as $key => $info):
                    $qty = intval($billetage[$key] ?? 0);
                    if ($qty > 0):
                ?>
                <tr>
                    <td><?= $info['label'] ?></td>
                    <td style="text-align: center; font-weight: 700;"><?= $qty ?></td>
                    <td style="text-align: right; font-weight: 700;"><?= number_format($qty * $info['val'], 0, ',', ' ') ?> FCFA</td>
                </tr>
                <?php endif; endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if (!empty($closing['notes'])): ?>
    <div style="background: #F8FAFC; border-radius: 8px; padding: 14px; margin-bottom: 25px; font-size: 0.88rem; color: #475569; border: 1px solid #E2E8F0;">
        <strong>Observations / Remarques :</strong> <?= nl2br(htmlspecialchars($closing['notes'])) ?>
    </div>
    <?php endif; ?>

    <!-- SIGNATURES -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 35px; padding-top: 20px; border-top: 1px solid #E2E8F0;">
        <div style="text-align: center;">
            <div style="font-size: 0.85rem; font-weight: 800; color: var(--c-navy); text-transform: uppercase; margin-bottom: 60px;">
                Le Caissier / Déclarant
            </div>
            <div style="border-top: 1px dashed #CBD5E1; padding-top: 5px; font-size: 0.78rem; color: #64748B;">
                <?= htmlspecialchars($closing['closed_by_name']) ?> (Signature)
            </div>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 0.85rem; font-weight: 800; color: var(--c-navy); text-transform: uppercase; margin-bottom: 60px;">
                Le Gérant / Auditeur
            </div>
            <div style="border-top: 1px dashed #CBD5E1; padding-top: 5px; font-size: 0.78rem; color: #64748B;">
                Visa & Validation de Clôture
            </div>
        </div>
    </div>
</div>
