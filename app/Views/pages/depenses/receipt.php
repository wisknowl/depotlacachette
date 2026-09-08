<div class="no-print" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <a href="<?= BASE_URL ?>/depenses" class="btn btn-primary" style="background: var(--c-gray-600);">
            <i class='bx bx-arrow-back'></i> Retour aux Dépenses
        </a>
    </div>
    <div style="display: flex; gap: 10px;">
        <button onclick="window.print()" class="btn btn-accent" style="padding: 10px 20px; font-weight: 700;">
            <i class='bx bx-printer'></i> Imprimer le Bon de Décaissement
        </button>
    </div>
</div>

<div class="card invoice-container" style="max-width: 800px; margin: 0 auto; padding: 30px; background: #fff; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
    
    <!-- HEADER -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0F172A; padding-bottom: 20px; margin-bottom: 25px;">
        <div>
            <h1 style="font-size: 1.6rem; font-weight: 900; color: var(--c-navy); margin: 0; text-transform: uppercase;">
                DÉPÔT LA CACHETTE
            </h1>
            <p style="margin: 4px 0 0 0; font-size: 0.88rem; color: #64748B;">
                Distribution & Vente de Boissons en Gros et Demi-Gros<br>
                Cameroun &bull; Douala &bull; Tél: 699 00 00 00 / 677 00 00 00
            </p>
        </div>
        <div style="text-align: right;">
            <div style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 800; background: <?= $expense['status'] === 'Paid' ? '#DCFCE7; color: #166534;' : '#FEE2E2; color: #991B1B;' ?> margin-bottom: 8px;">
                <?= $expense['status'] === 'Paid' ? '✓ DÉCAISSEMENT EFFECTUÉ' : '⛔ DÉPENSE ANNULÉE' ?>
            </div>
            <div style="font-size: 1.3rem; font-weight: 900; color: var(--c-navy); letter-spacing: 0.5px;">
                BON DE DÉCAISSEMENT DÉPENSE
            </div>
            <div style="font-size: 1.1rem; font-weight: 800; color: var(--c-danger); margin-top: 2px;">
                N° <?= htmlspecialchars($expense['id']) ?>
            </div>
            <div style="font-size: 0.85rem; color: #64748B; margin-top: 4px;">
                Date : <strong><?= date('d/m/Y', strtotime($expense['expense_date'])) ?></strong>
            </div>
        </div>
    </div>

    <!-- EXPENSE & CASH REGISTER INFO -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; padding: 15px; background: #F8FAFC; border-radius: 8px; border: 1px solid #E2E8F0;">
        <div>
            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #64748B; margin-bottom: 5px;">Bénéficiaire / Destinataire</div>
            <div style="font-size: 1.1rem; font-weight: 800; color: var(--c-navy);">
                <?= !empty($expense['beneficiary']) ? htmlspecialchars($expense['beneficiary']) : 'Non spécifié / Divers' ?>
            </div>
            <div style="font-size: 0.88rem; color: #475569; margin-top: 5px;">
                Catégorie de Charge : <strong style="color: var(--c-navy);"><?= htmlspecialchars($expense['category_name'] ?: 'Générale') ?></strong>
            </div>
        </div>

        <div>
            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #64748B; margin-bottom: 5px;">Détails du Décaissement</div>
            <div style="font-size: 0.88rem; color: #1E293B;">
                Mode de Règlement : <strong><?= htmlspecialchars($expense['payment_method_name'] ?: 'Espèces') ?></strong>
            </div>
            <div style="font-size: 0.88rem; color: #1E293B; margin-top: 3px;">
                Caisse Débitée : <strong><?= htmlspecialchars($expense['cash_account_name'] ?: 'Caisse Principale') ?></strong>
            </div>
            <div style="font-size: 0.88rem; color: #1E293B; margin-top: 3px;">
                Caissier / Opérateur : <strong><?= htmlspecialchars($expense['user_name'] ?: 'Admin') ?></strong>
            </div>
            <?php if (!empty($expense['reference'])): ?>
                <div style="font-size: 0.85rem; color: #64748B; margin-top: 3px;">
                    Réf Pièce Externe : <strong><?= htmlspecialchars($expense['reference']) ?></strong>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- AMOUNT BOX -->
    <div style="margin: 25px 0; padding: 20px; background: #FEF2F2; border: 2px solid #F87171; border-radius: 8px; text-align: center;">
        <div style="font-size: 0.9rem; font-weight: 800; color: #991B1B; text-transform: uppercase; letter-spacing: 0.5px;">
            Montant Total Décaissé de la Caisse
        </div>
        <div style="font-size: 2.2rem; font-weight: 900; color: #B91C1C; margin: 6px 0;">
            -<?= number_format($expense['amount'], 0, ',', ' ') ?> FCFA
        </div>
        <div style="font-size: 0.85rem; color: #991B1B; font-style: italic;">
            Décaissement au titre des charges d'exploitation et frais généraux du dépôt.
        </div>
    </div>

    <!-- DESCRIPTION & MOTIF -->
    <div style="padding: 15px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; margin-bottom: 30px;">
        <div style="font-size: 0.8rem; color: #64748B; font-weight: 700; text-transform: uppercase;">Motif / Description de la Dépense</div>
        <div style="font-size: 0.95rem; color: #334155; margin-top: 4px; font-weight: 600;">
            <?= !empty($expense['description']) ? nl2br(htmlspecialchars($expense['description'])) : 'Aucune description saisie.' ?>
        </div>
    </div>

    <!-- SIGNATURES -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 40px; text-align: center;">
        <div>
            <div style="font-size: 0.85rem; font-weight: 800; color: var(--c-navy); text-transform: uppercase;">
                Le Responsable Caisse (Décaissement)
            </div>
            <div style="height: 50px; margin-top: 10px; border-bottom: 1px dashed #94A3B8;"></div>
            <div style="font-size: 0.8rem; color: #64748B; margin-top: 6px;">(<?= htmlspecialchars($expense['user_name'] ?: 'Admin') ?>)</div>
        </div>
        <div>
            <div style="font-size: 0.85rem; font-weight: 800; color: var(--c-navy); text-transform: uppercase;">
                Le Bénéficiaire (Décharge & Reçu)
            </div>
            <div style="height: 50px; margin-top: 10px; border-bottom: 1px dashed #94A3B8;"></div>
            <div style="font-size: 0.8rem; color: #64748B; margin-top: 6px;">(<?= !empty($expense['beneficiary']) ? htmlspecialchars($expense['beneficiary']) : 'Émargement' ?>)</div>
        </div>
    </div>
</div>

<style>
@page {
    size: auto;
    margin: 8mm 12mm;
}
@media print {
    .no-print, header, .top-header, .sidebar, nav, .user-dropdown-container, .toggle-btn, .page-title {
        display: none !important;
    }
    body, .main-content, .page-content, .content-area {
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
        width: 100% !important;
    }
    .invoice-container {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
}
</style>
