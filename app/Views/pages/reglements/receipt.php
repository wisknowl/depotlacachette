<div class="no-print" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <a href="<?= BASE_URL ?>/reglements" class="btn btn-primary" style="background: var(--c-gray-600);">
            <i class='bx bx-arrow-back'></i> Retour aux Règlements
        </a>
    </div>
    <div style="display: flex; gap: 10px;">
        <button onclick="window.print()" class="btn btn-accent" style="padding: 10px 20px; font-weight: 700;">
            <i class='bx bx-printer'></i> Imprimer le Reçu d'Encaissement
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
            <div style="font-size: 1.3rem; font-weight: 900; color: var(--c-navy); letter-spacing: 0.5px;">
                REÇU D'ENCAISSEMENT
            </div>
            <div style="font-size: 1.1rem; font-weight: 800; color: #0284C7; margin-top: 2px;">
                N° <?= htmlspecialchars($payment['id']) ?>
            </div>
            <div style="font-size: 0.85rem; color: #64748B; margin-top: 4px;">
                Date : <strong><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></strong>
            </div>
        </div>
    </div>

    <!-- CLIENT & PAYMENT METHOD INFO -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; padding: 15px; background: #F8FAFC; border-radius: 8px; border: 1px solid #E2E8F0;">
        <div>
            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #64748B; margin-bottom: 5px;">Reçu de (Client)</div>
            <div style="font-size: 1.1rem; font-weight: 800; color: var(--c-navy);">
                <?= htmlspecialchars($payment['client_name']) ?>
            </div>
            <?php if (!empty($payment['client_phone'])): ?>
                <div style="font-size: 0.85rem; color: #475569; margin-top: 3px;">
                    <i class='bx bx-phone'></i> <?= htmlspecialchars($payment['client_phone']) ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($payment['client_address'])): ?>
                <div style="font-size: 0.85rem; color: #475569;">
                    <i class='bx bx-map'></i> <?= htmlspecialchars($payment['client_address']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #64748B; margin-bottom: 5px;">Détails de l'Encaissement</div>
            <div style="font-size: 0.88rem; color: #1E293B;">
                Mode de Paiement : <strong><?= htmlspecialchars($payment['payment_method_name'] ?: 'Espèces') ?></strong>
            </div>
            <div style="font-size: 0.88rem; color: #1E293B; margin-top: 3px;">
                Compte Récepteur : <strong><?= htmlspecialchars($payment['cash_account_name'] ?: 'Caisse Principale') ?></strong>
            </div>
            <div style="font-size: 0.88rem; color: #1E293B; margin-top: 3px;">
                Caissier / Opérateur : <strong><?= htmlspecialchars($payment['user_name'] ?: 'Admin') ?></strong>
            </div>
            <?php if (!empty($payment['reference'])): ?>
                <div style="font-size: 0.85rem; color: #64748B; margin-top: 3px;">
                    Réf / N° Chèque / Trx : <strong><?= htmlspecialchars($payment['reference']) ?></strong>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- AMOUNT BOX -->
    <div style="margin: 25px 0; padding: 20px; background: #EFF6FF; border: 2px solid #3B82F6; border-radius: 8px; text-align: center;">
        <div style="font-size: 0.9rem; font-weight: 800; color: #1E40AF; text-transform: uppercase; letter-spacing: 0.5px;">
            Montant Total Reçu & Encaissé
        </div>
        <div style="font-size: 2.2rem; font-weight: 900; color: #1E3A8A; margin: 6px 0;">
            <?= number_format($payment['amount'], 0, ',', ' ') ?> FCFA
        </div>
        <div style="font-size: 0.85rem; color: #3B82F6; font-style: italic;">
            Encaissement au titre de règlement partiel ou total de facture(s) en suspens.
        </div>
    </div>

    <!-- BALANCE SITUATION -->
    <div style="display: flex; justify-content: space-between; padding: 15px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; margin-bottom: 30px;">
        <div>
            <div style="font-size: 0.8rem; color: #64748B; font-weight: 700; text-transform: uppercase;">Observations / Notes</div>
            <div style="font-size: 0.88rem; color: #334155; margin-top: 3px;">
                <?= !empty($payment['notes']) ? htmlspecialchars($payment['notes']) : 'Règlement régulier sur compte client.' ?>
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 0.8rem; color: #64748B; font-weight: 700; text-transform: uppercase;">Solde Restant Dû par le Client</div>
            <div style="font-size: 1.15rem; font-weight: 900; color: <?= floatval($payment['current_remaining_debt']) > 0 ? 'var(--c-danger)' : 'var(--c-success)' ?>; margin-top: 3px;">
                <?= number_format(max(0, floatval($payment['current_remaining_debt'])), 0, ',', ' ') ?> FCFA
            </div>
        </div>
    </div>

    <!-- SIGNATURES -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 40px; text-align: center;">
        <div>
            <div style="font-size: 0.85rem; font-weight: 800; color: var(--c-navy); text-transform: uppercase;">
                Signature du Client / Remettant
            </div>
            <div style="height: 50px; margin-top: 10px; border-bottom: 1px dashed #94A3B8;"></div>
            <div style="font-size: 0.8rem; color: #64748B; margin-top: 6px;">Visa & Émargement</div>
        </div>
        <div>
            <div style="font-size: 0.85rem; font-weight: 800; color: var(--c-navy); text-transform: uppercase;">
                Pour le Dépôt La Cachette (Le Caissier)
            </div>
            <div style="height: 50px; margin-top: 10px; border-bottom: 1px dashed #94A3B8;"></div>
            <div style="font-size: 0.8rem; color: #64748B; margin-top: 6px;">(<?= htmlspecialchars($payment['user_name'] ?: 'Admin') ?>)</div>
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
