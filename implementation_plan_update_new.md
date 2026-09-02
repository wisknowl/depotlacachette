# Plan d'Implémentation : Module Dédié « Vente au Détail / À la Bouteille » (`/ventes/en-detail`)

Ce plan détaille la conception et l'intégration du nouveau module de **Vente au Détail (À la Bouteille)** sans toucher au formulaire existant de vente en gros/demi-gros, tout en assurant l'équilibre simultané des 4 grands livres (Caisse, Stock Liquide, Stock d'Emballages Vides et Dettes Bouteilles).

---

## 1. Analyse & Clarification Métier : Ajustements vs Vente au Détail

### A. Pourquoi l'Ajustement Liquide (Casse) n'impacte pas `emballage_stock` ?
Dans l'équation fondamentale du dépôt :
$$\text{Parc Total} = \text{Casiers Pleins (via stock\_movements)} + \text{Casiers Vides (emballage\_stock)} + \text{Dettes Clients} - \text{Dettes Fournisseurs}$$
* **Lors d'une casse de bouteille pleine (magasin) :**
  - La bouteille de bière est brisée $\rightarrow$ `stock_movements` enregistre $-1$ bouteille.
  - Le verre est en morceaux (inutilisable) $\rightarrow$ aucun verre vide n'entre dans `emballage_stock`.
  - Le compteur *Casiers Pleins* baisse de $\frac{1}{12}$, ce qui fait baisser le patrimoine total du dépôt de 1 bouteille. Le système est **physiquement et comptablement exact**.

### B. Ce qui se passe lors d'une Vente au Détail (ex: 7 bouteilles vendues) :
1. **Stock Liquide :** Déstocke $7$ bouteilles pleines ($\frac{7}{12}$ casier dans `stock_movements`).
2. **Caisse :** Encaissement immédiat ($7 \times 600\text{ FCFA} = 4\,200\text{ FCFA}$).
3. **Emballages physiques :**
   * *Si consommée sur place ou échangée avec du verre vide :* Les $7$ bouteilles vides en verre restent au dépôt $\rightarrow$ `emballage_stock.loose_bottles` fait $+7\text{ btls}$.
   * *Si emportée sans verre :* `client_emballage_debts.loose_bottles_due` fait $+7\text{ btls}$ (dette de bouteilles pures, **sans créer de dette de casier plastique**).

---

## 2. User Review Required

> [!IMPORTANT]
> **Points Clés de la Conception :**
> 1. **Zéro Régression :** Le formulaire de vente actuel (`/ventes/form`) reste 100% inchangé.
> 2. **Bouton Dropdown :** Le bouton `[+ Nouvelle Vente]` sur `/ventes` devient un menu déroulant permettant d'accéder en 1 clic soit à la *Vente en Gros*, soit à la *Vente au Détail*.
> 3. **Dette Bouteilles Vrac :** Un client qui emporte des bouteilles nues sans casier plastique accumule une dette strictement libellée en bouteilles (ex: `13 btl [SABC 12]`), sans fausse conversion en casier plastique `1 c. + 1 btl`.

---

## 3. Modifications Proposées

### A. Contrôleur & Routage (`VentesController.php`)
#### [MODIFY] [VentesController.php](file:///c:/xampp/htdocs/webapp/app/Controllers/VentesController.php)
- Ajouter la méthode `enDetail()` : charge la vue dédiée `/ventes/en_detail.php` avec les produits, clients et comptes de caisse.
- Ajouter la méthode `saveEnDetail()` : valide et enregistre la transaction de vente au détail.

---

### B. Modèle Ventes (`VentesModel.php`)
#### [MODIFY] [VentesModel.php](file:///c:/xampp/htdocs/webapp/app/Models/VentesModel.php)
- Ajouter la méthode `addRetailSale($data)` :
  - Calcule le total sur la base du prix unitaire bouteille (`price_unite`).
  - Déstocke le liquide dans `stock_movements` avec `format_type = 'unite'` et `stock_equivalent = round(qty / factor, 4)`.
  - Enregistre la transaction de caisse dans `cash_transactions`.
  - Met à jour `emballage_stock.loose_bottles` pour les bouteilles vides récupérées.
  - Met à jour `client_emballage_debts.loose_bottles_due` pour les bouteilles emportées sans retour (sans toucher à `crates_due`).

---

### C. Modèle Clients & Affichage des Dettes (`ClientsModel.php`)
#### [MODIFY] [ClientsModel.php](file:///c:/xampp/htdocs/webapp/app/Models/ClientsModel.php)
- Ajuster la concaténation de `packaging_debts_detail` pour que les clients ayant une dette pure de bouteilles (ex: `crates_due = 0` et `loose_bottles_due = 13`) affichent fidèlement **`13 btl. [SABC 12]`** sans forcer un modulo erroné.

---

### D. Vue Dédiée « Vente au Détail » (`ventes/en_detail.php`)
#### [NEW] [en_detail.php](file:///c:/xampp/htdocs/webapp/app/Views/pages/ventes/en_detail.php)
- Formulaire ultra-rapide type "Caisse Comptoir" :
  - Sélection du client (Client Comptoir par défaut).
  - Lignes d'articles dynamiques avec sélection du produit, quantité en bouteilles, prix unitaire.
  - Sélecteur de mode d'emballage par ligne :
    - 🟢 *Consommation sur place (Verre consigné reste au dépôt)*
    - 🔵 *À emporter avec échange de bouteilles vides*
    - 🔴 *À emporter sans bouteilles (Dette de verre)*
  - Bloc d'encaissement (Espèces, Avoir ou Crédit).
  - Modale de confirmation avant impression de la facture.

---

### E. Bouton Dropdown `[+ Nouvelle Vente]` (`ventes/index.php` & `ventes/invoice.php`)
#### [MODIFY] [ventes/index.php](file:///c:/xampp/htdocs/webapp/app/Views/pages/ventes/index.php)
- Remplacer le simple bouton par un menu déroulant élégant :
  - 📦 **Vente en Gros & Demi-Gros** $\rightarrow$ `/ventes/form`
  - 🍾 **Vente au Détail (Bouteilles)** $\rightarrow$ `/ventes/en-detail`

---

## 4. Plan de Vérification

### Tests Automatisés
- Créer un script `scratch/test_vente_en_detail.php` pour simuler :
  1. **Scénario 1 (Sur place) :** Vente de 7 bouteilles de Beaufort bues sur place $\rightarrow$ vérification : $-7$ bières en stock liquide, $+7$ bouteilles vides dans `emballage_stock`, $+4\,200\text{ FCFA}$ en caisse, dette client $= 0$.
  2. **Scénario 2 (Emporté sans verre) :** Vente de 5 bouteilles de Kadji emportées $\rightarrow$ vérification : $-5$ bières en stock, $+5$ bouteilles de dette dans `client_emballage_debts`, $0$ casier plastique dû.
  3. **Scénario 3 (Bilan Emballages) :** Vérification que `full_crates_stock` et `totalOwned` dans `EmballagesModel` restent parfaitement cohérents.

### Tests Manuels
- Tester l'interface `/ventes/en-detail` dans le navigateur : saisie rapide, calcul en direct, validation et affichage de la facture.
