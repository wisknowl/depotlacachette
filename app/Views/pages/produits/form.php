<div class="page-title">
    <i class='bx bx-package'></i>
    <h1><?= $title ?></h1>
</div>

<div class="card" style="max-width: 800px;">
    <div class="card-header">
        <i class='bx bx-edit'></i> <?= isset($produit) ? 'Modifier les Informations du Produit' : 'Fiche Nouveau Produit au Catalogue' ?>
    </div>
    <div class="card-body">
        <form action="<?= BASE_URL ?>/produits/save" method="POST">
            <?php if (isset($produit)): ?>
                <input type="hidden" name="id" value="<?= $produit['id'] ?>">
            <?php endif; ?>

            <div class="dashboard-grid">
                <div class="form-group" style="flex: 2;">
                    <label class="form-label">Nom du Produit <span style="color: var(--c-danger);">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="Ex: Castel Beer 65cl" value="<?= htmlspecialchars($produit['name'] ?? '') ?>">
                </div>

                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Sigle / Mnémonique Court</label>
                    <input type="text" name="short_code" class="form-control" placeholder="Ex: CB, GGFS, 3EXP..." style="text-transform: uppercase; font-weight: 700; color: #0284C7;" value="<?= htmlspecialchars($produit['short_code'] ?? '') ?>">
                </div>

                <div class="form-group" style="flex: 1.5;">
                    <label class="form-label">Catégorie <span style="color: var(--c-danger);">*</span></label>
                    <input list="category_list" name="category_name" id="input_category_name" class="form-control" required placeholder="Choisir ou saisir une catégorie..." value="<?= htmlspecialchars($produit['category_name'] ?? '') ?>" autocomplete="off" oninput="onCategoryChange()" onchange="onCategoryChange()">
                    <datalist id="category_list">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['name']) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Format / Volume de la Bouteille <span style="color: var(--c-danger);">*</span></label>
                    <input list="format_list" name="format_name" class="form-control" required placeholder="Choisir ou saisir un volume (ex: 65cl, 33cl, 1.5L...)" value="<?= htmlspecialchars($produit['format_name'] ?? '') ?>" autocomplete="off">
                    <datalist id="format_list">
                        <?php foreach ($formats as $fmt): ?>
                            <option value="<?= htmlspecialchars($fmt['name']) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div class="form-group">
                    <label class="form-label">Bouteilles<span style="color: var(--c-danger);">*</span></label>
                    <input list="factor_list" type="number" name="factor" class="form-control" required min="1" placeholder="Ex: 12, 24, 6..." value="<?= $produit['factor'] ?? 12 ?>">
                    <datalist id="factor_list">
                        <option value="24">24 bouteilles / casier ou carton</option>
                        <option value="12">12 bouteilles / casier ou pack</option>
                        <option value="6">6 bouteilles (Pack eau/jus)</option>
                        <option value="20">20 bouteilles / casier</option>
                        <option value="30">30 canettes (Plateau)</option>
                        <option value="48">48 unités</option>
                        <option value="1">1 unité (Vente unitaire)</option>
                    </datalist>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Prix d'Achat Référence (FCFA) <span style="color: var(--c-danger);">*</span></label>
                    <input type="number" step="1" name="purchase_price" class="form-control" required placeholder="Ex: 6500" value="<?= $produit['purchase_price'] ?? 0 ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Prix Vente Entier (FCFA) <span style="color: var(--c-danger);">*</span></label>
                    <input type="number" step="1" name="price_casier" id="input_price_casier" class="form-control" required placeholder="Ex: 7800" value="<?= $produit['price_casier'] ?? 0 ?>" oninput="onPriceCasierInput()">
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Prix Demi-Casier (FCFA)</label>
                    <input type="number" step="1" name="price_demi" id="input_price_demi" class="form-control" placeholder="Ex: 3900" value="<?= $produit['price_demi'] ?? 0 ?>" oninput="onPriceDemiInput()">
                </div>

                <div class="form-group">
                    <label class="form-label">Seuil d'Alerte Stock</label>
                    <input type="number" name="alert_stock" class="form-control" placeholder="Ex: 10" value="<?= $produit['alert_stock'] ?? 1 ?>">
                </div>
            </div>

            <!-- EMBALLAGE CONSIGNÉ & CASIER ASSOCIÉ -->
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 16px; margin-top: 15px; margin-bottom: 10px;">
                <div style="font-weight: 700; color: var(--c-navy); margin-bottom: 12px; display: flex; align-items: center; gap: 8px; font-size: 0.95rem;">
                    <i class='bx bx-archive' style="color: var(--c-accent); font-size: 1.2rem;"></i>
                    Configuration de l'Emballage (Casier & Bouteilles Consignées)
                </div>

                <div class="dashboard-grid" style="margin-bottom: 12px;">
                    <div class="form-group">
                        <label class="form-label">Type d'Emballage <span style="color: var(--c-danger);">*</span></label>
                        <select name="is_returnable" id="select_is_returnable" class="form-control" required onchange="onReturnableChange()">
                            <option value="1" <?= (!isset($produit) || ($produit['is_returnable'] ?? 1) == 1) ? 'selected' : '' ?>>🟢 Emballage Consigné / Récupérable (Bouteilles verre)</option>
                            <option value="0" <?= (isset($produit) && ($produit['is_returnable'] ?? 1) == 0) ? 'selected' : '' ?>>⚪ Emballage Perdu (Canettes, PET, Vins, Whiskies)</option>
                        </select>
                    </div>

                    <div class="form-group" id="group_packaging_type">
                        <label class="form-label">Modèle de Casier Associé</label>
                        <select name="packaging_type_id" id="select_packaging_type_id" class="form-control" onchange="onPackagingTypeChange()">
                            <option value="">-- Sélectionner un type de casier --</option>
                            <?php foreach ($packagingTypes as $pt): ?>
                                <option value="<?= $pt['id'] ?>" data-cost="<?= $pt['default_cost'] ?>" <?= (($produit['packaging_type_id'] ?? '') == $pt['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($pt['company']) ?> - <?= htmlspecialchars($pt['name']) ?> (<?= number_format($pt['default_cost'], 0, ',', ' ') ?> FCFA)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="group_cout_emballage" style="margin-bottom: 0;">
                    <label class="form-label">Coût Achat / Consigne Emballage Défaut (FCFA)</label>
                    <input type="number" step="1" name="cout_emballage" id="input_cout_emballage" class="form-control" placeholder="Ex: 3600" value="<?= $produit['cout_emballage'] ?? 3600 ?>" style="max-width: 300px;">
                    <small style="color: #64748B; font-size: 0.8rem; margin-top: 4px; display: block;">
                        Montant de consigne appliqué lors d'un approvisionnement si vous n'avez pas de casiers vides à échanger.
                    </small>
                </div>
            </div>

            <input type="hidden" name="price_unite" value="<?= $produit['price_unite'] ?? 0 ?>">

            <div style="margin-top: 25px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-accent"><i class='bx bx-save'></i> Enregistrer</button>
                <a href="<?= BASE_URL ?>/produits" class="btn btn-primary" style="background-color: var(--c-gray-600);"><i class='bx bx-x'></i> Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
let previousFullPrice = parseFloat(document.getElementById('input_price_casier').value) || 0;
let demiManuallyEdited = false;

// Check on load if the initial demi price was custom (different from exact half)
const initialDemi = parseFloat(document.getElementById('input_price_demi').value) || 0;
if (initialDemi > 0 && previousFullPrice > 0 && initialDemi !== Math.round(previousFullPrice / 2)) {
    demiManuallyEdited = true;
}

function isBeerCategory() {
    const catVal = (document.getElementById('input_category_name').value || '').toLowerCase();
    return catVal.includes('bier') || catVal.includes('bièr') || catVal.includes('beer');
}

function onCategoryChange() {
    const demiInput = document.getElementById('input_price_demi');
    if (!isBeerCategory()) {
        demiInput.value = 0;
        demiManuallyEdited = false;
    } else {
        demiManuallyEdited = false;
        onPriceCasierInput();
    }
}

function onPriceCasierInput() {
    const fullPrice = parseFloat(document.getElementById('input_price_casier').value) || 0;
    const demiInput = document.getElementById('input_price_demi');
    
    if (!isBeerCategory()) {
        demiInput.value = 0;
        return;
    }

    if (!demiManuallyEdited || demiInput.value === '' || demiInput.value === '0') {
        demiInput.value = (fullPrice > 0) ? Math.round(fullPrice / 2) : 0;
    }
    previousFullPrice = fullPrice;
}

function onPriceDemiInput() {
    const fullPrice = parseFloat(document.getElementById('input_price_casier').value) || 0;
    const demiVal = parseFloat(document.getElementById('input_price_demi').value) || 0;
    if (fullPrice > 0 && demiVal === Math.round(fullPrice / 2)) {
        demiManuallyEdited = false;
    } else {
        demiManuallyEdited = true;
    }
}

function onReturnableChange() {
    const isRet = document.getElementById('select_is_returnable').value === '1';
    const groupPkg = document.getElementById('group_packaging_type');
    const groupCost = document.getElementById('group_cout_emballage');
    const inputCost = document.getElementById('input_cout_emballage');
    const selectPkg = document.getElementById('select_packaging_type_id');

    if (!isRet) {
        groupPkg.style.display = 'none';
        groupCost.style.display = 'none';
        inputCost.value = 0;
        selectPkg.value = '';
    } else {
        groupPkg.style.display = 'block';
        groupCost.style.display = 'block';
        if (inputCost.value === '0' || inputCost.value === '') {
            inputCost.value = 3600;
        }
    }
}

function onPackagingTypeChange() {
    const selectPkg = document.getElementById('select_packaging_type_id');
    const selectedOption = selectPkg.options[selectPkg.selectedIndex];
    if (selectedOption && selectedOption.dataset.cost) {
        document.getElementById('input_cout_emballage').value = selectedOption.dataset.cost;
    }
}

document.addEventListener("DOMContentLoaded", function() {
    if (!isBeerCategory()) {
        const demiInput = document.getElementById('input_price_demi');
        if (demiInput) {
            demiInput.value = 0;
        }
    }
    onReturnableChange();
});
</script>
