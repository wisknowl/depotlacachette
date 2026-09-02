<div class="page-title">
    <i class='bx bx-store-alt'></i>
    <h1>Annuaire & Comptes Fournisseurs</h1>
</div>
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <span><i class='bx bx-list-ul'></i> Liste des Fournisseurs & Suivi des Dettes</span>
        <div style="display: flex; gap: 10px;">
            <a href="<?= BASE_URL ?>/paiementsFournisseurs/form" class="btn btn-primary" style="background-color: var(--c-amber); color: #000;"><i class='bx bx-wallet'></i> Payer une Dette</a>
            <a href="<?= BASE_URL ?>/fournisseurs/form" class="btn btn-accent"><i class='bx bx-plus'></i> Nouveau Fournisseur</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Nom du Fournisseur</th>
                    <th>Téléphone</th>
                    <th>Adresse</th>
                    <th>Nom du Contact</th>
                    <th>Dette Financière (Solde Dû)</th>
                    <th style="text-align: center;">Dette Emballages</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($fournisseurs)): ?>
                <tr><td colspan="7" style="text-align: center; padding: 25px;">Aucun fournisseur enregistré.</td></tr>
                <?php else: ?>
                    <?php foreach ($fournisseurs as $f): ?>
                    <?php 
                        $debt = floatval($f['solde_du']);
                        $cratesDue = intval($f['total_crates_due'] ?? 0);
                        $bottlesDue = intval($f['total_bottles_due'] ?? 0);
                        $debtBadge = ($debt > 0) ? '#f8d7da; color: #721c24; font-weight: bold;' : '#d4edda; color: #155724;';
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($f['name']) ?></strong></td>
                        <td><?= htmlspecialchars($f['phone'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($f['address'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($f['contact_name'] ?: '-') ?></td>
                        <td>
                            <span style="padding: 4px 10px; border-radius: 12px; font-size: 0.85rem; background: <?= $debtBadge ?>">
                                <?= number_format($debt, 0, ',', ' ') ?> FCFA
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <?php if ($cratesDue > 0 || $bottlesDue > 0): ?>
                                <a href="<?= BASE_URL ?>/emballages" style="padding: 3px 8px; background: #FEF2F2; color: #DC2626; border-radius: 6px; font-size: 0.82rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="Cliquer pour voir / solder dans le Parc d'Emballages">
                                    <i class='bx bx-archive'></i>
                                    <?= $cratesDue > 0 ? $cratesDue . ' cas.' : '' ?>
                                    <?= ($cratesDue > 0 && $bottlesDue > 0) ? ', ' : '' ?>
                                    <?= $bottlesDue > 0 ? $bottlesDue . ' btl.' : '' ?>
                                </a>
                            <?php else: ?>
                                <span style="color: #10B981; font-weight: 600; font-size: 0.85rem;">0</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($debt > 0): ?>
                                <a href="<?= BASE_URL ?>/paiementsFournisseurs/form/<?= $f['slug'] ?>" class="btn btn-primary" style="padding: 4px 8px; font-size: 0.8rem; background-color: var(--c-amber); color: #000;" title="Payer ce fournisseur"><i class='bx bx-credit-card'></i></a>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/fournisseurs/form/<?= $f['slug'] ?>" class="btn btn-primary" style="padding: 4px 8px; font-size: 0.8rem;" title="Modifier"><i class='bx bx-edit'></i></a>
                            <a href="<?= BASE_URL ?>/fournisseurs/delete/<?= $f['slug'] ?>" class="btn btn-primary" style="padding: 4px 8px; font-size: 0.8rem; background-color: var(--c-danger);" onclick="return confirm('Supprimer ce fournisseur ?')" title="Supprimer"><i class='bx bx-trash'></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
