<!-- PAGE HEADER & ACTION CONTROLS -->
<div class="page-title no-print" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div>
        <h1 style="display: flex; align-items: center; gap: 10px; margin: 0; font-size: 1.5rem;">
            <i class='bx bx-box' style="color: var(--c-accent);"></i> État Réel des Stocks & Niveaux d'Inventaire
        </h1>
        <p style="margin: 4px 0 0 0; color: #64748B; font-size: 0.88rem;">
            Inventaire physique, valorisation financière en FCFA et détection des ruptures en temps réel.
        </p>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <button type="button" class="btn btn-accent" onclick="window.print()" style="padding: 8px 16px; font-size: 0.88rem;">
            <i class='bx bx-printer'></i> Imprimer la Fiche d'Inventaire
        </button>
        <a href="<?= BASE_URL ?>/stock" class="btn btn-primary" style="padding: 8px 14px; font-size: 0.88rem; background: var(--c-navy);">
            <i class='bx bx-history'></i> Grand Livre des Mouvements
        </a>
        <?php if (\App\Core\Helper::isAdmin()): ?>
            <a href="<?= BASE_URL ?>/stock/form" class="btn btn-primary" style="padding: 8px 14px; font-size: 0.88rem; background: #64748B;">
                <i class='bx bx-plus-circle'></i> Casse / Ajustement
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- PRINT ONLY HEADER -->
<div class="print-only" style="display: none; margin-bottom: 20px; border-bottom: 2px solid #0F172A; padding-bottom: 12px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <h2 style="margin: 0; font-size: 1.4rem; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px;">FICHE D'ÉTAT DES STOCKS & INVENTAIRE PHYSIQUE</h2>
            <p style="margin: 4px 0 0 0; font-size: 0.9rem; color: #475569;">Dépôt de Boissons - Rapport Officiel d'Inventaire</p>
        </div>
        <div style="text-align: right; font-size: 0.85rem; color: #475569;">
            <p style="margin: 0;"><strong>Date d'Édition :</strong> <?= date('d/m/Y à H:i') ?></p>
            <p style="margin: 2px 0 0 0;"><strong>Filtre Actif :</strong> <?= ($filterStatus === 'rupture') ? 'Articles en Rupture' : (($filterStatus === 'alert') ? 'Articles en Alerte Seuil' : (($filterStatus === 'instock') ? 'Articles Disponibles' : 'Inventaire Complet')) ?></p>
            <p style="margin: 2px 0 0 0;"><strong>Édité par :</strong> <?= htmlspecialchars($_SESSION['user']['full_name'] ?? 'Administrateur') ?></p>
        </div>
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

<!-- EXECUTIVE VALUATION KPI CARDS -->
<div class="dashboard-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
    
    <!-- TOTAL CASIERS -->
    <div class="stat-card" style="background: white; border-radius: 10px; padding: 16px 20px; border: 1px solid #E2E8F0; display: flex; align-items: center; gap: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="width: 44px; height: 44px; border-radius: 8px; background: #EFF6FF; display: flex; align-items: center; justify-content: center; color: #2563EB; font-size: 1.4rem;">
            <i class='bx bx-cube'></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: #64748B; font-weight: 600; text-transform: uppercase;">Stock Magasin</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: #0F172A;"><?= number_format($totalCasiers, 1, ',', ' ') ?> <span style="font-size: 0.8rem; font-weight: 500; color: #64748B;">emb.</span></div>
        </div>
    </div>

    <!-- VALORISATION TOTALE -->
    <div class="stat-card" style="background: white; border-radius: 10px; padding: 16px 20px; border: 1px solid #E2E8F0; display: flex; align-items: center; gap: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="width: 44px; height: 44px; border-radius: 8px; background: #F0FDF4; display: flex; align-items: center; justify-content: center; color: #16A34A; font-size: 1.4rem;">
            <i class='bx bx-money'></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: #64748B; font-weight: 600; text-transform: uppercase;">Valeur Marchande (Achat)</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: #16A34A;"><?= number_format($totalValorisation, 0, ',', ' ') ?> <span style="font-size: 0.8rem; font-weight: 500;">FCFA</span></div>
        </div>
    </div>

    <!-- DISPONIBLES -->
    <div class="stat-card" style="background: white; border-radius: 10px; padding: 16px 20px; border: 1px solid #E2E8F0; display: flex; align-items: center; gap: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="width: 44px; height: 44px; border-radius: 8px; background: #ECFDF5; display: flex; align-items: center; justify-content: center; color: #059669; font-size: 1.4rem;">
            <i class='bx bx-check-shield'></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: #64748B; font-weight: 600; text-transform: uppercase;">Disponibles (> Seuil)</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: #059669;"><?= $countInStock ?> <span style="font-size: 0.8rem; font-weight: 500; color: #64748B;">réf.</span></div>
        </div>
    </div>

    <!-- ALERTE SEUIL -->
    <div class="stat-card" style="background: white; border-radius: 10px; padding: 16px 20px; border: 1px solid #E2E8F0; display: flex; align-items: center; gap: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="width: 44px; height: 44px; border-radius: 8px; background: #FFFBEB; display: flex; align-items: center; justify-content: center; color: #D97706; font-size: 1.4rem;">
            <i class='bx bx-error'></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: #64748B; font-weight: 600; text-transform: uppercase;">Seuil Critique</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: #D97706;"><?= $countAlert ?> <span style="font-size: 0.8rem; font-weight: 500; color: #64748B;">réf.</span></div>
        </div>
    </div>

    <!-- RUPTURE -->
    <div class="stat-card" style="background: white; border-radius: 10px; padding: 16px 20px; border: 1px solid #E2E8F0; display: flex; align-items: center; gap: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="width: 44px; height: 44px; border-radius: 8px; background: #FEF2F2; display: flex; align-items: center; justify-content: center; color: #DC2626; font-size: 1.4rem;">
            <i class='bx bx-x-circle'></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: #64748B; font-weight: 600; text-transform: uppercase;">En Rupture</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: #DC2626;"><?= $countRupture ?> <span style="font-size: 0.8rem; font-weight: 500; color: #64748B;">réf.</span></div>
        </div>
    </div>

</div>

<!-- PRINTABLE STATUS SHORTCUT TABS & SEARCH TOOLBAR -->
<div class="card no-print" style="margin-bottom: 20px; padding: 16px 20px; background: white;">
    
    <!-- 1-CLICK STATUS SHORTCUTS -->
    <div style="display: flex; gap: 10px; margin-bottom: 15px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9; align-items: center; flex-wrap: wrap;">
        <span style="font-size: 0.82rem; font-weight: 700; color: #64748B; margin-right: 5px;">Filtrer par Statut :</span>
        
        <a href="<?= BASE_URL ?>/stock/inventaire?status=all<?= !empty($filterSearch) ? '&search='.urlencode($filterSearch) : '' ?><?= !empty($filterCategoryId) ? '&category_id='.$filterCategoryId : '' ?><?= !empty($filterFormatId) ? '&format_id='.$formatId : '' ?>" 
           class="btn <?= ($filterStatus === 'all' || empty($filterStatus)) ? 'btn-accent' : 'btn-primary' ?>" 
           style="padding: 5px 12px; font-size: 0.82rem; <?= ($filterStatus !== 'all' && !empty($filterStatus)) ? 'background: #F1F5F9; color: #334155;' : '' ?>">
            <i class='bx bx-list-ul'></i> Tous les Produits (<?= $countTotal ?>)
        </a>

        <a href="<?= BASE_URL ?>/stock/inventaire?status=instock<?= !empty($filterSearch) ? '&search='.urlencode($filterSearch) : '' ?><?= !empty($filterCategoryId) ? '&category_id='.$filterCategoryId : '' ?><?= !empty($filterFormatId) ? '&format_id='.$formatId : '' ?>" 
           class="btn <?= ($filterStatus === 'instock') ? 'btn-accent' : 'btn-primary' ?>" 
           style="padding: 5px 12px; font-size: 0.82rem; <?= ($filterStatus !== 'instock') ? 'background: #F0FDF4; color: #16A34A; border: 1px solid #DCFCE7;' : '' ?>">
            🟢 En Stock (<?= $countInStock ?>)
        </a>

        <a href="<?= BASE_URL ?>/stock/inventaire?status=alert<?= !empty($filterSearch) ? '&search='.urlencode($filterSearch) : '' ?><?= !empty($filterCategoryId) ? '&category_id='.$filterCategoryId : '' ?><?= !empty($filterFormatId) ? '&format_id='.$formatId : '' ?>" 
           class="btn <?= ($filterStatus === 'alert') ? 'btn-accent' : 'btn-primary' ?>" 
           style="padding: 5px 12px; font-size: 0.82rem; <?= ($filterStatus !== 'alert') ? 'background: #FFFBEB; color: #D97706; border: 1px solid #FEF3C7;' : '' ?>">
            ⚠️ Alerte Seuil (<?= $countAlert ?>)
        </a>

        <a href="<?= BASE_URL ?>/stock/inventaire?status=rupture<?= !empty($filterSearch) ? '&search='.urlencode($filterSearch) : '' ?><?= !empty($filterCategoryId) ? '&category_id='.$filterCategoryId : '' ?><?= !empty($filterFormatId) ? '&format_id='.$formatId : '' ?>" 
           class="btn <?= ($filterStatus === 'rupture') ? 'btn-accent' : 'btn-primary' ?>" 
           style="padding: 5px 12px; font-size: 0.82rem; <?= ($filterStatus !== 'rupture') ? 'background: #FEF2F2; color: #DC2626; border: 1px solid #FEE2E2;' : '' ?>">
            ⛔ En Rupture (<?= $countRupture ?>)
        </a>
    </div>

    <!-- MULTI-CRITERIA SEARCH FORM -->
    <form action="<?= BASE_URL ?>/stock/inventaire" method="GET" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
        <input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus ?? 'all') ?>">

        <!-- SEARCH -->
        <div style="flex: 2; min-width: 200px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Recherche Produit</label>
            <div style="position: relative;">
                <input type="text" name="search" class="form-control" placeholder="Rechercher une boisson..." value="<?= htmlspecialchars($filterSearch ?? '') ?>" style="padding-left: 32px; font-size: 0.88rem;">
                <i class='bx bx-search' style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 1.1rem;"></i>
            </div>
        </div>

        <!-- CATEGORY -->
        <div style="flex: 1.5; min-width: 170px;">
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

        <!-- FORMAT -->
        <div style="flex: 1.2; min-width: 150px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Format / Volume</label>
            <select name="format_id" class="form-control" style="font-size: 0.88rem;">
                <option value="">-- Tous les formats --</option>
                <?php foreach ($formats as $fmt): ?>
                    <option value="<?= $fmt['id'] ?>" <?= (($filterFormatId ?? '') == $fmt['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($fmt['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- BUTTONS -->
        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-accent" style="padding: 8px 16px; font-size: 0.88rem;">
                <i class='bx bx-filter-alt'></i> Filtrer
            </button>
            <?php if (!empty($filterSearch) || !empty($filterCategoryId) || !empty($filterFormatId) || ($filterStatus !== 'all' && !empty($filterStatus))): ?>
                <a href="<?= BASE_URL ?>/stock/inventaire" class="btn btn-primary" style="padding: 8px 12px; font-size: 0.88rem; background: #64748B;" title="Réinitialiser tous les filtres">
                    <i class='bx bx-reset'></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- INVENTORY TABLE -->
<div class="card printable-card">
    <div class="card-header no-print" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span><i class='bx bx-table'></i> État Physique & Valorisation des Stocks</span>
            <span style="font-size: 0.82rem; font-weight: 700; color: var(--c-navy); background: #F1F5F9; padding: 3px 8px; border-radius: 6px;">
                <?= count($stockItems) ?> boisson(s) affichée(s)
            </span>
        </div>
        <div style="font-size: 0.85rem; color: #64748B;">
            Valorisation de la sélection : <strong style="color: var(--c-success); font-size: 0.95rem;"><?= number_format($filteredValorisation, 0, ',', ' ') ?> FCFA</strong> (<?= number_format($filteredCasiers, 1, ',', ' ') ?> emb.)
        </div>
    </div>

    <div class="table-responsive table-sticky-container">
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left;">
                    <th style="padding: 10px 12px;">Désignation Produit</th>
                    <th style="padding: 10px 12px;">Catégorie</th>
                    <th style="padding: 10px 12px;">Format</th>
                    <th style="padding: 10px 12px;">Btl/Emb.</th>
                    <th style="padding: 10px 12px;">Prix Achat Réf</th>
                    <th style="padding: 10px 12px; text-align: center;">Stock Casiers/Packs</th>
                    <th style="padding: 10px 12px;">Équivalent Détaillé</th>
                    <th style="padding: 10px 12px; text-align: right;">Valeur Stock (FCFA)</th>
                    <th style="padding: 10px 12px; text-align: center;">Statut</th>
                    <th class="no-print" style="padding: 10px 12px; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($stockItems)): ?>
                <tr>
                    <td colspan="10" style="text-align: center; padding: 35px; color: #64748B;">
                        <i class='bx bx-info-circle' style="font-size: 2rem; color: #94A3B8; display: block; margin-bottom: 8px;"></i>
                        Aucun produit ne correspond aux critères d'inventaire sélectionnés.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($stockItems as $item): 
                        $stock = floatval($item['current_stock']);
                        $alert = floatval($item['alert_stock']);
                        $factor = max(1, intval($item['factor'] ?? 24));
                        $purchasePrice = floatval($item['purchase_price']);
                        $valeurLigne = max(0, $stock) * $purchasePrice;

                        // Detailed bottle computation
                        $fullCrates = floor(max(0, $stock));
                        $fraction = max(0, $stock) - $fullCrates;
                        $looseBottles = round($fraction * $factor);

                        // Status badge determination
                        if ($stock <= 0) {
                            $statusBadge = "<span style='padding: 3px 8px; background: #FEE2E2; color: #991B1B; border-radius: 12px; font-size: 0.75rem; font-weight: 700;'>⛔ Rupture</span>";
                            $rowBg = "background: rgba(254, 242, 242, 0.35);";
                        } elseif ($stock <= $alert) {
                            $statusBadge = "<span style='padding: 3px 8px; background: #FEF3C7; color: #92400E; border-radius: 12px; font-size: 0.75rem; font-weight: 700;'>⚠️ Alerte (&le; {$alert})</span>";
                            $rowBg = "background: rgba(254, 243, 199, 0.35);";
                        } else {
                            $statusBadge = "<span style='padding: 3px 8px; background: #DCFCE7; color: #166534; border-radius: 12px; font-size: 0.75rem; font-weight: 700;'>🟢 Disponible</span>";
                            $rowBg = "background: rgba(240, 253, 244, 0.45);";
                        }
                    ?>
                        <td style="padding: 10px 12px;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <?php if (!empty($item['short_code'])): ?>
                                    <span style="padding: 1px 6px; background: #EFF6FF; color: #0284C7; border: 1px solid #BFDBFE; border-radius: 4px; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.5px;">
                                        <?= htmlspecialchars($item['short_code']) ?>
                                    </span>
                                <?php endif; ?>
                                <strong><?= htmlspecialchars($item['name']) ?></strong>
                            </div>
                        </td>
                        <td style="padding: 10px 12px;"><span style="padding: 2px 8px; background: #F1F5F9; border-radius: 8px; font-size: 0.78rem;"><?= htmlspecialchars($item['category_name']) ?></span></td>
                        <td style="padding: 10px 12px; font-size: 0.85rem;"><?= htmlspecialchars($item['format_name']) ?></td>
                        <td style="padding: 10px 12px; font-size: 0.85rem;"><span class="badge" style="color: var(--c-navy); font-weight: 600;"><?= $factor ?></span></td>
                        <td style="padding: 10px 12px; font-size: 0.85rem;"><?= number_format($purchasePrice, 0, ',', ' ') ?> FCFA</td>
                        <td style="padding: 10px 12px; text-align: center; font-size: 1.05rem; font-weight: 700; color: <?= ($stock <= 0) ? '#DC2626' : (($stock <= $alert) ? '#D97706' : 'var(--c-navy)') ?>;">
                            <?= number_format($stock, 1, ',', ' ') ?>
                        </td>
                        <td style="padding: 10px 12px; font-size: 0.82rem; color: #475569;">
                            <?php if ($stock <= 0): ?>
                                <span style="color: #94A3B8;">0 bouteille</span>
                            <?php else: ?>
                                <strong><?= $fullCrates ?></strong> casier(s) <?= ($looseBottles > 0) ? " + <strong style='color: var(--c-accent);'>{$looseBottles}</strong> btls" : "" ?>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 10px 12px; text-align: right; font-weight: 700; color: var(--c-success); font-size: 0.9rem;">
                            <?= number_format($valeurLigne, 0, ',', ' ') ?> FCFA
                        </td>
                        <td style="padding: 10px 12px; text-align: center;">
                            <?= $statusBadge ?>
                        </td>
                        <td class="no-print" style="padding: 10px 12px; text-align: center; white-space: nowrap;">
                            <a href="<?= BASE_URL ?>/stock?product_id=<?= $item['id'] ?>" class="btn btn-primary" style="padding: 4px 8px; font-size: 0.78rem; background: var(--c-navy);" title="Voir les mouvements de ce produit">
                                <i class='bx bx-history'></i>
                            </a>
                            <?php if (\App\Core\Helper::isAdmin()): ?>
                                <a href="<?= BASE_URL ?>/stock/form?product_id=<?= $item['id'] ?>" class="btn btn-primary" style="padding: 4px 8px; font-size: 0.78rem; background: #64748B;" title="Déclarer une casse ou ajustement">
                                    <i class='bx bx-wrench'></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr style="background: #F1F5F9; font-weight: 700; border-top: 2px solid #CBD5E1;">
                    <td colspan="5" style="padding: 12px; text-align: right;">TOTAL INVENTAIRE SÉLECTIONNÉ :</td>
                    <td style="padding: 12px; text-align: center; font-size: 1.1rem; color: var(--c-navy);"><?= number_format($filteredCasiers, 1, ',', ' ') ?> emb.</td>
                    <td></td>
                    <td style="padding: 12px; text-align: right; font-size: 1.1rem; color: var(--c-success);"><?= number_format($filteredValorisation, 0, ',', ' ') ?> FCFA</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- PRINT FOOTER (SIGNATURES) -->
<div class="print-only" style="display: none; margin-top: 40px; page-break-inside: avoid;">
    <div style="display: flex; justify-content: space-between; gap: 50px;">
        <div style="flex: 1; border-top: 1px solid #475569; padding-top: 8px; text-align: center;">
            <p style="margin: 0; font-size: 0.85rem; font-weight: 700;">Visa du Gestionnaire de Dépôt / Magasinier</p>
            <p style="margin: 40px 0 0 0; font-size: 0.75rem; color: #64748B;">Date & Signature</p>
        </div>
        <div style="flex: 1; border-top: 1px solid #475569; padding-top: 8px; text-align: center;">
            <p style="margin: 0; font-size: 0.85rem; font-weight: 700;">Visa de la Direction / Contrôleur</p>
            <p style="margin: 40px 0 0 0; font-size: 0.75rem; color: #64748B;">Date & Signature</p>
        </div>
    </div>
</div>

<!-- PRINT CSS STYLING -->
<style>
@media print {
    .no-print, .sidebar, .header, nav, footer, .sidebar-backdrop {
        display: none !important;
    }
    .main-content {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    .print-only {
        display: block !important;
    }
    .printable-card {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
    body {
        background: white !important;
        color: black !important;
        font-size: 10pt !important;
    }
    table {
        border-collapse: collapse !important;
        width: 100% !important;
    }
    th, td {
        border: 1px solid #CBD5E1 !important;
        padding: 6px 8px !important;
        font-size: 9pt !important;
    }
    th {
        background-color: #F1F5F9 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .badge, span {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>
