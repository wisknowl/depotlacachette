<div class="page-title">
    <i class='bx bx-user'></i>
    <h1><?= $title ?></h1>
</div>

<?php if (!empty($flash_error)): ?>
    <div style="background: #FEE2E2; border-left: 4px solid var(--c-danger); color: #991B1B; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
        <i class='bx bx-error-circle' style="font-size: 1.4rem;"></i>
        <span><?= htmlspecialchars($flash_error) ?></span>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 650px;">
    <div class="card-header">
        <i class='bx bx-id-card'></i> Informations du Client
    </div>
    <div class="card-body">
        <form action="<?= BASE_URL ?>/clients/save<?= !empty($client['id']) ? '/' . $client['id'] : '' ?>" method="POST">
            <?php if (!empty($client['id'])): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($client['id']) ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label class="form-label">Nom du Client / Établissement <span style="color: var(--c-danger);">*</span></label>
                <input type="text" name="name" class="form-control" required placeholder="Ex: Snack Bar La Joie" value="<?= htmlspecialchars($client['name'] ?? '') ?>">
            </div>

            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone" class="form-control" placeholder="Ex: 699 00 00 00" value="<?= htmlspecialchars($client['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Type de Client</label>
                    <select name="client_type_id" class="form-control">
                        <option value="1" <?= (isset($client['client_type_id']) && $client['client_type_id'] == 1) ? 'selected' : '' ?>>Bar / Restaurant / Snack (Gros)</option>
                        <option value="2" <?= (isset($client['client_type_id']) && $client['client_type_id'] == 2) ? 'selected' : '' ?>>Boutique / Épicerie (Demi-Gros)</option>
                        <option value="3" <?= (isset($client['client_type_id']) && $client['client_type_id'] == 3) ? 'selected' : '' ?>>Particulier / Événement (Détail)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Plafond de Crédit Autorisé (FCFA) <span style="color: var(--c-danger);">*</span></label>
                <input type="number" step="1000" min="0" name="max_credit" class="form-control" required 
                       placeholder="Ex: 500000 (0 si Comptant Uniquement)" 
                       value="<?= htmlspecialchars($client['max_credit'] ?? '0') ?>">
                <small style="color: var(--c-gray-600); font-size: 0.82rem; margin-top: 4px; display: block;">
                    <strong>Important :</strong> Laisser à <strong>0 FCFA</strong> pour un client strictement au comptant (les ventes à crédit seront bloquées). Définir un montant positif pour autoriser les facilités de paiement jusqu'à cette limite.
                </small>
            </div>

            <div class="form-group">
                <label class="form-label">Adresse / Localisation</label>
                <input type="text" name="address" class="form-control" placeholder="Ex: Akwa, Face Salle des Fêtes" value="<?= htmlspecialchars($client['address'] ?? '') ?>">
            </div>

            <div style="margin-top: 25px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-accent"><i class='bx bx-save'></i> Enregistrer le Client</button>
                <a href="<?= BASE_URL ?>/clients" class="btn btn-primary" style="background-color: var(--c-gray-600);"><i class='bx bx-x'></i> Annuler</a>
            </div>
        </form>
    </div>
</div>
