<div class="page-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
    <div style="display: flex; align-items: center; gap: 10px;">
        <i class='bx bx-book-content' style="font-size: 2rem; color: var(--c-navy);"></i>
        <div>
            <h1 style="margin: 0; font-size: 1.6rem;"><?= $title ?></h1>
            <span style="font-size: 0.88rem; color: #64748B;">
                Traçabilité intégrale des manquants de tournée, avances et remboursements
            </span>
        </div>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button type="button" class="btn btn-accent" onclick="openReimbursementModal()" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class='bx bx-plus-circle'></i> Enregistrer un Remboursement
        </button>
        <a href="<?= BASE_URL ?>/payroll/employees" class="btn btn-primary" style="background-color: var(--c-navy); display: inline-flex; align-items: center; gap: 6px;">
            <i class='bx bx-group'></i> Équipe Collaborateurs
        </a>
        <a href="<?= BASE_URL ?>/payroll" class="btn btn-primary" style="background-color: var(--c-gray-600); display: inline-flex; align-items: center; gap: 6px;">
            <i class='bx bx-arrow-back'></i> Retour Paie
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
    <div style="background: #FEE2E2; border-left: 4px solid var(--c-danger); color: #991B1B; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
        <i class='bx bx-error-circle' style="font-size: 1.4rem;"></i>
        <span><?= htmlspecialchars($flash_error) ?></span>
    </div>
<?php endif; ?>

<!-- KPI STATS CARDS -->
<div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 25px;">
    
    <!-- CARD 1: SOLDE NET DU -->
    <?php 
    $netBalance = floatval($stats['net_balance'] ?? 0);
    $isDebit = ($netBalance > 0);
    $cardBg = $isDebit ? '#FFF5F5' : '#F0FDF4';
    $cardBorder = $isDebit ? 'var(--c-danger)' : 'var(--c-success)';
    $cardColor = $isDebit ? '#991B1B' : '#166534';
    $cardIcon = $isDebit ? 'bx-error-circle' : 'bx-check-double';
    ?>
    <div class="stat-card" style="border-left: 4px solid <?= $cardBorder ?>; background: <?= $cardBg ?>;">
        <div class="stat-icon" style="background-color: rgba(<?= $isDebit ? '220, 38, 38' : '22, 101, 52' ?>, 0.15); color: <?= $cardColor ?>;">
            <i class='bx <?= $cardIcon ?>'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="color: <?= $cardColor ?>;">
                <?= number_format(abs($netBalance), 0, ',', ' ') ?> FCFA
            </div>
            <div class="stat-label" style="color: <?= $cardColor ?>; font-weight: 700;">
                <?= $isDebit ? 'Solde Dû par le Collaborateur' : ($netBalance < 0 ? 'Crédit en faveur du Collaborateur' : 'Compte Parfaitement Soldé (0 F)') ?>
            </div>
        </div>
    </div>

    <!-- CARD 2: TOTAL DEBITS (MANQUANTS / CHARGES) -->
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(220, 38, 38, 0.1); color: var(--c-danger);">
            <i class='bx bx-trending-down'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="color: var(--c-danger);">
                <?= number_format($stats['total_debit'] ?? 0, 0, ',', ' ') ?> FCFA
            </div>
            <div class="stat-label">Total Débits (Manquants & Avances)</div>
        </div>
    </div>

    <!-- CARD 3: TOTAL CREDITS (REMBOURSEMENTS) -->
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(22, 101, 52, 0.1); color: var(--c-success);">
            <i class='bx bx-trending-up'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="color: var(--c-success);">
                <?= number_format($stats['total_credit'] ?? 0, 0, ',', ' ') ?> FCFA
            </div>
            <div class="stat-label">Total Remboursements Encaissés</div>
        </div>
    </div>

    <!-- CARD 4: NOMBRE D'ECRITURES -->
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(13, 27, 42, 0.1); color: var(--c-navy);">
            <i class='bx bx-receipt'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= intval($stats['total_entries'] ?? 0) ?></div>
            <div class="stat-label">Écritures au Grand-Livre</div>
        </div>
    </div>
</div>

<!-- FILTERS CARD -->
<div class="card" style="margin-bottom: 25px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <span><i class='bx bx-filter-alt'></i> Filtrer les Mouvements du Personnel</span>
        <?php if (!empty($selected_employee_id) || !empty($selected_type) || !empty($date_from) || !empty($date_to) || !empty($search)): ?>
            <a href="<?= BASE_URL ?>/payroll/ledger" style="font-size: 0.85rem; color: var(--c-danger); font-weight: 700; text-decoration: none;">
                <i class='bx bx-x'></i> Réinitialiser tous les filtres
            </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <form action="<?= BASE_URL ?>/payroll/ledger" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
            
            <!-- SELECT EMPLOYEE -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700;">Collaborateur :</label>
                <select name="employee_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Tous les collaborateurs --</option>
                    <?php foreach ($employees as $emp): ?>
                        <?php 
                        $bal = floatval($emp['net_balance'] ?? 0);
                        $balText = ($bal > 0) ? " (⚠️ " . number_format($bal, 0, ',', ' ') . " F dû)" : " (0 F)";
                        ?>
                        <option value="<?= $emp['id'] ?>" <?= ($selected_employee_id == $emp['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($emp['name']) ?> - <?= htmlspecialchars($emp['role']) ?><?= $balText ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- SELECT TRANSACTION TYPE -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700;">Type d'Opération :</label>
                <select name="type" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Toutes les opérations --</option>
                    <option value="Tournee_Shortage" <?= ($selected_type === 'Tournee_Shortage') ? 'selected' : '' ?>>⚠️ Manquant Décharge Tournée</option>
                    <option value="Shortage_Reimbursement" <?= ($selected_type === 'Shortage_Reimbursement') ? 'selected' : '' ?>>✅ Remboursement Espèces Caisse</option>
                    <option value="Advance" <?= ($selected_type === 'Advance') ? 'selected' : '' ?>>💼 Avance sur Salaire</option>
                    <option value="Salary_Deduction" <?= ($selected_type === 'Salary_Deduction') ? 'selected' : '' ?>>✂️ Retenue sur Salaire</option>
                    <option value="Adjustment" <?= ($selected_type === 'Adjustment') ? 'selected' : '' ?>>⚙️ Ajustement Comptable</option>
                </select>
            </div>

            <!-- DATE FROM -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700;">Date Début :</label>
                <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($date_from) ?>">
            </div>

            <!-- DATE TO -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700;">Date Fin :</label>
                <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($date_to) ?>">
            </div>

            <!-- SEARCH KEYWORD -->
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700;">Recherche / Réf :</label>
                <input type="text" name="search" class="form-control" placeholder="Réf tournée, motif..." value="<?= htmlspecialchars($search) ?>">
            </div>

            <!-- SUBMIT / RESET -->
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="background-color: var(--c-navy); flex: 1; height: 38px;">
                    <i class='bx bx-search'></i> Filtrer
                </button>
                <a href="<?= BASE_URL ?>/payroll/ledger" class="btn btn-primary" style="background-color: var(--c-gray-600); height: 38px; display: inline-flex; align-items: center; justify-content: center;" title="Réinitialiser">
                    <i class='bx bx-refresh'></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- LEDGER ENTRIES TABLE -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class='bx bx-list-check' style="font-size: 1.3rem; color: var(--c-navy);"></i>
            <span style="font-weight: 700; font-size: 1.05rem;">
                Mouvements du Grand-Livre (<?= count($entries) ?> écritures)
            </span>
            <?php if (!empty($selected_employee)): ?>
                <span style="background: var(--c-navy); color: #FFF; padding: 2px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 700;">
                    Filtre : <?= htmlspecialchars($selected_employee['name']) ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date & Heure</th>
                    <th>Collaborateur</th>
                    <th>Type d'Écriture</th>
                    <th>Source de Vérité / Réf</th>
                    <th>Motif & Description</th>
                    <th style="text-align: right;">Débit (+ Dû)</th>
                    <th style="text-align: right;">Crédit (- Remboursé)</th>
                    <th>Opérateur</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($entries)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 35px; color: var(--c-gray-600);">
                            <i class='bx bx-folder-open' style="font-size: 2.5rem; color: #CBD5E1; display: block; margin-bottom: 8px;"></i>
                            Aucune écriture comptable trouvée pour ces critères de recherche.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($entries as $row): ?>
                    <tr>
                        <!-- DATE -->
                        <td style="white-space: nowrap; font-size: 0.85rem; color: #475569;">
                            <i class='bx bx-calendar' style="vertical-align: middle; color: #94A3B8;"></i>
                            <strong><?= date('d/m/Y', strtotime($row['transaction_date'])) ?></strong>
                            <div style="font-size: 0.75rem; color: #94A3B8;"><?= date('H:i', strtotime($row['transaction_date'])) ?></div>
                        </td>

                        <!-- COLLABORATEUR -->
                        <td>
                            <a href="<?= BASE_URL ?>/payroll/ledger?employee_id=<?= $row['employee_id'] ?>" style="font-weight: 800; color: var(--c-navy); text-decoration: none;" title="Filtrer sur cet employé">
                                <?= htmlspecialchars($row['employee_name']) ?>
                            </a>
                            <div>
                                <span style="padding: 1px 6px; background: var(--c-gray-200); border-radius: 6px; font-size: 0.75rem; color: #475569; font-weight: 600;">
                                    <?= htmlspecialchars($row['employee_role']) ?>
                                </span>
                            </div>
                        </td>

                        <!-- TRANSACTION TYPE -->
                        <td>
                            <?php if ($row['transaction_type'] === 'Tournee_Shortage'): ?>
                                <span style="padding: 3px 8px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; background: #FEE2E2; color: #991B1B; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class='bx bx-error-circle'></i> Manquant Tournée
                                </span>
                            <?php elseif ($row['transaction_type'] === 'Shortage_Reimbursement'): ?>
                                <span style="padding: 3px 8px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; background: #DCFCE7; color: #166534; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class='bx bx-check-circle'></i> Remboursement Caisse
                                </span>
                            <?php elseif ($row['transaction_type'] === 'Advance'): ?>
                                <span style="padding: 3px 8px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; background: #FEF3C7; color: #92400E; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class='bx bx-time-five'></i> Avance Salaire
                                </span>
                            <?php else: ?>
                                <span style="padding: 3px 8px; border-radius: 8px; font-size: 0.78rem; font-weight: 700; background: #E0F2FE; color: #0369A1;">
                                    <?= htmlspecialchars($row['transaction_type']) ?>
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- SOURCE DE VERITE & REF (CLICKABLE LINK) -->
                        <td style="white-space: nowrap;">
                            <?php if ($row['source_type'] === 'Tournee' && !empty($row['source_id'])): ?>
                                <a href="<?= BASE_URL ?>/tournees/details/<?= $row['source_id'] ?>" class="btn btn-primary" style="padding: 3px 8px; font-size: 0.8rem; background-color: var(--c-navy); text-decoration: none;" title="Ouvrir la fiche de la tournée source">
                                    <i class='bx bx-truck'></i> <?= htmlspecialchars($row['reference'] ?: 'Tournée #' . $row['source_id']) ?>
                                </a>
                            <?php elseif ($row['source_type'] === 'Payroll' && !empty($row['source_id'])): ?>
                                <a href="<?= BASE_URL ?>/payroll/receipt/<?= $row['source_id'] ?>" class="btn btn-primary" style="padding: 3px 8px; font-size: 0.8rem; background-color: var(--c-navy); text-decoration: none;" title="Voir le bulletin de paie">
                                    <i class='bx bx-receipt'></i> <?= htmlspecialchars($row['reference'] ?: $row['source_id']) ?>
                                </a>
                            <?php elseif ($row['source_type'] === 'Caisse'): ?>
                                <a href="<?= BASE_URL ?>/caisse" class="btn btn-primary" style="padding: 3px 8px; font-size: 0.8rem; background-color: #0284C7; text-decoration: none;" title="Voir le journal de caisse">
                                    <i class='bx bx-wallet'></i> <?= htmlspecialchars($row['reference'] ?: 'Reçu Caisse') ?>
                                </a>
                            <?php else: ?>
                                <span style="font-weight: 700; color: #64748B;">
                                    <?= htmlspecialchars($row['reference'] ?: '-') ?>
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- MOTIF & DESCRIPTION -->
                        <td style="font-size: 0.88rem; color: #1E293B; max-width: 380px;">
                            <div><?= htmlspecialchars($row['description']) ?></div>
                            <?php if (!empty($row['notes'])): ?>
                                <div style="font-size: 0.78rem; color: #64748B; font-style: italic; margin-top: 3px;">
                                    Note : <?= htmlspecialchars($row['notes']) ?>
                                </div>
                            <?php endif; ?>
                        </td>

                        <!-- DEBIT (+ DU) -->
                        <td style="text-align: right; white-space: nowrap;">
                            <?php if ($row['amount_debit'] > 0): ?>
                                <span style="font-weight: 800; font-size: 1rem; color: var(--c-danger);">
                                    +<?= number_format($row['amount_debit'], 0, ',', ' ') ?> F
                                </span>
                            <?php else: ?>
                                <span style="color: #CBD5E1;">-</span>
                            <?php endif; ?>
                        </td>

                        <!-- CREDIT (- REMBOURSE) -->
                        <td style="text-align: right; white-space: nowrap;">
                            <?php if ($row['amount_credit'] > 0): ?>
                                <span style="font-weight: 800; font-size: 1rem; color: var(--c-success);">
                                    -<?= number_format($row['amount_credit'], 0, ',', ' ') ?> F
                                </span>
                            <?php else: ?>
                                <span style="color: #CBD5E1;">-</span>
                            <?php endif; ?>
                        </td>

                        <!-- OPERATEUR -->
                        <td style="white-space: nowrap; font-size: 0.8rem; color: #64748B;">
                            <i class='bx bx-user' style="vertical-align: middle;"></i>
                            <?= htmlspecialchars($row['creator_name'] ?: 'Système') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- REIMBURSEMENT MODAL -->
<div id="reimbursement_modal" style="display: none; position: fixed; inset: 0; background: rgba(13, 27, 42, 0.6); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
    <div style="background: #FFF; width: 100%; max-width: 520px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden;">
        
        <div style="background: var(--c-navy); color: #FFF; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 1.1rem;">
                <i class='bx bx-money-withdraw' style="font-size: 1.3rem; color: var(--c-amber);"></i>
                <span>Enregistrer un Remboursement / Régularisation</span>
            </div>
            <button type="button" onclick="closeReimbursementModal()" style="background: none; border: none; color: #FFF; font-size: 1.4rem; cursor: pointer;">
                &times;
            </button>
        </div>

        <form action="<?= BASE_URL ?>/payroll/saveReimbursement" method="POST" style="padding: 20px;">
            
            <!-- EMPLOYEE SELECT -->
            <div class="form-group">
                <label class="form-label" style="font-weight: 700;">Collaborateur concerné <span style="color: var(--c-danger);">*</span></label>
                <select name="employee_id" id="modal_employee_id" class="form-control" required onchange="onModalEmployeeChange()">
                    <option value="">-- Choisir le collaborateur --</option>
                    <?php foreach ($employees as $emp): ?>
                        <option value="<?= $emp['id'] ?>" 
                                data-balance="<?= floatval($emp['net_balance'] ?? 0) ?>"
                                <?= ($selected_employee_id == $emp['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($emp['name']) ?> (<?= htmlspecialchars($emp['role']) ?>) - Solde : <?= number_format($emp['net_balance'], 0, ',', ' ') ?> F
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- CURRENT BALANCE BADGE -->
            <div id="modal_balance_info" style="margin-bottom: 15px; padding: 10px 14px; border-radius: 8px; font-size: 0.88rem; background: #F1F5F9; color: #334155; display: flex; justify-content: space-between; align-items: center;">
                <span>Solde actuel dû par le collaborateur :</span>
                <strong id="modal_balance_val" style="color: var(--c-danger); font-size: 1.05rem;">0 FCFA</strong>
            </div>

            <!-- AMOUNT -->
            <div class="form-group">
                <label class="form-label" style="font-weight: 700;">Montant Encaissé (FCFA) <span style="color: var(--c-danger);">*</span></label>
                <div style="display: flex; gap: 8px;">
                    <input type="number" step="1" min="1" name="amount" id="modal_amount" class="form-control" required placeholder="Ex: 900">
                    <button type="button" class="btn btn-primary" onclick="fillTotalDue()" style="background-color: var(--c-gray-600); font-size: 0.8rem; white-space: nowrap;">
                        Tout solder
                    </button>
                </div>
            </div>

            <!-- CASH ACCOUNT -->
            <div class="form-group">
                <label class="form-label" style="font-weight: 700;">Caisse Réceptrice des Fonds <span style="color: var(--c-danger);">*</span></label>
                <select name="cash_account_id" id="modal_cash_account" class="form-control" required>
                    <?php foreach ($cash_accounts as $ca): ?>
                        <option value="<?= $ca['id'] ?>">
                            <?= htmlspecialchars($ca['name']) ?> (Solde : <?= number_format($ca['current_balance'], 0, ',', ' ') ?> F)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small style="color: #64748B; font-size: 0.78rem;">Les fonds seront automatiquement crédités dans cette caisse.</small>
            </div>

            <!-- DATE -->
            <div class="form-group">
                <label class="form-label" style="font-weight: 700;">Date du Versement :</label>
                <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>

            <!-- NOTES -->
            <div class="form-group">
                <label class="form-label" style="font-weight: 700;">Justificatif / Notes :</label>
                <input type="text" name="notes" class="form-control" placeholder="Ex: Régularisation manquant TR00002 remis en espèces">
            </div>

            <div style="margin-top: 25px; display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-primary" onclick="closeReimbursementModal()" style="background-color: var(--c-gray-600);">
                    Annuler
                </button>
                <button type="submit" class="btn btn-accent" style="font-weight: 700; padding: 10px 20px;">
                    <i class='bx bx-check-circle'></i> Encaisser & Mettre à jour le Grand-Livre
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openReimbursementModal() {
    const modal = document.getElementById('reimbursement_modal');
    modal.style.display = 'flex';
    onModalEmployeeChange();
}

function closeReimbursementModal() {
    const modal = document.getElementById('reimbursement_modal');
    modal.style.display = 'none';
}

function onModalEmployeeChange() {
    const select = document.getElementById('modal_employee_id');
    const selectedOpt = select.options[select.selectedIndex];
    const balVal = document.getElementById('modal_balance_val');
    const amountInput = document.getElementById('modal_amount');

    if (selectedOpt && selectedOpt.value) {
        const bal = parseFloat(selectedOpt.getAttribute('data-balance') || 0);
        balVal.textContent = new Intl.NumberFormat('fr-FR').format(bal) + ' FCFA';
        if (bal > 0) {
            balVal.style.color = 'var(--c-danger)';
            if (!amountInput.value || amountInput.value == '0') {
                amountInput.value = bal;
            }
        } else {
            balVal.style.color = 'var(--c-success)';
        }
    } else {
        balVal.textContent = '0 FCFA';
    }
}

function fillTotalDue() {
    const select = document.getElementById('modal_employee_id');
    const selectedOpt = select.options[select.selectedIndex];
    if (selectedOpt && selectedOpt.value) {
        const bal = parseFloat(selectedOpt.getAttribute('data-balance') || 0);
        if (bal > 0) {
            document.getElementById('modal_amount').value = bal;
        }
    }
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeReimbursementModal();
    }
});
</script>
