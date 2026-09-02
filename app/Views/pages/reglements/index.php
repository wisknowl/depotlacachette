<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div class="page-title" style="margin-bottom: 0;">
        <i class='bx bx-check-double'></i>
        <h1>Règlements & Encaissements Clients</h1>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="<?= BASE_URL ?>/reglements/form" class="btn btn-accent" style="display: flex; align-items: center; gap: 6px; font-weight: 600;">
            <i class='bx bx-plus-circle'></i> Encaisser Règlement / Avoir
        </a>
        <a href="<?= BASE_URL ?>/caisse" class="btn btn-primary" style="background-color: var(--c-navy); display: flex; align-items: center; gap: 6px; font-weight: 600;">
            <i class='bx bx-wallet'></i> Vérifier Journal Caisse
        </a>
    </div>
</div>

<div class="dashboard-grid" style="margin-bottom: 25px;">
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(16, 185, 129, 0.15); color: var(--c-success);">
            <i class='bx bx-check-shield'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($totalReglements, 0, ',', ' ') ?> FCFA</div>
            <div class="stat-label">Total Dettes Clients Encaissées</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(13, 27, 42, 0.1); color: var(--c-navy);">
            <i class='bx bx-receipt'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= count($reglements) ?></div>
            <div class="stat-label">Nombre de Règlements</div>
        </div>
    </div>
</div>

<!-- SEARCH & FILTER TOOLBAR -->
<div class="card" style="margin-bottom: 20px; padding: 16px 20px; background: white;">
    <form action="<?= BASE_URL ?>/reglements" method="GET" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
        
        <!-- SEARCH -->
        <div style="flex: 2; min-width: 180px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Recherche par N° / Réf</label>
            <div style="position: relative;">
                <input type="text" name="search" class="form-control" placeholder="N° reçu, réf, notes..." value="<?= htmlspecialchars($filterSearch ?? '') ?>" style="padding-left: 32px; font-size: 0.88rem;">
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
            <?php if (!empty($filterSearch) || !empty($filterClientId) || !empty($filterCashAccountId) || !empty($filterStartDate) || !empty($filterEndDate) || !empty($filterPeriod)): ?>
                <a href="<?= BASE_URL ?>/reglements" class="btn btn-primary" style="padding: 8px 12px; font-size: 0.88rem; background: #64748B;" title="Réinitialiser les filtres">
                    <i class='bx bx-reset'></i>
                </a>
            <?php endif; ?>
        </div>
    </form>

    <!-- QUICK PERIOD SHORTCUTS -->
    <div style="display: flex; gap: 8px; margin-top: 12px; padding-top: 10px; border-top: 1px solid #F1F5F9; align-items: center; flex-wrap: wrap;">
        <span style="font-size: 0.8rem; font-weight: 700; color: #64748B;">Période :</span>
        <a href="<?= BASE_URL ?>/reglements?period=today" class="btn <?= (($filterPeriod ?? '') === 'today') ? 'btn-accent' : 'btn-primary' ?>" style="padding: 3px 8px; font-size: 0.75rem; <?= (($filterPeriod ?? '') !== 'today') ? 'background: #F1F5F9; color: #334155;' : '' ?>">
            Aujourd'hui
        </a>
        <a href="<?= BASE_URL ?>/reglements?period=week" class="btn <?= (($filterPeriod ?? '') === 'week') ? 'btn-accent' : 'btn-primary' ?>" style="padding: 3px 8px; font-size: 0.75rem; <?= (($filterPeriod ?? '') !== 'week') ? 'background: #F1F5F9; color: #334155;' : '' ?>">
            Cette Semaine
        </a>
        <a href="<?= BASE_URL ?>/reglements?period=month" class="btn <?= (($filterPeriod ?? '') === 'month') ? 'btn-accent' : 'btn-primary' ?>" style="padding: 3px 8px; font-size: 0.75rem; <?= (($filterPeriod ?? '') !== 'month') ? 'background: #F1F5F9; color: #334155;' : '' ?>">
            Ce Mois
        </a>
        <a href="<?= BASE_URL ?>/reglements" class="btn btn-primary" style="padding: 3px 8px; font-size: 0.75rem; background: #F1F5F9; color: #334155;">
            Tous les règlements
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span><i class='bx bx-list-ul'></i> Historique des Règlements Encaissés</span>
            <span style="font-size: 0.82rem; font-weight: 700; color: var(--c-navy); background: #F1F5F9; padding: 3px 8px; border-radius: 6px;">
                <?= count($reglements) ?> règlement(s)
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
            <span style="font-size: 0.85rem; color: #64748B;">
                Total Sélection : <strong style="color: var(--c-success); font-size: 0.95rem;"><?= number_format($totalReglements, 0, ',', ' ') ?> FCFA</strong>
            </span>
            <a href="<?= BASE_URL ?>/reglements/form" class="btn btn-accent"><i class='bx bx-plus'></i> Encaisser un Règlement</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>N° Règlement</th>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Montant Encaissé</th>
                    <th>Mode de Paiement</th>
                    <th>Compte Récepteur</th>
                    <th>Notes / Réf</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reglements)): ?>
                <tr><td colspan="8" style="text-align: center; padding: 25px;">Aucun règlement client enregistré.</td></tr>
                <?php else: ?>
                    <?php foreach ($reglements as $r): ?>
                    <tr>
                        <td>
                            <a href="<?= BASE_URL ?>/reglements/receipt/<?= htmlspecialchars($r['id']) ?>" style="font-weight: 700; color: #0284C7; text-decoration: none;">
                                <i class='bx bx-receipt'></i> <?= htmlspecialchars($r['id']) ?>
                            </a>
                        </td>
                        <td><?= date('d/m/Y', strtotime($r['payment_date'])) ?></td>
                        <td><strong><?= htmlspecialchars($r['client_name']) ?></strong></td>
                        <td style="color: var(--c-success); font-weight: bold; font-size: 1.05rem;">
                            +<?= number_format($r['amount'], 0, ',', ' ') ?> FCFA
                        </td>
                        <td><span style="padding: 2px 8px; background: var(--c-gray-200); border-radius: 10px; font-size: 0.8rem;"><?= htmlspecialchars($r['payment_method_name']) ?></span></td>
                        <td><?= htmlspecialchars($r['cash_account_name']) ?></td>
                        <td><?= htmlspecialchars($r['reference'] ?: ($r['notes'] ?: '-')) ?></td>
                        <td style="display: flex; gap: 6px;">
                            <a href="<?= BASE_URL ?>/reglements/receipt/<?= $r['id'] ?>" class="btn btn-primary" style="padding: 4px 8px; font-size: 0.8rem; background-color: var(--c-navy);" title="Imprimer le Reçu">
                                <i class='bx bx-printer'></i>
                            </a>
                            <?php if (\App\Core\Helper::isAdmin()): ?>
                                <a href="<?= BASE_URL ?>/reglements/delete/<?= $r['id'] ?>" class="btn btn-primary" style="padding: 4px 8px; font-size: 0.8rem; background-color: var(--c-danger);" title="Annuler le règlement (Admin)" onclick="return confirm('Voulez-vous annuler ce règlement ? Cela restaurera la dette du client et déduira le montant de la caisse.')">
                                    <i class='bx bx-trash'></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
