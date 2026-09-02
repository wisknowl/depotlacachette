<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div class="page-title" style="margin-bottom: 0;">
        <i class='bx bx-money-withdraw'></i>
        <h1>Dépenses & Charges</h1>
    </div>
    <div>
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

<div class="dashboard-grid" style="margin-bottom: 25px;">
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(230, 57, 70, 0.1); color: var(--c-danger);">
            <i class='bx bx-trending-down'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($totalAmount, 0, ',', ' ') ?> FCFA</div>
            <div class="stat-label">Total Dépenses Enregistrées</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(13, 27, 42, 0.1); color: var(--c-navy);">
            <i class='bx bx-receipt'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= count($depenses) ?></div>
            <div class="stat-label">Nombre d'Opérations</div>
        </div>
    </div>
</div>

<!-- SEARCH & FILTER TOOLBAR -->
<div class="card" style="margin-bottom: 20px; padding: 16px 20px; background: white;">
    <form action="<?= BASE_URL ?>/depenses" method="GET" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
        
        <!-- SEARCH -->
        <div style="flex: 2; min-width: 180px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Recherche Motif / Bénéficiaire</label>
            <div style="position: relative;">
                <input type="text" name="search" class="form-control" placeholder="Motif, bénéficiaire, réf..." value="<?= htmlspecialchars($filterSearch ?? '') ?>" style="padding-left: 32px; font-size: 0.88rem;">
                <i class='bx bx-search' style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 1.1rem;"></i>
            </div>
        </div>

        <!-- CATEGORY -->
        <div style="flex: 1.5; min-width: 160px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Catégorie</label>
            <select name="category_id" class="form-control" style="font-size: 0.88rem;">
                <option value="">-- Toutes les catégories --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= (($filterCategoryId ?? '') == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- CASH ACCOUNT -->
        <div style="flex: 1.2; min-width: 150px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Compte Décaissé</label>
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
            <?php if (!empty($filterSearch) || !empty($filterCategoryId) || !empty($filterCashAccountId) || !empty($filterStartDate) || !empty($filterEndDate) || !empty($filterPeriod)): ?>
                <a href="<?= BASE_URL ?>/depenses" class="btn btn-primary" style="padding: 8px 12px; font-size: 0.88rem; background: #64748B;" title="Réinitialiser les filtres">
                    <i class='bx bx-reset'></i>
                </a>
            <?php endif; ?>
        </div>
    </form>

    <!-- QUICK PERIOD SHORTCUTS -->
    <div style="display: flex; gap: 8px; margin-top: 12px; padding-top: 10px; border-top: 1px solid #F1F5F9; align-items: center; flex-wrap: wrap;">
        <span style="font-size: 0.8rem; font-weight: 700; color: #64748B;">Période :</span>
        <a href="<?= BASE_URL ?>/depenses?period=today" class="btn <?= (($filterPeriod ?? '') === 'today') ? 'btn-accent' : 'btn-primary' ?>" style="padding: 3px 8px; font-size: 0.75rem; <?= (($filterPeriod ?? '') !== 'today') ? 'background: #F1F5F9; color: #334155;' : '' ?>">
            Aujourd'hui
        </a>
        <a href="<?= BASE_URL ?>/depenses?period=week" class="btn <?= (($filterPeriod ?? '') === 'week') ? 'btn-accent' : 'btn-primary' ?>" style="padding: 3px 8px; font-size: 0.75rem; <?= (($filterPeriod ?? '') !== 'week') ? 'background: #F1F5F9; color: #334155;' : '' ?>">
            Cette Semaine
        </a>
        <a href="<?= BASE_URL ?>/depenses?period=month" class="btn <?= (($filterPeriod ?? '') === 'month') ? 'btn-accent' : 'btn-primary' ?>" style="padding: 3px 8px; font-size: 0.75rem; <?= (($filterPeriod ?? '') !== 'month') ? 'background: #F1F5F9; color: #334155;' : '' ?>">
            Ce Mois
        </a>
        <a href="<?= BASE_URL ?>/depenses" class="btn btn-primary" style="padding: 3px 8px; font-size: 0.75rem; background: #F1F5F9; color: #334155;">
            Toutes les dépenses
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span><i class='bx bx-list-ul'></i> Historique des Dépenses & Décaissements</span>
            <span style="font-size: 0.82rem; font-weight: 700; color: var(--c-navy); background: #F1F5F9; padding: 3px 8px; border-radius: 6px;">
                <?= count($depenses) ?> opération(s)
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
            <span style="font-size: 0.85rem; color: #64748B;">
                Total Sélection : <strong style="color: var(--c-danger); font-size: 0.95rem;"><?= number_format($totalAmount, 0, ',', ' ') ?> FCFA</strong>
            </span>
            <a href="<?= BASE_URL ?>/depenses/form" class="btn btn-accent"><i class='bx bx-plus-circle'></i> Nouvelle Dépense</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Réf / ID</th>
                    <th>Date</th>
                    <th>Catégorie</th>
                    <th>Bénéficiaire</th>
                    <th>Description / Motif</th>
                    <th>Compte Décaissé</th>
                    <th>Montant</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($depenses)): ?>
                <tr><td colspan="8" style="text-align: center; padding: 25px; color: var(--c-gray-600);">Aucune dépense enregistrée.</td></tr>
                <?php else: ?>
                    <?php foreach ($depenses as $d): ?>
                    <tr>
                        <td>
                            <a href="<?= BASE_URL ?>/depenses/receipt/<?= htmlspecialchars($d['id']) ?>" style="font-weight: 800; color: var(--c-navy); text-decoration: none;" title="Imprimer le Bon de Décaissement">
                                <i class='bx bx-file'></i> <?= htmlspecialchars($d['id']) ?>
                            </a>
                        </td>
                        <td><?= date('d/m/Y', strtotime($d['expense_date'])) ?></td>
                        <td><span style="padding: 3px 10px; background: var(--c-gray-200); border-radius: 12px; font-size: 0.8rem; font-weight: 600;"><?= htmlspecialchars($d['category_name']) ?></span></td>
                        <td><?= htmlspecialchars($d['beneficiary'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($d['description'] ?: '-') ?></td>
                        <td>
                            <span style="padding: 3px 8px; background: #F1F5F9; color: var(--c-navy); border-radius: 8px; font-size: 0.8rem; font-weight: 700;">
                                <?= htmlspecialchars($d['cash_account_name'] ?: ($d['payment_method'] ?: 'Caisse')) ?>
                            </span>
                        </td>
                        <td style="color: var(--c-danger); font-weight: 800; font-size: 1.05rem;">-<?= number_format($d['amount'], 0, ',', ' ') ?> FCFA</td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 6px; justify-content: center;">
                                <a href="<?= BASE_URL ?>/depenses/receipt/<?= $d['id'] ?>" class="btn btn-primary" style="padding: 4px 8px; font-size: 0.8rem; background-color: var(--c-navy);" title="Imprimer le Bon de Décaissement">
                                    <i class='bx bx-printer'></i>
                                </a>
                                <?php if (\App\Core\Helper::isAdmin()): ?>
                                    <a href="<?= BASE_URL ?>/depenses/delete/<?= $d['id'] ?>" class="btn btn-primary" style="padding: 4px 8px; font-size: 0.8rem; background-color: var(--c-danger);" onclick="return confirm('Supprimer cette dépense ?\n\nCela réintégrera le montant de <?= number_format($d['amount'], 0, ',', ' ') ?> FCFA dans la caisse.')" title="Supprimer la dépense (Admin)">
                                        <i class='bx bx-trash'></i>
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