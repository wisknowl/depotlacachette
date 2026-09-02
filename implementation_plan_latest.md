# Plan d'Implémentation - Remise Commerciale, Gestion des Avoirs / Monnaie en Attente & Correction Formulaire Ventes

Ce plan détaille l'intégration complète de la **Remise Commerciale** et de la **Déduction / Création d'Avoirs Clients (Monnaie en attente)** dans le module Ventes, ainsi que la correction du bug d'affichage JavaScript sur le formulaire de vente.

---

## 1. Contexte & Objectifs

1. **Correction Frontend Formulaire Ventes :**  
   Corriger le mismatch d'identifiants JavaScript (`partial_paid_disp` et `partial_due_disp`) pour que le récapitulatif du paiement partiel affiche les vrais montants versés et les dettes restantes au lieu de `0 FCFA`.
2. **Remise Commerciale / Déduction sur Vente :**  
   - Ajouter le champ `discount_amount` dans la table `sales` et dans [schema.sql](file:///c:/xampp/htdocs/webapp/database/schema.sql).
   - Permettre la saisie d'une **Remise / Réduction (FCFA)** dans le formulaire [ventes/form.php](file:///c:/xampp/htdocs/webapp/app/Views/pages/ventes/form.php).
   - Recalculer en direct :
     $$\text{Sous-Total Brut} - \text{Remise / Avoir Déduit} = \mathbf{\text{NET À PAYER}}$$
   - Synchroniser le champ *Montant Encaissé (Cash)* et recalculer automatiquement la dette client éventuelle.
   - Afficher les détails (Sous-total, Remise, Net à Payer, Versé, Reste dû) sur la facture imprimable [invoice.php](file:///c:/xampp/htdocs/webapp/app/Views/pages/ventes/invoice.php).
3. **Détection & Gestion des Avoirs Clients (Monnaie en attente) :**  
   - Détecter si le client sélectionné a un solde créditeur / avoir ($< 0$ FCFA) et afficher le badge `🟢 Avoir Disponible : X FCFA`.
   - Fournir un bouton rapide pour appliquer cet avoir directement sur la commande en cours.
   - Ajouter une modale rapide **« + Enregistrer un Avoir / Reliquat Monnaie »** (sans quitter le travail en cours) accessible depuis `/ventes/form`, `/clients` et `/reglements`.

---

## 2. Modifications Proposées

### A. Base de Données & Schéma
#### [MODIFY] [database/schema.sql](file:///c:/xampp/htdocs/webapp/database/schema.sql)
- Ajouter la colonne `discount_amount` `DECIMAL(15,2) NOT NULL DEFAULT 0.00` à la table `sales`.
- Exécuter la migration SQL `ALTER TABLE sales ADD COLUMN discount_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER total_amount;` (si non présente).

---

### B. Modèle & Contrôleur des Ventes
#### [MODIFY] [app/Models/VentesModel.php](file:///c:/xampp/htdocs/webapp/app/Models/VentesModel.php)
- Récupérer `discount_amount` depuis `$data['discount_amount'] ?? 0`.
- Calculer le Net à Payer : `$netAmount = max(0, $subTotal - $discountAmount);`.
- Enregistrer `total_amount = $netAmount` (ou total brut et net), `discount_amount = $discountAmount`.
- Calculer l'encaissement et la dette :
  - `$amountPaid = min($netAmount, max(0, floatval($data['amount_paid'] ?? $netAmount)));`
  - `$amountDue = max(0, $netAmount - $amountPaid);`

#### [MODIFY] [app/Controllers/VentesController.php](file:///c:/xampp/htdocs/webapp/app/Controllers/VentesController.php)
- Transmettre `discount_amount` dans le tableau `$data` lors de `save()`.

---

### C. Vues des Ventes & Facturation
#### [MODIFY] [app/Views/pages/ventes/form.php](file:///c:/xampp/htdocs/webapp/app/Views/pages/ventes/form.php)
1. **Corriger le bug JS :** Mettre à jour `calculateTotals()` pour cibler correctement `partial_paid_disp` et `partial_due_disp`.
2. **Ajouter le bloc Remise & Avoir :**
   - Champ `discount_amount` avec placeholder `0 FCFA`.
   - Badge d'avoir disponible dynamique à la sélection du client (`data-debt < 0`).
   - Bouton « Appliquer l'Avoir » en un clic.
3. **Modale Rapide « Enregistrer Reliquat Monnaie » :**
   - Permet au caissier de créditer le compte d'un client lorsqu'il manque des pièces de monnaie physique en caisse.

#### [MODIFY] [app/Views/pages/ventes/invoice.php](file:///c:/xampp/htdocs/webapp/app/Views/pages/ventes/invoice.php)
- Afficher la décomposition financière claire :
  - **Sous-Total Brut :** `7 200 FCFA`
  - **Remise / Avoir Déduit :** `-200 FCFA` *(si > 0)*
  - **NET À PAYER :** `7 000 FCFA`
  - **Montant Versé (Comptant) :** `6 000 FCFA`
  - **Reste Dû (Créance Client) :** `1 000 FCFA`

---

### D. Vue Clients & Règlements
#### [MODIFY] [app/Views/pages/clients/index.php](file:///c:/xampp/htdocs/webapp/app/Views/pages/clients/index.php)
- Mettre en évidence les soldes créditeurs (avoirs) en badge vert : `Avoir : 200 FCFA 🟢 (Monnaie en attente)`.
- Ajouter un bouton d'action rapide `+ Avoir / Monnaie`.

#### [MODIFY] [app/Views/pages/reglements/index.php](file:///c:/xampp/htdocs/webapp/app/Views/pages/reglements/index.php)
- Ajouter le bouton `+ Enregistrer un Avoir / Acompte`.

---

## 3. Plan de Vérification

### Tests Automatisés & Script
- Vérification de la syntaxe PHP sur tous les fichiers modifiés (`php -l`).
- Script de test (`scratch/test_ventes_remise_avoir.php`) vérifiant :
  1. La création d'une vente avec remise commerciale de 200 FCFA (Brut: 7 200 $\rightarrow$ Net: 7 000 $\rightarrow$ Versé: 6 000 $\rightarrow$ Dette: 1 000).
  2. La création d'un avoir client et sa déduction automatique sur la vente suivante.
  3. L'intégrité de la facture générée.

### Vérification Manuelle
- Tester le formulaire de vente dans le navigateur : modification des montants, calculs partiels en temps réel sans erreur JS dans la console.
