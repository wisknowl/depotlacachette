<div class="page-title">
    <i class='bx bx-user-pin'></i>
    <h1>Gestion des Utilisateurs & Rôles</h1>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <span><i class='bx bx-list-ul'></i> Comptes Utilisateurs Actifs</span>
        <a href="<?= BASE_URL ?>/users/form" class="btn btn-accent"><i class='bx bx-user-plus'></i> Ajouter un Utilisateur</a>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom Complet</th>
                    <th>Identifiant (Username)</th>
                    <th>Rôle & Privilèges</th>
                    <th>Statut</th>
                    <th>Date de Création</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <?php 
                    $roleBadge = 'background: #E5E7EB; color: #374151;';
                    if ($u['role'] === 'Admin') $roleBadge = 'background: #FEF3C7; color: #92400E; font-weight: bold;';
                    if ($u['role'] === 'Caissier') $roleBadge = 'background: #D1FAE5; color: #065F46; font-weight: bold;';
                    if ($u['role'] === 'Vendeur') $roleBadge = 'background: #DBEAFE; color: #1E40AF; font-weight: bold;';
                ?>
                <tr>
                    <td><strong>#<?= $u['id'] ?></strong></td>
                    <td>
                        <strong><?= htmlspecialchars($u['full_name'] ?: $u['username']) ?></strong>
                        <?php if (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $u['id']): ?>
                            <span style="font-size: 0.75rem; padding: 2px 6px; background: var(--c-navy); color: #fff; border-radius: 4px; margin-left: 5px;">Vous</span>
                        <?php endif; ?>
                    </td>
                    <td><code><?= htmlspecialchars($u['username']) ?></code></td>
                    <td>
                        <span style="padding: 4px 10px; border-radius: 12px; font-size: 0.82rem; <?= $roleBadge ?>">
                            <?= htmlspecialchars($u['role']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($u['is_active']): ?>
                            <span style="padding: 3px 8px; border-radius: 10px; font-size: 0.8rem; background: #D1FAE5; color: #065F46;">Actif</span>
                        <?php else: ?>
                            <span style="padding: 3px 8px; border-radius: 10px; font-size: 0.8rem; background: #FEE2E2; color: #991B1B;">Désactivé</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <a href="<?= BASE_URL ?>/users/form/<?= $u['id'] ?>" class="btn btn-primary" style="padding: 4px 8px; font-size: 0.8rem;" title="Modifier"><i class='bx bx-edit'></i></a>
                        <?php if ($u['id'] != 1 && (!isset($_SESSION['user']['id']) || $_SESSION['user']['id'] != $u['id'])): ?>
                            <a href="<?= BASE_URL ?>/users/delete/<?= $u['id'] ?>" class="btn btn-primary" style="padding: 4px 8px; font-size: 0.8rem; background-color: var(--c-danger);" onclick="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?')" title="Supprimer"><i class='bx bx-trash'></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
