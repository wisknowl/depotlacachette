# Migration de "Dépôt La Cachette" vers une Application Web (PHP / MySQL)

## Objectif
Transformer le système de gestion de stock basé sur Excel en une véritable application web robuste, sécurisée, et responsive. L'application utilisera PHP (Vanilla MVC) et MySQL, tout en conservant l'identité visuelle (Bleu nuit / Ambre) et la logique métier relationnelle établie dans la version V4.

## Compréhension du Besoin
- **Technologie** : PHP pur (sans framework lourd comme Laravel), mais structuré de manière professionnelle avec le patron **MVC (Modèle-Vue-Contrôleur)**.
- **Base de données** : MySQL avec un schéma fortement normalisé (faisant suite au modèle V4) et un script de "seed" adapté au contexte Camerounais (ex: FCFA, MTN MoMo, Orange Money, bières locales).
- **Interface Utilisateur (UI)** : Design moderne, "wow effect", avec une barre latérale rétractable (Sidebar), un tableau de bord (Dashboard), des composants réutilisables, et des animations fluides.
- **Fonctionnalité clé** : Exportation des rapports au format Excel.

## User Review Required

> [!IMPORTANT]
> **Choix de la bibliothèque d'export Excel**
> Pour générer les fichiers Excel depuis PHP, je prévois d'utiliser **PhpSpreadsheet** (via Composer). Cela nécessitera que nous initialisions Composer dans le dossier du projet. Êtes-vous d'accord avec ce choix technique ?

> [!IMPORTANT]
> **Dossier de destination et Serveur Local**
> L'application doit tourner sur un serveur web local (comme XAMPP, WAMP, ou Laragon). Dans quel dossier exact de votre ordinateur (ex: `C:\xampp\htdocs\cachette`) souhaitez-vous que je crée les fichiers du projet PHP ?

## Proposed Architecture (MVC Sans Framework)

L'arborescence du projet sera organisée pour être évolutive (scalable) et sécurisée :

```text
/cachette-app
├── public/                 # Racine du serveur web (Document Root) pour la sécurité
│   ├── index.php           # Point d'entrée unique (Front Controller / Routeur)
│   ├── css/style.css       # Design System (Bleu/Ambre, UI moderne)
│   ├── js/app.js           # Logique front-end (Toggle sidebar, interactivité)
│   └── img/                # Favicon et Logos (fournis par vous)
├── app/                    # Code source de l'application (Hors d'atteinte publique)
│   ├── Core/               # Routeur, Connexion DB (PDO), Contrôleur parent
│   ├── Controllers/        # Logique des pages (DashboardController, VentesController...)
│   ├── Models/             # Logique base de données (Produit, Client, Vente...)
│   └── Views/              # Templates HTML (génération des pages)
│       ├── layout/         # Header, Footer, Sidebar
│       └── pages/          # Vues spécifiques (dashboard, formulaires, tables)
├── config/                 # Fichier de configuration (DB_HOST, DB_USER...)
├── database/               # Scripts SQL
│   ├── schema.sql          # Création des tables (Normalisation stricte)
│   └── seed.sql            # Données de test (Cameroun)
└── composer.json           # Gestionnaire de paquets (PhpSpreadsheet)
```

### Base de données (MySQL)

Une évolution directe du modèle relationnel de la V4 :
- **Référentiels** : `categories`, `formats`, `payment_methods`, `users`, `client_types`...
- **Entités Principales** : `products`, `clients`, `suppliers`.
- **Transactions** : `sales`, `purchases`, `stock_movements`, `expenses`, `client_payments`, `supplier_payments`.
- **Trésorerie** : `cash_transactions` (Livre de caisse unifié).

*(Des contraintes d'intégrité référentielle `FOREIGN KEY` seront appliquées avec `ON DELETE RESTRICT` pour garantir la cohérence absolue des données à tout moment).*

## Verification Plan

1. **Phase 1 : Socle technique**
   - Écriture du `schema.sql` et `seed.sql`.
   - Création de la structure MVC (Routeur, PDO, Autoloader).
2. **Phase 2 : Design et Intégration UI**
   - Création du système de design (variables CSS pour le thème Bleu/Ambre).
   - Développement du layout principal (Sidebar responsive, Header).
3. **Phase 3 : Fonctionnalités métiers**
   - Développement des Modèles et Contrôleurs (CRUD Produits, Ventes, Caisse).
   - Implémentation du Tableau de Bord avec les KPI dynamiques.
4. **Phase 4 : Outils**
   - Intégration de PhpSpreadsheet.
   - Génération des rapports d'exportation.
