<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div class="page-title" style="margin-bottom: 0;">
        <i class='bx bx-cart'></i>
        <h1>Ventes & Facturation</h1>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="<?= BASE_URL ?>/emballages" class="btn btn-primary" style="background-color: var(--c-navy); display: flex; align-items: center; gap: 6px; font-weight: 600;">
            <i class='bx bx-archive'></i> Parc d'Emballages
        </a>
        <a href="<?= BASE_URL ?>/stock" class="btn btn-primary" style="background-color: var(--c-navy); display: flex; align-items: center; gap: 6px; font-weight: 600;">
            <i class='bx bx-box'></i> Vérifier Mouvements Stock
        </a>
        <a href="<?= BASE_URL ?>/caisse" class="btn btn-primary" style="background-color: var(--c-navy); display: flex; align-items: center; gap: 6px; font-weight: 600;">
            <i class='bx bx-wallet'></i> Vérifier Journal Caisse
        </a>
    </div>
</div>

<?php if (!empty($flash_success)): ?>
    <div style="background: #D1FAE5; border-left: 4px solid var(--c-success); color: #065F46; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
        <i class='bx bx-check-circle' style="font-size: 1.4rem;"></i>
        <span><?= htmlspecialchars($flash_success) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($flash_error)): ?>
    <div style="background: #FEE2E2; border-left: 4px solid var(--c-danger); color: #991B1B; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
        <i class='bx bx-error-circle' style="font-size: 1.4rem;"></i>
        <span><?= htmlspecialchars($flash_error) ?></span>
    </div>
<?php endif; ?>

<!-- STATUS TABS & SEARCH TOOLBAR -->
<div class="card" style="margin-bottom: 20px; padding: 16px 20px; background: white;">
    
    <!-- 1-CLICK STATUS TABS -->
    <div style="display: flex; gap: 10px; margin-bottom: 15px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9; align-items: center; flex-wrap: wrap;">
        <span style="font-size: 0.82rem; font-weight: 700; color: #64748B; margin-right: 5px;">Règlement :</span>
        
        <a href="<?= BASE_URL ?>/ventes?payment_mode=all<?= !empty($filterSearch) ? '&search='.urlencode($filterSearch) : '' ?><?= !empty($filterClientId) ? '&client_id='.$filterClientId : '' ?><?= !empty($filterCashAccountId) ? '&cash_account_id='.$filterCashAccountId : '' ?>" 
           class="btn <?= ($filterPaymentMode === 'all' || empty($filterPaymentMode)) ? 'btn-accent' : 'btn-primary' ?>" 
           style="padding: 5px 12px; font-size: 0.82rem; <?= ($filterPaymentMode !== 'all' && !empty($filterPaymentMode)) ? 'background: #F1F5F9; color: #334155;' : '' ?>">
            <i class='bx bx-list-ul'></i> Toutes les Ventes (<?= $countTotal ?>)
        </a>

        <a href="<?= BASE_URL ?>/ventes?payment_mode=cash<?= !empty($filterSearch) ? '&search='.urlencode($filterSearch) : '' ?><?= !empty($filterClientId) ? '&client_id='.$filterClientId : '' ?><?= !empty($filterCashAccountId) ? '&cash_account_id='.$filterCashAccountId : '' ?>" 
           class="btn <?= ($filterPaymentMode === 'cash') ? 'btn-accent' : 'btn-primary' ?>" 
           style="padding: 5px 12px; font-size: 0.82rem; <?= ($filterPaymentMode !== 'cash') ? 'background: #F0FDF4; color: #16A34A; border: 1px solid #DCFCE7;' : '' ?>">
            🟢 Au Comptant (<?= $countCash ?>)
        </a>

        <a href="<?= BASE_URL ?>/ventes?payment_mode=credit<?= !empty($filterSearch) ? '&search='.urlencode($filterSearch) : '' ?><?= !empty($filterClientId) ? '&client_id='.$filterClientId : '' ?><?= !empty($filterCashAccountId) ? '&cash_account_id='.$filterCashAccountId : '' ?>" 
           class="btn <?= ($filterPaymentMode === 'credit') ? 'btn-accent' : 'btn-primary' ?>" 
           style="padding: 5px 12px; font-size: 0.82rem; <?= ($filterPaymentMode !== 'credit') ? 'background: #FFFBEB; color: #D97706; border: 1px solid #FEF3C7;' : '' ?>">
            🟡 À Crédit / Créances (<?= $countCredit ?>)
        </a>
    </div>

    <!-- MULTI-CRITERIA FILTER FORM -->
    <form action="<?= BASE_URL ?>/ventes" method="GET" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
        <input type="hidden" name="payment_mode" value="<?= htmlspecialchars($filterPaymentMode ?? 'all') ?>">

        <!-- SEARCH -->
        <div style="flex: 2; min-width: 180px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Recherche par N° Facture / Réf</label>
            <div style="position: relative;">
                <input type="text" name="search" class="form-control" placeholder="N° facture, réf, notes..." value="<?= htmlspecialchars($filterSearch ?? '') ?>" style="padding-left: 32px; font-size: 0.88rem;">
                <i class='bx bx-search' style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 1.1rem;"></i>
            </div>
        </div>

        <!-- CLIENT -->
        <div style="flex: 1.5; min-width: 160px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Client</label>
            <select name="client_id" class="form-control" style="font-size: 0.88rem;">
                <option value="">-- Tous les clients --</option>
                <?php foreach ($clients as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (($filterClientId ?? '') == $c['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- CASH ACCOUNT -->
        <div style="flex: 1.2; min-width: 150px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Compte d'Encaissement</label>
            <select name="cash_account_id" class="form-control" style="font-size: 0.88rem;">
                <option value="">-- Tous les comptes --</option>
                <?php foreach ($cashAccounts as $ca): ?>
                    <option value="<?= $ca['id'] ?>" <?= (($filterCashAccountId ?? '') == $ca['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ca['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- DATES -->
        <div style="display: flex; align-items: flex-end; gap: 6px;">
            <div>
                <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Du</label>
                <input type="date" name="start_date" class="form-control" style="padding: 4px 8px; font-size: 0.85rem;" value="<?= htmlspecialchars($filterStartDate ?? '') ?>">
            </div>
            <div>
                <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Au</label>
                <input type="date" name="end_date" class="form-control" style="padding: 4px 8px; font-size: 0.85rem;" value="<?= htmlspecialchars($filterEndDate ?? '') ?>">
            </div>
        </div>

        <!-- BUTTONS -->
        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-accent" style="padding: 8px 16px; font-size: 0.88rem;">
                <i class='bx bx-filter-alt'></i> Filtrer
            </button>
            <?php if (!empty($filterSearch) || !empty($filterClientId) || ($filterPaymentMode !== 'all' && !empty($filterPaymentMode)) || !empty($filterCashAccountId) || !empty($filterStartDate) || !empty($filterEndDate) || !empty($filterPeriod)): ?>
                <a href="<?= BASE_URL ?>/ventes" class="btn btn-primary" style="padding: 8px 12px; font-size: 0.88rem; background: #64748B;" title="Réinitialiser les filtres">
                    <i class='bx bx-reset'></i>
                </a>
            <?php endif; ?>
        </div>
    </form>

    <!-- QUICK PERIOD SHORTCUTS -->
    <div style="display: flex; gap: 8px; margin-top: 12px; padding-top: 10px; border-top: 1px solid #F1F5F9; align-items: center; flex-wrap: wrap;">
        <span style="font-size: 0.8rem; font-weight: 700; color: #64748B;">Période :</span>
        <a href="<?= BASE_URL ?>/ventes?period=today<?= !empty($filterPaymentMode) ? '&payment_mode='.$filterPaymentMode : '' ?>" class="btn <?= (($filterPeriod ?? '') === 'today') ? 'btn-accent' : 'btn-primary' ?>" style="padding: 3px 8px; font-size: 0.75rem; <?= (($filterPeriod ?? '') !== 'today') ? 'background: #F1F5F9; color: #334155;' : '' ?>">
            Aujourd'hui
        </a>
        <a href="<?= BASE_URL ?>/ventes?period=week<?= !empty($filterPaymentMode) ? '&payment_mode='.$filterPaymentMode : '' ?>" class="btn <?= (($filterPeriod ?? '') === 'week') ? 'btn-accent' : 'btn-primary' ?>" style="padding: 3px 8px; font-size: 0.75rem; <?= (($filterPeriod ?? '') !== 'week') ? 'background: #F1F5F9; color: #334155;' : '' ?>">
            Cette Semaine
        </a>
        <a href="<?= BASE_URL ?>/ventes?period=month<?= !empty($filterPaymentMode) ? '&payment_mode='.$filterPaymentMode : '' ?>" class="btn <?= (($filterPeriod ?? '') === 'month') ? 'btn-accent' : 'btn-primary' ?>" style="padding: 3px 8px; font-size: 0.75rem; <?= (($filterPeriod ?? '') !== 'month') ? 'background: #F1F5F9; color: #334155;' : '' ?>">
            Ce Mois
        </a>
        <a href="<?= BASE_URL ?>/ventes" class="btn btn-primary" style="padding: 3px 8px; font-size: 0.75rem; background: #F1F5F9; color: #334155;">
            Toutes les ventes
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span><i class='bx bx-list-ul'></i> Journal des Ventes & Factures Multi-Produits</span>
            <span style="font-size: 0.82rem; font-weight: 700; color: var(--c-navy); background: #F1F5F9; padding: 3px 8px; border-radius: 6px;">
                <?= count($ventes) ?> facture(s) affichée(s)
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
            <span style="font-size: 0.85rem; color: #64748B;">
                Total Facturé Sélection : <strong style="color: var(--c-success); font-size: 0.95rem;"><?= number_format($filteredTotal, 0, ',', ' ') ?> FCFA</strong>
            </span>
            <!-- DROPDOWN NOUVELLE VENTE -->
            <div style="position: relative; display: inline-block;">
                <button type="button" class="btn btn-accent" id="btn_new_sale_dropdown" onclick="toggleNewSaleDropdown()" style="display: flex; align-items: center; gap: 6px; font-weight: 700;">
                    <i class='bx bx-plus-circle'></i> Nouvelle Vente <i class='bx bx-chevron-down' style="font-size: 1.1rem;"></i>
                </button>
                <div id="new_sale_dropdown_menu" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 6px; background: white; border: 1px solid #E2E8F0; border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15); min-width: 260px; z-index: 1000; overflow: hidden;">
                    <a href="<?= BASE_URL ?>/ventes/form" style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; color: var(--c-navy); text-decoration: none; font-size: 0.9rem; font-weight: 600; border-bottom: 1px solid #F1F5F9;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='white'">
                        <div style="width: 32px; height: 32px; border-radius: 6px; background: #EEF2FF; color: var(--c-navy); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                            <i class='bx bx-package'></i>
                        </div>
                        <div>
                            <div>Vente en Gros & Demi</div>
                            <div style="font-size: 0.75rem; color: #64748B; font-weight: 400;">Casiers entiers et demi-casiers</div>
                        </div>
                    </a>
                    <a href="<?= BASE_URL ?>/ventes/en-detail" style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; color: #059669; text-decoration: none; font-size: 0.9rem; font-weight: 600;" onmouseover="this.style.background='#ECFDF5'" onmouseout="this.style.background='white'">
                        <div style="width: 32px; height: 32px; border-radius: 6px; background: #D1FAE5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                            <i class='bx bx-wine'></i>
                        </div>
                        <div>
                            <div>Vente au Détail</div>
                            <div style="font-size: 0.75rem; color: #059669; font-weight: 400;">Bouteilles, bar & sur place</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>N° Facture</th>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Articles & Conditionnements</th>
                    <th>Montant Total</th>
                    <th>Règlement</th>
                    <th>Statut</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ventes)): ?>
                <tr><td colspan="8" style="text-align: center; padding: 30px; color: var(--c-gray-600);">Aucune facture enregistrée pour le moment.</td></tr>
                <?php else: ?>
                    <?php foreach ($ventes as $v): ?>
                    <?php 
                        $isCredit = ($v['payment_method_id'] == 5); 
                        $isValid = ($v['status'] === 'Valid');
                    ?>
                    <tr style="<?= !$isValid ? 'opacity: 0.65; background: #FFF5F5;' : '' ?>">
                        <td>
                            <a href="<?= BASE_URL ?>/ventes/invoice/<?= $v['id'] ?>" style="font-weight: 800; color: var(--c-navy); text-decoration: none;">
                                <?= htmlspecialchars($v['id']) ?>
                            </a>
                        </td>
                        <td><?= date('d/m/Y', strtotime($v['sale_date'])) ?></td>
                        <td><strong><?= htmlspecialchars($v['client_name']) ?></strong></td>
                        <td>
                            <div style="font-weight: 600; color: var(--c-navy-dark); font-size: 0.9rem;">
                                <?= htmlspecialchars($v['products_summary']) ?>
                            </div>
                            <small style="color: #64748B;">
                                <?= intval($v['item_count']) ?> ligne(s) d'articles
                            </small>
                        </td>
                        <td style="font-weight: 800; font-size: 1.05rem; color: <?= $isValid ? 'var(--c-success)' : '#991B1B' ?>;">
                            <?= number_format($v['total_amount'], 0, ',', ' ') ?> FCFA
                        </td>
                        <td>
                            <?php if ($isCredit): ?>
                                <span style="padding: 3px 8px; background: #FEF3C7; color: #92400E; border-radius: 10px; font-size: 0.78rem; font-weight: 700;">
                                    🟡 À Crédit
                                </span>
                            <?php else: ?>
                                <span style="padding: 3px 8px; background: #D1FAE5; color: #065F46; border-radius: 10px; font-size: 0.78rem; font-weight: 700;">
                                    🟢 <?= htmlspecialchars($v['cash_account_name'] ?: 'Caisse') ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($isValid): ?>
                                <span style="padding: 3px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 800; background: #DCFCE7; color: #166534;">
                                    Validée
                                </span>
                            <?php else: ?>
                                <span style="padding: 3px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 800; background: #FEE2E2; color: #991B1B;">
                                    Annulée
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 6px; justify-content: center;">
                                <a href="<?= BASE_URL ?>/ventes/invoice/<?= $v['id'] ?>" class="btn btn-primary" style="padding: 4px 9px; font-size: 0.85rem;" title="Voir / Imprimer Facture">
                                    <i class='bx bx-printer'></i>
                                </a>
                                <?php if ($isValid && \App\Core\Helper::isAdmin()): ?>
                                    <a href="<?= BASE_URL ?>/ventes/cancel/<?= $v['id'] ?>" class="btn btn-primary" style="padding: 4px 9px; font-size: 0.85rem; background-color: var(--c-danger);" onclick="return confirm('Annuler la facture <?= $v['id'] ?> ?\n\nLe stock vendu sera réintégré et la caisse/crédit sera rééquilibré automatiquement.')" title="Annuler la facture (Admin)">
                                        <i class='bx bx-x-circle'></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleNewSaleDropdown() {
    const menu = document.getElementById("new_sale_dropdown_menu");
    if (menu) {
        menu.style.display = (menu.style.display === "none" || menu.style.display === "") ? "block" : "none";
    }
}

document.addEventListener("click", function(e) {
    const btn = document.getElementById("btn_new_sale_dropdown");
    const menu = document.getElementById("new_sale_dropdown_menu");
    if (menu && btn && !btn.contains(e.target) && !menu.contains(e.target)) {
        menu.style.display = "none";
    }
});
</script>
