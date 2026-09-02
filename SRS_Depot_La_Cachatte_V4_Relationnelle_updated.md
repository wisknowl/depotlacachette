# SRS — Dépôt La Cachatte : Passage V3 → V4 "Relationnelle Excel"
### Software Requirements Specification pour la normalisation complète du classeur en architecture type MySQL + PHP + HTML, entièrement recréée dans Excel

**Fichier source analysé :** `Depot_La_Cachatte_Gestion_V3_Relationnelle.xlsx`
**Feuilles détectées :** Tableau de bord, Paramètres, Produits, Clients, Fournisseurs, Mouvements de stock, Ventes, Achats, Dépenses, Règlements, Paiements fournisseurs, Caisse, Guide

**Version 2 — modifications par rapport à la V1 de ce document :**
- `Caisse` : confirmé Option A (auto-alimentation VBA), `Référence source` transformée de champ saisi en champ calculé (§3.4, nouveau).
- 3 référentiels ajoutés après revue : `Ref_Utilisateurs`, `Ref_TypeClient`, `Ref_TypeSourceCaisse` (9 tables `Ref_*` au total, au lieu de 6).
- Nouvelle section §10bis expliquant précisément le mécanisme technique de masquage (Very Hidden + mot de passe VBA + protection de feuille) et pourquoi le développeur garde un accès total.
- Dictionnaire de données (§12) mis à jour avec les colonnes `ID Utilisateur` sur les 7 tables concernées.

---

## 1. Objectif

Transformer le classeur V3 (déjà fonctionnel, avec formules et listes déroulantes) en une architecture V4 organisée en **couches**, à l'image d'une application web :

| Monde web | Équivalent Excel V4 |
|---|---|
| Tables MySQL | Feuilles-Tables (données atomiques, ID en clé primaire) |
| Tables de jointure / lookup | Feuilles-Référentiels normalisées avec ID |
| Vues SQL / requêtes agrégées | Tableaux croisés dynamiques (TCD) cachés |
| Backend PHP | Modules VBA |
| Formulaires HTML | Feuilles-Formulaire avec Data Validation + UserForms VBA |
| Pages web | Feuilles-Vue (listes, recherche, impression) |
| Page d'accueil | Tableau de bord |
| Menu / navbar | Feuille "Menu" avec formes cliquables + liens hypertexte |

---

## 2. Diagnostic de l'existant (V3)

Ce qui est **déjà bon** et à conserver :
- ID auto-générés par formule (`P001`, `CL001`, `FO001`, `V00001`, `A00001`, `M00001`, `C00001`) → équivalent des `AUTO_INCREMENT` / clés primaires.
- Colonnes "libellé" (ex. `Produit`, `Client`, `Fournisseur` dans Ventes/Achats/Mouvements) calculées par `XLOOKUP`/array formula à partir de l'ID → c'est le bon réflexe relationnel (on ne stocke pas le texte, on le recalcule). **Ne rien changer ici.**
- Listes déroulantes (Data Validation) déjà branchées sur Produits, Clients, Fournisseurs et Paramètres → équivalent de vos `<select>` HTML.
- Champs calculés cohérents : stock actuel, statut rupture, solde client/fournisseur, solde caisse par compte.

Ce qui **casse la normalisation** et doit être corrigé :
1. **`Paramètres` n'est pas une table, c'est 6 tables collées côte à côte sans ID** (Catégories, Formats de vente, Modes de paiement, Types de mouvement, Comptes caisse, Catégories dépenses). Toutes les feuilles filles (Produits.Catégorie, Ventes.Format, Ventes.Mode de paiement, Dépenses.Catégorie, Caisse.Compte, Mouvements.Type…) stockent la **valeur texte**, pas un ID. C'est l'équivalent MySQL d'avoir mis `VARCHAR` au lieu d'un `INT FOREIGN KEY` — ça marche, mais ce n'est pas normalisé et ça casse si un libellé est renommé.
2. Aucune macro VBA présente dans le classeur (`vba_archive = None`) → aucune automatisation en arrière-plan pour l'instant, tout repose sur des formules.
3. Aucun nom défini (Named Ranges) → les formules et listes utilisent des références absolues (`$A:$A`), fragiles si on insère des lignes/colonnes.
4. `Tableau de bord` est une coquille vide (uniquement un titre) → le "landing sheet" n'existe pas encore visuellement.
5. `Caisse` fait déjà un lien polymorphe (`Type source` + `ID Source` + `Référence source`) vers Ventes/Achats/Dépenses/Règlements/Paiements → bon réflexe relationnel, mais non documenté ni protégé par validation.
6. Aucune séparation visuelle/fonctionnelle entre "feuille où on saisit" et "feuille où on consulte" — tout est mélangé dans la même grille tableau.
7. Pas de feuilles cachées : tout est visible, y compris ce qui devrait être du "backend" (référentiels, tables de calcul).

---

## 3. Modèle de données cible (schéma relationnel)

### 3.1 Tables-Entités (déjà atomiques dans V3 — à conserver telles quelles)

| Table | PK | FK sortantes | Champs stockés (saisis) | Champs calculés (formule/VBA) |
|---|---|---|---|---|
| `Produits` | ID Produit | ID Catégorie → Ref_Categories · ID Format → Ref_Formats · ID Fournisseur → Fournisseurs | Nom, Unité principale, Prix d'achat, 3 prix de vente, Stock initial, Stock minimum, Facteur casse | Stock actuel, Valeur du stock, Statut |
| `Clients` | ID Client | — | Nom, Téléphone, Adresse, Type client, Observation | Solde dû |
| `Fournisseurs` | ID Fournisseur | — | Nom, Téléphone, Adresse, Contact, Observation | Solde dû |
| `Mouvements_de_stock` | ID Mouvement | ID Produit → Produits · ID Type → Ref_TypeMouvement · ID Format → Ref_Formats | Date, Quantité saisie, Prix de référence, Référence, Utilisateur, Observation | Produit (libellé), Équivalent stock, Valeur casse/perte, Statut |
| `Ventes` | ID Vente | ID Client → Clients · ID Produit → Produits · ID Format → Ref_Formats · ID ModePaiement → Ref_ModesPaiement | Date, Quantité, Utilisateur, Référence | Client, Produit (libellés), Équivalent stock, Prix unitaire, Montant, Statut, Contrôle stock |
| `Achats` | ID Achat | ID Fournisseur → Fournisseurs · ID Produit → Produits · ID Format → Ref_Formats · ID ModePaiement → Ref_ModesPaiement | Date, Quantité, Prix d'achat, Utilisateur, Référence | Fournisseur, Produit (libellés), Équivalent stock, Montant |
| `Depenses` | ID Dépense | ID Catégorie → Ref_CategoriesDepenses · ID ModePaiement → Ref_ModesPaiement | Date, Description, Montant, Bénéficiaire, Utilisateur, Référence | Statut |
| `Reglements` | ID Règlement | ID Client → Clients · ID Compte → Ref_ComptesCaisse | Date, Montant, Référence, Utilisateur, Observation | Client (libellé) |
| `Paiements_fournisseurs` | ID Paiement | ID Fournisseur → Fournisseurs · ID Compte → Ref_ComptesCaisse | Date, Montant, Référence, Utilisateur, Observation | Fournisseur (libellé) |
| `Caisse` | ID Opération | ID Compte → Ref_ComptesCaisse · ID TypeSource → Ref_TypeSourceCaisse · ID Source → lien polymorphe vers Ventes/Achats/Depenses/Reglements/Paiements_fournisseurs · ID_Utilisateur → Ref_Utilisateurs | Date, Description, Entrée, Sortie, Observation | Solde compte, Statut, Référence source *(désormais calculée, voir §3.4)*, Utilisateur *(libellé calculé)* |

> Le lien polymorphe de `Caisse` (Type source + ID Source) est l'équivalent d'une table MySQL sans FK stricte (comme un `payments` table qui référence `orders` ou `invoices` selon le type). On le documente, on ne le force pas en FK unique — c'est un choix correct pour ce cas d'usage.

### 3.4 Correction — `Référence source` (Caisse) n'est pas une donnée, c'est une valeur dérivée

**Diagnostic (vérifié directement dans les formules du fichier fourni) :** en V3, les colonnes `Date`, `Référence source`, `Type source`, `ID Source`, `Description`, `Compte`, `Entrée`, `Sortie`, `Utilisateur`, `Observation` de `Caisse` sont **toutes des cellules vides sans formule** — aucune n'est reliée automatiquement à Ventes/Achats/Dépenses/Règlements/Paiements. Seules `ID Opération`, `Solde compte` et `Statut` sont calculées. Autrement dit, dans le fichier actuel, `Caisse` est une seconde saisie manuelle intégrale — c'est la source de redondance la plus lourde du classeur.

`Référence source` en particulier ne doit **jamais être une saisie**. C'est simplement une copie du champ `Référence` qui existe déjà sur la ligne d'origine (Vente, Achat, Dépense, Règlement, Paiement fournisseur). Une fois qu'on a `ID TypeSource` + `ID Source`, `Référence source` se calcule par XLOOKUP vers la bonne table selon le type — exactement le même principe que les colonnes `Produit`/`Client`/`Fournisseur` déjà calculées ailleurs dans le classeur. La garder en saisie manuelle, c'est stocker deux fois la même information et risquer qu'elles divergent (anomalie de mise à jour classique en base de données non normalisée).

**Décision retenue (Option A) :** `Caisse` devient **auto-alimentée par VBA** pour toute ligne qui a une origine transactionnelle (Vente/Achat/Dépense réglés autrement qu'à Crédit, Règlement, Paiement fournisseur) — voir macro `modCaisseAuto` en §7. Les lignes de saisie manuelle directe dans `Caisse` sont réservées aux cas qui n'ont pas de table d'origine : solde d'ouverture, transfert entre comptes (ex. Espèces → Banque), correction (`Ajustement`). Pour ces cas-là, `ID Source` reste vide et `Référence source` est laissée éditable manuellement (ou vide).

### 3.2 Tables-Référentiels (à créer — normalisation de `Paramètres`)

Chaque bloc actuel de `Paramètres` devient une **vraie table** avec ID + Libellé, sur des feuilles séparées ou une feuille "Référentiels" en zones bien délimitées et nommées :

| Nouvelle table | PK | Champ | Remplace le bloc Paramètres |
|---|---|---|---|
| `Ref_Categories` | ID_Cat (C1, C2…) | Libellé (Bière, Eau, Jus, Boisson gazeuse, Boisson énergisante, Autre) | Colonne A |
| `Ref_Formats` | ID_Fmt (F1, F2…) | Libellé (Casier, Demi-casier, Palette, Demi-palette, Unité, Bouteille) | Colonne C |
| `Ref_ModesPaiement` | ID_MP (MP1…) | Libellé (Espèces, MTN MoMo, Orange Money, Banque, Crédit) | Colonne E |
| `Ref_TypeMouvement` | ID_TM (TM1…) | Libellé (Entrée, Vente, Casse, Perte, Ajustement, Retour) | Colonne G |
| `Ref_ComptesCaisse` | ID_Cpt (CP1…) | Libellé (Espèces, MTN MoMo, Orange Money, Banque) | Colonne I |
| `Ref_CategoriesDepenses` | ID_CD (CD1…) | Libellé (Transport, Électricité, Salaires, Réparation, Carburant, Nettoyage, Livraison, Autre) | Colonne K |
| `Ref_Utilisateurs` *(nouveau — ajouté après revue)* | ID_Utl (U1, U2…) | Nom, Rôle, Actif (Oui/Non) | Colonne `Utilisateur`, actuellement du texte libre répété dans **7 tables** (Mouvements, Ventes, Achats, Dépenses, Règlements, Paiements fournisseurs, Caisse) |
| `Ref_TypeClient` *(nouveau — ajouté après revue)* | ID_TC (TC1…) | Libellé (Particulier, Revendeur, Restaurant/Bar, Autre — à confirmer selon vos valeurs réelles) | Colonne `Type client` de `Clients`, actuellement texte libre |
| `Ref_TypeSourceCaisse` *(nouveau — ajouté après revue)* | ID_TSC (TSC1…) | Libellé (Vente, Achat, Dépense, Règlement, Paiement fournisseur, Ajustement) | Colonne `Type source` de `Caisse` — **à ne pas confondre** avec `Ref_TypeMouvement` (Entrée/Vente/Casse/Perte/Ajustement/Retour, qui décrit un mouvement de stock, pas un mouvement de caisse) |

> **Pourquoi `Utilisateur` mérite sa propre table :** un champ texte libre répété sur 7 tables est exactement le même risque que l'ancien `Paramètres` — une faute de frappe ("Jean" vs "jean" vs "Jean K.") casse silencieusement tous les regroupements par utilisateur (ex. "combien de ventes a fait Jean ce mois-ci ?"). Une table `Ref_Utilisateurs` avec ID stable règle ça, et devient aussi l'endroit naturel pour gérer plus tard des droits d'accès par rôle (qui peut ouvrir quel formulaire).

> **Champs volontairement laissés en texte libre (pas de normalisation nécessaire) :** `Référence` (Ventes/Achats/Dépenses/Règlements/Paiements — numéro de pièce unique, pas une catégorie répétée), `Bénéficiaire` (Dépenses — nom de tiers ouvert), `Observation`/`Description` (notes libres), `Contact` (Fournisseurs). Ces champs ne créent pas d'anomalie de mise à jour car ils ne sont ni des catégories fermées ni recalculés par agrégation — les normaliser ajouterait de la complexité sans bénéfice.

**Pourquoi normaliser les référentiels en général :** aujourd'hui, si vous renommez "MTN MoMo" en "MoMo (MTN)" dans Paramètres, toutes les lignes déjà saisies dans Ventes/Règlements gardent l'ancien texte et ne matchent plus dans les `SUMIFS`/TCD. Avec un ID stable, le libellé peut changer sans casser l'historique — exactement le rôle d'une clé étrangère.

Chaque table Ventes/Achats/Dépenses/etc. garde alors deux colonnes : `ID_ModePaiement` (saisi via liste déroulante liée à `Ref_ModesPaiement`) et `ModePaiement` (libellé, calculé par XLOOKUP) — même logique que ce qui existe déjà pour Produits/Clients/Fournisseurs.

### 3.3 Tables de jointure (many-to-many) — optionnelles, à activer seulement si besoin réel

Le modèle actuel est entièrement 1-N (un produit a un seul fournisseur, une vente a un seul mode de paiement). Si un jour vous voulez :
- **Un produit avec plusieurs fournisseurs possibles** → créer `Rel_Produit_Fournisseur` (ID Produit, ID Fournisseur, Prix négocié, Délai livraison).
- **Une vente réglée par plusieurs modes de paiement en même temps** (ex. moitié espèces, moitié Mobile Money) → créer `Rel_Vente_Paiement` (ID Vente, ID ModePaiement, Montant).

Ne pas les créer maintenant — les ajouter seulement si le besoin métier apparaît, pour ne pas sur-complexifier.

---

## 4. Architecture des feuilles (structure du classeur V4)

```
CLASSEUR
│
├── 🏠 MENU                      [visible]  → navbar, boutons vers chaque module
├── 📊 DASHBOARD                 [visible]  → landing page, KPI, graphiques
│
├── ── SAISIE (formulaires "HTML-like") ──
├── 📝 FORM_Produit              [visible]  → formulaire 1 produit à la fois
├── 📝 FORM_Client               [visible]
├── 📝 FORM_Fournisseur          [visible]
├── 📝 FORM_Mouvement            [visible]
├── 📝 FORM_Vente                [visible]
├── 📝 FORM_Achat                [visible]
├── 📝 FORM_Depense               [visible]
├── 📝 FORM_Reglement            [visible]
├── 📝 FORM_PaiementFournisseur  [visible]
│
├── ── VUES (consultation, équivalent pages web "liste") ──
├── 👁 VIEW_Stock                [visible, lecture seule/protégée]
├── 👁 VIEW_Ventes               [visible, lecture seule/protégée]
├── 👁 VIEW_Achats               [visible, lecture seule/protégée]
├── 👁 VIEW_ComptesClients       [visible, lecture seule/protégée]
├── 👁 VIEW_ComptesFournisseurs  [visible, lecture seule/protégée]
├── 👁 VIEW_Caisse               [visible, lecture seule/protégée]
│
├── ── TABLES (base de données, équivalent MySQL) ──
├── 🗄 Produits, Clients, Fournisseurs, Mouvements_de_stock,
│    Ventes, Achats, Depenses, Reglements,
│    Paiements_fournisseurs, Caisse            [feuilles existantes, gardées, RENOMMÉES éventuellement sans espace/accent pour compat VBA]
│
├── ── RÉFÉRENTIELS (lookup tables) ──
├── 🔒 Ref_Categories, Ref_Formats, Ref_ModesPaiement,
│    Ref_TypeMouvement, Ref_ComptesCaisse, Ref_CategoriesDepenses,
│    Ref_Utilisateurs, Ref_TypeClient, Ref_TypeSourceCaisse   [MASQUÉES ou très protégées — 9 tables au total]
│
├── ── COUCHE ANALYTIQUE (backend caché) ──
├── 🔒 PIVOT_Data                [MASQUÉE] → zone de TCD sources
├── 🔒 PIVOT_KPI                 [MASQUÉE] → TCD alimentant le Dashboard
│
└── 📘 GUIDE                     [visible]  → mode d'emploi utilisateur
```

**Règle de visibilité :**
- Feuilles `Ref_*` et `PIVOT_*` → `xlSheetVeryHidden` (masquées y compris via clic droit, seulement visibles via VBA/éditeur), pour empêcher un utilisateur non-technique de les découvrir et de les casser.
- Feuilles `Produits`, `Ventes`, etc. (tables) → masquées normalement (`xlSheetHidden`) ou protégées par mot de passe si vous voulez garder un accès "admin" rapide.
- Feuilles `FORM_*`, `VIEW_*`, `MENU`, `DASHBOARD`, `GUIDE` → seules feuilles visibles par défaut pour l'utilisateur final.

---

## 5. Feuille MENU — Navbar

Une feuille dédiée, sans grille (`Affichage > Quadrillage` décoché), largeur de colonne réduite pour un rendu "app" :

- Bandeau supérieur : logo/texte "DÉPÔT LA CACHATTE" (cellule fusionnée, fond coloré).
- Rangée de **formes rectangulaires arrondies** stylées comme des boutons de menu web, une par module :
  - `Accueil / Dashboard` → lien vers `DASHBOARD`
  - `Nouveau Produit`, `Nouveau Client`, `Nouveau Fournisseur`
  - `Enregistrer une Vente`, `Enregistrer un Achat`, `Enregistrer un Mouvement de stock`
  - `Enregistrer une Dépense`, `Enregistrer un Règlement`, `Enregistrer un Paiement fournisseur`
  - `Voir le Stock`, `Voir les Ventes`, `Voir les Comptes clients`, `Voir la Caisse`
  - `Guide d'utilisation`
- Chaque forme : clic droit → "Lien hypertexte" → "Emplacement dans ce document" → feuille + cellule cible (ou macro assignée qui fait `Sheets("FORM_Vente").Activate` + reset du formulaire).
- Sur chaque feuille `FORM_*` et `VIEW_*`, ajouter un petit bouton "⬅ Retour au menu" en haut à gauche (même position partout, pour cohérence UX).

---

## 6. Formulaires (équivalent HTML `<form>`)

### 6.1 Méthode retenue : hybride

- **Formulaires simples** (Produit, Client, Fournisseur, Dépense) → zone de saisie sur feuille dédiée avec Data Validation stricte (listes, dates, nombres positifs), et un bouton "Enregistrer" qui déclenche une macro VBA.
- **Formulaires transactionnels** (Vente, Achat, Mouvement, Règlement, Paiement) → UserForm VBA (vraie fenêtre popup), car ils touchent plusieurs tables à la fois (ex. une vente doit vérifier le stock avant de valider) et bénéficient de contrôles plus stricts qu'une simple liste déroulante.

### 6.2 Exemple de spécification — `FORM_Vente` (UserForm)

| Contrôle | Type | Source / Règle de validation | Action |
|---|---|---|---|
| Date | DTPicker / TextBox | Défaut = aujourd'hui, refuse date future | — |
| Client | ComboBox | RowSource = `Clients[Nom]` (ou "Client comptant" si vente non enregistrée sous un compte) | Rempli `ID Client` en interne |
| Produit | ComboBox | RowSource = `Produits[Nom]`, filtré sur Statut ≠ "RUPTURE" | Rempli `ID Produit`, affiche stock dispo |
| Format | ComboBox | RowSource = `Ref_Formats[Libellé]` | — |
| Quantité | TextBox | Numérique > 0, ≤ stock disponible calculé en temps réel | Message d'erreur immédiat si dépassement |
| Mode de paiement | ComboBox | RowSource = `Ref_ModesPaiement[Libellé]` | Si "Crédit" → vérifie/alerte sur solde client |
| Bouton Valider | CommandButton | — | Appelle `EnregistrerVente()` (voir §7) |
| Bouton Annuler | CommandButton | — | `Unload Me` |

Le même schéma (ComboBox alimentées par les tables Ref_* et par les tables Entités, TextBox validées, bouton Valider → macro dédiée) s'applique à `FORM_Achat`, `FORM_Mouvement`, `FORM_Reglement`, `FORM_PaiementFournisseur`.

### 6.3 Formulaires simples — exemple `FORM_Produit` (feuille, pas UserForm)

Zone de saisie sur une feuille, une seule "fiche" à la fois (pas un tableau de 200 lignes) :

```
Nom du produit        [_______________]
Catégorie              [liste déroulante Ref_Categories]
Unité principale       [liste déroulante Ref_Formats]
Prix d'achat           [_____] FCFA
Prix Casier/Palette    [_____] FCFA
Prix Demi-format       [_____] FCFA
Prix Unité/Bouteille   [_____] FCFA
Stock initial          [_____]
Stock minimum          [_____]
Facteur casse (unités) [_____]
Fournisseur            [liste déroulante Fournisseurs]

        [ Enregistrer ]   [ Vider le formulaire ]
```

Le bouton "Enregistrer" exécute une macro qui : valide les champs obligatoires → trouve la première ligne vide de la table `Produits` → y écrit les valeurs → laisse les formules (ID, Stock actuel, Statut) se recalculer automatiquement → vide le formulaire → affiche un message de confirmation.

---

## 7. VBA — Modules d'automatisation (le "backend PHP")

| Module | Rôle |
|---|---|
| `modNavigation` | Gère tous les boutons de la navbar : activer une feuille, réinitialiser un formulaire, revenir au menu |
| `modSaisieProduits` | `EnregistrerProduit()`, `ViderFormProduit()`, `ModifierProduit(id)` |
| `modSaisieTiers` | Idem pour Clients et Fournisseurs |
| `modTransactions` | `EnregistrerVente()`, `EnregistrerAchat()`, `EnregistrerMouvement()` — contrôlent le stock disponible avant d'écrire, empêchent une vente si `STOCK INSUFFISANT` (le champ existe déjà en V3, colonne P de Ventes) |
| `modReglements` | `EnregistrerReglement()`, `EnregistrerPaiementFournisseur()` — poussent automatiquement une ligne miroir dans `Caisse` (Type source = "Règlement"/"Paiement fournisseur", ID Source = l'ID généré) |
| `modCaisseAuto` | **(Option A retenue)** À chaque validation d'une Vente (paiement ≠ Crédit), d'un Achat (paiement ≠ Crédit), d'une Dépense, d'un Règlement ou d'un Paiement fournisseur → insère automatiquement la ligne correspondante dans `Caisse` : `ID TypeSource` = table d'origine, `ID Source` = l'ID généré sur cette ligne, `Compte` = déduit du mode de paiement choisi, `Entrée`/`Sortie` = montant selon le sens. `Référence source` et `Utilisateur` ne sont **jamais tapés** ici, ils sont calculés par XLOOKUP à partir de `ID TypeSource`+`ID Source` une fois la ligne insérée. Seules les lignes sans origine (solde d'ouverture, transfert entre comptes, `Ajustement`) restent saisies à la main, avec `ID Source` laissé vide. |
| `modValidation` | Fonctions réutilisables : `ChampObligatoireRempli()`, `EstNombrePositif()`, `DateValide()`, `StockSuffisant(idProduit, quantite)` |
| `modDashboard` | Rafraîchit les TCD cachés (`ThisWorkbook.RefreshAll` ciblé) et met à jour les cartes KPI du Dashboard à l'ouverture du classeur (`Workbook_Open`) |
| `modSecurite` | Protège/déprotège les feuilles Tables et Ref_* le temps d'une écriture VBA (`Unprotect` → écrire → `Protect`), pour qu'un utilisateur ne puisse jamais modifier ces feuilles à la main |
| `modUtilitaires` | `ProchaineLigneVide(feuille)`, `Sauvegarde()` (copie horodatée du classeur), gestion des erreurs centralisée |

**Principe clé :** aucune donnée n'est jamais écrite directement par l'utilisateur dans les feuilles Tables — tout passe par une macro qui écrit dans la table après validation. L'utilisateur ne voit et ne touche que les feuilles `FORM_*`.

---

## 8. Tableaux croisés dynamiques cachés (couche "requêtes SQL agrégées")

Sur la feuille masquée `PIVOT_KPI`, créer des TCD sources connectés directement aux tables (pas de copier-coller de données), un TCD par indicateur :

| TCD | Source | Alimente |
|---|---|---|
| Ventes par produit / mois | `Ventes` | Graphique Dashboard "Top produits" |
| Ventes par mode de paiement | `Ventes` | Carte KPI "Répartition encaissements" |
| Marge par produit | `Ventes` + `Produits` (via Power Pivot / relation, ou colonne calculée) | Carte KPI "Marge du mois" |
| Stock sous seuil minimum | `Produits` filtré Statut | Carte KPI "Alertes stock" |
| Soldes clients à risque | `Clients` trié par Solde dû | Carte KPI "Créances" |
| Dépenses par catégorie | `Depenses` | Graphique Dashboard "Charges" |
| Solde caisse par compte | `Caisse` | Carte KPI "Trésorerie" |

Le `DASHBOARD` visible n'affiche que des **cellules liées** (`=PIVOT_KPI!...`) et des graphiques basés sur ces TCD — jamais les TCD eux-mêmes, ni les tables brutes.

---

## 9. Feuilles VIEW_* (équivalent "pages de consultation" web)

Chaque `VIEW_*` est une copie en **lecture seule** (protection de feuille activée, cellules non verrouillées uniquement sur les filtres) de la table correspondante, avec :
- Mise en forme conditionnelle (ex. rouge si `Statut = RUPTURE`, orange si `À CONTRÔLER`).
- Segments (Slicers) pour filtrer par date, produit, client, statut — équivalent d'un champ de recherche/filtre HTML.
- Un bouton "Exporter en PDF" (macro `ExportPDF(feuille)`) pour équivalent "imprimer/télécharger".

---

## 10. Sécurité et intégrité (équivalent contraintes MySQL)

| Contrainte MySQL | Équivalent Excel V4 |
|---|---|
| `PRIMARY KEY AUTO_INCREMENT` | Formule ID déjà en place, + feuille protégée pour empêcher l'édition manuelle de la colonne ID |
| `FOREIGN KEY` | Data Validation "Liste" pointant sur la colonne ID de la table référencée (déjà fait pour Produits/Clients/Fournisseurs ; à ajouter pour les Ref_*) |
| `NOT NULL` | Data Validation personnalisée + contrôle VBA avant écriture (`ChampObligatoireRempli`) |
| `CHECK (quantite > 0)` | Data Validation "Nombre entier/décimal supérieur à 0" |
| `UNIQUE` | Contrôle VBA avant écriture : `COUNTIF` sur la colonne concernée = 0 avant d'insérer |
| Transactions atomiques | Dans chaque macro `Enregistrer*()` : écrire toutes les lignes liées (ex. Vente + Caisse) dans un seul bloc, avec gestion d'erreur (`On Error GoTo Annuler`) qui annule les écritures partielles si une étape échoue |

---

## 10bis. Comment les tables sont "cachées pour l'utilisateur, pas pour le développeur"

C'est un mécanisme d'interface, pas une séparation de données : trois verrous empilés, chacun contournable seulement par celui qui détient les clés (vous, le développeur/mainteneur).

1. **`xlSheetVeryHidden` — le verrou principal.** Excel connaît trois états de visibilité d'une feuille : Visible, Hidden, et **Very Hidden**. Une feuille `Hidden` peut être révélée par n'importe quel utilisateur via clic droit sur un onglet → "Afficher". Une feuille **Very Hidden** n'apparaît **pas du tout** dans cette boîte de dialogue — aucun bouton du ruban Excel normal ne permet de la révéler. La seule façon de changer cette propriété est d'ouvrir l'éditeur VBA (`Alt+F11`) → sélectionner la feuille dans l'explorateur de projet → changer la propriété `Visible` à `-1 - xlSheetVisible` dans la fenêtre Properties. Un utilisateur qui ne fait que cliquer sur le Menu/Dashboard/Formulaires n'ouvre jamais `Alt+F11` — il ne sait même pas que `Ref_Utilisateurs`, `Ref_Categories`, `PIVOT_KPI`, `Produits`, `Ventes`, etc. existent.

2. **Mot de passe du projet VBA — le verrou du code.** `Alt+F11` → clic droit sur le projet → VBAProject Properties → onglet Protection → mot de passe. Ceci verrouille **tout l'éditeur VBA**, pas seulement le code : même un utilisateur curieux qui arrive à ouvrir `Alt+F11` ne peut ni parcourir l'explorateur de projet pour trouver les feuilles very-hidden, ni lire le code des macros. Ce mot de passe est détenu par vous (le développeur) et documenté dans vos notes de build — jamais communiqué aux utilisateurs finaux.

3. **Protection de feuille + protection de structure du classeur — la défense en profondeur.** Même si une feuille était révélée, chaque `Ref_*`, `PIVOT_*` et table brute est protégée (Révision → Protéger la feuille, avec mot de passe), donc aucune cellule n'est modifiable directement à la main. La protection de la structure du classeur (Révision → Protéger le classeur) bloque en plus l'ajout/suppression/renommage de feuilles.

**Côté développeur**, l'accès reste toujours total : vous détenez le mot de passe VBA et les mots de passe de protection de feuille, donc `Alt+F11` s'ouvre normalement pour vous, la fenêtre Properties permet de basculer la visibilité à volonté, et les macros que vous écrivez peuvent elles-mêmes démasquer → modifier → remasquer par code (c'est exactement ce que fait `modSecurite` : `Unprotect` → écrire → `Protect`, et la même logique s'applique pour un démasquage temporaire lors d'une maintenance). Les seules portes que voit l'utilisateur final sont Menu, Dashboard, Formulaires et Vues — tout le reste est invisible par architecture, pas seulement par convention.

---

## 11. Feuille dupliquée `Guide`

Étendre la feuille `Guide` existante avec une colonne "Feuille concernée" et un lien hypertexte par ligne, pour qu'elle serve aussi de sommaire/plan du site.

---

## 12. Feuille de conversion — Dictionnaire de données complet (annexe technique)

### 12.1 Table `Produits`
| Colonne | Type | Règle |
|---|---|---|
| ID Produit | Texte (PK, auto) | `P` + 3 chiffres |
| Nom du produit | Texte | Obligatoire, unique |
| ID Catégorie *(nouveau)* | Texte (FK → Ref_Categories) | Liste déroulante |
| ID Format principal *(nouveau)* | Texte (FK → Ref_Formats) | Liste déroulante |
| Prix d'achat, Prix Casier/Palette, Prix Demi-format, Prix Unité/Bouteille | Nombre | ≥ 0 |
| Stock initial, Stock minimum | Nombre entier | ≥ 0 |
| Stock actuel *(calculé)*, Valeur du stock *(calculé)*, Statut *(calculé)* | — | Formule existante, inchangée |
| Facteur casse (unités) | Nombre entier | ≥ 1 |
| ID Fournisseur | Texte (FK → Fournisseurs) | Liste déroulante |

### 12.2 Table `Clients` / `Fournisseurs`
ID (PK), Nom/Entreprise, Téléphone, Adresse, **ID Type client *(nouveau, FK → Ref_TypeClient, Clients uniquement)*** / Contact (Fournisseurs), Solde dû *(calculé)*, Observation.

### 12.3 Table `Mouvements_de_stock`
ID Mouvement (PK), Date, ID Produit (FK), ID Type Mouvement *(nouveau, FK → Ref_TypeMouvement)*, ID Format *(nouveau, FK → Ref_Formats)*, Quantité saisie, Équivalent stock *(calculé)*, Prix de référence, Valeur casse/perte *(calculé)*, Référence, **ID Utilisateur *(nouveau, FK → Ref_Utilisateurs)***, Utilisateur *(libellé calculé)*, Observation, Statut *(calculé)*.

### 12.4 Table `Ventes`
ID Vente (PK), Date, ID Client (FK), ID Produit (FK), ID Format *(nouveau, FK)*, Quantité, Équivalent stock *(calculé)*, Prix unitaire *(calculé)*, Montant *(calculé)*, ID ModePaiement *(nouveau, FK)*, **ID Utilisateur *(nouveau, FK)***, Utilisateur *(calculé)*, Référence, Statut *(calculé)*, Contrôle stock *(calculé)*.

### 12.5 Table `Achats`
Structure symétrique à Ventes, avec ID Fournisseur (FK) au lieu de ID Client, et le même **ID Utilisateur** normalisé.

### 12.6 Table `Depenses`
ID Dépense (PK), Date, ID Catégorie *(nouveau, FK → Ref_CategoriesDepenses)*, Description, Montant, ID ModePaiement *(nouveau, FK)*, Bénéficiaire, **ID Utilisateur *(nouveau, FK)***, Utilisateur *(calculé)*, Référence, Statut.

### 12.7 Table `Reglements` / `Paiements_fournisseurs`
ID (PK), Date, ID Client / ID Fournisseur (FK), Montant, ID Compte *(nouveau, FK → Ref_ComptesCaisse)*, Référence, **ID Utilisateur *(nouveau, FK)***, Utilisateur *(calculé)*, Observation.

### 12.8 Table `Caisse` (Option A — auto-alimentée)
ID Opération (PK), Date, **ID TypeSource *(nouveau, FK → Ref_TypeSourceCaisse)***, ID Source *(FK polymorphe vers Ventes/Achats/Depenses/Reglements/Paiements_fournisseurs, vide si ligne manuelle)*, Référence source *(**calculée**, XLOOKUP vers la table pointée par ID TypeSource+ID Source — plus jamais saisie)*, Description, ID Compte *(nouveau, FK → Ref_ComptesCaisse)*, Entrée, Sortie, Solde compte *(calculé)*, **ID Utilisateur *(nouveau, FK, ou hérité automatiquement de la ligne source)***, Utilisateur *(calculé)*, Observation, Statut *(calculé)*.
Lignes insérées automatiquement par `modCaisseAuto` pour tout ce qui a une origine transactionnelle ; lignes manuelles réservées aux soldes d'ouverture, transferts inter-comptes et ajustements (`ID TypeSource` = "Ajustement", `ID Source` vide).

### 12.9 Nouvelles tables référentielles
- `Ref_Utilisateurs` : ID_Utl (PK), Nom, Rôle, Actif
- `Ref_TypeClient` : ID_TC (PK), Libellé
- `Ref_TypeSourceCaisse` : ID_TSC (PK), Libellé

---

## 13. Feuille de route de mise en œuvre (par phases)

1. **Phase 1 — Normalisation des référentiels** : créer les **9** tables `Ref_*` avec ID (Categories, Formats, ModesPaiement, TypeMouvement, ComptesCaisse, CategoriesDepenses, **Utilisateurs, TypeClient, TypeSourceCaisse**), migrer les colonnes texte existantes vers des colonnes ID + XLOOKUP libellé, sans casser les données déjà saisies (mapping texte→ID une seule fois). Inclut la suppression de la saisie manuelle de `Référence source` dans Caisse, remplacée par une formule.
2. **Phase 2 — Verrouillage et masquage** : renommer proprement les feuilles Tables (sans accents/espaces si besoin VBA), les masquer, masquer très fortement les Ref_* et futures PIVOT_*, protéger par mot de passe.
3. **Phase 3 — Dashboard** : construire les TCD sur `PIVOT_KPI`, puis les cartes KPI et graphiques sur `DASHBOARD`.
4. **Phase 4 — Menu / Navbar** : créer la feuille `MENU`, les formes-boutons, les liens hypertexte.
5. **Phase 5 — Formulaires simples** : `FORM_Produit`, `FORM_Client`, `FORM_Fournisseur`, `FORM_Depense` + macros associées.
6. **Phase 6 — Formulaires transactionnels (UserForm VBA)** : Vente, Achat, Mouvement, Règlement, Paiement fournisseur, avec logique métier (contrôle stock, écriture miroir en Caisse).
7. **Phase 7 — Vues de consultation** (`VIEW_*`) avec filtres/segments et export PDF.
8. **Phase 8 — Sécurisation finale** : protections de feuilles, mot de passe VBA, sauvegarde automatique à l'ouverture/fermeture.
9. **Phase 9 — Recette utilisateur** : tester chaque formulaire avec des cas limites (stock insuffisant, champ vide, doublon, date future) avant mise en production.

---

## 14. Ce que ce document ne couvre pas (à décider avec vous ensuite)

- Le mapping exact texte → nouvel ID pour chaque référentiel (à faire une fois, sur vos données réelles actuelles).
- Le code VBA complet de chaque macro (ce document donne le plan ; le code est l'étape suivante, module par module).
- Le design visuel précis (couleurs, polices) du Dashboard et du Menu.
- La gestion multi-utilisateur simultanée (Excel n'est pas fait pour l'accès concurrent comme MySQL ; si plusieurs personnes saisissent en même temps, il faudra en discuter séparément — SharePoint/OneDrive co-authoring a des limites avec VBA).
