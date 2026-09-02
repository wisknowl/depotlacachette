<div class="no-print" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <a href="<?= BASE_URL ?>/paiementsFournisseurs" class="btn btn-primary" style="background: var(--c-gray-600);">
            <i class='bx bx-arrow-back'></i> Retour aux Règlements Fournisseurs
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
            <div style="font-size: 1.3rem; font-weight: 900; color: var(--c-navy); letter-spacing: 0.5px;">
                BON DE DÉCAISSEMENT
            </div>
            <div style="font-size: 1.1rem; font-weight: 800; color: #9D174D; margin-top: 2px;">
                N° <?= htmlspecialchars($paiement['id']) ?>
            </div>
            <div style="font-size: 0.85rem; color: #64748B; margin-top: 4px;">
                Date : <strong><?= date('d/m/Y', strtotime($paiement['payment_date'])) ?></strong>
            </div>
        </div>
    </div>

    <!-- SUPPLIER & DISBURSEMENT INFO -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; padding: 15px; background: #F8FAFC; border-radius: 8px; border: 1px solid #E2E8F0;">
        <div>
            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #64748B; margin-bottom: 5px;">Payé à l'Ordre de (Fournisseur)</div>
            <div style="font-size: 1.1rem; font-weight: 800; color: var(--c-navy);">
                <?= htmlspecialchars($paiement['supplier_name']) ?>
            </div>
            <?php if (!empty($paiement['supplier_phone'])): ?>
                <div style="font-size: 0.85rem; color: #475569; margin-top: 3px;">
                    <i class='bx bx-phone'></i> <?= htmlspecialchars($paiement['supplier_phone']) ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($paiement['supplier_address'])): ?>
                <div style="font-size: 0.85rem; color: #475569;">
                    <i class='bx bx-map'></i> <?= htmlspecialchars($paiement['supplier_address']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #64748B; margin-bottom: 5px;">Détails du Décaissement</div>
            <div style="font-size: 0.88rem; color: #1E293B;">
                Mode de Règlement : <strong><?= htmlspecialchars($paiement['payment_method_name'] ?: 'Espèces') ?></strong>
            </div>
            <div style="font-size: 0.88rem; color: #1E293B; margin-top: 3px;">
                Caisse Débitée : <strong><?= htmlspecialchars($paiement['cash_account_name'] ?: 'Caisse Principale') ?></strong>
            </div>
            <div style="font-size: 0.88rem; color: #1E293B; margin-top: 3px;">
                Caissier / Opérateur : <strong><?= htmlspecialchars($paiement['user_name'] ?: 'Admin') ?></strong>
            </div>
            <?php if (!empty($paiement['reference'])): ?>
                <div style="font-size: 0.85rem; color: #64748B; margin-top: 3px;">
                    N° Chèque / Virement / Réf : <strong><?= htmlspecialchars($paiement['reference']) ?></strong>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- AMOUNT BOX -->
    <div style="margin: 25px 0; padding: 20px; background: #FDF2F8; border: 2px solid #EC4899; border-radius: 8px; text-align: center;">
        <div style="font-size: 0.9rem; font-weight: 800; color: #9D174D; text-transform: uppercase; letter-spacing: 0.5px;">
            Montant Total Décaissé
        </div>
        <div style="font-size: 2.2rem; font-weight: 900; color: #831843; margin: 6px 0;">
            <?= number_format($paiement['amount'], 0, ',', ' ') ?> FCFA
        </div>
        <div style="font-size: 0.85rem; color: #BE185D; font-style: italic;">
            Règlement au titre de compensation de facture(s) d'achats à crédit / approvisionnement.
        </div>
    </div>

    <!-- BALANCE SITUATION -->
    <div style="display: flex; justify-content: space-between; padding: 15px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; margin-bottom: 30px;">
        <div>
            <div style="font-size: 0.8rem; color: #64748B; font-weight: 700; text-transform: uppercase;">Motif / Notes</div>
            <div style="font-size: 0.88rem; color: #334155; margin-top: 3px;">
                <?= !empty($paiement['notes']) ? htmlspecialchars($paiement['notes']) : 'Règlement de facture fournisseur.' ?>
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 0.8rem; color: #64748B; font-weight: 700; text-transform: uppercase;">Dette Restante Due au Fournisseur</div>
            <div style="font-size: 1.15rem; font-weight: 900; color: <?= floatval($paiement['current_remaining_debt']) > 0 ? 'var(--c-amber)' : 'var(--c-success)' ?>; margin-top: 3px;">
                <?= number_format(max(0, floatval($paiement['current_remaining_debt'])), 0, ',', ' ') ?> FCFA
            </div>
        </div>
    </div>

    <!-- SIGNATURES -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 40px; text-align: center;">
        <div>
            <div style="font-size: 0.85rem; font-weight: 800; color: var(--c-navy); text-transform: uppercase;">
                Le Responsable Caisse (Décaissement)
            </div>
            <div style="height: 50px; margin-top: 10px; border-bottom: 1px dashed #94A3B8;"></div>
            <div style="font-size: 0.8rem; color: #64748B; margin-top: 6px;">(<?= htmlspecialchars($paiement['user_name'] ?: 'Admin') ?>)</div>
        </div>
        <div>
            <div style="font-size: 0.85rem; font-weight: 800; color: var(--c-navy); text-transform: uppercase;">
                Le Représentant Fournisseur (Décharge)
            </div>
            <div style="height: 50px; margin-top: 10px; border-bottom: 1px dashed #94A3B8;"></div>
            <div style="font-size: 0.8rem; color: #64748B; margin-top: 6px;">Visa & Émargement</div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print, header, .sidebar, nav {
        display: none !important;
    }
    body, .main-content, .content-area {
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
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
