<div class="page-title">
    <i class='bx bx-user-circle'></i>
    <h1>Mon Profil & Sécurité</h1>
</div>

<div class="card" style="max-width: 650px;">
    <div class="card-body">
        <?php if (!empty($message)): ?>
            <div style="padding: 12px 15px; background: #D1FAE5; color: #065F46; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
                <i class='bx bx-check-circle'></i> <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/users/saveProfile" method="POST">
            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Identifiant (Non modifiable)</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['username'] ?? '') ?>" disabled style="background: var(--c-gray-200);">
                </div>
                <div class="form-group">
                    <label class="form-label">Rôle Attribué</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['role'] ?? '') ?>" disabled style="background: var(--c-gray-200); font-weight: bold;">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nom Complet Affiché</label>
                <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" placeholder="Votre nom">
            </div>

            <div style="margin: 25px 0 15px; padding-top: 15px; border-top: 1px solid var(--c-gray-200);">
                <h3 style="font-size: 1rem; color: var(--c-navy); margin-bottom: 15px;"><i class='bx bx-lock-alt'></i> Modifier mon Mot de Passe</h3>
            </div>

            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Nouveau Mot de Passe</label>
                    <input type="password" name="password" class="form-control" placeholder="Laisser vide pour conserver l'actuel">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmer le Mot de Passe</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirmer le nouveau mot de passe">
                </div>
            </div>

            <div style="margin-top: 25px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-accent"><i class='bx bx-save'></i> Mettre à jour mon profil</button>
                <a href="<?= BASE_URL ?>/home" class="btn btn-primary" style="background-color: var(--c-gray-600);"><i class='bx bx-x'></i> Retour</a>
            </div>
        </form>
    </div>
</div>
