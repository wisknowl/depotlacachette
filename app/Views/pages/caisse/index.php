<?php 
$currentUserRole = $_SESSION['user']['role'] ?? 'Admin';
$isAdmin = ($currentUserRole === 'Admin');
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
    <div class="page-title" style="margin-bottom: 0;">
        <i class='bx bx-wallet'></i>
        <h1>Caisse & Trésorerie</h1>
    </div>
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <a href="<?= BASE_URL ?>/caisse/cloture" class="btn btn-accent" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; background: #059669; border-color: #059669; color: white; padding: 10px 18px; border-radius: 8px; box-shadow: 0 2px 8px rgba(5,150,105,0.25);">
            <i class='bx bx-calculator' style="font-size: 1.2rem;"></i> Clôture de Caisse & Billetage
        </a>
        <?php if ($isAdmin): ?>
        <a href="<?= BASE_URL ?>/caisse/form" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; background: var(--c-navy); padding: 10px 18px; border-radius: 8px;">
            <i class='bx bx-transfer-alt' style="font-size: 1.2rem;"></i> Alimentation / Transfert
        </a>
        <?php endif; ?>
    </div>
</div>


<?php if (!empty($flash_success)): ?>
    <div style="background: #D1FAE5; border-left: 4px solid var(--c-success); color: #065F46; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
        <i class='bx bx-check-circle' style="font-size: 1.4rem;"></i>
        <span><?= htmlspecialchars($flash_success) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($flash_error)): ?>
    <div style="background: #FEE2E2; border-left: 4px solid var(--c-danger); color: #991B1B; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
        <i class='bx bx-error-circle' style="font-size: 1.4rem;"></i>
        <span><?= htmlspecialchars($flash_error) ?></span>
    </div>
<?php endif; ?>

<!-- GLOBAL STATS (WITH NET PROFIT OF THE MONTH) -->
<div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 25px;">
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(244, 164, 35, 0.15); color: var(--c-amber);">
            <i class='bx bx-wallet'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="color: var(--c-navy);"><?= number_format($totalGlobalSolde, 0, ',', ' ') ?> FCFA</div>
            <div class="stat-label">Solde Global Disponible</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(46, 139, 87, 0.15); color: var(--c-success);">
            <i class='bx bx-arrow-to-bottom'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($totalEntrees, 0, ',', ' ') ?> FCFA</div>
            <div class="stat-label">Total des Encaissements</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(230, 57, 70, 0.15); color: var(--c-danger);">
            <i class='bx bx-arrow-from-bottom'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($totalSorties, 0, ',', ' ') ?> FCFA</div>
            <div class="stat-label">Total des Décaissements</div>
        </div>
    </div>
    
    <!-- BENEFICE NET CARD -->
    <?php $isCaisseProfitable = (($netProfitMonth ?? 0) >= 0); ?>
    <div class="stat-card" style="border-left: 4px solid <?= $isCaisseProfitable ? '#059669' : '#DC2626' ?>; background: <?= $isCaisseProfitable ? '#F0FDF4' : '#FEF2F2' ?>;">
        <div class="stat-icon" style="background-color: <?= $isCaisseProfitable ? 'rgba(5, 150, 105, 0.15)' : 'rgba(220, 38, 38, 0.15)' ?>; color: <?= $isCaisseProfitable ? '#059669' : '#DC2626' ?>;">
            <i class='bx <?= $isCaisseProfitable ? 'bx-trophy' : 'bx-error-alt' ?>'></i>
        </div>
        <div class="stat-content" style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: baseline;">
                <div class="stat-value" style="color: <?= $isCaisseProfitable ? '#047857' : '#DC2626' ?>; font-size: 1.25rem;">
                    <?= ($isCaisseProfitable ? '+' : '') . number_format($netProfitMonth ?? 0, 0, ',', ' ') ?> FCFA
                </div>
                <span style="font-size: 0.75rem; font-weight: 700; padding: 2px 6px; border-radius: 4px; background: <?= $isCaisseProfitable ? '#DCFCE7' : '#FEE2E2' ?>; color: <?= $isCaisseProfitable ? '#166534' : '#991B1B' ?>;">
                    <?= $netMarginPctMonth ?? 0 ?>%
                </span>
            </div>
            <div class="stat-label" style="font-weight: 800; color: <?= $isCaisseProfitable ? '#065F46' : '#991B1B' ?>;">
                Bénéfice Net (Ce Mois)
            </div>
            <div style="margin-top: 5px;">
                <a href="<?= BASE_URL ?>/rentabilite" target="_blank" style="font-size: 0.78rem; font-weight: 700; color: #0284C7; display: inline-flex; align-items: center; gap: 4px; text-decoration: underline;">
                    <i class='bx bx-line-chart'></i> P&L & Rentabilité <i class='bx bx-link-external'></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ACCOUNTS SUMMARY -->
<div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); margin-bottom: 30px;">
    <?php foreach ($accounts as $acc): ?>
    <div class="card" style="margin-bottom: 0; border-top: 4px solid var(--c-amber);">
        <div class="card-body">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h3 style="margin: 0; font-size: 1.05rem;"><?= htmlspecialchars($acc['name']) ?></h3>
                <span style="padding: 2px 8px; background: var(--c-gray-200); border-radius: 10px; font-size: 0.75rem; font-weight: 600;"><?= htmlspecialchars($acc['payment_method_name'] ?: 'Compte') ?></span>
            </div>
            <div style="font-size: 1.4rem; font-weight: bold; color: var(--c-navy); margin-bottom: 8px;">
                <?= number_format($acc['current_balance'], 0, ',', ' ') ?> <span style="font-size: 0.85rem;">FCFA</span>
            </div>
            <div style="font-size: 0.8rem; color: var(--c-gray-600); display: flex; justify-content: space-between;">
                <span style="color: var(--c-success); font-weight: 600;">+<?= number_format($acc['total_in'], 0, ',', ' ') ?> FCFA</span>
                <span style="color: var(--c-danger); font-weight: 600;">-<?= number_format($acc['total_out'], 0, ',', ' ') ?> FCFA</span>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- SEARCH & FILTER TOOLBAR FOR CASH JOURNAL -->
<div class="card" style="margin-bottom: 20px; padding: 16px 20px; background: white;">
    <form action="<?= BASE_URL ?>/caisse" method="GET" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
        
        <!-- SEARCH -->
        <div style="flex: 2; min-width: 180px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Recherche par Réf / Motif</label>
            <div style="position: relative;">
                <input type="text" name="search" class="form-control" placeholder="Réf, motif, auteur..." value="<?= htmlspecialchars($filterSearch ?? '') ?>" style="padding-left: 32px; font-size: 0.88rem;">
                <i class='bx bx-search' style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 1.1rem;"></i>
            </div>
        </div>

        <!-- ACCOUNT -->
        <div style="flex: 1.5; min-width: 160px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Compte Trésorerie</label>
            <select name="account_id" class="form-control" style="font-size: 0.88rem;">
                <option value="">-- Tous les comptes --</option>
                <?php foreach ($accounts as $acc): ?>
                    <option value="<?= $acc['id'] ?>" <?= (($filterAccountId ?? '') == $acc['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($acc['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- TRANSACTION TYPE -->
        <div style="flex: 1.5; min-width: 160px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Type d'Opération</label>
            <select name="type" class="form-control" style="font-size: 0.88rem;">
                <option value="">-- Toutes les opérations --</option>
                <option value="Sale" <?= (($filterType ?? '') === 'Sale') ? 'selected' : '' ?>>🟢 Vente</option>
                <option value="ClientPayment" <?= (($filterType ?? '') === 'ClientPayment') ? 'selected' : '' ?>>🔵 Règlement Client</option>
                <option value="Deposit" <?= (($filterType ?? '') === 'Deposit') ? 'selected' : '' ?>>💵 Apport / Alimentation</option>
                <option value="Purchase" <?= (($filterType ?? '') === 'Purchase') ? 'selected' : '' ?>>🟡 Achat Fournisseur</option>
                <option value="Expense" <?= (($filterType ?? '') === 'Expense') ? 'selected' : '' ?>>🔴 Dépense</option>
                <option value="SupplierPayment" <?= (($filterType ?? '') === 'SupplierPayment') ? 'selected' : '' ?>>🟣 Paiement Fournisseur</option>
                <option value="Payroll" <?= (($filterType ?? '') === 'Payroll') ? 'selected' : '' ?>>💼 Paie Personnel</option>
                <option value="Transfer" <?= (($filterType ?? '') === 'Transfer') ? 'selected' : '' ?>>🔄 Transfert</option>
            </select>
        </div>

        <!-- DIRECTION -->
        <div style="flex: 1; min-width: 120px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Sens de Flux</label>
            <select name="direction" class="form-control" style="font-size: 0.88rem;">
                <option value="">-- Tous --</option>
                <option value="IN" <?= (($filterDirection ?? '') === 'IN') ? 'selected' : '' ?>>Entrées (+)</option>
                <option value="OUT" <?= (($filterDirection ?? '') === 'OUT') ? 'selected' : '' ?>>Sorties (-)</option>
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
            <?php if (!empty($filterSearch) || !empty($filterAccountId) || !empty($filterType) || !empty($filterDirection) || !empty($filterStartDate) || !empty($filterEndDate) || !empty($filterPeriod)): ?>
                <a href="<?= BASE_URL ?>/caisse" class="btn btn-primary" style="padding: 8px 12px; font-size: 0.88rem; background: #64748B;" title="Réinitialiser les filtres">
                    <i class='bx bx-reset'></i>
                </a>
            <?php endif; ?>
        </div>
    </form>

    <!-- QUICK PERIOD SHORTCUTS -->
    <div style="display: flex; gap: 8px; margin-top: 12px; padding-top: 10px; border-top: 1px solid #F1F5F9; align-items: center; flex-wrap: wrap;">
        <span style="font-size: 0.8rem; font-weight: 700; color: #64748B;">Raccourcis :</span>
        <a href="<?= BASE_URL ?>/caisse?period=today" class="btn <?= (($filterPeriod ?? '') === 'today') ? 'btn-accent' : 'btn-primary' ?>" style="padding: 3px 8px; font-size: 0.75rem; <?= (($filterPeriod ?? '') !== 'today') ? 'background: #F1F5F9; color: #334155;' : '' ?>">
            Aujourd'hui
        </a>
        <a href="<?= BASE_URL ?>/caisse?period=week" class="btn <?= (($filterPeriod ?? '') === 'week') ? 'btn-accent' : 'btn-primary' ?>" style="padding: 3px 8px; font-size: 0.75rem; <?= (($filterPeriod ?? '') !== 'week') ? 'background: #F1F5F9; color: #334155;' : '' ?>">
            Cette Semaine
        </a>
        <a href="<?= BASE_URL ?>/caisse?period=month" class="btn <?= (($filterPeriod ?? '') === 'month') ? 'btn-accent' : 'btn-primary' ?>" style="padding: 3px 8px; font-size: 0.75rem; <?= (($filterPeriod ?? '') !== 'month') ? 'background: #F1F5F9; color: #334155;' : '' ?>">
            Ce Mois
        </a>
        <a href="<?= BASE_URL ?>/caisse" class="btn btn-primary" style="padding: 3px 8px; font-size: 0.75rem; background: #F1F5F9; color: #334155;">
            Tous les mouvements
        </a>
    </div>
</div>

<!-- TRANSACTIONS HISTORY -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <span><i class='bx bx-history'></i> Journal des Mouvements de Caisse</span>
            <span style="font-size: 0.82rem; font-weight: 700; color: var(--c-navy); background: #F1F5F9; padding: 3px 8px; border-radius: 6px;">
                <?= count($transactions) ?> ligne(s)
            </span>
        </div>
        <?php if (\App\Core\Helper::isAdmin()): ?>
            <a href="<?= BASE_URL ?>/caisse/form" class="btn btn-accent"><i class='bx bx-plus-circle'></i> Alimentation / Opération de Caisse</a>
        <?php else: ?>
            <button type="button" class="btn" disabled style="background: var(--c-gray-400); color: #64748B; cursor: not-allowed; opacity: 0.65; border: none; font-size: 0.88rem; padding: 8px 16px; border-radius: 6px;" title="Action réservée à l'Administrateur">
                <i class='bx bx-lock-alt'></i> Alimentation / Opération de Caisse
            </button>
        <?php endif; ?>
    </div>

    <!-- FILTERED TOTALS SUMMARY BAR -->
    <div style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; font-size: 0.85rem;">
        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <span>Total Entrées : <strong style="color: var(--c-success);">+<?= number_format($filteredIn ?? 0, 0, ',', ' ') ?> FCFA</strong></span>
            <span>Total Sorties : <strong style="color: var(--c-danger);">-<?= number_format($filteredOut ?? 0, 0, ',', ' ') ?> FCFA</strong></span>
            <span>Flux Net Filtré : <strong style="color: <?= (($filteredNet ?? 0) >= 0) ? 'var(--c-success)' : 'var(--c-danger)' ?>;"><?= (($filteredNet ?? 0) >= 0 ? '+' : '') . number_format($filteredNet ?? 0, 0, ',', ' ') ?> FCFA</strong></span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date & Heure</th>
                    <th>Type</th>
                    <th>Pièce / Réf</th>
                    <th>Compte</th>
                    <th>Description</th>
                    <th>Entrée (+)</th>
                    <th>Sortie (-)</th>
                    <th>Auteur</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                <tr><td colspan="8" style="text-align: center; padding: 25px;">Aucun mouvement de caisse enregistré.</td></tr>
                <?php else: ?>
                    <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($t['transaction_date'])) ?></td>
                        <td>
                            <?php 
                            $badgeStyle = "padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 700;";
                            if ($t['transaction_type'] === 'Sale') {
                                echo "<span style='{$badgeStyle} background: #D1FAE5; color: #065F46;'>🟢 Vente</span>";
                            } elseif ($t['transaction_type'] === 'Purchase') {
                                echo "<span style='{$badgeStyle} background: #FEF3C7; color: #92400E;'>🟡 Achat</span>";
                            } elseif ($t['transaction_type'] === 'Expense') {
                                echo "<span style='{$badgeStyle} background: #FEE2E2; color: #991B1B;'>🔴 Dépense</span>";
                            } elseif (in_array($t['transaction_type'], ['ClientPayment', 'Client Payment'])) {
                                echo "<span style='{$badgeStyle} background: #E0E7FF; color: #3730A3;'>🔵 Règlement Client</span>";
                            } elseif (in_array($t['transaction_type'], ['SupplierPayment', 'Supplier Payment'])) {
                                echo "<span style='{$badgeStyle} background: #FCE7F3; color: #9D174D;'>🟣 Paiement Fournisseur</span>";
                            } elseif ($t['transaction_type'] === 'Deposit') {
                                echo "<span style='{$badgeStyle} background: #DCFCE7; color: #166534;'>💵 Apport / Alimentation</span>";
                            } elseif ($t['transaction_type'] === 'Transfer') {
                                echo "<span style='{$badgeStyle} background: #F3E8FF; color: #6B21A8;'>🔄 Transfert</span>";
                            } elseif ($t['transaction_type'] === 'Payroll') {
                                echo "<span style='{$badgeStyle} background: #FEF9C3; color: #854D0E;'>💼 Paie Personnel</span>";
                            } elseif ($t['transaction_type'] === 'Withdrawal') {
                                echo "<span style='{$badgeStyle} background: #FEE2E2; color: #991B1B;'>📤 Retrait</span>";
                            } else {
                                echo "<span style='{$badgeStyle} background: #E2E8F0; color: #334155;'>{$t['transaction_type']}</span>";
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($t['transaction_type'] === 'Sale' && !empty($t['source_id'])): ?>
                                <a href="<?= BASE_URL ?>/ventes/invoice/<?= htmlspecialchars($t['source_id']) ?>" style="font-weight: 700; color: #065F46; text-decoration: none;" title="Voir la Facture Vente">
                                    <i class='bx bx-receipt'></i> <?= htmlspecialchars($t['source_id']) ?>
                                </a>
                            <?php elseif ($t['transaction_type'] === 'Purchase' && !empty($t['source_id'])): ?>
                                <a href="<?= BASE_URL ?>/achats/order/<?= htmlspecialchars($t['source_id']) ?>" style="font-weight: 700; color: #92400E; text-decoration: none;" title="Voir le Bon d'Approvisionnement">
                                    <i class='bx bx-file-blank'></i> <?= htmlspecialchars($t['source_id']) ?>
                                </a>
                            <?php elseif ($t['transaction_type'] === 'Expense' && !empty($t['source_id'])): ?>
                                <a href="<?= BASE_URL ?>/depenses/receipt/<?= htmlspecialchars($t['source_id']) ?>" style="font-weight: 700; color: #991B1B; text-decoration: none;" title="Voir le Bon de Décaissement">
                                    <i class='bx bx-money-withdraw'></i> <?= htmlspecialchars($t['source_id']) ?>
                                </a>
                            <?php elseif (in_array($t['transaction_type'], ['ClientPayment', 'Client Payment']) && !empty($t['source_id'])): ?>
                                <a href="<?= BASE_URL ?>/reglements/receipt/<?= htmlspecialchars($t['source_id']) ?>" style="font-weight: 700; color: #3730A3; text-decoration: none;" title="Imprimer le Reçu d'Encaissement">
                                    <i class='bx bx-check-shield'></i> <?= htmlspecialchars($t['source_id']) ?>
                                </a>
                            <?php elseif (in_array($t['transaction_type'], ['SupplierPayment', 'Supplier Payment']) && !empty($t['source_id'])): ?>
                                <a href="<?= BASE_URL ?>/paiementsFournisseurs/receipt/<?= htmlspecialchars($t['source_id']) ?>" style="font-weight: 700; color: #9D174D; text-decoration: none;" title="Imprimer le Bon de Décaissement">
                                    <i class='bx bx-wallet'></i> <?= htmlspecialchars($t['source_id']) ?>
                                </a>
                            <?php elseif ($t['transaction_type'] === 'Payroll' && !empty($t['source_id'])): ?>
                                <a href="<?= BASE_URL ?>/payroll/receipt/<?= htmlspecialchars($t['source_id']) ?>" style="font-weight: 700; color: #854D0E; text-decoration: none;" title="Voir le Bulletin de Paie">
                                    <i class='bx bx-user-check'></i> <?= htmlspecialchars($t['source_id']) ?>
                                </a>
                            <?php else: ?>
                                <span style="color: #64748B; font-weight: 600;"><?= htmlspecialchars($t['source_id'] ?: '-') ?></span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= htmlspecialchars($t['account_name'] ?? 'Caisse') ?></strong></td>
                        <td><?= htmlspecialchars($t['description']) ?></td>
                        <td style="color: var(--c-success); font-weight: 700;">
                            <?= floatval($t['amount_in']) > 0 ? '+' . number_format($t['amount_in'], 0, ',', ' ') . ' FCFA' : '-' ?>
                        </td>
                        <td style="color: var(--c-danger); font-weight: 700;">
                            <?= floatval($t['amount_out']) > 0 ? '-' . number_format($t['amount_out'], 0, ',', ' ') . ' FCFA' : '-' ?>
                        </td>
                        <td><?= htmlspecialchars($t['username'] ?: 'Admin') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
