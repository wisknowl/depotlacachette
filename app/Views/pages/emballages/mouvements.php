<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div class="page-title" style="margin-bottom: 0;">
        <i class='bx bx-history'></i>
        <h1>Journal des Mouvements d'Emballages (Casiers & Bouteilles)</h1>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="<?= BASE_URL ?>/emballages/ajustement" class="btn btn-primary" style="background-color: #6366F1; display: flex; align-items: center; gap: 6px; font-weight: 600;">
            <i class='bx bx-slider-alt'></i> Ajustement / Stock Initial
        </a>
        <a href="<?= BASE_URL ?>/emballages" class="btn btn-primary" style="background: var(--c-navy); display: flex; align-items: center; gap: 6px;">
            <i class='bx bx-archive'></i> Tableau de Bord Emballages
        </a>
        <a href="<?= BASE_URL ?>/emballages/achat" class="btn btn-accent" style="display: flex; align-items: center; gap: 6px;">
            <i class='bx bx-plus'></i> Acheter Emballages Vides
        </a>
    </div>
</div>

<!-- SEARCH & FILTER TOOLBAR -->
<div class="card" style="margin-bottom: 20px; padding: 16px 20px; background: white;">
    <form action="<?= BASE_URL ?>/emballages/mouvements" method="GET" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
        
        <!-- SEARCH -->
        <div style="flex: 2; min-width: 180px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Recherche par Note / Tiers</label>
            <div style="position: relative;">
                <input type="text" name="search" class="form-control" placeholder="Recherche note, client, fournisseur..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>" style="padding-left: 32px; font-size: 0.88rem;">
                <i class='bx bx-search' style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 1.1rem;"></i>
            </div>
        </div>

        <!-- PACKAGING TYPE -->
        <div style="flex: 1.5; min-width: 160px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Modèle Casier</label>
            <select name="packaging_type_id" class="form-control" style="font-size: 0.88rem;">
                <option value="">-- Tous les modèles --</option>
                <?php foreach ($packagingTypes as $pt): ?>
                    <option value="<?= $pt['id'] ?>" <?= (($filters['packaging_type_id'] ?? '') == $pt['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($pt['company']) ?> - <?= htmlspecialchars($pt['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- MOVEMENT TYPE -->
        <div style="flex: 1.3; min-width: 150px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Type de Flux</label>
            <select name="movement_type" class="form-control" style="font-size: 0.88rem;">
                <option value="">-- Tous les flux --</option>
                <option value="Initial_Stock" <?= (($filters['movement_type'] ?? '') === 'Initial_Stock') ? 'selected' : '' ?>>Stock Initial</option>
                <option value="Adjustment_Plus" <?= (($filters['movement_type'] ?? '') === 'Adjustment_Plus') ? 'selected' : '' ?>>Ajustement (+)</option>
                <option value="Adjustment_Minus" <?= (($filters['movement_type'] ?? '') === 'Adjustment_Minus') ? 'selected' : '' ?>>Ajustement (-)</option>
                <option value="Breakage_Empty" <?= (($filters['movement_type'] ?? '') === 'Breakage_Empty') ? 'selected' : '' ?>>Casse Vides</option>
                <option value="Purchase_Empty" <?= (($filters['movement_type'] ?? '') === 'Purchase_Empty') ? 'selected' : '' ?>>Achat Vides</option>
                <option value="Purchase_Drink" <?= (($filters['movement_type'] ?? '') === 'Purchase_Drink') ? 'selected' : '' ?>>Échange Camion Achat</option>
                <option value="Sale_Drink" <?= (($filters['movement_type'] ?? '') === 'Sale_Drink') ? 'selected' : '' ?>>Sortie Vente</option>
                <option value="Client_Return" <?= (($filters['movement_type'] ?? '') === 'Client_Return') ? 'selected' : '' ?>>Restitution Client</option>
                <option value="Route_Return" <?= (($filters['movement_type'] ?? '') === 'Route_Return') ? 'selected' : '' ?>>Retour Tournée (Décharge)</option>
                <option value="Supplier_Return" <?= (($filters['movement_type'] ?? '') === 'Supplier_Return') ? 'selected' : '' ?>>Restitution Fournisseur</option>
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
            <?php if (!empty($filters['search']) || !empty($filters['packaging_type_id']) || !empty($filters['movement_type']) || !empty($filters['start_date']) || !empty($filters['end_date'])): ?>
                <a href="<?= BASE_URL ?>/emballages/mouvements" class="btn btn-primary" style="padding: 8px 12px; font-size: 0.88rem; background: #64748B;" title="Réinitialiser">
                    <i class='bx bx-reset'></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <span><i class='bx bx-list-ul'></i> Historique Chronologique des Mouvements d'Emballages</span>
        <span style="font-size: 0.82rem; font-weight: 700; color: var(--c-navy); background: #F1F5F9; padding: 3px 8px; border-radius: 6px;">
            <?= count($movements) ?> enregistrement(s)
        </span>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type Opération</th>
                    <th>Modèle Casier</th>
                    <th>Tiers (Client / Fournisseur)</th>
                    <th style="text-align: center;">Entrée Casiers</th>
                    <th style="text-align: center;">Sortie Casiers</th>
                    <th style="text-align: center;">Bouteilles Vrac</th>
                    <th>Montant / Réf</th>
                    <th>Agent</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($movements)): ?>
                <tr><td colspan="10" style="text-align: center; padding: 30px; color: var(--c-gray-600);">Aucun mouvement d'emballages enregistré.</td></tr>
                <?php else: ?>
                    <?php foreach ($movements as $m): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($m['created_at'])) ?></td>
                        <td>
                            <?php 
                                $typeLabels = [
                                    'Initial_Stock' => ['Stock Initial', '#16A34A', '#DCFCE7'],
                                    'Adjustment_Plus' => ['Ajustement (+)', '#16A34A', '#DCFCE7'],
                                    'Adjustment_Minus' => ['Ajustement (-)', '#DC2626', '#FEE2E2'],
                                    'Breakage_Empty' => ['Casse Vides', '#DC2626', '#FEE2E2'],
                                    'Purchase_Empty' => ['Achat Vides', '#16A34A', '#DCFCE7'],
                                    'Purchase_Drink' => ['Sortie Vides (Échange Camion)', '#0284C7', '#E0F2FE'],
                                    'Sale_Drink' => ['Sortie Vente', '#D97706', '#FEF3C7'],
                                    'Client_Return' => ['Entrée Vides (Retour Client)', '#16A34A', '#DCFCE7'],
                                    'Route_Return' => ['Retour Tournée (Décharge)', '#059669', '#D1FAE5'],
                                    'Supplier_Return' => ['Sortie Vides (Retour Fournisseur)', '#475569', '#F1F5F9'],
                                    'Breakage' => ['Casse / Perte', '#DC2626', '#FEE2E2'],
                                    'Adjustment' => ['Ajustement', '#8B5CF6', '#EDE9FE'],
                                    'Sale_Cancellation' => ['Annulation Vente (Sortie Vides)', '#DC2626', '#FEE2E2'],
                                    'Purchase_Cancellation' => ['Annulation Achat (Entrée Vides)', '#DC2626', '#FEE2E2']
                                ];
                                $lbl = $typeLabels[$m['movement_type']] ?? [$m['movement_type'], '#334155', '#F1F5F9'];
                            ?>
                            <span style="padding: 3px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; color: <?= $lbl[1] ?>; background: <?= $lbl[2] ?>;">
                                <?= $lbl[0] ?>
                            </span>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($m['packaging_name']) ?></strong>
                            <small style="display: block; color: #64748B;"><?= htmlspecialchars($m['packaging_color']) ?></small>
                        </td>
                        <td>
                            <?php if (!empty($m['client_name'])): ?>
                                <span style="color: var(--c-navy); font-weight: 600;">Client : <?= htmlspecialchars($m['client_name']) ?></span>
                            <?php elseif (!empty($m['supplier_name'])): ?>
                                <span style="color: #475569; font-weight: 600;">Fournisseur : <?= htmlspecialchars($m['supplier_name']) ?></span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center; font-weight: 700; color: #16A34A;">
                            <?= ($m['crates_in'] > 0) ? '+' . $m['crates_in'] : '-' ?>
                        </td>
                        <td style="text-align: center; font-weight: 700; color: #DC2626;">
                            <?= ($m['crates_out'] > 0) ? '-' . $m['crates_out'] : '-' ?>
                        </td>
                        <td style="text-align: center;">
                            <?php if ($m['bottles_in'] > 0): ?>
                                <span style="color: #16A34A; font-weight: 600;">+<?= $m['bottles_in'] ?> btl</span>
                            <?php elseif ($m['bottles_out'] > 0): ?>
                                <span style="color: #DC2626; font-weight: 600;">-<?= $m['bottles_out'] ?> btl</span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($m['reference_id'])): ?>
                                <div style="margin-bottom: 2px;">
                                    <?php if (str_starts_with($m['reference_id'], 'V')): ?>
                                        <a href="<?= BASE_URL ?>/ventes/invoice/<?= htmlspecialchars($m['reference_id']) ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 2px; font-weight: 700; color: #1D4ED8; font-size: 0.82rem; text-decoration: none;" title="Voir la facture de vente">
                                            <i class='bx bx-receipt'></i> <?= htmlspecialchars($m['reference_id']) ?>
                                        </a>
                                    <?php elseif (str_starts_with($m['reference_id'], 'A')): ?>
                                        <span style="font-weight: 700; color: #0284C7; font-size: 0.82rem;"><i class='bx bx-cart'></i> <?= htmlspecialchars($m['reference_id']) ?></span>
                                    <?php elseif (is_numeric($m['reference_id']) && intval($m['reference_id']) > 0): ?>
                                        <?php 
                                            $tRef = !empty($m['tournee_reference']) 
                                                ? $m['tournee_reference'] 
                                                : (preg_match('/TR\d+/', $m['notes'] ?? '', $matches) ? $matches[0] : 'TR' . str_pad($m['reference_id'], 5, '0', STR_PAD_LEFT));
                                        ?>
                                        <a href="<?= BASE_URL ?>/tournees/details/<?= htmlspecialchars($m['reference_id']) ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 2px; font-weight: 700; color: #059669; font-size: 0.82rem; text-decoration: none;" title="Voir la tournée <?= htmlspecialchars($tRef) ?>">
                                            <i class='bx bx-trip'></i> <?= htmlspecialchars($tRef) ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #64748B; font-weight: 600; font-size: 0.82rem;"><?= htmlspecialchars($m['reference_id']) ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($m['total_cost'] > 0): ?>
                                <strong><?= number_format($m['total_cost'], 0, ',', ' ') ?> F</strong>
                            <?php elseif (empty($m['reference_id'])): ?>
                                <span style="color: #94A3B8;">Échange</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($m['username'])): ?>
                                <span style="padding: 2px 6px; border-radius: 4px; background: #F1F5F9; color: #475569; font-size: 0.8rem; font-weight: 600;">
                                    <i class='bx bx-user'></i> <?= htmlspecialchars($m['username']) ?>
                                </span>
                            <?php else: ?>
                                <span style="color: #94A3B8;">-</span>
                            <?php endif; ?>
                        </td>
                        <td style="color: #64748B; font-size: 0.85rem;"><?= htmlspecialchars($m['notes'] ?: '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
