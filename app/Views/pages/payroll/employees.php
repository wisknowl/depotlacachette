<div class="page-title">
    <i class='bx bx-group'></i>
    <h1><?= $title ?></h1>
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

<style>
.employees-grid {
    display: grid;
    grid-template-columns: minmax(300px, 340px) minmax(0, 1fr);
    gap: 20px;
    align-items: start;
}
@media (max-width: 1080px) {
    .employees-grid {
        grid-template-columns: minmax(0, 1fr);
    }
}
.emp-table-card {
    min-width: 0;
    max-width: 100%;
    overflow: hidden;
    padding: 0 !important;
}
.emp-table-card .card-header {
    padding: 16px 20px;
    margin-bottom: 0;
    border-bottom: 1px solid var(--c-gray-200);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}
.emp-table-responsive {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    display: block;
    -webkit-overflow-scrolling: touch;
}
.emp-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 660px;
}
.emp-table th {
    background-color: var(--c-navy);
    color: #FFFFFF;
    padding: 10px 14px;
    font-size: 0.82rem;
    font-weight: 600;
    white-space: nowrap;
    border-bottom: 2px solid var(--c-navy-dark);
}
.emp-table td {
    padding: 10px 14px;
    font-size: 0.85rem;
    vertical-align: middle;
    border-bottom: 1px solid var(--c-gray-200);
}
.emp-table tbody tr:hover {
    background-color: #F8FAFC;
}
</style>

<div class="employees-grid">
    
    <!-- ADD / EDIT EMPLOYEE FORM -->
    <div class="card" style="min-width: 0; max-width: 100%;">
        <div class="card-header">
            <span id="emp_form_title"><i class='bx bx-user-plus'></i> Ajouter un Employé</span>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>/payroll/saveEmployee" method="POST">
                <input type="hidden" name="id" id="emp_id" value="0">
                
                <div class="form-group">
                    <label class="form-label">Nom &amp; Prénom <span style="color: var(--c-danger);">*</span></label>
                    <input type="text" name="name" id="emp_name" class="form-control" required placeholder="Ex: Pierre Tchounkeu">
                </div>

                <div class="form-group">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone" id="emp_phone" class="form-control" placeholder="Ex: 677 88 99 00">
                </div>

                <div class="form-group">
                    <label class="form-label">Fonction / Rôle au Dépôt <span style="color: var(--c-danger);">*</span></label>
                    <input type="text" name="role" id="emp_role" class="form-control" required placeholder="Ex: Chauffeur-Livreur, Magasinier, Caissière...">
                </div>

                <div class="form-group">
                    <label class="form-label">Salaire de Base Mensuel (FCFA)</label>
                    <input type="number" step="500" name="base_salary" id="emp_salary" class="form-control" placeholder="Ex: 85000" value="0">
                </div>

                <div class="form-group">
                    <label class="form-label">Statut</label>
                    <select name="status" id="emp_status" class="form-control">
                        <option value="Active">Actif</option>
                        <option value="Inactive">Inactif / Démissionnaire</option>
                    </select>
                </div>

                <div style="margin-top: 20px; display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-accent" style="flex: 1;">
                        <i class='bx bx-save'></i> Enregistrer
                    </button>
                    <button type="button" class="btn btn-primary" onclick="resetEmpForm()" style="background-color: var(--c-gray-600);">
                        Effacer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- EMPLOYEES LIST -->
    <div class="card emp-table-card">
        <div class="card-header">
            <span style="font-weight: 700; font-size: 1rem; color: var(--c-navy);">
                <i class='bx bx-list-ul'></i> Équipe du Dépôt (<?= count($employees) ?> collaborateurs)
            </span>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <a href="<?= BASE_URL ?>/payroll/ledger" class="btn btn-primary" style="padding: 5px 12px; font-size: 0.8rem; background-color: var(--c-navy); border-radius: 6px; white-space: nowrap;">
                    <i class='bx bx-book-content'></i> Grand-Livre Général
                </a>
                <a href="<?= BASE_URL ?>/payroll" class="btn btn-primary" style="padding: 5px 12px; font-size: 0.8rem; background-color: var(--c-gray-600); border-radius: 6px; white-space: nowrap;">
                    <i class='bx bx-arrow-back'></i> Retour Paie
                </a>
            </div>
        </div>
        <div class="emp-table-responsive table-responsive">
            <table class="emp-table">
                <thead>
                    <tr>
                        <th>Nom &amp; Prénom</th>
                        <th>Fonction</th>
                        <th>Téléphone</th>
                        <th style="text-align: right;">Salaire Base</th>
                        <th style="text-align: center;">Solde Compte</th>
                        <th style="text-align: center;">Statut</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($employees)): ?>
                    <tr><td colspan="7" style="text-align: center; padding: 25px; color: var(--c-gray-600);">Aucun employé enregistré.</td></tr>
                    <?php else: ?>
                        <?php foreach ($employees as $emp): ?>
                        <tr>
                            <td style="white-space: nowrap;">
                                <strong><?= htmlspecialchars($emp['name']) ?></strong>
                            </td>
                            <td style="white-space: nowrap;">
                                <span style="padding: 2px 8px; background: var(--c-gray-200); border-radius: 8px; font-size: 0.8rem; font-weight: 600;">
                                    <?= htmlspecialchars($emp['role']) ?>
                                </span>
                            </td>
                            <td style="white-space: nowrap;">
                                <?= htmlspecialchars($emp['phone'] ?: '-') ?>
                            </td>
                            <td style="font-weight: 700; color: var(--c-navy); text-align: right; white-space: nowrap;">
                                <?= number_format($emp['base_salary'], 0, ',', ' ') ?> FCFA
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <?php 
                                $bal = floatval($emp['net_balance'] ?? 0); 
                                if ($bal > 0): ?>
                                    <span style="padding: 3px 8px; border-radius: 6px; font-size: 0.78rem; font-weight: 800; background: #FEE2E2; color: #991B1B; display: inline-flex; align-items: center; gap: 4px;" title="Manquant(s) ou dette(s) non remboursés">
                                        <i class='bx bx-error-circle'></i> <?= number_format($bal, 0, ',', ' ') ?> FCFA
                                    </span>
                                <?php elseif ($bal < 0): ?>
                                    <span style="padding: 3px 8px; border-radius: 6px; font-size: 0.78rem; font-weight: 700; background: #E0F2FE; color: #0369A1;">
                                        +<?= number_format(abs($bal), 0, ',', ' ') ?> FCFA
                                    </span>
                                <?php else: ?>
                                    <span style="padding: 3px 8px; border-radius: 6px; font-size: 0.78rem; font-weight: 700; background: #DCFCE7; color: #166534; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class='bx bx-check-circle'></i> 0 FCFA
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <?php if ($emp['status'] === 'Active'): ?>
                                    <span style="padding: 3px 8px; border-radius: 10px; font-size: 0.75rem; font-weight: 700; background: #DCFCE7; color: #166534;">Actif</span>
                                <?php else: ?>
                                    <span style="padding: 3px 8px; border-radius: 10px; font-size: 0.75rem; font-weight: 700; background: #FEE2E2; color: #991B1B;">Inactif</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <div style="display: inline-flex; gap: 5px; justify-content: center; align-items: center;">
                                    <a href="<?= BASE_URL ?>/payroll/ledger?employee_id=<?= $emp['id'] ?>" class="btn btn-primary" style="padding: 5px 9px; font-size: 0.78rem; background-color: #0284C7; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;" title="Consulter le Grand-Livre / Mouvements de <?= htmlspecialchars($emp['name']) ?>">
                                        <i class='bx bx-book-content'></i> Mouvements
                                    </a>
                                    <button type="button" class="btn btn-primary" style="padding: 5px 8px; font-size: 0.78rem; background-color: var(--c-navy); border-radius: 6px;" onclick="editEmp(<?= htmlspecialchars(json_encode($emp), ENT_QUOTES, 'UTF-8') ?>)" title="Modifier">
                                        <i class='bx bx-edit'></i>
                                    </button>
                                    <?php if (\App\Core\Helper::isAdmin() && $emp['status'] === 'Active'): ?>
                                        <a href="<?= BASE_URL ?>/payroll/deleteEmployee/<?= $emp['id'] ?>" class="btn btn-primary" style="padding: 5px 8px; font-size: 0.78rem; background-color: var(--c-danger); border-radius: 6px;" onclick="return confirm('Désactiver cet employé ?')" title="Désactiver">
                                            <i class='bx bx-user-x'></i>
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
</div>

<script>
function editEmp(emp) {
    document.getElementById('emp_form_title').innerHTML = "<i class='bx bx-edit'></i> Modifier l'Employé #" + emp.id;
    document.getElementById('emp_id').value = emp.id;
    document.getElementById('emp_name').value = emp.name;
    document.getElementById('emp_phone').value = emp.phone || '';
    document.getElementById('emp_role').value = emp.role;
    document.getElementById('emp_salary').value = emp.base_salary;
    document.getElementById('emp_status').value = emp.status;
    // Smooth scroll to form on mobile
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetEmpForm() {
    document.getElementById('emp_form_title').innerHTML = "<i class='bx bx-user-plus'></i> Ajouter un Employé";
    document.getElementById('emp_id').value = '0';
    document.getElementById('emp_name').value = '';
    document.getElementById('emp_phone').value = '';
    document.getElementById('emp_role').value = '';
    document.getElementById('emp_salary').value = '0';
    document.getElementById('emp_status').value = 'Active';
}
</script>
