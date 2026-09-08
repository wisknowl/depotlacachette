<div style="margin-bottom: 20px;" class="no-print">
    <a href="<?= BASE_URL ?>/payroll" class="btn btn-primary" style="background-color: var(--c-navy);">
        <i class='bx bx-arrow-back'></i> Retour au Journal de Paie
    </a>
    <button onclick="window.print()" class="btn btn-accent" style="margin-left: 10px;">
        <i class='bx bx-printer'></i> Imprimer le Bulletin de Paie
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
$typeTitle = 'BULLETIN DE PAIE & RÈGLEMENT SALAIRE';
if ($payment['payment_type'] === 'Advance') {
    $typeTitle = 'REÇU D\'AVANCE SUR SALAIRE';
} elseif ($payment['payment_type'] === 'Bonus') {
    $typeTitle = 'BON DE GRATIFICATION / PRIME';
} elseif ($payment['payment_type'] === 'Overtime') {
    $typeTitle = 'BON DE RÈGLEMENT HEURES SUPPLÉMENTAIRES';
}
?>

<div class="card receipt-container" style="max-width: 800px; margin: 0 auto; background: #FFFFFF; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); padding: 35px; border: 1px solid #E2E8F0;">
    
    <!-- HEADER -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #E2E8F0; padding-bottom: 20px; margin-bottom: 25px;">
        <div>
            <h2 style="font-size: 1.6rem; font-weight: 900; color: var(--c-navy); margin: 0 0 5px 0; letter-spacing: -0.5px;">
                DÉPOT LA CACHETTE
            </h2>
            <div style="font-size: 0.88rem; color: var(--c-gray-600); line-height: 1.4;">
                Distribution de Boissons & Brasseries<br>
                Yaoundé, Cameroun
            </div>
        </div>
        <div style="text-align: right;">
            <div style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 800; background: <?= $payment['status'] === 'Valid' ? '#DCFCE7; color: #166534;' : '#FEE2E2; color: #991B1B;' ?> margin-bottom: 8px;">
                <?= $payment['status'] === 'Valid' ? '✓ DÉCAISSEMENT EFFECTUÉ' : '⛔ PAIEMENT ANNULÉ' ?>
            </div>
            <div style="font-size: 1.3rem; font-weight: 800; color: var(--c-navy-dark);">
                <?= htmlspecialchars($payment['id']) ?>
            </div>
            <div style="font-size: 0.85rem; color: var(--c-gray-600);">
                Date : <strong><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></strong>
            </div>
        </div>
    </div>

    <!-- DOCUMENT TITLE -->
    <div style="text-align: center; margin-bottom: 25px; padding: 12px; background: #F8FAFC; border-radius: 8px; border-left: 4px solid var(--c-navy);">
        <h3 style="margin: 0; font-size: 1.2rem; color: var(--c-navy); text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">
            <?= $typeTitle ?>
        </h3>
        <div style="font-size: 0.9rem; color: #64748B; margin-top: 4px;">
            Période : <strong><?= htmlspecialchars($payment['period'] ?: 'Courante') ?></strong>
        </div>
    </div>

    <!-- EMPLOYEE & PAYMENT INFO -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
        <div style="background: #F8FAFC; border-radius: 8px; padding: 18px; border: 1px solid #E2E8F0;">
            <div style="font-size: 0.78rem; text-transform: uppercase; font-weight: 800; color: #64748B; margin-bottom: 6px;">Informations sur l'Employé :</div>
            <div style="font-size: 1.15rem; font-weight: 800; color: var(--c-navy);"><?= htmlspecialchars($payment['employee_name']) ?></div>
            <div style="font-size: 0.9rem; color: var(--c-gray-600); margin-top: 4px;">Fonction : <strong><?= htmlspecialchars($payment['employee_role']) ?></strong></div>
            <?php if (!empty($payment['employee_phone'])): ?>
                <div style="font-size: 0.85rem; color: var(--c-gray-600); margin-top: 2px;">Tél : <?= htmlspecialchars($payment['employee_phone']) ?></div>
            <?php endif; ?>
            <div style="font-size: 0.85rem; color: #64748B; margin-top: 6px;">
                Salaire de Base Référence : <strong><?= number_format($payment['employee_base_salary'], 0, ',', ' ') ?> FCFA</strong>
            </div>
        </div>

        <div style="background: #F8FAFC; border-radius: 8px; padding: 18px; border: 1px solid #E2E8F0;">
            <div style="font-size: 0.78rem; text-transform: uppercase; font-weight: 800; color: #64748B; margin-bottom: 6px;">Détails du Versement :</div>
            <div style="font-size: 0.9rem; color: var(--c-gray-600);">Mode de Paiement : <strong>Espèces (<?= htmlspecialchars($payment['cash_account_name']) ?>)</strong></div>
            <div style="font-size: 0.9rem; color: var(--c-gray-600); margin-top: 4px;">Caissier / Émetteur : <strong><?= htmlspecialchars($payment['username'] ?: 'Admin') ?></strong></div>
            <?php if (!empty($payment['reference'])): ?>
                <div style="font-size: 0.85rem; color: var(--c-gray-600); margin-top: 4px;">Réf Pièce : <?= htmlspecialchars($payment['reference']) ?></div>
            <?php endif; ?>
            <?php if (!empty($payment['notes'])): ?>
                <div style="font-size: 0.85rem; color: #64748B; margin-top: 6px; font-style: italic;">
                    Observations : <?= nl2br(htmlspecialchars($payment['notes'])) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- AMOUNT HIGHLIGHT BOX -->
    <div style="background: #ECFDF5; border: 2px dashed #059669; border-radius: 10px; padding: 20px; text-align: center; margin-bottom: 30px;">
        <div style="font-size: 0.88rem; text-transform: uppercase; font-weight: 700; color: #065F46; letter-spacing: 0.5px;">
            Montant Total Net Perçu en Espèces :
        </div>
        <div style="font-size: 2.2rem; font-weight: 900; color: #047857; margin: 6px 0;">
            <?= number_format($payment['amount'], 0, ',', ' ') ?> FCFA
        </div>
        <div style="font-size: 0.85rem; color: #065F46; font-style: italic;">
            Montant certifié décaissé de la caisse du Dépôt La Cachette.
        </div>
    </div>

    <!-- SIGNATURES -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 35px; padding-top: 20px; border-top: 1px solid #E2E8F0;">
        <div style="text-align: center;">
            <div style="font-size: 0.85rem; font-weight: 800; color: var(--c-navy); text-transform: uppercase; margin-bottom: 60px;">
                Signature de l'Employé(e) / Bénéficiaire
            </div>
            <div style="border-top: 1px dashed #CBD5E1; padding-top: 5px; font-size: 0.78rem; color: #64748B;">
                (Précédé de la mention « Reçu le montant »)
            </div>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 0.85rem; font-weight: 800; color: var(--c-navy); text-transform: uppercase; margin-bottom: 60px;">
                Pour la Direction / Le Responsable Caisse
            </div>
            <div style="border-top: 1px dashed #CBD5E1; padding-top: 5px; font-size: 0.78rem; color: #64748B;">
                <?= htmlspecialchars($payment['username'] ?: 'Admin') ?> (Visa & Cachet)
            </div>
        </div>
    </div>
</div>
