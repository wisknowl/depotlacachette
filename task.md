# Migration vers WebApp (PHP / MySQL)

- [x] Phase 1 : Socle Technique & Base de données
  - [x] Créer l'arborescence du projet avec le dossier `public/assets`
  - [x] Écrire `database/schema.sql` (Modèle de base de données relationnel)
  - [x] Écrire `database/seed.sql` (Jeux de données du Cameroun : noms, boissons)
  - [x] Créer l'architecture `Core` PHP (Routeur, Connexion PDO, Base Controller)
  - [x] Configurer `config/config.php`
- [x] Phase 2 : Design et Intégration UI
  - [x] Créer `public/assets/css/style.css` (Thème Bleu/Ambre)
  - [x] Créer `public/assets/js/app.js` (Interactivité de la sidebar)
  - [x] Créer le Layout principal (`app/Views/layout/main.php`, `header.php`, `sidebar.php`)
- [ ] Phase 3 : Fonctionnalités métiers (MVC)
  - [ ] Séparer les menus (Caisse, Dépenses, Clients, Fournisseurs)
  - [ ] Créer les Contrôleurs (Ventes, Achats, Caisse, Depenses, Stock, Clients, Fournisseurs)
  - [ ] Créer les Vues des modules avec les tableaux de données
  - [ ] Créer les Vues interactives (Dashboard, Tableaux de données, Formulaires)
- [ ] Phase 4 : Exportation Excel (PhpSpreadsheet)
  - *Reporté après la validation des fonctionnalités métier de base.*
