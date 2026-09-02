<div class="page-title">
    <i class='bx bx-package'></i>
    <h1>Catalogue & Tarifs Produits</h1>
</div>

<!-- SEARCH & FILTER TOOLBAR -->
<div class="card" style="margin-bottom: 20px; padding: 16px 20px; background: white;">
    <form action="<?= BASE_URL ?>/produits" method="GET" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
        
        <!-- SEARCH -->
        <div style="flex: 2; min-width: 200px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Recherche par Nom</label>
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

        <!-- FACTOR (NUMBER OF BOTTLES) -->
        <div style="flex: 1; min-width: 140px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Btl</label>
            <select name="factor" class="form-control" style="font-size: 0.88rem;">
                <option value="">-- Tous --</option>
                <?php foreach ($factors as $f): ?>
                    <option value="<?= $f ?>" <?= (($filterFactor ?? '') == $f) ? 'selected' : '' ?>>
                        <?= $f ?> bouteille(s)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- RETURNABLE FILTER -->
        <div style="flex: 1.2; min-width: 150px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Type Emballage</label>
            <select name="is_returnable" class="form-control" style="font-size: 0.88rem;">
                <option value="">-- Tous --</option>
                <option value="1" <?= (($filterIsReturnable ?? '') === 1 || ($filterIsReturnable ?? '') === '1') ? 'selected' : '' ?>>🟢 Consigné / Verre</option>
                <option value="0" <?= (($filterIsReturnable ?? '') === 0 || ($filterIsReturnable ?? '') === '0') ? 'selected' : '' ?>>⚪ Perdu / Canette / PET</option>
            </select>
        </div>

        <!-- PACKAGING TYPE FILTER -->
        <div style="flex: 1.6; min-width: 200px;">
            <label class="form-label" style="font-size: 0.82rem; margin-bottom: 4px; color: #64748B;">Modèle de Casier Associé</label>
            <select name="packaging_type_id" class="form-control" style="font-size: 0.88rem;">
                <option value="">-- Tous les casiers --</option>
                <?php foreach ($packagingTypes as $pt): ?>
                    <option value="<?= $pt['id'] ?>" <?= (($filterPackagingTypeId ?? '') == $pt['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($pt['company']) ?> &bull; <?= htmlspecialchars($pt['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- BUTTONS -->
        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-accent" style="padding: 8px 16px; font-size: 0.88rem;">
                <i class='bx bx-filter-alt'></i> Filtrer
            </button>
            <?php if (!empty($filterSearch) || !empty($filterCategoryId) || !empty($filterFormatId) || !empty($filterFactor) || ($filterIsReturnable !== null && $filterIsReturnable !== '') || !empty($filterPackagingTypeId)): ?>
                <a href="<?= BASE_URL ?>/produits" class="btn btn-primary" style="padding: 8px 12px; font-size: 0.88rem; background: #64748B;" title="Réinitialiser les filtres">
                    <i class='bx bx-reset'></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div>
            <span><i class='bx bx-list-ul'></i> Liste des Boissons & Conditionnements</span>
            <span style="font-size: 0.82rem; font-weight: 700; color: var(--c-navy); margin-left: 10px; background: #F1F5F9; padding: 3px 8px; border-radius: 6px;">
                <?= count($produits) ?> produit(s)
            </span>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="<?= BASE_URL ?>/emballages" class="btn btn-primary" style="background: var(--c-navy); padding: 7px 12px; font-size: 0.85rem;">
                <i class='bx bx-archive'></i> Gestion Emballages
            </a>
            <a href="<?= BASE_URL ?>/stock/inventaire" class="btn btn-primary" style="background: #0284C7; padding: 7px 12px; font-size: 0.85rem;">
                <i class='bx bx-box'></i> État Réel des Stocks
            </a>
            <a href="<?= BASE_URL ?>/produits/form" class="btn btn-accent"><i class='bx bx-plus'></i> Nouveau Produit</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Désignation Produit</th>
                    <th>Catégorie</th>
                    <th>Format</th>
                    <th>Btl</th>
                    <th>Emballage Consigné</th>
                    <th>Prix Achat (Réf)</th>
                    <th>Prix Vente</th>
                    <th>Prix Demi-Casier</th>
                    <th>Alerte</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($produits)): ?>
                <tr><td colspan="10" style="text-align: center; padding: 25px;">Aucun produit dans le catalogue.</td></tr>
                <?php else: ?>
                    <?php foreach ($produits as $p): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <?php if (!empty($p['short_code'])): ?>
                                    <span style="padding: 1px 6px; background: #EFF6FF; color: #0284C7; border: 1px solid #BFDBFE; border-radius: 4px; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.5px;">
                                        <?= htmlspecialchars($p['short_code']) ?>
                                    </span>
                                <?php endif; ?>
                                <strong><?= htmlspecialchars($p['name']) ?></strong>
                            </div>
                        </td>
                        <td><span style="padding: 2px 8px; background: var(--c-gray-200); border-radius: 10px; font-size: 0.8rem;"><?= htmlspecialchars($p['category_name']) ?></span></td>
                        <td><?= htmlspecialchars($p['format_name']) ?></td>
                        <td><span class="badge" style="font-weight: 600; color: var(--c-navy);"><?= htmlspecialchars($p['factor']) ?></span></td>
                        <td>
                            <?php if (!empty($p['is_returnable'])): ?>
                                <span style="padding: 3px 8px; background: #DCFCE7; color: #16A34A; border-radius: 6px; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;" title="<?= htmlspecialchars($p['packaging_name'] ?? 'Casier consigné') ?>">
                                    🟢 <?= number_format($p['cout_emballage'], 0, ',', ' ') ?> F
                                </span>
                            <?php else: ?>
                                <span style="padding: 3px 8px; background: #F1F5F9; color: #64748B; border-radius: 6px; font-size: 0.8rem;" title="Emballage perdu / Canette / PET">
                                    ⚪ Perdu
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><?= number_format($p['purchase_price'], 0, ',', ' ') ?> FCFA</td>
                        <td style="color: var(--c-amber-dark); font-weight: bold;"><?= number_format($p['price_casier'], 0, ',', ' ') ?> FCFA</td>
                        <td><?= number_format($p['price_demi'], 0, ',', ' ') ?> FCFA</td>
                        <td>
                            <span style="padding: 2px 8px; background: rgba(239, 68, 68, 0.1); color: var(--c-danger); border-radius: 10px; font-size: 0.8rem; font-weight: 600;">
                                &le; <?= htmlspecialchars($p['alert_stock']) ?> 
                            </span>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/produits/form/<?= $p['slug'] ?: $p['id'] ?>" class="btn btn-primary" style="padding: 4px 8px; font-size: 0.8rem;"><i class='bx bx-edit'></i></a>
                            <a href="<?= BASE_URL ?>/produits/delete/<?= $p['slug'] ?: $p['id'] ?>" class="btn btn-primary" style="padding: 4px 8px; font-size: 0.8rem; background-color: var(--c-danger);" onclick="return confirm('Supprimer ce produit du catalogue ?')"><i class='bx bx-trash'></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
