<div class="page-title">
    <i class='bx bx-store-alt'></i>
    <h1><?= $title ?></h1>
</div>
<div class="card">
    <div class="card-body">
        <form action="<?= BASE_URL ?>/fournisseurs/save" method="POST">
            <?php if (isset($fournisseur['id'])): ?>
                <input type="hidden" name="id" value="<?= $fournisseur['id'] ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label class="form-label">Nom du Fournisseur</label>
                <input type="text" name="name" class="form-control" required value="<?= isset($fournisseur) ? htmlspecialchars($fournisseur['name']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Téléphone</label>
                <input type="text" name="phone" class="form-control" value="<?= isset($fournisseur) ? htmlspecialchars($fournisseur['phone']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Adresse</label>
                <input type="text" name="address" class="form-control" value="<?= isset($fournisseur) ? htmlspecialchars($fournisseur['address']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Nom du Contact</label>
                <input type="text" name="contact_name" class="form-control" value="<?= isset($fournisseur) ? htmlspecialchars($fournisseur['contact_name']) : '' ?>">
            </div>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-accent"><i class='bx bx-save'></i> Enregistrer</button>
                <a href="<?= BASE_URL ?>/fournisseurs" class="btn btn-primary" style="background-color: var(--c-gray-600);"><i class='bx bx-x'></i> Annuler</a>
            </div>
        </form>
    </div>
</div>
