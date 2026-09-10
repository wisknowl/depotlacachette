<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div class="page-title" style="margin-bottom: 0;">
        <i class='bx bx-archive'></i>
        <h1>Gestion du Parc d'Emballages (Casiers & Bouteilles Consignées)</h1>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="<?= BASE_URL ?>/emballages/ajustement" class="btn btn-primary" style="background: #64748B; display: flex; align-items: center; gap: 6px; font-weight: 600;">
            <i class='bx bx-slider-alt'></i> Ajustement Stock Emballages
        </a>
        <a href="<?= BASE_URL ?>/emballages/achat" class="btn btn-accent" style="display: flex; align-items: center; gap: 6px; font-weight: 700;">
            <i class='bx bx-cart-add'></i> Acheter des Emballages Vides
        </a>
        <a href="<?= BASE_URL ?>/emballages/mouvements" class="btn btn-primary" style="background-color: var(--c-navy); display: flex; align-items: center; gap: 6px; font-weight: 600;">
            <i class='bx bx-history'></i> Journal Mouvements
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

<!-- 5 EXECUTIVE KPI SUMMARY CARDS -->
<div class="dashboard-grid" style="margin-bottom: 25px;">
    
    <!-- 1. CASIERS PLEINS -->
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(16, 185, 129, 0.15); color: var(--c-success);">
            <i class='bx bx-package'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($summary['total_full_crates'], ($summary['total_full_crates'] == intval($summary['total_full_crates']) ? 0 : 1), ',', ' ') ?></div>
            <div class="stat-label">Casiers Pleins (En Magasin)</div>
        </div>
    </div>

    <!-- 2. CASIERS VIDES AU DÉPÔT -->
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(2, 132, 199, 0.15); color: #0284C7;">
            <i class='bx bx-box'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="color: #0284C7;"><?= number_format($summary['total_empty_crates'], 0, ',', ' ') ?></div>
            <div class="stat-label">Casiers Vides Disponibles (Échange)</div>
        </div>
    </div>

    <!-- 3. CASIERS CHEZ LES CLIENTS -->
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(245, 158, 11, 0.15); color: #D97706;">
            <i class='bx bx-user-check'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="color: #D97706;">
                <?= number_format($summary['total_client_crates'], 0, ',', ' ') ?>
                <?php if ($summary['total_client_bottles'] > 0): ?>
                    <span style="font-size: 0.85rem; font-weight: 500; color: #64748B;">(+<?= $summary['total_client_bottles'] ?> btl)</span>
                <?php endif; ?>
            </div>
            <div class="stat-label">Casiers Détenus par Clients</div>
        </div>
    </div>

    <!-- 4. CASIERS DUS AUX FOURNISSEURS -->
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(220, 38, 38, 0.12); color: #DC2626;">
            <i class='bx bx-buildings'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="color: #DC2626;"><?= number_format($summary['total_supplier_crates'], 0, ',', ' ') ?></div>
            <div class="stat-label">Dettes Casiers Fournisseurs</div>
        </div>
    </div>

    <!-- 5. VALEUR DU PARC EN FCFA -->
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(13, 27, 42, 0.1); color: var(--c-navy);">
            <i class='bx bx-money'></i>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="color: var(--c-navy); font-size: 1.25rem;">
                <?= number_format($summary['total_asset_value'], 0, ',', ' ') ?> F
            </div>
            <div class="stat-label">Valeur du Capital Emballages</div>
        </div>
    </div>
</div>

<!-- SECTION 1: ÉTAT DU STOCK PAR MODÈLE DE CASIER -->
<div class="card" style="margin-bottom: 25px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <span><i class='bx bx-list-check'></i> Répartition du Stock d'Emballages par Modèle & Brasserie</span>
        <button type="button" class="btn btn-primary" onclick="openRestitutionModal()" style="background: #16A34A; padding: 6px 12px; font-size: 0.85rem;">
            <i class='bx bx-download'></i> Restitution Emballages Client
        </button>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Modèle de Casier</th>
                    <th>Brasserie</th>
                    <th>Couleur</th>
                    <th>Capacité</th>
                    <th style="text-align: center;">Pleins au Dépôt</th>
                    <th style="text-align: center; background: #64748B;">Vides au Dépôt</th>
                    <th style="text-align: center;">Chez les Clients</th>
                    <th style="text-align: center;">Dû aux Brasseries</th>
                    <th style="text-align: right;">Total Détenu</th>
                    <th style="text-align: right;">Valeur Estimée</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($summary['packaging_types'] as $pt): ?>
                <?php 
                    $totalOwned = floatval($pt['full_crates_stock']) + floatval($pt['empty_crates']) + floatval($pt['client_crates_due']) - floatval($pt['supplier_crates_due']);
                    $valLine = max(0, $totalOwned) * floatval($pt['default_cost']);
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($pt['name']) ?></strong></td>
                    <td>
                        <span style="padding: 3px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; background: #F1F5F9; color: var(--c-navy);">
                            <?= htmlspecialchars($pt['company']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($pt['color']) ?></td>
                    <td><?= htmlspecialchars($pt['bottles_per_crate']) ?> btls (<?= htmlspecialchars($pt['bottle_volume']) ?>)</td>
                    <td style="text-align: center; font-weight: 600; color: var(--c-success);">
                        <?= number_format($pt['full_crates_stock'], ($pt['full_crates_stock'] == intval($pt['full_crates_stock']) ? 0 : 1), ',', ' ') ?>
                    </td>
                    <td style="text-align: center; font-weight: 700; color: #0284C7; background: #F0F9FF;">
                        <?= number_format($pt['empty_crates'], 0, ',', ' ') ?>
                        <?php if ($pt['loose_bottles'] > 0): ?>
                            <small style="display: block; font-size: 0.75rem; color: #64748B;">+<?= $pt['loose_bottles'] ?> btl vrac</small>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center; font-weight: 600; color: #D97706;">
                        <?= number_format($pt['client_crates_due'], 0, ',', ' ') ?>
                        <?php if ($pt['client_bottles_due'] > 0): ?>
                            <small style="display: block; font-size: 0.75rem; color: #D97706;">+<?= $pt['client_bottles_due'] ?> btl</small>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center; font-weight: 600; color: #DC2626;">
                        <?= number_format($pt['supplier_crates_due'], 0, ',', ' ') ?>
                    </td>
                    <td style="text-align: right; font-weight: 800; color: var(--c-navy);">
                        <?= number_format($totalOwned, ($totalOwned == intval($totalOwned) ? 0 : 1), ',', ' ') ?> casiers
                    </td>
                    <td style="text-align: right; font-weight: 600; color: #334155;">
                        <?= number_format($valLine, 0, ',', ' ') ?> FCFA
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="dashboard-grid" style="grid-template-columns: 1fr 1fr; gap: 20px;">
    
    <!-- SECTION 2: DETTES D'EMBALLAGES CLIENTS -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span><i class='bx bx-user-pin'></i> Dettes d'Emballages Clients</span>
            <?php 
            $groupedClientDebts = [];
            foreach ($clientDebts as $cd) {
                $cid = $cd['client_id'];
                if (!isset($groupedClientDebts[$cid])) {
                    $groupedClientDebts[$cid] = [
                        'client_id' => $cd['client_id'],
                        'client_name' => $cd['client_name'],
                        'client_phone' => $cd['client_phone'] ?? '',
                        'models' => []
                    ];
                }
                $groupedClientDebts[$cid]['models'][] = $cd;
            }
            ?>
            <span style="font-size: 0.8rem; font-weight: 700; color: #D97706; background: #FEF3C7; padding: 2px 8px; border-radius: 6px;">
                <?= count($groupedClientDebts) ?> client(s) endetté(s) (<?= count($clientDebts) ?> ligne(s))
            </span>
        </div>
        <div class="table-responsive">
            <table class="table" style="font-size: 0.88rem;">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Type Emballage / Modèle</th>
                        <th>Boissons (Sigles)</th>
                        <th style="text-align: center;">Casiers Dûs</th>
                        <th style="text-align: center;">Bouteilles Vrac</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($groupedClientDebts)): ?>
                    <tr><td colspan="6" style="text-align: center; padding: 20px; color: var(--c-gray-600);">Aucune dette d'emballage client en cours.</td></tr>
                    <?php else: ?>
                        <?php foreach ($groupedClientDebts as $clientGroup): ?>
                            <?php 
                            $rowCount = count($clientGroup['models']);
                            foreach ($clientGroup['models'] as $idx => $cd): 
                            ?>
                            <tr style="<?= $idx === 0 ? 'border-top: 2px solid #E2E8F0;' : '' ?>">
                                <?php if ($idx === 0): ?>
                                <td rowspan="<?= $rowCount ?>" style="vertical-align: middle; background: #F8FAFC; border-right: 1px solid #E2E8F0; width: 22%;">
                                    <strong style="color: var(--c-navy); font-size: 0.95rem; display: block;"><?= htmlspecialchars($clientGroup['client_name']) ?></strong>
                                    <?php if (!empty($clientGroup['client_phone'])): ?>
                                        <small style="display: block; color: #64748B; margin-top: 2px;"><i class='bx bx-phone'></i> <?= htmlspecialchars($clientGroup['client_phone']) ?></small>
                                    <?php endif; ?>
                                    <span style="display: inline-block; margin-top: 4px; font-size: 0.72rem; color: #64748B; font-weight: 700; background: #E2E8F0; padding: 2px 6px; border-radius: 4px;">
                                        <?= $rowCount ?> modèle(s)
                                    </span>
                                </td>
                                <?php endif; ?>
                                <td>
                                    <span style="font-size: 0.84rem; font-weight: 700; color: #1E293B; display: block;">
                                        <?= htmlspecialchars($cd['packaging_name']) ?>
                                    </span>
                                    <?php if (!empty($cd['source_invoices'])): ?>
                                        <div style="margin-top: 4px; display: flex; flex-direction: column; gap: 3px;">
                                            <?php foreach ($cd['source_invoices'] as $inv): ?>
                                                <a href="<?= BASE_URL ?>/ventes/invoice/<?= htmlspecialchars($inv['sale_id']) ?>" 
                                                   target="_blank" 
                                                   title="Facture <?= htmlspecialchars($inv['sale_id']) ?> du <?= htmlspecialchars($inv['sale_date']) ?> : <?= $inv['crates_due'] ?> casier(s) / <?= $inv['bottles_due'] ?> btl non rendus initialement (<?= htmlspecialchars($inv['products']) ?>)"
                                                   style="display: inline-flex; align-items: center; gap: 3px; padding: 2px 6px; background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; border-radius: 4px; font-size: 0.72rem; font-weight: 700; text-decoration: none; width: fit-content;">
                                                    <i class='bx bx-receipt'></i> Facture <?= htmlspecialchars($inv['sale_id']) ?>
                                                    <span style="color: #64748B; font-weight: 600; font-size: 0.68rem;">(<?= htmlspecialchars($inv['sale_date']) ?>)</span>
                                                    <span style="color: #DC2626; font-size: 0.68rem; font-weight: 800;">[+<?= $inv['crates_due'] ?> c.]</span>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <small style="color: #94A3B8; font-size: 0.72rem; display: block; margin-top: 3px;">
                                            <i class='bx bx-history'></i> Dette antérieure / reprise
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($cd['product_sigles'])): ?>
                                        <div style="display: flex; flex-wrap: wrap; gap: 3px;">
                                            <?php foreach (explode(', ', $cd['product_sigles']) as $s): ?>
                                                <span style="padding: 1px 5px; background: #EFF6FF; color: #0284C7; border: 1px solid #BFDBFE; border-radius: 4px; font-size: 0.72rem; font-weight: 800;">
                                                    <?= htmlspecialchars($s) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #94A3B8; font-size: 0.78rem;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($cd['crates_due'] > 0): ?>
                                        <span style="padding: 3px 8px; background: #FEF2F2; color: #DC2626; border-radius: 6px; font-weight: 800; font-size: 0.88rem;">
                                            <?= $cd['crates_due'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #94A3B8; font-weight: 600;">0</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($cd['loose_bottles_due'] > 0): ?>
                                        <span style="padding: 3px 8px; background: #FFFBEB; color: #D97706; border-radius: 6px; font-weight: 700;">
                                            +<?= $cd['loose_bottles_due'] ?> btl
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #94A3B8; font-weight: 600;">0</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php 
                                    $invSummary = '';
                                    if (!empty($cd['source_invoices'])) {
                                        $invParts = [];
                                        foreach ($cd['source_invoices'] as $inv) {
                                            $invParts[] = $inv['sale_id'] . ' (' . $inv['sale_date'] . ')';
                                        }
                                        $invSummary = implode(', ', $invParts);
                                    }
                                    ?>
                                    <button type="button" class="btn btn-primary" onclick="quickRestitute(<?= $cd['client_id'] ?>, <?= $cd['packaging_type_id'] ?>, <?= $cd['crates_due'] ?>, <?= $cd['loose_bottles_due'] ?>, '<?= htmlspecialchars(addslashes($clientGroup['client_name'])) ?>', '<?= htmlspecialchars(addslashes($cd['packaging_name'])) ?>', '<?= htmlspecialchars(addslashes($invSummary)) ?>')" style="padding: 4px 10px; font-size: 0.78rem; background: #16A34A; display: inline-flex; align-items: center; gap: 4px; font-weight: 700;" title="Enregistrer le retour de ces emballages">
                                        <i class='bx bx-check'></i> Rendre
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECTION 3: DETTES D'EMBALLAGES FOURNISSEURS -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span><i class='bx bx-buildings'></i> Dettes d'Emballages envers les Brasseries</span>
            <span style="font-size: 0.8rem; font-weight: 700; color: #DC2626; background: #FEE2E2; padding: 2px 8px; border-radius: 6px;">
                <?= count($supplierDebts) ?> ligne(s)
            </span>
        </div>
        <div class="table-responsive">
            <table class="table" style="font-size: 0.88rem;">
                <thead>
                    <tr>
                        <th>Fournisseur</th>
                        <th>Type Emballage</th>
                        <th style="text-align: center;">Casiers à Restituer</th>
                        <th style="text-align: center;">Bouteilles Vrac</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($supplierDebts)): ?>
                    <tr><td colspan="4" style="text-align: center; padding: 20px; color: var(--c-gray-600);">Aucune dette d'emballage envers les brasseries.</td></tr>
                    <?php else: ?>
                        <?php foreach ($supplierDebts as $sd): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($sd['supplier_name']) ?></strong></td>
                            <td><?= htmlspecialchars($sd['packaging_name']) ?></td>
                            <td style="text-align: center;">
                                <?php if ($sd['crates_due'] > 0): ?>
                                    <span style="padding: 3px 8px; background: #FEF2F2; color: #DC2626; border-radius: 6px; font-weight: 700;">
                                        <?= $sd['crates_due'] ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #94A3B8; font-weight: 600;">0</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($sd['loose_bottles_due'] > 0): ?>
                                    <span style="padding: 3px 8px; background: #FFFBEB; color: #D97706; border-radius: 6px; font-weight: 700;">
                                        +<?= $sd['loose_bottles_due'] ?> btl
                                    </span>
                                <?php else: ?>
                                    <span style="color: #94A3B8; font-weight: 600;">0</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL RESTITUTION EMBALLAGES CLIENT -->
<div id="modal_restitution" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
    <div class="card" style="max-width: 520px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-weight: 700; color: var(--c-navy); font-size: 1rem;"><i class='bx bx-download' style="color: #16A34A;"></i> Restitution d'Emballages Client</span>
            <button type="button" onclick="closeRestitutionModal()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: #64748B;">&times;</button>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>/emballages/saveRestitutionClient" method="POST">
                <!-- INFO BANNER -->
                <div id="modal_debt_info" style="display: none; background: #EFF6FF; border: 1px solid #BFDBFE; border-left: 4px solid #0284C7; padding: 10px 14px; border-radius: 6px; margin-bottom: 15px; font-size: 0.88rem; color: #1E40AF;">
                    <div><strong>Client :</strong> <span id="modal_info_client"></span></div>
                    <div style="margin-top: 2px;"><strong>Modèle :</strong> <span id="modal_info_model"></span></div>
                    <div id="modal_info_invoices_row" style="display: none; margin-top: 3px;"><strong>Facture(s) Source(s) :</strong> <span id="modal_info_invoices" style="font-weight: 700; color: #1D4ED8;"></span></div>
                    <div style="margin-top: 4px; color: #D97706; font-weight: 700;">
                        ⚠️ Dette Actuelle : <span id="modal_info_debt"></span>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">Client <span style="color: var(--c-danger);">*</span></label>
                    <select name="client_id" id="modal_client_id" class="form-control" required>
                        <option value="">-- Choisir le client --</option>
                        <?php foreach ($clients as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">Modèle de Casier / Emballage <span style="color: var(--c-danger);">*</span></label>
                    <select name="packaging_type_id" id="modal_packaging_type_id" class="form-control" required>
                        <option value="">-- Choisir le modèle --</option>
                        <?php foreach ($packagingTypes as $pt): ?>
                            <option value="<?= $pt['id'] ?>" data-factor="<?= $pt['bottles_per_crate'] ?>">
                                <?= htmlspecialchars($pt['company']) ?> - <?= htmlspecialchars($pt['name']) ?> (<?= $pt['bottles_per_crate'] ?> btls)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="dashboard-grid" style="margin-bottom: 15px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 700;">Nombre de Casiers Rendus</label>
                        <input type="number" name="crates_returned" id="modal_crates_returned" class="form-control" min="0" value="0" style="font-size: 1rem; font-weight: 700; text-align: center;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 700;">Bouteilles Vrac Rendues</label>
                        <input type="number" name="loose_bottles_returned" id="modal_bottles_returned" class="form-control" min="0" value="0" style="font-size: 1rem; font-weight: 700; text-align: center;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">Notes / Observation</label>
                    <input type="text" name="notes" class="form-control" placeholder="Ex: Retour casiers vides au dépôt...">
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-primary" onclick="closeRestitutionModal()" style="background: #64748B;">Annuler</button>
                    <button type="submit" class="btn btn-accent" style="font-weight: 700;"><i class='bx bx-check'></i> Valider le Retour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRestitutionModal() {
    document.getElementById('modal_restitution').style.display = 'flex';
}
function closeRestitutionModal() {
    document.getElementById('modal_restitution').style.display = 'none';
}
function quickRestitute(clientId, pkgId, crates, bottles, clientName, modelName, invoicesInfo) {
    document.getElementById('modal_client_id').value = clientId;
    document.getElementById('modal_packaging_type_id').value = pkgId;
    document.getElementById('modal_crates_returned').value = crates;
    document.getElementById('modal_bottles_returned').value = bottles;

    var infoBanner = document.getElementById('modal_debt_info');
    if (infoBanner && clientName && modelName) {
        document.getElementById('modal_info_client').innerText = clientName;
        document.getElementById('modal_info_model').innerText = modelName;
        var debtText = crates + ' casier(s)';
        if (bottles > 0) debtText += ' + ' + bottles + ' btl(s) vrac';
        document.getElementById('modal_info_debt').innerText = debtText;

        var invRow = document.getElementById('modal_info_invoices_row');
        if (invRow) {
            if (invoicesInfo) {
                document.getElementById('modal_info_invoices').innerText = invoicesInfo;
                invRow.style.display = 'block';
            } else {
                invRow.style.display = 'none';
            }
        }

        infoBanner.style.display = 'block';
    } else if (infoBanner) {
        infoBanner.style.display = 'none';
    }

    openRestitutionModal();
}
</script>
