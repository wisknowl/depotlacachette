<div class="page-title">
    <i class='bx bx-user-pin'></i>
    <h1><?= $title ?></h1>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-body">
        <form action="<?= BASE_URL ?>/users/save" method="POST">
            <?php if ($user): ?>
                <input type="hidden" name="id" value="<?= $user['id'] ?>">
            <?php endif; ?>

            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Identifiant de Connexion (Username)</label>
                    <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($user['username'] ?? '') ?>" placeholder="Ex: e.kamga">
                </div>
                <div class="form-group">
                    <label class="form-label">Nom Complet / Titulaire</label>
                    <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" placeholder="Ex: Emmanuel Kamga">
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Rôle & Privilèges</label>
                    <select name="role" class="form-control" required>
                        <option value="Admin" <?= (isset($user['role']) && $user['role'] === 'Admin') ? 'selected' : '' ?>>👑 Administrateur (Accès Total)</option>
                        <option value="Caissier" <?= (isset($user['role']) && $user['role'] === 'Caissier') ? 'selected' : '' ?>>💰 Caissier (Caisse, Ventes, Règlements, Dépenses)</option>
                        <option value="Vendeur" <?= (isset($user['role']) && $user['role'] === 'Vendeur') ? 'selected' : '' ?>>🛒 Vendeur (Ventes & Stocks)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Mot de Passe <?= $user ? '<span style="font-weight: normal; color: var(--c-gray-600);">(Laisser vide pour ne pas changer)</span>' : '' ?></label>
                    <input type="password" name="password" class="form-control" <?= $user ? '' : 'required' ?> placeholder="••••••••">
                </div>
            </div>

            <div class="form-group" style="margin-top: 15px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" <?= (!isset($user['is_active']) || $user['is_active'] == 1) ? 'checked' : '' ?>>
                    <span>Compte Actif (Permettre la connexion à l'application)</span>
                </label>
            </div>

            <div style="margin-top: 25px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-accent"><i class='bx bx-check'></i> Enregistrer l'Utilisateur</button>
                <a href="<?= BASE_URL ?>/users" class="btn btn-primary" style="background-color: var(--c-gray-600);"><i class='bx bx-x'></i> Annuler</a>
            </div>
        </form>
    </div>
</div>
