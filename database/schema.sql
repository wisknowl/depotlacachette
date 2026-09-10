-- ========================================================
-- DEPOT LA CACHETTE - SCHEMA DE BASE DE DONNEES V4.5
-- Synchronisé avec Architecture Événementielle, Grand Livre,
-- Tournées de Livraison, Avoirs Clients & Audit Système
-- ========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- 1. SECURITE & UTILISATEURS
-- --------------------------------------------------------

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `full_name` VARCHAR(100) DEFAULT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('Admin', 'Caissier', 'Vendeur') NOT NULL DEFAULT 'Vendeur',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `username`, `full_name`, `password_hash`, `role`, `is_active`) VALUES
(1, 'admin', 'Administrateur', '$2y$10$eNx0c83Ea1H7XzZ5Ws2g4.toJtG7Y9NZO.OL4WjteI5o1OoyDna8C', 'Admin', 1),
(2, 'kamga', 'M. Kamga (Caissier)', '$2y$10$sCAVSkbtyqlaadMg2Zff8.ckAp8eq3beKzOjD4JOSSf1BBAvtzPCS', 'Caissier', 1),
(3, 'ndongo_vente', 'M. Ndongo (Vendeur)', '$2y$10$kxNtjwtEe8asW0GXg4PCcuOWBLAWNaPnKNP4xQTPjbiMdYeAuBj9W', 'Vendeur', 1)
ON DUPLICATE KEY UPDATE `username`=VALUES(`username`);

-- --------------------------------------------------------
-- 2. PARAMETRAGES GLOBAUX, COMPTES & MODES DE PAIEMENT
-- --------------------------------------------------------

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Bières'),
(3, 'Jus & Sodas'),
(4, 'Eaux Minérales'),
(5, 'Vins & Spiritueux')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

DROP TABLE IF EXISTS `formats`;
CREATE TABLE `formats` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `formats` (`id`, `name`) VALUES
(1, '0,60l/60cl'),
(2, '0,65l/65cl'),
(3, '33cl/330ml'),
(4, '0,50l/50cl'),
(5, '150cl/1,5L'),
(6, 'Fût 30L'),
(7, '1L'),
(8, '35cl')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

DROP TABLE IF EXISTS `client_types`;
CREATE TABLE `client_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `client_types` (`id`, `name`) VALUES
(1, 'Bar / Snack'),
(2, 'Boutique / Alimentation'),
(3, 'Particulier'),
(4, 'Client VIP')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

DROP TABLE IF EXISTS `movement_types`;
CREATE TABLE `movement_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `direction` ENUM('IN', 'OUT') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `movement_types` (`id`, `name`, `direction`) VALUES
(1, 'Achat Fournisseur', 'IN'),
(2, 'Vente Client', 'OUT'),
(3, 'Casse / Avarie', 'OUT'),
(4, 'Consommation Interne', 'OUT'),
(5, 'Ajustement Positif (+)', 'IN'),
(6, 'Ajustement Négatif (-)', 'OUT'),
(7, 'Annulation Vente (Retour Stock)', 'IN'),
(8, 'Annulation Achat (Déstockage)', 'OUT'),
(9, 'Chargement Tournée Route', 'OUT'),
(10, 'Retour Invendus Tournée', 'IN')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `direction`=VALUES(`direction`);

DROP TABLE IF EXISTS `payment_methods`;
CREATE TABLE `payment_methods` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `type` ENUM('Cash', 'Mobile Money', 'Bank', 'Credit') DEFAULT 'Cash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `payment_methods` (`id`, `name`, `type`) VALUES
(1, 'Espèces', 'Cash'),
(2, 'Orange Money', 'Mobile Money'),
(3, 'MTN MoMo', 'Mobile Money'),
(4, 'Virement Bancaire', 'Bank'),
(5, 'À Crédit', 'Credit')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `type`=VALUES(`type`);

DROP TABLE IF EXISTS `cash_accounts`;
CREATE TABLE `cash_accounts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `payment_method_id` INT DEFAULT NULL,
    KEY `payment_method_id` (`payment_method_id`),
    CONSTRAINT `cash_accounts_ibfk_1` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `cash_accounts` (`id`, `name`, `payment_method_id`) VALUES
(1, 'Caisse Principale', 1),
(2, 'Compte Orange Money', 2),
(3, 'Compte MTN MoMo', 3),
(4, 'Compte Afriland First Bank', 4)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

DROP TABLE IF EXISTS `expense_categories`;
CREATE TABLE `expense_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `expense_categories` (`id`, `name`) VALUES
(1, 'Salaire Personnel'),
(2, 'Transport & Logistique'),
(3, 'Électricité & Eau (ENEO/CAMWATER)'),
(4, 'Taxes & Impôts'),
(5, 'Frais Divers')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- --------------------------------------------------------
-- 3. PARC D'EMBALLAGES & TYPES DE CASIERS
-- --------------------------------------------------------

DROP TABLE IF EXISTS `packaging_types`;
CREATE TABLE `packaging_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `company` VARCHAR(50) NOT NULL,
    `color` VARCHAR(50) NOT NULL,
    `bottles_per_crate` INT NOT NULL DEFAULT 12,
    `bottle_volume` VARCHAR(20) NOT NULL DEFAULT '65cl',
    `default_cost` DECIMAL(10,2) NOT NULL DEFAULT 3600.00,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `packaging_types` (`id`, `name`, `company`, `color`, `bottles_per_crate`, `bottle_volume`, `default_cost`) VALUES
(1, 'Casier SABC GM 12 Bouteilles (65/50cl)', 'SABC', 'Rouge', 12, '65cl', 3600.00),
(2, 'Casier SABC PM 24 Bouteilles (33cl)', 'SABC', 'Rouge', 24, '33cl', 4200.00),
(3, 'Casier Guinness GM 12 Bouteilles (65/60cl)', 'Guinness', 'Jaune', 12, '65cl', 3600.00),
(4, 'Casier Guinness PM 24 Bouteilles (33cl)', 'Guinness', 'Jaune', 24, '33cl', 4500.00),
(5, 'Casier UCB GM 12 Bouteilles (65cl)', 'UCB', 'Noir', 12, '65cl', 3600.00),
(6, 'Casier UCB PM 24 Bouteilles (33cl)', 'UCB', 'Noir', 24, '33cl', 4200.00)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- --------------------------------------------------------
-- 4. TIERS (FOURNISSEURS & CLIENTS), PRODUITS & EMPLOYES
-- --------------------------------------------------------

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL UNIQUE,
    `slug` VARCHAR(255) DEFAULT NULL,
    `address` VARCHAR(255) DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `contact_name` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `clients`;
CREATE TABLE `clients` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL UNIQUE,
    `slug` VARCHAR(255) DEFAULT NULL,
    `address` VARCHAR(255) DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `client_type_id` INT NOT NULL,
    `max_credit` DECIMAL(15,2) DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `slug` (`slug`),
    KEY `client_type_id` (`client_type_id`),
    CONSTRAINT `clients_ibfk_1` FOREIGN KEY (`client_type_id`) REFERENCES `client_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL UNIQUE,
    `short_code` VARCHAR(20) DEFAULT NULL,
    `slug` VARCHAR(255) DEFAULT NULL,
    `category_id` INT NOT NULL,
    `format_id` INT NOT NULL,
    `is_returnable` TINYINT(1) NOT NULL DEFAULT 1,
    `cout_emballage` DECIMAL(10,2) NOT NULL DEFAULT 3600.00,
    `packaging_type_id` INT DEFAULT NULL,
    `purchase_price` DECIMAL(15,2) NOT NULL,
    `price_casier` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `price_demi` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `price_unite` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `alert_stock` INT NOT NULL DEFAULT 0,
    `factor` INT NOT NULL DEFAULT 12,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `slug` (`slug`),
    KEY `category_id` (`category_id`),
    KEY `format_id` (`format_id`),
    KEY `packaging_type_id` (`packaging_type_id`),
    CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
    CONSTRAINT `products_ibfk_2` FOREIGN KEY (`format_id`) REFERENCES `formats` (`id`),
    CONSTRAINT `products_ibfk_3` FOREIGN KEY (`packaging_type_id`) REFERENCES `packaging_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `role` VARCHAR(100) NOT NULL,
    `base_salary` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 5. GESTION DES EMBALLAGES (STOCK, DETTES & MOUVEMENTS)
-- --------------------------------------------------------

DROP TABLE IF EXISTS `emballage_stock`;
CREATE TABLE `emballage_stock` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `packaging_type_id` INT NOT NULL UNIQUE,
    `empty_crates` INT NOT NULL DEFAULT 0,
    `loose_bottles` INT NOT NULL DEFAULT 0,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `emballage_stock_ibfk_1` FOREIGN KEY (`packaging_type_id`) REFERENCES `packaging_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `client_emballage_debts`;
CREATE TABLE `client_emballage_debts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT NOT NULL,
    `packaging_type_id` INT NOT NULL,
    `crates_due` INT NOT NULL DEFAULT 0,
    `loose_bottles_due` INT NOT NULL DEFAULT 0,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_client_pkg` (`client_id`, `packaging_type_id`),
    KEY `packaging_type_id` (`packaging_type_id`),
    CONSTRAINT `client_emballage_debts_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
    CONSTRAINT `client_emballage_debts_ibfk_2` FOREIGN KEY (`packaging_type_id`) REFERENCES `packaging_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `supplier_emballage_debts`;
CREATE TABLE `supplier_emballage_debts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `supplier_id` INT NOT NULL,
    `packaging_type_id` INT NOT NULL,
    `crates_due` INT NOT NULL DEFAULT 0,
    `loose_bottles_due` INT NOT NULL DEFAULT 0,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_supplier_pkg` (`supplier_id`, `packaging_type_id`),
    KEY `packaging_type_id` (`packaging_type_id`),
    CONSTRAINT `supplier_emballage_debts_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
    CONSTRAINT `supplier_emballage_debts_ibfk_2` FOREIGN KEY (`packaging_type_id`) REFERENCES `packaging_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `emballage_movements`;
CREATE TABLE `emballage_movements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `packaging_type_id` INT NOT NULL,
    `movement_type` VARCHAR(50) NOT NULL,
    `reference_id` INT DEFAULT NULL,
    `client_id` INT DEFAULT NULL,
    `supplier_id` INT DEFAULT NULL,
    `crates_in` INT NOT NULL DEFAULT 0,
    `crates_out` INT NOT NULL DEFAULT 0,
    `bottles_in` INT NOT NULL DEFAULT 0,
    `bottles_out` INT NOT NULL DEFAULT 0,
    `unit_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `notes` TEXT DEFAULT NULL,
    `created_by` INT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY `packaging_type_id` (`packaging_type_id`),
    CONSTRAINT `emballage_movements_ibfk_1` FOREIGN KEY (`packaging_type_id`) REFERENCES `packaging_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 6. GESTION DES TOURNEES (LIVRAISON CAMION & DECHARGE)
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tournee_items`;
DROP TABLE IF EXISTS `tournees`;
CREATE TABLE `tournees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `reference` VARCHAR(50) NOT NULL UNIQUE,
    `tournee_date` DATE NOT NULL,
    `driver_id` INT NOT NULL,
    `vehicle_name` VARCHAR(100) DEFAULT NULL,
    `status` ENUM('En_Route', 'Cloturee', 'Annulee') NOT NULL DEFAULT 'En_Route',
    `total_loaded_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_sold_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_cash_collected` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_expenses` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `cash_deposited` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `cash_account_id` INT DEFAULT NULL,
    `cash_shortage` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `notes` TEXT DEFAULT NULL,
    `created_by` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `closed_at` DATETIME DEFAULT NULL,
    KEY `tournee_date` (`tournee_date`),
    KEY `driver_id` (`driver_id`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tournee_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tournee_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `format_type` ENUM('casier', 'demi', 'unite') NOT NULL DEFAULT 'casier',
    `has_demi` TINYINT(1) NOT NULL DEFAULT 0,
    `stock_equivalent` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `qty_loaded` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `qty_sold` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `qty_returned` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `qty_shortage` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `demi_unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `notes` VARCHAR(255) DEFAULT NULL,
    KEY `tournee_id` (`tournee_id`),
    KEY `product_id` (`product_id`),
    CONSTRAINT `tournee_items_ibfk_1` FOREIGN KEY (`tournee_id`) REFERENCES `tournees` (`id`) ON DELETE CASCADE,
    CONSTRAINT `tournee_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 7. ACHATS FOURNISSEURS & DETAILS
-- --------------------------------------------------------

DROP TABLE IF EXISTS `purchase_items`;
DROP TABLE IF EXISTS `purchases`;
CREATE TABLE `purchases` (
    `id` VARCHAR(20) NOT NULL PRIMARY KEY,
    `purchase_date` DATE NOT NULL,
    `supplier_invoice_date` DATE NULL DEFAULT NULL,
    `supplier_id` INT NOT NULL,
    `total_amount` DECIMAL(15,2) NOT NULL,
    `additional_fees` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `payment_method_id` INT NOT NULL,
    `cash_account_id` INT DEFAULT NULL,
    `reference` VARCHAR(100) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `user_id` INT NOT NULL,
    `status` ENUM('Valid', 'Cancelled') NOT NULL DEFAULT 'Valid',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `total_emballage_amount` DECIMAL(10,2) DEFAULT 0.00,
    KEY `supplier_id` (`supplier_id`),
    KEY `payment_method_id` (`payment_method_id`),
    KEY `user_id` (`user_id`),
    KEY `fk_purchases_cash_account` (`cash_account_id`),
    CONSTRAINT `fk_purchases_cash_account` FOREIGN KEY (`cash_account_id`) REFERENCES `cash_accounts` (`id`) ON DELETE SET NULL,
    CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
    CONSTRAINT `purchases_ibfk_4` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`),
    CONSTRAINT `purchases_ibfk_5` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `purchase_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `purchase_id` VARCHAR(50) NOT NULL,
    `product_id` INT NOT NULL,
    `format_type` VARCHAR(50) NOT NULL DEFAULT 'casier',
    `has_demi` TINYINT(1) NOT NULL DEFAULT 0,
    `quantity` DECIMAL(10,2) NOT NULL,
    `unit_price` DECIMAL(15,2) NOT NULL,
    `demi_unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `ristourne_unit` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_ristourne` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_price` DECIMAL(15,2) NOT NULL,
    `stock_equivalent` DECIMAL(10,2) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    `is_returnable` TINYINT(1) DEFAULT 1,
    `empties_returned` INT NOT NULL DEFAULT 0,
    `emballage_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `emballage_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    KEY `purchase_id` (`purchase_id`),
    KEY `product_id` (`product_id`),
    CONSTRAINT `purchase_items_ibfk_1` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE,
    CONSTRAINT `purchase_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 8. VENTES CLIENTS & DETAILS
-- --------------------------------------------------------

DROP TABLE IF EXISTS `sale_items`;
DROP TABLE IF EXISTS `sales`;
CREATE TABLE `sales` (
    `id` VARCHAR(20) NOT NULL PRIMARY KEY,
    `sale_date` DATE NOT NULL,
    `client_id` INT NOT NULL,
    `total_amount` DECIMAL(15,2) NOT NULL,
    `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `avoir_used` DECIMAL(15,2) DEFAULT 0.00,
    `amount_paid` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `amount_due` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `excess_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `payment_method_id` INT NOT NULL,
    `cash_account_id` INT DEFAULT NULL,
    `reference` VARCHAR(100) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `user_id` INT NOT NULL,
    `tournee_id` INT DEFAULT NULL,
    `sale_type` ENUM('comptoir', 'route') NOT NULL DEFAULT 'comptoir',
    `status` ENUM('Valid', 'Cancelled') NOT NULL DEFAULT 'Valid',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `client_id` (`client_id`),
    KEY `payment_method_id` (`payment_method_id`),
    KEY `user_id` (`user_id`),
    KEY `fk_sales_cash_account` (`cash_account_id`),
    KEY `idx_sales_tournee_id` (`tournee_id`),
    CONSTRAINT `fk_sales_cash_account` FOREIGN KEY (`cash_account_id`) REFERENCES `cash_accounts` (`id`) ON DELETE SET NULL,
    CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
    CONSTRAINT `sales_ibfk_4` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`),
    CONSTRAINT `sales_ibfk_5` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `sale_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sale_id` VARCHAR(50) NOT NULL,
    `product_id` INT NOT NULL,
    `format_type` VARCHAR(50) NOT NULL DEFAULT 'casier',
    `has_demi` TINYINT(1) NOT NULL DEFAULT 0,
    `quantity` DECIMAL(10,2) NOT NULL,
    `unit_price` DECIMAL(15,2) NOT NULL,
    `demi_unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_price` DECIMAL(15,2) NOT NULL,
    `stock_equivalent` DECIMAL(10,2) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `is_returnable` TINYINT(1) DEFAULT 1,
    `crates_out` INT DEFAULT 0,
    `bottles_out` INT DEFAULT 0,
    `crates_returned` INT DEFAULT 0,
    `bottles_returned` INT DEFAULT 0,
    KEY `idx_sale_id` (`sale_id`),
    KEY `product_id` (`product_id`),
    CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
    CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 9. MOUVEMENTS DE STOCK
-- --------------------------------------------------------

DROP TABLE IF EXISTS `stock_movements`;
CREATE TABLE `stock_movements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `movement_date` DATE NOT NULL,
    `product_id` INT NOT NULL,
    `format_id` INT NOT NULL,
    `format_type` VARCHAR(50) NOT NULL DEFAULT 'casier',
    `movement_type_id` INT NOT NULL,
    `source_type` VARCHAR(50) DEFAULT NULL,
    `source_id` VARCHAR(50) DEFAULT NULL,
    `quantity` DECIMAL(10,2) NOT NULL,
    `stock_equivalent` DECIMAL(10,2) NOT NULL,
    `unit_price` DECIMAL(15,2) DEFAULT 0.00,
    `reference` VARCHAR(100) DEFAULT NULL,
    `user_id` INT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `product_id` (`product_id`),
    KEY `format_id` (`format_id`),
    KEY `movement_type_id` (`movement_type_id`),
    KEY `user_id` (`user_id`),
    KEY `idx_stock_source` (`source_type`, `source_id`),
    CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
    CONSTRAINT `stock_movements_ibfk_2` FOREIGN KEY (`format_id`) REFERENCES `formats` (`id`),
    CONSTRAINT `stock_movements_ibfk_3` FOREIGN KEY (`movement_type_id`) REFERENCES `movement_types` (`id`),
    CONSTRAINT `stock_movements_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 10. REGLEMENTS CLIENTS & FOURNISSEURS
-- --------------------------------------------------------

DROP TABLE IF EXISTS `client_payments`;
CREATE TABLE `client_payments` (
    `id` VARCHAR(20) NOT NULL PRIMARY KEY,
    `payment_date` DATE NOT NULL,
    `client_id` INT NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `payment_method_id` INT NOT NULL,
    `cash_account_id` INT NOT NULL DEFAULT 1,
    `reference` VARCHAR(100) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `user_id` INT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `client_id` (`client_id`),
    KEY `payment_method_id` (`payment_method_id`),
    KEY `user_id` (`user_id`),
    KEY `cash_account_id` (`cash_account_id`),
    CONSTRAINT `client_payments_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
    CONSTRAINT `client_payments_ibfk_2` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`),
    CONSTRAINT `client_payments_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
    CONSTRAINT `client_payments_ibfk_4` FOREIGN KEY (`cash_account_id`) REFERENCES `cash_accounts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `supplier_payments`;
CREATE TABLE `supplier_payments` (
    `id` VARCHAR(20) NOT NULL PRIMARY KEY,
    `payment_date` DATE NOT NULL,
    `supplier_id` INT NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `payment_method_id` INT NOT NULL,
    `cash_account_id` INT NOT NULL DEFAULT 1,
    `reference` VARCHAR(100) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `user_id` INT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `supplier_id` (`supplier_id`),
    KEY `payment_method_id` (`payment_method_id`),
    KEY `user_id` (`user_id`),
    KEY `cash_account_id` (`cash_account_id`),
    CONSTRAINT `supplier_payments_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
    CONSTRAINT `supplier_payments_ibfk_2` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`),
    CONSTRAINT `supplier_payments_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
    CONSTRAINT `supplier_payments_ibfk_4` FOREIGN KEY (`cash_account_id`) REFERENCES `cash_accounts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 11. DEPENSES, PAIE DU PERSONNEL & CLOTURES DE CAISSE
-- --------------------------------------------------------

DROP TABLE IF EXISTS `expenses`;
CREATE TABLE `expenses` (
    `id` VARCHAR(20) NOT NULL PRIMARY KEY,
    `expense_date` DATE NOT NULL,
    `category_id` INT NOT NULL,
    `description` TEXT DEFAULT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `payment_method_id` INT NOT NULL,
    `beneficiary` VARCHAR(150) DEFAULT NULL,
    `reference` VARCHAR(100) DEFAULT NULL,
    `user_id` INT NOT NULL,
    `tournee_id` INT DEFAULT NULL,
    `status` ENUM('Paid', 'Pending', 'Cancelled') NOT NULL DEFAULT 'Paid',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `category_id` (`category_id`),
    KEY `payment_method_id` (`payment_method_id`),
    KEY `user_id` (`user_id`),
    KEY `idx_expenses_tournee_id` (`tournee_id`),
    CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `expense_categories` (`id`),
    CONSTRAINT `expenses_ibfk_2` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`),
    CONSTRAINT `expenses_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `payroll_payments`;
CREATE TABLE `payroll_payments` (
    `id` VARCHAR(50) NOT NULL PRIMARY KEY,
    `payment_date` DATE NOT NULL,
    `employee_id` INT NOT NULL,
    `payment_type` ENUM('Salary', 'Advance', 'Bonus', 'Overtime') NOT NULL DEFAULT 'Salary',
    `amount` DECIMAL(15,2) NOT NULL,
    `cash_account_id` INT NOT NULL,
    `period` VARCHAR(50) DEFAULT NULL,
    `reference` VARCHAR(100) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `user_id` INT NOT NULL,
    `status` ENUM('Valid', 'Cancelled') NOT NULL DEFAULT 'Valid',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `employee_id` (`employee_id`),
    KEY `cash_account_id` (`cash_account_id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `payroll_payments_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
    CONSTRAINT `payroll_payments_ibfk_2` FOREIGN KEY (`cash_account_id`) REFERENCES `cash_accounts` (`id`),
    CONSTRAINT `payroll_payments_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `cash_transactions`;
CREATE TABLE `cash_transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `transaction_date` DATETIME NOT NULL,
    `transaction_type` ENUM('Sale', 'Purchase', 'Expense', 'ClientPayment', 'SupplierPayment', 'Deposit', 'Transfer', 'Withdrawal', 'Payroll', 'Tournee') NOT NULL,
    `source_id` VARCHAR(20) DEFAULT NULL,
    `description` VARCHAR(255) NOT NULL,
    `cash_account_id` INT NOT NULL,
    `amount_in` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `amount_out` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `user_id` INT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `cash_account_id` (`cash_account_id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `cash_transactions_ibfk_1` FOREIGN KEY (`cash_account_id`) REFERENCES `cash_accounts` (`id`),
    CONSTRAINT `cash_transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `cash_closings`;
CREATE TABLE `cash_closings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `closing_date` DATE NOT NULL,
    `cash_account_id` INT NOT NULL,
    `opening_balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_in` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_out` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `theoretical_balance` DECIMAL(15,2) NOT NULL,
    `physical_cash` DECIMAL(15,2) NOT NULL,
    `difference` DECIMAL(15,2) NOT NULL,
    `billetage` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`billetage`)),
    `notes` TEXT DEFAULT NULL,
    `closed_by` INT NOT NULL,
    `status` ENUM('Closed', 'Validated') NOT NULL DEFAULT 'Closed',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `cash_account_id` (`cash_account_id`),
    KEY `closed_by` (`closed_by`),
    CONSTRAINT `cash_closings_ibfk_1` FOREIGN KEY (`cash_account_id`) REFERENCES `cash_accounts` (`id`),
    CONSTRAINT `cash_closings_ibfk_2` FOREIGN KEY (`closed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 12. JOURNAL D'AUDIT SYSTEME & TRACABILITE
-- --------------------------------------------------------

DROP TABLE IF EXISTS `system_audit_logs`;
CREATE TABLE `system_audit_logs` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `user_name` VARCHAR(100) NOT NULL,
    `user_role` VARCHAR(50) NOT NULL,
    `action` ENUM('CREATE', 'UPDATE', 'CANCEL', 'DELETE', 'LOGIN', 'LOGOUT', 'AUTH_FAIL', 'PRICE_CHANGE', 'CREDIT_LIMIT_CHANGE', 'STOCK_ADJUST', 'CASH_CLOSING', 'SYSTEM') NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `record_id` VARCHAR(50) NULL,
    `description` TEXT NOT NULL,
    `old_values` LONGTEXT NULL,
    `new_values` LONGTEXT NULL,
    `reason` VARCHAR(255) NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_audit_module` (`module`),
    KEY `idx_audit_user` (`user_id`),
    KEY `idx_audit_action` (`action`),
    KEY `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ========================================================
-- FIN DU SCHEMA V4.5 - STRUCTURE COMPLETEMENT SYNCHRONISEE
-- ========================================================
