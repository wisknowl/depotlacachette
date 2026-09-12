<div class="page-title">
    <i class='bx bx-user-check'></i>
    <h1>Paie du Personnel & Avances sur Salaire</h1>
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

<!-- MONTHLY STATS -->
<div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 25px;">
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(46, 139, 87, 0.15); color: var(--c-success);">
            <i class='bx bx-money'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($stats['total_salaires'], 0, ',', ' ') ?> FCFA</div>
            <div class="stat-label">Salaires Payés (Mois en cours)</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(244, 164, 35, 0.15); color: var(--c-amber);">
            <i class='bx bx-time-five'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($stats['total_avances'], 0, ',', ' ') ?> FCFA</div>
            <div class="stat-label">Avances sur Salaire Accordées</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(2, 132, 199, 0.15); color: #0284C7;">
            <i class='bx bx-award'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($stats['total_primes'], 0, ',', ' ') ?> FCFA</div>
            <div class="stat-label">Primes & Gratifications</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(13, 27, 42, 0.1); color: var(--c-navy);">
            <i class='bx bx-group'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= count($employees) ?></div>
            <div class="stat-label">Employés Actifs au Dépôt</div>
        </div>
    </div>

    <?php if (!empty($total_employee_debt) && $total_employee_debt > 0): ?>
    <div class="stat-card" style="border-left: 4px solid var(--c-danger); background: #FFFBFB;">
        <div class="stat-icon" style="background-color: rgba(220, 38, 38, 0.15); color: var(--c-danger);">
            <i class='bx bx-error-alt'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="color: var(--c-danger);"><?= number_format($total_employee_debt, 0, ',', ' ') ?> FCFA</div>
            <div class="stat-label">
                <a href="<?= BASE_URL ?>/payroll/ledger" style="color: #991B1B; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;" title="Consulter les manquants de tournée et dettes en cours">
                    Manquants en Cours <i class='bx bx-right-arrow-alt'></i>
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- PAYROLL JOURNAL TABLE -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <span><i class='bx bx-history'></i> Journal des Décaissements de Paie</span>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="<?= BASE_URL ?>/payroll/ledger" class="btn btn-primary" style="background-color: var(--c-navy); padding: 8px 14px;">
                <i class='bx bx-book-content'></i> Grand-Livre Collaborateurs
            </a>
            <a href="<?= BASE_URL ?>/payroll/employees" class="btn btn-primary" style="background-color: var(--c-navy); padding: 8px 14px;">
                <i class='bx bx-user'></i> Gérer les Employés
            </a>
            <a href="<?= BASE_URL ?>/payroll/form" class="btn btn-accent">
                <i class='bx bx-plus-circle'></i> Effectuer un Paiement
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>N° Pièce</th>
                    <th>Date</th>
                    <th>Employé</th>
                    <th>Fonction</th>
                    <th>Type de Versement</th>
                    <th>Période</th>
                    <th>Compte Caisse</th>
                    <th>Montant Décaissé</th>
                    <th>Statut</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                <tr><td colspan="10" style="text-align: center; padding: 30px; color: var(--c-gray-600);">Aucun versement de paie enregistré.</td></tr>
                <?php else: ?>
                    <?php foreach ($payments as $p): ?>
                    <?php $isValid = ($p['status'] === 'Valid'); ?>
                    <tr style="<?= !$isValid ? 'opacity: 0.65; background: #FFF5F5;' : '' ?>">
                        <td>
                            <a href="<?= BASE_URL ?>/payroll/receipt/<?= $p['id'] ?>" style="font-weight: 800; color: var(--c-navy); text-decoration: none;">
                                <?= htmlspecialchars($p['id']) ?>
                            </a>
                        </td>
                        <td><?= date('d/m/Y', strtotime($p['payment_date'])) ?></td>
                        <td><strong><?= htmlspecialchars($p['employee_name']) ?></strong></td>
                        <td><span style="font-size: 0.85rem; color: #64748B;"><?= htmlspecialchars($p['employee_role']) ?></span></td>
                        <td>
                            <?php if ($p['payment_type'] === 'Salary'): ?>
                                <span style="padding: 3px 8px; background: #DCFCE7; color: #166534; border-radius: 8px; font-size: 0.8rem; font-weight: 700;">
                                    Salaire Mensuel
                                </span>
                            <?php elseif ($p['payment_type'] === 'Advance'): ?>
                                <span style="padding: 3px 8px; background: #FEF3C7; color: #92400E; border-radius: 8px; font-size: 0.8rem; font-weight: 700;">
                                    Avance sur Salaire
                                </span>
                            <?php else: ?>
                                <span style="padding: 3px 8px; background: #E0F2FE; color: #0369A1; border-radius: 8px; font-size: 0.8rem; font-weight: 700;">
                                    <?= htmlspecialchars($p['payment_type']) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['period'] ?: '-') ?></td>
                        <td>
                            <span style="padding: 2px 8px; background: var(--c-gray-200); border-radius: 6px; font-size: 0.78rem; font-weight: 600;">
                                <?= htmlspecialchars($p['cash_account_name']) ?>
                            </span>
                        </td>
                        <td style="color: var(--c-danger); font-weight: 800; font-size: 1.05rem;">
                            -<?= number_format($p['amount'], 0, ',', ' ') ?> FCFA
                        </td>
                        <td>
                            <?php if ($isValid): ?>
                                <span style="padding: 3px 8px; border-radius: 10px; font-size: 0.75rem; font-weight: 800; background: #DCFCE7; color: #166534;">
                                    Payé
                                </span>
                            <?php else: ?>
                                <span style="padding: 3px 8px; border-radius: 10px; font-size: 0.75rem; font-weight: 800; background: #FEE2E2; color: #991B1B;">
                                    Annulé
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 6px; justify-content: center;">
                                <a href="<?= BASE_URL ?>/payroll/receipt/<?= $p['id'] ?>" class="btn btn-primary" style="padding: 4px 8px; font-size: 0.8rem; background-color: var(--c-navy);" title="Imprimer le Bulletin de Paie">
                                    <i class='bx bx-printer'></i>
                                </a>
                                <?php if ($isValid && \App\Core\Helper::isAdmin()): ?>
                                    <a href="<?= BASE_URL ?>/payroll/cancel/<?= $p['id'] ?>" class="btn btn-primary" style="padding: 4px 8px; font-size: 0.8rem; background-color: var(--c-danger);" onclick="return confirm('Annuler ce paiement <?= $p['id'] ?> ?\n\nLe montant sera réintégré dans la caisse.')" title="Annuler le paiement (Admin)">
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
