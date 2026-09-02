<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div class="page-title" style="margin-bottom: 0;">
        <i class='bx bx-user'></i>
        <h1>Répertoire des Clients & Suivi des Dettes</h1>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="<?= BASE_URL ?>/reglements" class="btn btn-primary" style="background-color: var(--c-navy); display: flex; align-items: center; gap: 6px; font-weight: 600;">
            <i class='bx bx-check-double'></i> Règlements Clients
        </a>
        <a href="<?= BASE_URL ?>/ventes" class="btn btn-primary" style="background-color: var(--c-navy); display: flex; align-items: center; gap: 6px; font-weight: 600;">
            <i class='bx bx-cart'></i> Journal des Ventes
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

<!-- KPI EXECUTIVE SUMMARY CARDS -->
<div class="dashboard-grid" style="margin-bottom: 25px;">
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(13, 27, 42, 0.1); color: var(--c-navy);">
            <i class='bx bx-group'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $countTotal ?></div>
            <div class="stat-label">Total Clients Enregistrés</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(220, 38, 38, 0.12); color: #DC2626;">
            <i class='bx bx-error-circle'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="color: #DC2626;"><?= $countDebt ?></div>
            <div class="stat-label">Clients avec Dettes Actives</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(245, 158, 11, 0.15); color: #D97706;">
            <i class='bx bx-money-withdraw'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="color: #D97706;"><?= number_format($totalGlobalDebt, 0, ',', ' ') ?> FCFA</div>
            <div class="stat-label">Total Créances Clients Dues</div>
        </div>
    </div>
</div>

<!-- SEARCH & FILTER TOOLBAR -->
<div class="card" style="margin-bottom: 20px; padding: 16px 20px; background: white;">
    
    <!-- 1-CLICK DEBT STATUS TABS -->
    <div style="display: flex; gap: 10px; margin-bottom: 15px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9; align-items: center; flex-wrap: wrap;">
        <span style="font-size: 0.82rem; font-weight: 700; color: #64748B; margin-right: 5px;">Statut Dette :</span>
        
        <a href="<?= BASE_URL ?>/clients?debt_status=all<?= !empty($filterSearch) ? '&search='.urlencode($filterSearch) : '' ?><?= !empty($filterClientTypeId) ? '&client_type_id='.$filterClientTypeId : '' ?><?= !empty($filterSort) ? '&sort='.$filterSort : '' ?>" 
           class="btn <?= ($filterDebtStatus === 'all' || empty($filterDebtStatus)) ? 'btn-accent' : 'btn-primary' ?>" 
           style="padding: 5px 12px; font-size: 0.82rem; <?= ($filterDebtStatus !== 'all' && !empty($filterDebtStatus)) ? 'background: #F1F5F9; color: #334155;' : '' ?>">
            <i class='bx bx-list-ul'></i> Tous les Clients (<?= $countTotal ?>)
        </a>

        <a href="<?= BASE_URL ?>/clients?debt_status=debt<?= !empty($filterSearch) ? '&search='.urlencode($filterSearch) : '' ?><?= !empty($filterClientTypeId) ? '&client_type_id='.$filterClientTypeId : '' ?><?= !empty($filterSort) ? '&sort='.$filterSort : '' ?>" 
           class="btn <?= ($filterDebtStatus === 'debt') ? 'btn-accent' : 'btn-primary' ?>" 
           style="padding: 5px 12px; font-size: 0.82rem; <?= ($filterDebtStatus !== 'debt') ? 'background: #FEF2F2; color: #DC2626; border: 1px solid #FEE2E2;' : '' ?>">
            🔴 Endettés (<?= $countDebt ?>)
        </a>

        <a href="<?= BASE_URL ?>/clients?debt_status=no_debt<?= !empty($filterSearch) ? '&search='.urlencode($filterSearch) : '' ?><?= !empty($filterClientTypeId) ? '&client_type_id='.$filterClientTypeId : '' ?><?= !empty($filterSort) ? '&sort='.$filterSort : '' ?>" 
           class="btn <?= ($filterDebtStatus === 'no_debt') ? 'btn-accent' : 'btn-primary' ?>" 
           style="padding: 5px 12px; font-size: 0.82rem; <?= ($filterDebtStatus !== 'no_debt') ? 'background: #F0FDF4; color: #16A34A; border: 1px solid #DCFCE7;' : '' ?>">
            🟢 Soldés / Sans Dette (<?= $countNoDebt ?>)
        </a>

        <?php if ($countLimitExceeded > 0): ?>
        <a href="<?= BASE_URL ?>/clients?debt_status=limit_exceeded<?= !empty($filterSearch) ? '&search='.urlencode($filterSearch) : '' ?><?= !empty($filterClientTypeId) ? '&client_type_id='.$filterClientTypeId : '' ?><?= !empty($filterSort) ? '&sort='.$filterSort : '' ?>" 
           class="btn <?= ($filterDebtStatus === 'limit_exceeded') ? 'btn-accent' : 'btn-primary' ?>" 
           style="padding: 5px 12px; font-size: 0.82rem; <?= ($filterDebtStatus !== 'limit_exceeded') ? 'background: #FFFBEB; color: #B45309; border: 1px solid #FEF3C7;' : '' ?>">
            ⚠️ Plafond Dépassé (<?= $countLimitExceeded ?>)
        </a>
        <?php endif; ?>
    </div>

    <!-- MULTI-CRITERIA FILTER FORM -->
    <form action="<?= BASE_URL ?>/clients" method="GET" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
        <input type="hidden" name="debt_status" value="<?= htmlspecialchars($filterDebtStatus ?? 'all') ?>">

        <!-- SEARCH -->
        <div style="flex: 2; min-width: 200px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Recherche par Nom, Téléphone, Adresse</label>
            <div style="position: relative;">
                <input type="text" name="search" class="form-control" placeholder="Nom, contact, quartier..." value="<?= htmlspecialchars($filterSearch ?? '') ?>" style="padding-left: 32px; font-size: 0.88rem;">
                <i class='bx bx-search' style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 1.1rem;"></i>
            </div>
        </div>

        <!-- TYPE DE CLIENT -->
        <div style="flex: 1.5; min-width: 170px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Catégorie / Type Client</label>
            <select name="client_type_id" class="form-control" style="font-size: 0.88rem;">
                <option value="">-- Tous les types --</option>
                <?php foreach ($types as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= (($filterClientTypeId ?? '') == $t['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- TRI / ORDRE -->
        <div style="flex: 1.3; min-width: 160px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Trier par</label>
            <select name="sort" class="form-control" style="font-size: 0.88rem;">
                <option value="debt_desc" <?= (($filterSort ?? '') === 'debt_desc') ? 'selected' : '' ?>>Solde Dû Décroissant</option>
                <option value="name_asc" <?= (($filterSort ?? '') === 'name_asc') ? 'selected' : '' ?>>Nom Client (A - Z)</option>
                <option value="limit_desc" <?= (($filterSort ?? '') === 'limit_desc') ? 'selected' : '' ?>>Plafond Crédit</option>
            </select>
        </div>

        <!-- BUTTONS -->
        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-accent" style="padding: 8px 16px; font-size: 0.88rem;">
                <i class='bx bx-filter-alt'></i> Filtrer
            </button>
            <?php if (!empty($filterSearch) || !empty($filterClientTypeId) || ($filterDebtStatus !== 'all' && !empty($filterDebtStatus)) || ($filterSort !== 'debt_desc' && !empty($filterSort))): ?>
                <a href="<?= BASE_URL ?>/clients" class="btn btn-primary" style="padding: 8px 12px; font-size: 0.88rem; background: #64748B;" title="Réinitialiser les filtres">
                    <i class='bx bx-reset'></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span><i class='bx bx-list-ul'></i> Répertoire des Clients & Soldes Dûs</span>
            <span style="font-size: 0.82rem; font-weight: 700; color: var(--c-navy); background: #F1F5F9; padding: 3px 8px; border-radius: 6px;">
                <?= count($clients) ?> client(s) affiché(s)
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
            <span style="font-size: 0.85rem; color: #64748B;">
                Total Dettes Sélection : <strong style="color: #DC2626; font-size: 0.95rem;"><?= number_format($totalFilteredDebt, 0, ',', ' ') ?> FCFA</strong>
            </span>
            <a href="<?= BASE_URL ?>/clients/form" class="btn btn-accent"><i class='bx bx-plus'></i> Nouveau Client</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Nom Client</th>
                    <th>Téléphone</th>
                    <th>Adresse</th>
                    <th>Type</th>
                    <th>Plafond Crédit</th>
                    <th>Dette Restante (Solde Dû)</th>
                    <th style="text-align: center;">Dette Emballages</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clients)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 30px; color: var(--c-gray-600);">
                        <i class='bx bx-search' style="font-size: 2rem; display: block; margin-bottom: 8px; color: #94A3B8;"></i>
                        Aucun client ne correspond à vos critères de recherche.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($clients as $c): ?>
                    <?php 
                        $debt = floatval($c['solde_du']);
                        $maxCredit = floatval($c['max_credit']);
                        $cratesDue = intval($c['total_crates_due'] ?? 0);
                        $bottlesDue = intval($c['total_bottles_due'] ?? 0);
                        $debtBadge = 'background: #F1F5F9; color: #475569; font-weight: 600;'; // 0 debt
                        if ($debt > 0) {
                            $debtBadge = ($maxCredit > 0 && $debt >= $maxCredit) 
                                ? 'background: #FEE2E2; color: #B91C1C; font-weight: 700; border: 1px solid #FECACA;' 
                                : 'background: #FEF3C7; color: #B45309; font-weight: 700; border: 1px solid #FDE68A;';
                        } elseif ($debt < 0) {
                            $debtBadge = 'background: #ECFDF5; color: #047857; font-weight: 800; border: 1px solid #A7F3D0;';
                        }
                    ?>
                    <tr>
                        <td>
                            <strong style="color: var(--c-navy); font-size: 0.95rem;"><?= htmlspecialchars($c['name']) ?></strong>
                        </td>
                        <td><?= htmlspecialchars($c['phone'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($c['address'] ?: '-') ?></td>
                        <td>
                            <span style="padding: 3px 8px; background: #F1F5F9; color: #334155; border-radius: 6px; font-size: 0.8rem; font-weight: 600;">
                                <?= htmlspecialchars($c['type_name']) ?>
                            </span>
                        </td>
                        <td style="color: #64748B; font-weight: 500;">
                            <?= number_format($c['max_credit'], 0, ',', ' ') ?> FCFA
                        </td>
                        <td>
                            <span style="padding: 4px 10px; border-radius: 8px; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 4px; <?= $debtBadge ?>">
                                <?php if ($debt > 0): ?>
                                    <?php if ($maxCredit > 0 && $debt >= $maxCredit): ?>
                                        <i class='bx bx-error' title="Plafond de crédit atteint ou dépassé !"></i>
                                    <?php endif; ?>
                                    <?= number_format($debt, 0, ',', ' ') ?> FCFA
                                <?php elseif ($debt < 0): ?>
                                    <i class='bx bx-gift' title="Avoir Client / Monnaie en attente"></i>
                                    Avoir: +<?= number_format(abs($debt), 0, ',', ' ') ?> FCFA
                                <?php else: ?>
                                    0 FCFA
                                <?php endif; ?>
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <?php if (!empty($c['packaging_debts_detail'])): ?>
                                <a href="<?= BASE_URL ?>/emballages" style="padding: 4px 8px; background: #FEF2F2; color: #DC2626; border-radius: 6px; font-size: 0.78rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; border: 1px solid #FECACA; line-height: 1.3;" title="Cliquer pour voir / solder dans le Parc d'Emballages">
                                    <i class='bx bx-archive'></i>
                                    <?= htmlspecialchars($c['packaging_debts_detail']) ?>
                                </a>
                            <?php else: ?>
                                <span style="color: #10B981; font-weight: 700; font-size: 0.85rem;">0 🟢</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <div style="display: inline-flex; gap: 6px;">
                                <?php if ($debt > 0): ?>
                                    <a href="<?= BASE_URL ?>/reglements/form/<?= $c['slug'] ?: $c['id'] ?>" class="btn btn-primary" style="padding: 5px 9px; font-size: 0.82rem; background-color: var(--c-success); display: inline-flex; align-items: center; gap: 4px;" title="Encaisser paiement">
                                        <i class='bx bx-money'></i> Encaisser
                                    </a>
                                <?php else: ?>
                                    <a href="<?= BASE_URL ?>/reglements/form/<?= $c['slug'] ?: $c['id'] ?>?type=avoir" class="btn btn-secondary" style="padding: 5px 8px; font-size: 0.8rem; color: #059669; border-color: #A7F3D0; background: #ECFDF5; display: inline-flex; align-items: center; gap: 3px;" title="+ Enregistrer un Avoir / Monnaie non rendue">
                                        <i class='bx bx-wallet-alt'></i> + Avoir
                                    </a>
                                <?php endif; ?>
                                <a href="<?= BASE_URL ?>/clients/form/<?= $c['slug'] ?: $c['id'] ?>" class="btn btn-primary" style="padding: 5px 8px; font-size: 0.82rem;" title="Modifier les informations">
                                    <i class='bx bx-edit'></i>
                                </a>
                                <a href="<?= BASE_URL ?>/clients/delete/<?= $c['slug'] ?: $c['id'] ?>" class="btn btn-primary" style="padding: 5px 8px; font-size: 0.82rem; background-color: var(--c-danger);" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?')" title="Supprimer">
                                    <i class='bx bx-trash'></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
