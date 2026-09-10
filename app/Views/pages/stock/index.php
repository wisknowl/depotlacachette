<div class="page-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div>
        <h1 style="display: flex; align-items: center; gap: 10px; margin: 0; font-size: 1.5rem;">
            <i class='bx bx-history' style="color: var(--c-navy);"></i> Grand Livre des Mouvements de Stock
        </h1>
        <p style="margin: 4px 0 0 0; color: #64748B; font-size: 0.88rem;">
            Journal chronologique et traçabilité intégrale des entrées, sorties, casses et ajustements.
        </p>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="<?= BASE_URL ?>/stock/inventaire" class="btn btn-accent" style="background: #0284C7;padding: 8px 16px; font-size: 0.88rem;">
            <i class='bx bx-box'></i> État Réel des Stocks (Inventaire)
        </a>
        <?php if (\App\Core\Helper::isAdmin()): ?>
            <a href="<?= BASE_URL ?>/stock/form" class="btn btn-primary" style="padding: 8px 14px; font-size: 0.88rem; background: #64748B;">
                <i class='bx bx-wrench'></i> Casse / Ajustement
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
    <div style="background: #FEE2E2; border-left: 4px solid var(--c-danger); color: #991B1B; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
        <i class='bx bx-error-circle' style="font-size: 1.4rem;"></i>
        <span><?= htmlspecialchars($flash_error) ?></span>
    </div>
<?php endif; ?>

<!-- STATS SUMMARY CARDS -->
<div class="dashboard-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 20px;">
    <div class="stat-card" style="background: white; border-radius: 10px; padding: 16px 20px; border: 1px solid #E2E8F0; display: flex; align-items: center; gap: 15px;">
        <div style="width: 44px; height: 44px; border-radius: 8px; background: #ECFDF5; display: flex; align-items: center; justify-content: center; color: #059669; font-size: 1.4rem;">
            <i class='bx bx-down-arrow-circle'></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: #64748B; font-weight: 600; text-transform: uppercase;">Total Entrées (Période)</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: #059669;">+<?= number_format($totalIn, 1, ',', ' ') ?> <span style="font-size: 0.8rem; font-weight: 500;">btls</span></div>
        </div>
    </div>

    <div class="stat-card" style="background: white; border-radius: 10px; padding: 16px 20px; border: 1px solid #E2E8F0; display: flex; align-items: center; gap: 15px;">
        <div style="width: 44px; height: 44px; border-radius: 8px; background: #FEF2F2; display: flex; align-items: center; justify-content: center; color: #DC2626; font-size: 1.4rem;">
            <i class='bx bx-up-arrow-circle'></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: #64748B; font-weight: 600; text-transform: uppercase;">Total Sorties (Période)</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: #DC2626;">-<?= number_format($totalOut, 1, ',', ' ') ?> <span style="font-size: 0.8rem; font-weight: 500;">btls</span></div>
        </div>
    </div>

    <div class="stat-card" style="background: white; border-radius: 10px; padding: 16px 20px; border: 1px solid #E2E8F0; display: flex; align-items: center; gap: 15px;">
        <div style="width: 44px; height: 44px; border-radius: 8px; background: #EFF6FF; display: flex; align-items: center; justify-content: center; color: #2563EB; font-size: 1.4rem;">
            <i class='bx bx-transfer'></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: #64748B; font-weight: 600; text-transform: uppercase;">Flux Net Période</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: <?= (($totalIn - $totalOut) >= 0) ? '#059669' : '#DC2626' ?>;">
                <?= (($totalIn - $totalOut) >= 0 ? '+' : '') . number_format($totalIn - $totalOut, 1, ',', ' ') ?> <span style="font-size: 0.8rem; font-weight: 500;">btls</span>
            </div>
        </div>
    </div>
</div>

<!-- MOVEMENTS FILTER TOOLBAR -->
<div class="card" style="margin-bottom: 20px; padding: 16px 20px; background: white;">
    <form method="GET" action="<?= BASE_URL ?>/stock" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
        
        <!-- SEARCH -->
        <div style="flex: 2; min-width: 200px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Recherche Libre</label>
            <div style="position: relative;">
                <input type="text" name="search" class="form-control" placeholder="Nom boisson, V00018, ref..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>" style="padding-left: 32px; font-size: 0.88rem;">
                <i class='bx bx-search' style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 1.1rem;"></i>
            </div>
        </div>

        <!-- PRODUCT -->
        <div style="flex: 1.8; min-width: 180px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Filtrer par Boisson</label>
            <select name="product_id" class="form-control" style="font-size: 0.88rem;">
                <option value="">-- Tous les produits --</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= (($filters['product_id'] ?? '') == $p['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- MOVEMENT TYPE -->
        <div style="flex: 1.4; min-width: 160px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Type de Mouvement</label>
            <select name="movement_type_id" class="form-control" style="font-size: 0.88rem;">
                <option value="">-- Tous les types --</option>
                <?php foreach ($allMovementTypes as $mt): ?>
                    <option value="<?= $mt['id'] ?>" <?= (($filters['movement_type_id'] ?? '') == $mt['id']) ? 'selected' : '' ?>>
                        <?= $mt['direction'] === 'IN' ? '🟢' : '🔴' ?> <?= htmlspecialchars($mt['name']) ?>
                    </option>
                <?php endforeach; ?>
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
            <?php if (!empty($filters['search']) || !empty($filters['product_id']) || !empty($filters['movement_type_id']) || !empty($filters['start_date']) || !empty($filters['end_date']) || !empty($filterPeriod)): ?>
                <a href="<?= BASE_URL ?>/stock" class="btn btn-primary" style="padding: 8px 12px; font-size: 0.88rem; background: #64748B;" title="Réinitialiser les filtres">
                    <i class='bx bx-reset'></i>
                </a>
            <?php endif; ?>
        </div>
    </form>

    <!-- QUICK PERIOD SHORTCUTS -->
    <div style="display: flex; gap: 8px; margin-top: 12px; padding-top: 10px; border-top: 1px solid #F1F5F9; align-items: center; flex-wrap: wrap;">
        <span style="font-size: 0.8rem; font-weight: 700; color: #64748B;">Raccourcis :</span>
        <a href="<?= BASE_URL ?>/stock?period=today" class="btn <?= (($filterPeriod ?? '') === 'today') ? 'btn-accent' : 'btn-primary' ?>" style="padding: 3px 8px; font-size: 0.75rem; <?= (($filterPeriod ?? '') !== 'today') ? 'background: #F1F5F9; color: #334155;' : '' ?>">
            Aujourd'hui
        </a>
        <a href="<?= BASE_URL ?>/stock?period=week" class="btn <?= (($filterPeriod ?? '') === 'week') ? 'btn-accent' : 'btn-primary' ?>" style="padding: 3px 8px; font-size: 0.75rem; <?= (($filterPeriod ?? '') !== 'week') ? 'background: #F1F5F9; color: #334155;' : '' ?>">
            Cette Semaine
        </a>
        <a href="<?= BASE_URL ?>/stock?period=month" class="btn <?= (($filterPeriod ?? '') === 'month') ? 'btn-accent' : 'btn-primary' ?>" style="padding: 3px 8px; font-size: 0.75rem; <?= (($filterPeriod ?? '') !== 'month') ? 'background: #F1F5F9; color: #334155;' : '' ?>">
            Ce Mois
        </a>
        <a href="<?= BASE_URL ?>/stock" class="btn btn-primary" style="padding: 3px 8px; font-size: 0.75rem; background: #F1F5F9; color: #334155;">
            Tous les mouvements
        </a>
    </div>
</div>

<style>
.table-sticky-container {
    max-height: 650px;
    overflow-y: auto;
    overflow-x: auto;
}
.table-sticky-container table thead th {
    position: sticky;
    top: 0;
    z-index: 5;
    background: var(--c-navy) !important;
    color: #FFFFFF !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.12);
}
</style>

<!-- MOVEMENTS LEDGER TABLE -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span><i class='bx bx-list-ul'></i> Écritures du Grand Livre des Mouvements</span>
            <span style="font-size: 0.82rem; font-weight: 700; color: var(--c-navy); background: #F1F5F9; padding: 3px 8px; border-radius: 6px;">
                <?= count($movements) ?> écriture(s)
            </span>
        </div>
    </div>
    
    <div class="table-responsive table-sticky-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Date & Heure</th>
                    <th>Produit</th>
                    <th>Type Mouvement</th>
                    <th>Conditionnement</th>
                    <th>Quantité Reçue/Sortie</th>
                    <th>Équivalent Volume</th>
                    <th>Document / Référence</th>
                    <th>Auteur</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($movements)): ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 30px; color: #64748B;">
                        <i class='bx bx-info-circle' style="font-size: 1.8rem; color: #94A3B8; display: block; margin-bottom: 6px;"></i>
                        Aucun mouvement de stock enregistré pour cette sélection.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($movements as $m): ?>
                    <?php 
                        $isIncoming = ($m['direction'] === 'IN');
                        $isCancelled = !empty($m['is_cancelled']);
                        $badgeBg = $isCancelled ? '#F1F5F9; color: #94A3B8; text-decoration: line-through;' : ($isIncoming ? '#DCFCE7; color: #166534;' : '#FEE2E2; color: #991B1B;');
                        $pkgLabel = \App\Core\Helper::formatPackagingLabel($m['category_name'] ?? '', $m['format_name'] ?? '', $m['format_type'] ?? 'casier');
                        $unit = \App\Core\Helper::getPackagingUnit($m['category_name'] ?? '', $m['format_name'] ?? '');
                    ?>
                    <tr style="<?= $isCancelled ? 'opacity: 0.6; background: #F8FAFC;' : '' ?>">
                        <td><?= date('d/m/Y H:i', strtotime(!empty($m['created_at']) ? $m['created_at'] : $m['movement_date'])) ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <?php if (!empty($m['short_code'])): ?>
                                    <span style="padding: 1px 6px; background: #EFF6FF; color: #0284C7; border: 1px solid #BFDBFE; border-radius: 4px; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.5px;">
                                        <?= htmlspecialchars($m['short_code']) ?>
                                    </span>
                                <?php endif; ?>
                                <strong style="<?= $isCancelled ? 'text-decoration: line-through; color: #94A3B8;' : '' ?>">
                                    <?= htmlspecialchars($m['product_name']) ?>
                                </strong>
                            </div>
                        </td>
                        <td>
                            <span style="padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; background: <?= $badgeBg ?>">
                                <?= $isIncoming ? '⬇ Entrée : ' : '⬆ Sortie : ' ?>
                                <?= htmlspecialchars($m['movement_type'] ?: 'Mouvement') ?>
                            </span>
                        </td>
                        <td>
                            <span style="padding: 2px 8px; border-radius: 8px; font-size: 0.78rem; font-weight: 600; background: <?= ($m['format_type'] ?? '') === 'demi' ? '#FEF3C7; color: #92400E;' : '#E2E8F0; color: #334155;' ?>">
                                <?= htmlspecialchars($pkgLabel) ?>
                            </span>
                        </td>
                        <td style="font-weight: 700; <?= $isCancelled ? 'text-decoration: line-through;' : '' ?>">
                            <?= number_format($m['quantity'], 0) ?>
                        </td>
                        <td style="font-weight: 800; color: <?= $isCancelled ? '#94A3B8' : ($isIncoming ? '#16A34A' : '#DC2626') ?>; <?= $isCancelled ? 'text-decoration: line-through;' : '' ?>">
                            <?= $isIncoming ? '+' : '-' ?><?= number_format($m['stock_equivalent'], 1) ?> <?= htmlspecialchars($unit['unit_name']) ?>
                        </td>
                        <td>
                            <?php if ($m['source_type'] === 'Sale' && !empty($m['source_id'])): ?>
                                <a href="<?= BASE_URL ?>/ventes/invoice/<?= htmlspecialchars($m['source_id']) ?>" style="font-weight: 700; text-decoration: none; color: var(--c-navy);">
                                    <i class='bx bx-receipt'></i> <?= htmlspecialchars($m['source_id']) ?>
                                </a>
                            <?php elseif ($m['source_type'] === 'Purchase' && !empty($m['source_id'])): ?>
                                <a href="<?= BASE_URL ?>/achats/order/<?= htmlspecialchars($m['source_id']) ?>" style="font-weight: 700; text-decoration: none; color: var(--c-navy);">
                                    <i class='bx bx-file-blank'></i> <?= htmlspecialchars($m['source_id']) ?>
                                </a>
                            <?php elseif ((str_starts_with($m['source_type'], 'Tournee') || in_array($m['source_type'], ['Tournee', 'TourneeReturn', 'TourneeCancellation', 'TourneeReturnReversal'])) && !empty($m['source_id'])): ?>
                                <?php 
                                    $tourneeRef = !empty($m['tournee_reference']) 
                                        ? $m['tournee_reference'] 
                                        : (!empty($m['reference']) && str_starts_with($m['reference'], 'TR') ? $m['reference'] : 'TR' . str_pad($m['source_id'], 5, '0', STR_PAD_LEFT));
                                ?>
                                <a href="<?= BASE_URL ?>/tournees/details/<?= htmlspecialchars($m['source_id']) ?>" style="font-weight: 700; text-decoration: none; color: #059669;" title="Voir les détails de la tournée <?= htmlspecialchars($tourneeRef) ?>">
                                    <i class='bx bx-trip'></i> <?= htmlspecialchars($tourneeRef) ?>
                                </a>
                            <?php else: ?>
                                <span><?= htmlspecialchars($m['reference'] ?: ($m['source_id'] ?: '-')) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($m['username'] ?: 'Admin') ?></td>
                        <td style="text-align: center; white-space: nowrap;">
                            <?php if ($isCancelled): ?>
                                <span style="padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; background: #F1F5F9; color: #94A3B8;">
                                    <i class='bx bx-x'></i> Annulé
                                </span>
                            <?php elseif ($m['source_type'] === 'AdjustmentCancellation'): ?>
                                <span style="padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; background: #FEF3C7; color: #D97706;" title="Contre-passation automatique suite à une annulation">
                                    <i class='bx bx-undo'></i> Contre-passation
                                </span>
                            <?php elseif ($m['source_type'] === 'Sale' || $m['source_type'] === 'SaleCancellation'): ?>
                                <span style="font-size: 0.75rem; color: #94A3B8; font-weight: 600;">Géré via Ventes</span>
                            <?php elseif ($m['source_type'] === 'Purchase' || $m['source_type'] === 'PurchaseCancellation'): ?>
                                <span style="font-size: 0.75rem; color: #94A3B8; font-weight: 600;">Géré via Achats</span>
                            <?php elseif (str_starts_with($m['source_type'], 'Tournee') || in_array($m['source_type'], ['Tournee', 'TourneeReturn', 'TourneeCancellation', 'TourneeReturnReversal'])): ?>
                                <?php 
                                    $tourneeRef = !empty($m['tournee_reference']) 
                                        ? $m['tournee_reference'] 
                                        : (!empty($m['reference']) && str_starts_with($m['reference'], 'TR') ? $m['reference'] : 'TR' . str_pad($m['source_id'], 5, '0', STR_PAD_LEFT));
                                ?>
                                <?php if (!empty($m['source_id'])): ?>
                                    <a href="<?= BASE_URL ?>/tournees/details/<?= htmlspecialchars($m['source_id']) ?>" style="font-size: 0.75rem; color: #059669; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 2px;" title="Géré via la tournée <?= htmlspecialchars($tourneeRef) ?>">
                                        <i class='bx bx-trip'></i> Tournée <?= htmlspecialchars($tourneeRef) ?>
                                    </a>
                                <?php else: ?>
                                    <span style="font-size: 0.75rem; color: #059669; font-weight: 600;">Géré via Tournée</span>
                                <?php endif; ?>
                            <?php elseif ($m['source_type'] === 'InitialStock' || empty($m['source_type'])): ?>
                                <span style="font-size: 0.75rem; color: #94A3B8; font-weight: 600;">Stock Initial</span>
                            <?php elseif ($m['source_type'] === 'Adjustment' && \App\Core\Helper::isAdmin()): ?>
                                <a href="<?= BASE_URL ?>/stock/cancel/<?= $m['id'] ?>" 
                                   class="btn btn-primary" 
                                   style="padding: 3px 8px; font-size: 0.78rem; background-color: var(--c-danger); border-radius: 6px;" 
                                   onclick="return confirm('Annuler ce mouvement d\'ajustement de stock #<?= $m['id'] ?> (<?= htmlspecialchars(addslashes($m['product_name'])) ?> : <?= $m['quantity'] ?> <?= htmlspecialchars(addslashes($pkgLabel)) ?>) ?\n\nUne contre-passation inverse sera créée pour rééquilibrer le stock et le P&L.')" 
                                   title="Annuler ce mouvement d'ajustement / casse">
                                    <i class='bx bx-x-circle'></i> Annuler
                                </a>
                            <?php else: ?>
                                <span style="color: #CBD5E1;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>