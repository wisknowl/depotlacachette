<div class="page-title">
    <i class='bx bx-grid-alt'></i>
    <h1>Tableau de Bord - Dépôt La Cachette</h1>
</div>

<!-- TOP STATS (FINANCIAL OVERVIEW) -->
<div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
    <div class="stat-card" style="border-left: 4px solid #059669;">
        <div class="stat-icon" style="background-color: rgba(46, 139, 87, 0.15); color: var(--c-success);">
            <i class='bx bx-trending-up'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($totalCA, 0, ',', ' ') ?> FCFA</div>
            <div class="stat-label">Chiffre d'Affaires Réalisé</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(244, 164, 35, 0.15); color: var(--c-amber);">
            <i class='bx bx-wallet'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="color: var(--c-navy);"><?= number_format($soldeCaisse, 0, ',', ' ') ?> FCFA</div>
            <div class="stat-label">Solde Trésorerie / Caisses</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(255, 193, 7, 0.15); color: #b78103;">
            <i class='bx bx-user-voice'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="color: #b78103;"><?= number_format($totalCreancesClients, 0, ',', ' ') ?> FCFA</div>
            <div class="stat-label">Créances Clients (Dettes à recouvrer)</div>
        </div>
    </div>

    <div class="stat-card" style="border-left: 4px solid #EF4444;">
        <div class="stat-icon" style="background-color: rgba(220, 53, 69, 0.15); color: var(--c-danger);">
            <i class='bx bx-briefcase-alt'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="color: var(--c-danger);"><?= number_format($totalDettesFournisseurs, 0, ',', ' ') ?> FCFA</div>
            <div class="stat-label">Dettes Fournisseurs (À régler)</div>
        </div>
    </div>
</div>

<!-- SECONDARY ROW (WITH NET PROFIT & QUARTERLY REBATES) -->
<div class="dashboard-grid" style="margin-top: 15px; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
    
    <!-- BENEFICE NET REEL (MOIS EN COURS) -->
    <?php $isHomeProfitable = (($netProfitMonth ?? 0) >= 0); ?>
    <div class="stat-card" style="border-left: 4px solid <?= $isHomeProfitable ? '#059669' : '#DC2626' ?>; background: <?= $isHomeProfitable ? '#F0FDF4' : '#FEF2F2' ?>;">
        <div class="stat-icon" style="background-color: <?= $isHomeProfitable ? 'rgba(5, 150, 105, 0.15)' : 'rgba(220, 38, 38, 0.15)' ?>; color: <?= $isHomeProfitable ? '#059669' : '#DC2626' ?>;">
            <i class='bx <?= $isHomeProfitable ? 'bx-trophy' : 'bx-error-alt' ?>'></i>
        </div>
        <div class="stat-content" style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: baseline;">
                <div class="stat-value" style="color: <?= $isHomeProfitable ? '#047857' : '#DC2626' ?>;">
                    <?= ($isHomeProfitable ? '+' : '') . number_format($netProfitMonth ?? 0, 0, ',', ' ') ?> FCFA
                </div>
                <span style="font-size: 0.78rem; font-weight: 700; padding: 2px 6px; border-radius: 4px; background: <?= $isHomeProfitable ? '#DCFCE7' : '#FEE2E2' ?>; color: <?= $isHomeProfitable ? '#166534' : '#991B1B' ?>;">
                    <?= $netMarginPctMonth ?? 0 ?>%
                </span>
            </div>
            <div class="stat-label" style="font-weight: 800; color: <?= $isHomeProfitable ? '#065F46' : '#991B1B' ?>;">
                Bénéfice Net Réel (Ce Mois)
            </div>
            <div style="margin-top: 6px;">
                <a href="<?= BASE_URL ?>/rentabilite" target="_blank" style="font-size: 0.8rem; font-weight: 700; color: #0284C7; display: inline-flex; align-items: center; gap: 4px; text-decoration: underline;">
                    <i class='bx bx-line-chart'></i> Analyse Complète Rentabilité & P&L <i class='bx bx-link-external'></i>
                </a>
            </div>
        </div>
    </div>

    <!-- RISTOURNES FOURNISSEURS CARD -->
    <div class="stat-card" style="border-left: 4px solid #0284C7;">
        <div class="stat-icon" style="background-color: rgba(2, 132, 199, 0.15); color: #0284C7;">
            <i class='bx bx-gift'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="color: #0284C7;"><?= number_format($trimesterRistournes, 0, ',', ' ') ?> FCFA</div>
            <div class="stat-label">
                Ristournes Trimestrielles Attendues (T<?= $currentQuarter ?> <?= date('Y') ?>)
            </div>
            <small style="color: #64748B; font-size: 0.78rem; display: block; margin-top: 3px;">
                Bonus cumulés sur achats à percevoir en fin de trimestre
            </small>
        </div>
    </div>

    <!-- DEPENSES CARD -->
    <div class="stat-card" style="border-left: 4px solid #EF4444;">
        <div class="stat-icon" style="background-color: rgba(230, 57, 70, 0.1); color: var(--c-danger);">
            <i class='bx bx-money-withdraw'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($totalDepenses, 0, ',', ' ') ?> FCFA</div>
            <div class="stat-label">Charges & Dépenses Cumulées</div>
        </div>
    </div>
    
    <!-- ALERTE STOCK CARD -->
    <div class="stat-card" style="border-left: 4px solid #EF4444;">
        <div class="stat-icon" style="background-color: rgba(230, 57, 70, 0.1); color: var(--c-danger);">
            <i class='bx bx-error-alt'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="<?= ($alertStockCount > 0) ? 'color: var(--c-danger);' : '' ?>"><?= $alertStockCount ?></div>
            <div class="stat-label">Produits en Alerte Stock / Rupture</div>
        </div>
    </div>
</div>

<!-- SUPPLIER RISTOURNES BREAKDOWN (IF ANY) -->
<?php if (!empty($supplierRistournes)): ?>
<div class="card" style="margin-top: 15px; border-left: 4px solid #0284C7;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <span><i class='bx bx-gift'></i> Détail des Ristournes Cumulées par Brasserie / Fournisseur (Trimestre T<?= $currentQuarter ?> <?= date('Y') ?>)</span>
        <a href="<?= BASE_URL ?>/achats" class="btn btn-primary" style="padding: 3px 8px; font-size: 0.75rem; background: #0284C7;">Voir Achats</a>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Fournisseur / Brasserie</th>
                    <th>Volume Acheté (Casiers / Packs)</th>
                    <th>Ristourne Totale Attendue</th>
                    <th>Statut d'Encaissement</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($supplierRistournes as $sr): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($sr['supplier_name']) ?></strong></td>
                    <td style="font-weight: 700; color: var(--c-navy);"><?= number_format($sr['total_casiers'], 1) ?> Unité(s)</td>
                    <td style="font-weight: 800; font-size: 1.05rem; color: #0284C7;">
                        +<?= number_format($sr['total_ristourne'], 0, ',', ' ') ?> FCFA
                    </td>
                    <td>
                        <span style="padding: 3px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; background: #E0F2FE; color: #0369A1;">
                            <i class='bx bx-time'></i> À percevoir en fin de trimestre
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- RECENT ACTIVITY GRID -->
<div class="dashboard-grid" style="grid-template-columns: 1fr 1fr; margin-top: 15px;">
    <!-- RECENT SALES -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span><i class='bx bx-cart'></i> Dernières Ventes</span>
            <a href="<?= BASE_URL ?>/ventes" class="btn btn-primary" style="padding: 3px 8px; font-size: 0.75rem;">Voir tout</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Client</th>
                        <th>Montant</th>
                        <th>Paiement</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentSales)): ?>
                    <tr><td colspan="4" style="text-align: center; padding: 20px;">Aucune vente récente.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentSales as $s): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($s['id']) ?></strong></td>
                            <td><?= htmlspecialchars($s['client_name']) ?></td>
                            <td style="color: var(--c-success); font-weight: bold;"><?= number_format($s['total_amount'], 0, ',', ' ') ?> FCFA</td>
                            <td><span style="padding: 2px 6px; background: var(--c-gray-200); border-radius: 8px; font-size: 0.75rem;"><?= htmlspecialchars($s['payment_method_name']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- RECENT CASH FLOW -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span><i class='bx bx-transfer'></i> Flux de Trésorerie Récents</span>
            <a href="<?= BASE_URL ?>/caisse" class="btn btn-primary" style="padding: 3px 8px; font-size: 0.75rem;">Voir caisse</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Compte</th>
                        <th>Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentTransactions)): ?>
                    <tr><td colspan="3" style="text-align: center; padding: 20px;">Aucun flux récent.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentTransactions as $t): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($t['transaction_type']) ?></strong></td>
                            <td><?= htmlspecialchars($t['account_name']) ?></td>
                            <td>
                                <?php if ($t['amount_in'] > 0): ?>
                                    <span style="color: var(--c-success); font-weight: bold;">+<?= number_format($t['amount_in'], 0, ',', ' ') ?> FCFA</span>
                                <?php else: ?>
                                    <span style="color: var(--c-danger); font-weight: bold;">-<?= number_format($t['amount_out'], 0, ',', ' ') ?> FCFA</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
