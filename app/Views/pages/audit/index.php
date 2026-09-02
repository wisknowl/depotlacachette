<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
    <div class="page-title" style="margin-bottom: 0;">
        <i class='bx bx-book-content'></i>
        <h1><?= htmlspecialchars($title) ?></h1>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="<?= BASE_URL ?>/caisse/cloture" class="btn btn-accent" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; background: #059669; border-color: #059669; color: white; padding: 10px 18px; border-radius: 8px; box-shadow: 0 2px 8px rgba(5,150,105,0.25);">
            <i class='bx bx-calculator' style="font-size: 1.2rem;"></i> Clôture de Caisse Journalière
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

<!-- AUDIT NAVIGATION TABS -->
<div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 2px solid #E2E8F0; padding-bottom: 2px; flex-wrap: wrap;">
    <a href="<?= BASE_URL ?>/audit?tab=ledger" class="btn" style="border-radius: 8px 8px 0 0; padding: 10px 20px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; border: none; border-bottom: 3px solid <?= ($activeTab === 'ledger') ? 'var(--c-navy)' : 'transparent' ?>; background: <?= ($activeTab === 'ledger') ? 'white' : '#F1F5F9' ?>; color: <?= ($activeTab === 'ledger') ? 'var(--c-navy)' : '#64748B' ?>;">
        <i class='bx bx-list-check' style="font-size: 1.25rem;"></i> 1. Grand Livre Unifié des Flux
    </a>
    <a href="<?= BASE_URL ?>/audit?tab=system" class="btn" style="border-radius: 8px 8px 0 0; padding: 10px 20px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; border: none; border-bottom: 3px solid <?= ($activeTab === 'system') ? '#0284C7' : 'transparent' ?>; background: <?= ($activeTab === 'system') ? 'white' : '#F1F5F9' ?>; color: <?= ($activeTab === 'system') ? '#0284C7' : '#64748B' ?>;">
        <i class='bx bx-shield-quarter' style="font-size: 1.25rem;"></i> 2. Journal Système & Sécurité (Diffs)
    </a>
    <a href="<?= BASE_URL ?>/audit?tab=closings" class="btn" style="border-radius: 8px 8px 0 0; padding: 10px 20px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; border: none; border-bottom: 3px solid <?= ($activeTab === 'closings') ? '#059669' : 'transparent' ?>; background: <?= ($activeTab === 'closings') ? 'white' : '#F1F5F9' ?>; color: <?= ($activeTab === 'closings') ? '#059669' : '#64748B' ?>;">
        <i class='bx bx-archive-in' style="font-size: 1.25rem;"></i> 3. Registre des Clôtures de Caisse
    </a>
</div>

<!-- ======================================================== -->
<!-- TAB 1 : GRAND LIVRE UNIFIE DES FLUX -->
<!-- ======================================================== -->
<?php if ($activeTab === 'ledger'): ?>
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body" style="padding: 16px 20px;">
        <form method="GET" action="<?= BASE_URL ?>/audit" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
            <input type="hidden" name="tab" value="ledger">
            
            <div style="flex: 1; min-width: 140px;">
                <label class="form-label" style="font-size: 0.8rem; margin-bottom: 4px; color: #64748B;">Date Début</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($ledgerFilters['start_date']) ?>">
            </div>

            <div style="flex: 1; min-width: 140px;">
                <label class="form-label" style="font-size: 0.8rem; margin-bottom: 4px; color: #64748B;">Date Fin</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($ledgerFilters['end_date']) ?>">
            </div>

            <div style="flex: 1.5; min-width: 180px;">
                <label class="form-label" style="font-size: 0.8rem; margin-bottom: 4px; color: #64748B;">Filtrer par Module</label>
                <select name="module" class="form-control">
                    <option value="">Tous les Modules</option>
                    <option value="Ventes" <?= ($ledgerFilters['module'] === 'Ventes') ? 'selected' : '' ?>>🛒 Ventes</option>
                    <option value="Achats" <?= ($ledgerFilters['module'] === 'Achats') ? 'selected' : '' ?>>📦 Achats</option>
                    <option value="Dépenses" <?= ($ledgerFilters['module'] === 'Dépenses') ? 'selected' : '' ?>>💸 Dépenses</option>
                    <option value="Règlements" <?= ($ledgerFilters['module'] === 'Règlements') ? 'selected' : '' ?>>💰 Règlements Clients</option>
                    <option value="Paiements Fournisseurs" <?= ($ledgerFilters['module'] === 'Paiements Fournisseurs') ? 'selected' : '' ?>>🤝 Paiements Fournisseurs</option>
                    <option value="Paie" <?= ($ledgerFilters['module'] === 'Paie') ? 'selected' : '' ?>>👥 Paie Personnel</option>
                    <option value="Stock" <?= ($ledgerFilters['module'] === 'Stock') ? 'selected' : '' ?>>🍾 Mouvements Stock</option>
                    <option value="Emballages" <?= ($ledgerFilters['module'] === 'Emballages') ? 'selected' : '' ?>>📦 Parc d'Emballages (Casiers/Vides)</option>
                </select>
            </div>

            <div style="flex: 1.5; min-width: 160px;">
                <label class="form-label" style="font-size: 0.8rem; margin-bottom: 4px; color: #64748B;">Utilisateur / Auteur</label>
                <select name="user_id" class="form-control">
                    <option value="">Tous les Utilisateurs</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($ledgerFilters['user_id'] == $u['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['username']) ?> (<?= htmlspecialchars($u['role']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="background: var(--c-navy); padding: 9px 18px; font-weight: 700;">
                    <i class='bx bx-filter-alt'></i> Filtrer
                </button>
                <a href="<?= BASE_URL ?>/audit?tab=ledger" class="btn btn-secondary" style="padding: 9px 14px;" title="Réinitialiser les filtres">
                    <i class='bx bx-reset'></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <span style="font-weight: 800; color: var(--c-navy);">
            <i class='bx bx-book-bookmark'></i> Grand Livre Global des Transactions (<?= number_format($pagination['totalCount'], 0, ',', ' ') ?> événements)
        </span>
        <span style="font-size: 0.82rem; color: #64748B;">
            Page <?= $pagination['page'] ?> sur <?= max(1, $pagination['totalPages']) ?>
        </span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table table-striped" style="margin-bottom: 0; font-size: 0.88rem;">
                <thead>
                    <tr style="background: #F8FAFC;">
                        <th style="width: 150px;">Date & Heure</th>
                        <th style="width: 140px;">Module</th>
                        <th style="width: 110px;">Référence</th>
                        <th>Description de l'Événement</th>
                        <th style="text-align: right; width: 140px;">Montant / Flux</th>
                        <th style="width: 100px; text-align: center;">Statut</th>
                        <th style="width: 120px;">Auteur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($auditTrail)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #94A3B8;">
                                <i class='bx bx-search' style="font-size: 2.5rem; display: block; margin-bottom: 10px;"></i>
                                Aucun événement trouvé pour ces critères de filtre.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($auditTrail as $row): 
                            $modClass = 'badge-primary';
                            $modIcon = 'bx-cube';
                            if ($row['module'] === 'Ventes') { $modClass = 'badge-success'; $modIcon = 'bx-cart'; }
                            elseif ($row['module'] === 'Achats') { $modClass = 'badge-info'; $modIcon = 'bx-store'; }
                            elseif ($row['module'] === 'Dépenses') { $modClass = 'badge-danger'; $modIcon = 'bx-money-withdraw'; }
                            elseif ($row['module'] === 'Règlements') { $modClass = 'badge-warning'; $modIcon = 'bx-check-double'; }
                            elseif ($row['module'] === 'Paiements Fournisseurs') { $modClass = 'badge-secondary'; $modIcon = 'bx-check-shield'; }
                            elseif ($row['module'] === 'Paie') { $modClass = 'badge-dark'; $modIcon = 'bx-user-check'; }
                            elseif ($row['module'] === 'Stock') { $modClass = 'badge-purple'; $modIcon = 'bx-box'; }
                            elseif ($row['module'] === 'Emballages') { $modClass = 'badge-teal'; $modIcon = 'bx-archive'; }
                        ?>
                        <tr>
                            <td style="white-space: nowrap; color: #64748B;">
                                <i class='bx bx-time-five' style="font-size: 0.85rem;"></i> <?= date('d/m/Y H:i', strtotime($row['event_date'])) ?>
                            </td>
                            <td>
                                <span style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 0.78rem; background: #F1F5F9; color: var(--c-navy); border: 1px solid #CBD5E1;">
                                    <i class='bx <?= $modIcon ?>'></i> <?= htmlspecialchars($row['module']) ?>
                                </span>
                            </td>
                            <td>
                                <strong style="color: var(--c-navy);"><?= htmlspecialchars($row['reference_id']) ?></strong>
                            </td>
                            <td style="color: #334155;">
                                <?= htmlspecialchars($row['description']) ?>
                            </td>
                            <td style="text-align: right; font-weight: 700;">
                                <?php if ($row['amount'] > 0): ?>
                                    <span style="color: <?= ($row['flow_direction'] === 'IN') ? 'var(--c-success)' : 'var(--c-danger)' ?>;">
                                        <?= ($row['flow_direction'] === 'IN') ? '+' : '-' ?><?= number_format($row['amount'], 0, ',', ' ') ?> FCFA
                                    </span>
                                <?php else: ?>
                                    <span style="color: #94A3B8;">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <span style="padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; background: #DCFCE7; color: #166534;">
                                    <?= htmlspecialchars($row['status']) ?>
                                </span>
                            </td>
                            <td style="color: #475569; font-weight: 600;">
                                <i class='bx bx-user' style="font-size: 0.85rem;"></i> <?= htmlspecialchars($row['author']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- PAGINATION -->
    <?php if ($pagination['totalPages'] > 1): ?>
    <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; background: white; border-top: 1px solid #E2E8F0;">
        <div style="font-size: 0.85rem; color: #64748B;">
            Affichage de <?= min($pagination['totalCount'], $pagination['offset'] + 1) ?> à <?= min($pagination['totalCount'], $pagination['offset'] + $pagination['limit']) ?> sur <?= $pagination['totalCount'] ?> entrées
        </div>
        <div style="display: flex; gap: 5px;">
            <?php if ($pagination['page'] > 1): ?>
                <a href="<?= BASE_URL ?>/audit?tab=ledger&page=1&module=<?= urlencode($ledgerFilters['module']) ?>&user_id=<?= urlencode($ledgerFilters['user_id']) ?>&start_date=<?= urlencode($ledgerFilters['start_date']) ?>&end_date=<?= urlencode($ledgerFilters['end_date']) ?>" class="btn btn-secondary btn-sm"><i class='bx bx-chevrons-left'></i></a>
                <a href="<?= BASE_URL ?>/audit?tab=ledger&page=<?= $pagination['page'] - 1 ?>&module=<?= urlencode($ledgerFilters['module']) ?>&user_id=<?= urlencode($ledgerFilters['user_id']) ?>&start_date=<?= urlencode($ledgerFilters['start_date']) ?>&end_date=<?= urlencode($ledgerFilters['end_date']) ?>" class="btn btn-secondary btn-sm"><i class='bx bx-chevron-left'></i></a>
            <?php endif; ?>

            <span class="btn btn-sm" style="background: var(--c-navy); color: white; font-weight: 700;">Page <?= $pagination['page'] ?> / <?= $pagination['totalPages'] ?></span>

            <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                <a href="<?= BASE_URL ?>/audit?tab=ledger&page=<?= $pagination['page'] + 1 ?>&module=<?= urlencode($ledgerFilters['module']) ?>&user_id=<?= urlencode($ledgerFilters['user_id']) ?>&start_date=<?= urlencode($ledgerFilters['start_date']) ?>&end_date=<?= urlencode($ledgerFilters['end_date']) ?>" class="btn btn-secondary btn-sm"><i class='bx bx-chevron-right'></i></a>
                <a href="<?= BASE_URL ?>/audit?tab=ledger&page=<?= $pagination['totalPages'] ?>&module=<?= urlencode($ledgerFilters['module']) ?>&user_id=<?= urlencode($ledgerFilters['user_id']) ?>&start_date=<?= urlencode($ledgerFilters['start_date']) ?>&end_date=<?= urlencode($ledgerFilters['end_date']) ?>" class="btn btn-secondary btn-sm"><i class='bx bx-chevrons-right'></i></a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>


<!-- ======================================================== -->
<!-- TAB 2 : JOURNAL D'AUDIT SYSTEME & SECURITE (DIFFS) -->
<!-- ======================================================== -->
<?php if ($activeTab === 'system'): ?>
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body" style="padding: 16px 20px;">
        <form method="GET" action="<?= BASE_URL ?>/audit" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
            <input type="hidden" name="tab" value="system">
            
            <div style="flex: 2; min-width: 180px;">
                <label class="form-label" style="font-size: 0.8rem; margin-bottom: 4px; color: #64748B;">Recherche par Mot-Clé / Réf</label>
                <input type="text" name="search" class="form-control" placeholder="Rechercher action, motif, ID..." value="<?= htmlspecialchars($systemFilters['search']) ?>">
            </div>

            <div style="flex: 1.2; min-width: 140px;">
                <label class="form-label" style="font-size: 0.8rem; margin-bottom: 4px; color: #64748B;">Action</label>
                <select name="action" class="form-control">
                    <option value="">Toutes les actions</option>
                    <?php foreach ($auditActions as $act): ?>
                        <option value="<?= htmlspecialchars($act) ?>" <?= ($systemFilters['action'] === $act) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($act) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex: 1.2; min-width: 140px;">
                <label class="form-label" style="font-size: 0.8rem; margin-bottom: 4px; color: #64748B;">Module</label>
                <select name="module" class="form-control">
                    <option value="">Tous les modules</option>
                    <?php foreach ($auditModules as $mod): ?>
                        <option value="<?= htmlspecialchars($mod) ?>" <?= ($systemFilters['module'] === $mod) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($mod) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex: 1.2; min-width: 140px;">
                <label class="form-label" style="font-size: 0.8rem; margin-bottom: 4px; color: #64748B;">Utilisateur</label>
                <select name="user_id" class="form-control">
                    <option value="">Tous les utilisateurs</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($systemFilters['user_id'] == $u['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['username']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="background: #0284C7; padding: 9px 18px; font-weight: 700;">
                    <i class='bx bx-filter-alt'></i> Filtrer
                </button>
                <a href="<?= BASE_URL ?>/audit?tab=system" class="btn btn-secondary" style="padding: 9px 14px;">
                    <i class='bx bx-reset'></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; background: #F8FAFC;">
        <span style="font-weight: 800; color: #0369A1;">
            <i class='bx bx-shield-quarter'></i> Journal des Événements & Traçabilité Système (<?= number_format($pagination['totalCount'], 0, ',', ' ') ?> logs)
        </span>
        <span style="font-size: 0.82rem; color: #64748B;">
            Page <?= $pagination['page'] ?> sur <?= max(1, $pagination['totalPages']) ?>
        </span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table table-striped" style="margin-bottom: 0; font-size: 0.86rem;">
                <thead>
                    <tr style="background: #F1F5F9;">
                        <th style="width: 145px;">Horodatage</th>
                        <th style="width: 115px;">Action</th>
                        <th style="width: 110px;">Module</th>
                        <th style="width: 100px;">Réf ID</th>
                        <th>Description / Motif</th>
                        <th style="width: 140px;">Utilisateur & Rôle</th>
                        <th style="width: 120px;">IP / Source</th>
                        <th style="width: 100px; text-align: center;">Détails (Diff)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($systemAuditLogs)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #94A3B8;">
                                <i class='bx bx-check-shield' style="font-size: 2.5rem; display: block; margin-bottom: 10px; color: #CBD5E1;"></i>
                                Aucun log d'audit système enregistré pour ces filtres.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($systemAuditLogs as $log): 
                            $actColor = '#475569';
                            $actBg = '#F1F5F9';
                            if ($log['action'] === 'LOGIN') { $actColor = '#166534'; $actBg = '#DCFCE7'; }
                            elseif ($log['action'] === 'LOGOUT') { $actColor = '#64748B'; $actBg = '#F1F5F9'; }
                            elseif ($log['action'] === 'AUTH_FAIL') { $actColor = '#991B1B'; $actBg = '#FEE2E2'; }
                            elseif ($log['action'] === 'PRICE_CHANGE') { $actColor = '#9A3412'; $actBg = '#FFEDD5'; }
                            elseif ($log['action'] === 'CREDIT_LIMIT_CHANGE') { $actColor = '#7E22CE'; $actBg = '#F3E8FF'; }
                            elseif ($log['action'] === 'CANCEL' || $log['action'] === 'DELETE') { $actColor = '#991B1B'; $actBg = '#FEE2E2'; }
                            elseif ($log['action'] === 'CREATE') { $actColor = '#0369A1'; $actBg = '#E0F2FE'; }
                            elseif ($log['action'] === 'UPDATE') { $actColor = '#1D4ED8'; $actBg = '#DBEAFE'; }
                            elseif ($log['action'] === 'CASH_CLOSING') { $actColor = '#065F46'; $actBg = '#D1FAE5'; }

                            $hasDiff = (!empty($log['old_values']) || !empty($log['new_values']));
                        ?>
                        <tr>
                            <td style="white-space: nowrap; color: #64748B;">
                                <i class='bx bx-time' style="font-size: 0.85rem;"></i> <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                            </td>
                            <td>
                                <span style="display: inline-block; padding: 2px 7px; border-radius: 4px; font-size: 0.75rem; font-weight: 800; background: <?= $actBg ?>; color: <?= $actColor ?>;">
                                    <?= htmlspecialchars($log['action']) ?>
                                </span>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($log['module']) ?></strong>
                            </td>
                            <td>
                                <code style="background: #F1F5F9; padding: 2px 4px; border-radius: 4px; font-size: 0.8rem; color: #475569;"><?= htmlspecialchars($log['record_id'] ?: '-') ?></code>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #1E293B;"><?= htmlspecialchars($log['description']) ?></div>
                                <?php if (!empty($log['reason'])): ?>
                                    <div style="font-size: 0.78rem; color: #DC2626; margin-top: 2px;">
                                        <em>Motif : <?= htmlspecialchars($log['reason']) ?></em>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--c-navy);"><?= htmlspecialchars($log['user_name']) ?></div>
                                <div style="font-size: 0.74rem; color: #64748B;"><?= htmlspecialchars($log['user_role']) ?></div>
                            </td>
                            <td style="font-size: 0.78rem; color: #64748B;">
                                <div><i class='bx bx-globe'></i> <?= htmlspecialchars($log['ip_address']) ?></div>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($hasDiff): ?>
                                    <button type="button" class="btn btn-sm" onclick="showDiffModal(<?= htmlspecialchars(json_encode($log)) ?>)" style="background: #E0F2FE; color: #0284C7; border: 1px solid #BAE6FD; padding: 3px 8px; font-size: 0.75rem; font-weight: 700; border-radius: 4px;" title="Voir les modifications Avant/Après">
                                        <i class='bx bx-code-alt'></i> Diff
                                    </button>
                                <?php else: ?>
                                    <span style="color: #CBD5E1;">&mdash;</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- PAGINATION -->
    <?php if ($pagination['totalPages'] > 1): ?>
    <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; background: white; border-top: 1px solid #E2E8F0;">
        <div style="font-size: 0.85rem; color: #64748B;">
            Affichage de <?= min($pagination['totalCount'], $pagination['offset'] + 1) ?> à <?= min($pagination['totalCount'], $pagination['offset'] + $pagination['limit']) ?> sur <?= $pagination['totalCount'] ?> logs
        </div>
        <div style="display: flex; gap: 5px;">
            <?php if ($pagination['page'] > 1): ?>
                <a href="<?= BASE_URL ?>/audit?tab=system&page=1&action=<?= urlencode($systemFilters['action']) ?>&module=<?= urlencode($systemFilters['module']) ?>&user_id=<?= urlencode($systemFilters['user_id']) ?>&search=<?= urlencode($systemFilters['search']) ?>" class="btn btn-secondary btn-sm"><i class='bx bx-chevrons-left'></i></a>
                <a href="<?= BASE_URL ?>/audit?tab=system&page=<?= $pagination['page'] - 1 ?>&action=<?= urlencode($systemFilters['action']) ?>&module=<?= urlencode($systemFilters['module']) ?>&user_id=<?= urlencode($systemFilters['user_id']) ?>&search=<?= urlencode($systemFilters['search']) ?>" class="btn btn-secondary btn-sm"><i class='bx bx-chevron-left'></i></a>
            <?php endif; ?>

            <span class="btn btn-sm" style="background: #0284C7; color: white; font-weight: 700;">Page <?= $pagination['page'] ?> / <?= $pagination['totalPages'] ?></span>

            <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                <a href="<?= BASE_URL ?>/audit?tab=system&page=<?= $pagination['page'] + 1 ?>&action=<?= urlencode($systemFilters['action']) ?>&module=<?= urlencode($systemFilters['module']) ?>&user_id=<?= urlencode($systemFilters['user_id']) ?>&search=<?= urlencode($systemFilters['search']) ?>" class="btn btn-secondary btn-sm"><i class='bx bx-chevron-right'></i></a>
                <a href="<?= BASE_URL ?>/audit?tab=system&page=<?= $pagination['totalPages'] ?>&action=<?= urlencode($systemFilters['action']) ?>&module=<?= urlencode($systemFilters['module']) ?>&user_id=<?= urlencode($systemFilters['user_id']) ?>&search=<?= urlencode($systemFilters['search']) ?>" class="btn btn-secondary btn-sm"><i class='bx bx-chevrons-right'></i></a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- MODAL DIFF AVANT / APRES -->
<div id="diffModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 12px; width: 100%; max-width: 800px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); border: 1px solid #E2E8F0;">
        <div style="padding: 18px 24px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; background: #F8FAFC; border-radius: 12px 12px 0 0;">
            <div style="font-weight: 800; font-size: 1.1rem; color: var(--c-navy); display: flex; align-items: center; gap: 8px;">
                <i class='bx bx-git-compare' style="color: #0284C7; font-size: 1.4rem;"></i> Comparaison Avant / Après (Audit Diff)
            </div>
            <button type="button" onclick="closeDiffModal()" style="background: none; border: none; font-size: 1.5rem; color: #64748B; cursor: pointer;">&times;</button>
        </div>
        <div style="padding: 24px;">
            <div id="diffSummaryHeader" style="margin-bottom: 20px; padding: 12px; background: #F1F5F9; border-radius: 8px; font-size: 0.9rem;"></div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <div style="font-weight: 800; color: #991B1B; font-size: 0.85rem; text-transform: uppercase; margin-bottom: 8px; display: flex; align-items: center; gap: 4px;">
                        <i class='bx bx-minus-circle'></i> État Avant Modification :
                    </div>
                    <pre id="modalOldValues" style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 8px; padding: 12px; font-size: 0.82rem; color: #7F1D1D; white-space: pre-wrap; font-family: monospace; max-height: 350px; overflow-y: auto;"></pre>
                </div>
                <div>
                    <div style="font-weight: 800; color: #166534; font-size: 0.85rem; text-transform: uppercase; margin-bottom: 8px; display: flex; align-items: center; gap: 4px;">
                        <i class='bx bx-plus-circle'></i> État Après Modification :
                    </div>
                    <pre id="modalNewValues" style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 12px; font-size: 0.82rem; color: #14532D; white-space: pre-wrap; font-family: monospace; max-height: 350px; overflow-y: auto;"></pre>
                </div>
            </div>
        </div>
        <div style="padding: 14px 24px; border-top: 1px solid #E2E8F0; text-align: right; background: #F8FAFC; border-radius: 0 0 12px 12px;">
            <button type="button" onclick="closeDiffModal()" class="btn btn-secondary" style="padding: 8px 18px;">Fermer</button>
        </div>
    </div>
</div>

<script>
function showDiffModal(log) {
    document.getElementById('diffSummaryHeader').innerHTML = 
        '<strong>Action :</strong> ' + (log.action || '-') + ' | <strong>Module :</strong> ' + (log.module || '-') + ' | <strong>Réf :</strong> ' + (log.record_id || '-') + '<br>' +
        '<strong>Auteur :</strong> ' + (log.user_name || '-') + ' (' + (log.user_role || '-') + ') | <strong>Date :</strong> ' + (log.created_at || '-') + '<br>' +
        '<strong>Description :</strong> ' + (log.description || '-');

    document.getElementById('modalOldValues').textContent = log.old_values ? log.old_values : 'Aucune donnée précédente (Création)';
    document.getElementById('modalNewValues').textContent = log.new_values ? log.new_values : 'Aucune nouvelle donnée (Suppression)';
    
    document.getElementById('diffModal').style.display = 'flex';
}

function closeDiffModal() {
    document.getElementById('diffModal').style.display = 'none';
}
</script>
<?php endif; ?>


<!-- ======================================================== -->
<!-- TAB 3 : REGISTRE DES CLOTURES DE CAISSE (ARCHIVES) -->
<!-- ======================================================== -->
<?php if ($activeTab === 'closings'): ?>
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; background: #F8FAFC;">
        <span style="font-weight: 800; color: #065F46;">
            <i class='bx bx-archive-in'></i> Historique des Procès-Verbaux de Clôture de Caisse (<?= count($closings) ?> clôtures archivées)
        </span>
        <a href="<?= BASE_URL ?>/caisse/cloture" class="btn btn-accent btn-sm" style="background: #059669; border-color: #059669; color: white;">
            <i class='bx bx-plus'></i> Nouvelle Clôture Journalière
        </a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table table-striped" style="margin-bottom: 0; font-size: 0.88rem;">
                <thead>
                    <tr style="background: #F1F5F9;">
                        <th>Date Clôture</th>
                        <th>Compte de Caisse</th>
                        <th style="text-align: right;">Solde Initial</th>
                        <th style="text-align: right;">Total Entrées (+)</th>
                        <th style="text-align: right;">Total Sorties (-)</th>
                        <th style="text-align: right;">Solde Théorique</th>
                        <th style="text-align: right;">Espèces Physiques</th>
                        <th style="text-align: right;">Écart Constaté</th>
                        <th style="width: 110px; text-align: center;">Statut</th>
                        <th>Clôturé Par</th>
                        <th style="text-align: center; width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($closings)): ?>
                        <tr>
                            <td colspan="11" style="text-align: center; padding: 40px; color: #94A3B8;">
                                <i class='bx bx-receipt' style="font-size: 2.5rem; display: block; margin-bottom: 10px;"></i>
                                Aucune clôture de caisse enregistrée dans l'historique.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($closings as $c): 
                            $diff = floatval($c['difference']);
                            $isZero = ($diff == 0);
                            $isNeg = ($diff < 0);
                        ?>
                        <tr>
                            <td style="font-weight: 700; color: var(--c-navy);">
                                <?= date('d/m/Y', strtotime($c['closing_date'])) ?>
                            </td>
                            <td><?= htmlspecialchars($c['cash_account_name']) ?></td>
                            <td style="text-align: right; color: #64748B;"><?= number_format($c['opening_balance'], 0, ',', ' ') ?></td>
                            <td style="text-align: right; color: var(--c-success);">+<?= number_format($c['total_in'], 0, ',', ' ') ?></td>
                            <td style="text-align: right; color: var(--c-danger);">-<?= number_format($c['total_out'], 0, ',', ' ') ?></td>
                            <td style="text-align: right; font-weight: 700; color: var(--c-navy);"><?= number_format($c['theoretical_balance'], 0, ',', ' ') ?></td>
                            <td style="text-align: right; font-weight: 800;"><?= number_format($c['physical_cash'], 0, ',', ' ') ?></td>
                            <td style="text-align: right; font-weight: 800;">
                                <?php if ($isZero): ?>
                                    <span style="color: var(--c-success);">0 FCFA</span>
                                <?php elseif ($isNeg): ?>
                                    <span style="color: var(--c-danger);"><?= number_format($diff, 0, ',', ' ') ?> FCFA</span>
                                <?php else: ?>
                                    <span style="color: #D97706;">+<?= number_format($diff, 0, ',', ' ') ?> FCFA</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <span style="padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; background: <?= $isZero ? '#DCFCE7; color: #166534;' : ($isNeg ? '#FEE2E2; color: #991B1B;' : '#FEF3C7; color: #92400E;') ?>">
                                    <?= $isZero ? 'Équilibrée' : ($isNeg ? 'Manquant' : 'Excédent') ?>
                                </span>
                            </td>
                            <td style="color: #475569; font-size: 0.84rem;">
                                <?= htmlspecialchars($c['closed_by_name']) ?>
                            </td>
                            <td style="text-align: center;">
                                <a href="<?= BASE_URL ?>/caisse/receipt/<?= $c['id'] ?>" class="btn btn-sm btn-primary" style="background: var(--c-navy); padding: 4px 10px; font-size: 0.78rem;" title="Consulter et imprimer le procès-verbal">
                                    <i class='bx bx-printer'></i> PV
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
