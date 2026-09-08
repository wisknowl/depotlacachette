<div class="page-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
    <div style="display: flex; align-items: center; gap: 12px;">
        <div style="background: linear-gradient(135deg, #0284C7, #0369A1); color: white; width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; box-shadow: 0 4px 6px -1px rgba(2, 132, 199, 0.3);">
            <i class='bx bx-trip'></i>
        </div>
        <div>
            <h1 style="margin: 0; font-size: 1.4rem; font-weight: 800; color: #1E293B;"><?= $title ?></h1>
            <p style="margin: 0; font-size: 0.85rem; color: #64748B;">Suivi des chargements camions, dépouillement des factures carnet et réconciliation des caisses.</p>
        </div>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/tournees/nouveau" class="btn btn-accent" style="padding: 9px 18px; font-weight: 700; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px -1px rgba(244, 164, 35, 0.3);">
            <i class='bx bx-plus-circle' style="font-size: 1.2rem;"></i> Nouveau Chargement Camion
        </a>
    </div>
</div>

<?php if (!empty($flash_success)): ?>
    <div style="background: #DEF7EC; border-left: 4px solid #31C48D; color: #03543F; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <i class='bx bx-check-circle' style="font-size: 1.25rem;"></i>
        <span><?= htmlspecialchars($flash_success) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($flash_error)): ?>
    <div style="background: #FDE8E8; border-left: 4px solid #F98080; color: #9B1C1C; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <i class='bx bx-error-circle' style="font-size: 1.25rem;"></i>
        <span><?= htmlspecialchars($flash_error) ?></span>
    </div>
<?php endif; ?>

<!-- KPI STATS CARDS -->
<?php
$countEnRoute = 0;
$countCloturee = 0;
$totalLoadedVal = 0;
$totalSoldVal = 0;
$totalCashDepositedVal = 0;

foreach ($tournees as $t) {
    if ($t['status'] === 'En_Route') $countEnRoute++;
    if ($t['status'] === 'Cloturee') $countCloturee++;
    if ($t['status'] !== 'Annulee') {
        $totalLoadedVal += floatval($t['total_loaded_amount']);
        $totalSoldVal += floatval($t['total_sold_amount']);
        $totalCashDepositedVal += floatval($t['cash_deposited']);
    }
}
?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <!-- En route -->
    <div style="background: white; border-radius: 10px; border: 1px solid #E2E8F0; padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center;">
        <div>
            <span style="font-size: 0.8rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Tournées En Cours</span>
            <div style="font-size: 1.6rem; font-weight: 900; color: #0284C7; margin-top: 4px;"><?= $countEnRoute ?></div>
            <span style="font-size: 0.75rem; color: #0284C7; font-weight: 600;">Camions sur la route</span>
        </div>
        <div style="background: #E0F2FE; color: #0284C7; width: 44px; height: 44px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class='bx bx-car'></i>
        </div>
    </div>

    <!-- Clôturées -->
    <div style="background: white; border-radius: 10px; border: 1px solid #E2E8F0; padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center;">
        <div>
            <span style="font-size: 0.8rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Tournées Clôturées</span>
            <div style="font-size: 1.6rem; font-weight: 900; color: #16A34A; margin-top: 4px;"><?= $countCloturee ?></div>
            <span style="font-size: 0.75rem; color: #16A34A; font-weight: 600;">Déchargées et réconciliées</span>
        </div>
        <div style="background: #DCFCE7; color: #16A34A; width: 44px; height: 44px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class='bx bx-check-shield'></i>
        </div>
    </div>

    <!-- Total Marchandises Chargées -->
    <div style="background: white; border-radius: 10px; border: 1px solid #E2E8F0; padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center;">
        <div>
            <span style="font-size: 0.8rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Total Chargé</span>
            <div style="font-size: 1.35rem; font-weight: 900; color: #1E293B; margin-top: 4px;"><?= number_format($totalLoadedVal, 0, ',', ' ') ?> F</div>
            <span style="font-size: 0.75rem; color: #64748B; font-weight: 600;">Valeur sortie dépôt</span>
        </div>
        <div style="background: #F1F5F9; color: #475569; width: 44px; height: 44px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class='bx bx-package'></i>
        </div>
    </div>

    <!-- Total Cash Versé -->
    <div style="background: white; border-radius: 10px; border: 1px solid #E2E8F0; padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center;">
        <div>
            <span style="font-size: 0.8rem; font-weight: 700; color: #64748B; text-transform: uppercase;">Cash Versé en Caisse</span>
            <div style="font-size: 1.35rem; font-weight: 900; color: #059669; margin-top: 4px;"><?= number_format($totalCashDepositedVal, 0, ',', ' ') ?> F</div>
            <span style="font-size: 0.75rem; color: #059669; font-weight: 600;">Total encaissé net</span>
        </div>
        <div style="background: #ECFDF5; color: #059669; width: 44px; height: 44px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class='bx bx-wallet'></i>
        </div>
    </div>
</div>

<!-- FILTERS CARD -->
<div class="card" style="margin-bottom: 20px; padding: 16px 20px; background: white;">
    <form action="<?= BASE_URL ?>/tournees" method="GET" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
        <!-- SEARCH -->
        <div style="flex: 2; min-width: 180px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Recherche Réf / Chauffeur / Véhicule</label>
            <div style="position: relative;">
                <input type="text" name="search" class="form-control" placeholder="Rechercher TR..., chauffeur..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>" style="padding-left: 32px; font-size: 0.88rem;">
                <i class='bx bx-search' style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 1.1rem;"></i>
            </div>
        </div>

        <!-- DRIVER -->
        <div style="flex: 1.5; min-width: 160px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Chauffeur / Livreur</label>
            <select name="driver_id" class="form-control" style="font-size: 0.88rem;">
                <option value="">-- Tous les livreurs --</option>
                <?php foreach ($drivers as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= (($filters['driver_id'] ?? '') == $d['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['name']) ?> (<?= htmlspecialchars($d['role']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- STATUS -->
        <div style="flex: 1.2; min-width: 140px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Statut</label>
            <select name="status" class="form-control" style="font-size: 0.88rem;">
                <option value="">-- Tous les statuts --</option>
                <option value="En_Route" <?= (($filters['status'] ?? '') === 'En_Route') ? 'selected' : '' ?>>🟢 En Route</option>
                <option value="Cloturee" <?= (($filters['status'] ?? '') === 'Cloturee') ? 'selected' : '' ?>>🏁 Clôturée</option>
                <option value="Annulee" <?= (($filters['status'] ?? '') === 'Annulee') ? 'selected' : '' ?>>⚪ Annulée</option>
            </select>
        </div>

        <!-- DATES -->
        <div style="display: flex; align-items: flex-end; gap: 6px;">
            <div>
                <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Du</label>
                <input type="date" name="start_date" class="form-control" style="padding: 4px 8px; font-size: 0.85rem;" value="<?= htmlspecialchars($filters['start_date'] ?? '') ?>">
            </div>
            <div>
                <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Au</label>
                <input type="date" name="end_date" class="form-control" style="padding: 4px 8px; font-size: 0.85rem;" value="<?= htmlspecialchars($filters['end_date'] ?? '') ?>">
            </div>
        </div>

        <!-- BUTTONS -->
        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-accent" style="padding: 8px 16px; font-size: 0.88rem;">
                <i class='bx bx-filter-alt'></i> Filtrer
            </button>
            <?php if (!empty($filters['search']) || !empty($filters['driver_id']) || !empty($filters['status']) || !empty($filters['start_date']) || !empty($filters['end_date'])): ?>
                <a href="<?= BASE_URL ?>/tournees" class="btn btn-primary" style="padding: 8px 12px; font-size: 0.88rem; background: #64748B;" title="Réinitialiser">
                    <i class='bx bx-reset'></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- LIST TABLE -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <span><i class='bx bx-list-ul'></i> Historique des Tournées de Vente Route</span>
        <span style="font-size: 0.82rem; font-weight: 700; color: var(--c-navy); background: #F1F5F9; padding: 3px 8px; border-radius: 6px;">
            <?= count($tournees) ?> enregistrement(s)
        </span>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Réf. Tournée</th>
                    <th>Date</th>
                    <th>Chauffeur / Livreur</th>
                    <th>Véhicule / Engin</th>
                    <th style="text-align: right;">Valeur Chargée</th>
                    <th style="text-align: center;">Factures Carnet</th>
                    <th style="text-align: right;">Ventes Réalisées</th>
                    <th style="text-align: right;">Cash Versé</th>
                    <th style="text-align: center;">Statut</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tournees)): ?>
                <tr>
                    <td colspan="10" style="text-align: center; padding: 40px 20px; color: #64748B;">
                        <i class='bx bx-trip' style="font-size: 3rem; color: #CBD5E1; display: block; margin-bottom: 10px;"></i>
                        Aucune tournée de vente enregistrée pour le moment.<br>
                        <a href="<?= BASE_URL ?>/tournees/nouveau" style="color: #0284C7; font-weight: 700; text-decoration: underline; margin-top: 6px; display: inline-block;">
                            Créer un premier bon de chargement
                        </a>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($tournees as $t): ?>
                    <tr>
                        <td>
                            <strong style="color: #0284C7; font-size: 0.95rem;"><?= htmlspecialchars($t['reference']) ?></strong>
                        </td>
                        <td>
                            <?= date('d/m/Y', strtotime($t['tournee_date'])) ?>
                            <small style="display: block; color: #94A3B8; font-size: 0.72rem;"><?= date('H:i', strtotime($t['created_at'])) ?></small>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($t['driver_name'] ?: 'Non assigné') ?></strong>
                            <?php if (!empty($t['driver_phone'])): ?>
                                <small style="display: block; color: #64748B;"><?= htmlspecialchars($t['driver_phone']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="background: #F1F5F9; border: 1px solid #E2E8F0; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; color: #475569;">
                                <i class='bx bx-car'></i> <?= htmlspecialchars($t['vehicle_name'] ?: 'Standard') ?>
                            </span>
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #1E293B;">
                            <?= number_format($t['total_loaded_amount'], 0, ',', ' ') ?> F
                            <small style="display: block; color: #64748B; font-weight: 400; font-size: 0.75rem;"><?= $t['item_count'] ?> art. chargés</small>
                        </td>
                        <td style="text-align: center;">
                            <span style="display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 700; background: <?= $t['sales_count'] > 0 ? '#E0F2FE' : '#F1F5F9' ?>; color: <?= $t['sales_count'] > 0 ? '#0284C7' : '#64748B' ?>;">
                                <?= $t['sales_count'] ?> facture(s)
                            </span>
                        </td>
                        <td style="text-align: right; font-weight: 800; color: <?= $t['status'] === 'Cloturee' ? '#16A34A' : '#475569' ?>;">
                            <?= number_format($t['status'] === 'Cloturee' ? $t['total_sold_amount'] : $t['calculated_sales_total'], 0, ',', ' ') ?> F
                        </td>
                        <td style="text-align: right; font-weight: 800; color: #059669;">
                            <?= ($t['status'] === 'Cloturee') ? number_format($t['cash_deposited'], 0, ',', ' ') . ' F' : '<span style="color: #94A3B8;">En attente</span>' ?>
                            <?php if ($t['cash_shortage'] > 0): ?>
                                <small style="display: block; color: #DC2626; font-size: 0.72rem; font-weight: 700;">(-<?= number_format($t['cash_shortage'], 0, ',', ' ') ?> F manquant)</small>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <?php if ($t['status'] === 'En_Route'): ?>
                                <span style="background: #E0F2FE; color: #0284C7; padding: 4px 8px; border-radius: 6px; font-size: 0.78rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                    <span style="width: 7px; height: 7px; border-radius: 50%; background: #0284C7; display: inline-block;"></span> En Route
                                </span>
                            <?php elseif ($t['status'] === 'Cloturee'): ?>
                                <span style="background: #DCFCE7; color: #16A34A; padding: 4px 8px; border-radius: 6px; font-size: 0.78rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class='bx bx-check'></i> Clôturée
                                </span>
                            <?php else: ?>
                                <span style="background: #FEE2E2; color: #DC2626; padding: 4px 8px; border-radius: 6px; font-size: 0.78rem; font-weight: 700;">
                                    Annulée
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center; white-space: nowrap;">
                            <a href="<?= BASE_URL ?>/tournees/details/<?= $t['id'] ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.8rem; background: <?= $t['status'] === 'En_Route' ? '#0284C7' : '#475569' ?>;" title="Ouvrir le dossier / Dépouillement">
                                <?= ($t['status'] === 'En_Route') ? "<i class='bx bx-edit'></i> Décharge" : "<i class='bx bx-show'></i> Détails" ?>
                            </a>
                            <a href="<?= BASE_URL ?>/tournees/imprimerChargement/<?= $t['id'] ?>" target="_blank" class="btn btn-primary" style="padding: 5px 8px; font-size: 0.8rem; background: #64748B;" title="Imprimer le bon de chargement">
                                <i class='bx bx-printer'></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
