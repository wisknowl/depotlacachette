-- ========================================================
-- DEPOT LA CACHETTE - SCHEMA DE BASE DE DONNEES V4.3
-- Synchronisé avec Architecture Événementielle, Grand Livre & Audit
-- ========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- 1. SECURITE, UTILISATEURS & ROLES
-- --------------------------------------------------------

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `description` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'Admin', 'Administrateur général - Accès complet au système'),
(2, 'Gestionnaire', 'Gestionnaire de stock & ventes'),
(3, 'Caissier', 'Responsable de caisse et encaissements')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `full_name` VARCHAR(100) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` VARCHAR(50) NOT NULL DEFAULT 'Admin',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 2. PARAMETRAGES GLOBAUX & COMPTES
-- --------------------------------------------------------

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `formats`;
CREATE TABLE `formats` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `client_types`;
CREATE TABLE `client_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `movement_types`;
CREATE TABLE `movement_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `direction` ENUM('IN', 'OUT') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `movement_types` (`id`, `name`, `direction`) VALUES
(1, 'Achat Fournisseur', 'IN'),
(2, 'Vente Client', 'OUT'),
(3, 'Casse / Avarie', 'OUT'),
(4, 'Consommation Interne', 'OUT'),
(5, 'Ajustement Positif (+)', 'IN'),
(6, 'Ajustement Négatif (-)', 'OUT'),
(7, 'Annulation Vente (Retour Stock)', 'IN'),
(8, 'Annulation Achat (Déstockage)', 'OUT')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `direction`=VALUES(`direction`);

DROP TABLE IF EXISTS `payment_methods`;
CREATE TABLE `payment_methods` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `type` VARCHAR(50) DEFAULT 'Cash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `payment_methods` (`id`, `name`, `type`) VALUES
(1, 'Espèces', 'Cash'),
(2, 'Orange Money', 'Mobile Money'),
(3, 'MTN Mobile Money', 'Mobile Money'),
(4, 'Virement Bancaire', 'Bank'),
(5, 'À Crédit', 'Credit')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

DROP TABLE IF EXISTS `cash_accounts`;
CREATE TABLE `cash_accounts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `payment_method_id` INT DEFAULT NULL,
    FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `cash_accounts` (`id`, `name`, `payment_method_id`) VALUES
(1, 'Caisse Principale (Espèces)', 1),
(2, 'Compte Orange Money', 2),
(3, 'Compte MTN Mobile Money', 3),
(4, 'Compte Bancaire (BICEC)', 4)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

DROP TABLE IF EXISTS `expense_categories`;
CREATE TABLE `expense_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 3. PARC D'EMBALLAGES & TYPES
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 4. TIERS & PRODUITS
-- --------------------------------------------------------

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL UNIQUE,
    `slug` VARCHAR(255) DEFAULT NULL,
    `address` VARCHAR(255) DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `contact_name` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
    FOREIGN KEY (`client_type_id`) REFERENCES `client_types`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`format_id`) REFERENCES `formats`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`packaging_type_id`) REFERENCES `packaging_types`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 5. GESTION DES EMBALLAGES (STOCKS, DETTES & MOUVEMENTS)
-- --------------------------------------------------------

DROP TABLE IF EXISTS `emballage_stock`;
CREATE TABLE `emballage_stock` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `packaging_type_id` INT NOT NULL UNIQUE,
    `empty_crates` INT NOT NULL DEFAULT 0,
    `loose_bottles` INT NOT NULL DEFAULT 0,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`packaging_type_id`) REFERENCES `packaging_types`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `client_emballage_debts`;
CREATE TABLE `client_emballage_debts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT NOT NULL,
    `packaging_type_id` INT NOT NULL,
    `crates_due` INT NOT NULL DEFAULT 0,
    `loose_bottles_due` INT NOT NULL DEFAULT 0,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_client_pkg` (`client_id`, `packaging_type_id`),
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`packaging_type_id`) REFERENCES `packaging_types`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `supplier_emballage_debts`;
CREATE TABLE `supplier_emballage_debts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `supplier_id` INT NOT NULL,
    `packaging_type_id` INT NOT NULL,
    `crates_due` INT NOT NULL DEFAULT 0,
    `loose_bottles_due` INT NOT NULL DEFAULT 0,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_supplier_pkg` (`supplier_id`, `packaging_type_id`),
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`packaging_type_id`) REFERENCES `packaging_types`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `emballage_movements`;
CREATE TABLE `emballage_movements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `packaging_type_id` INT NOT NULL,
    `movement_type` ENUM('Purchase_Drink','Purchase_Empty','Sale_Drink','Client_Return','Supplier_Return','Breakage','Adjustment','Sale_Cancellation','Initial_Stock','Adjustment_Plus','Adjustment_Minus','Casse') NOT NULL,
    `reference_id` VARCHAR(50) DEFAULT NULL,
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
    FOREIGN KEY (`packaging_type_id`) REFERENCES `packaging_types`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 6. TRANSACTIONS COMMERCIALES & STOCKS
-- --------------------------------------------------------

DROP TABLE IF EXISTS `purchases`;
CREATE TABLE `purchases` (
    `id` VARCHAR(50) PRIMARY KEY,
    `purchase_date` DATE NOT NULL,
    `supplier_invoice_date` DATE NULL DEFAULT NULL,
    `supplier_id` INT NOT NULL,
    `total_amount` DECIMAL(15,2) NOT NULL,
    `payment_method_id` INT NOT NULL,
    `cash_account_id` INT DEFAULT NULL,
    `reference` VARCHAR(100) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `user_id` INT NOT NULL,
    `status` ENUM('Valid', 'Cancelled') NOT NULL DEFAULT 'Valid',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`cash_account_id`) REFERENCES `cash_accounts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `purchase_items`;
CREATE TABLE `purchase_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `purchase_id` VARCHAR(50) NOT NULL,
    `product_id` INT NOT NULL,
    `format_type` VARCHAR(50) NOT NULL DEFAULT 'casier',
    `quantity` DECIMAL(10,2) NOT NULL,
    `unit_price` DECIMAL(15,2) NOT NULL,
    `ristourne_unit` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_ristourne` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_price` DECIMAL(15,2) NOT NULL,
    `stock_equivalent` DECIMAL(10,2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_purchase_items_pid` (`purchase_id`),
    FOREIGN KEY (`purchase_id`) REFERENCES `purchases`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `sales`;
CREATE TABLE `sales` (
    `id` VARCHAR(50) PRIMARY KEY,
    `sale_date` DATE NOT NULL,
    `client_id` INT NOT NULL,
    `total_amount` DECIMAL(15,2) NOT NULL,
    `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `amount_paid` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `amount_due` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `payment_method_id` INT NOT NULL,
    `cash_account_id` INT DEFAULT NULL,
    `reference` VARCHAR(100) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `user_id` INT NOT NULL,
    `status` ENUM('Valid', 'Cancelled') NOT NULL DEFAULT 'Valid',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`cash_account_id`) REFERENCES `cash_accounts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `sale_items`;
CREATE TABLE `sale_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sale_id` VARCHAR(50) NOT NULL,
    `product_id` INT NOT NULL,
    `format_type` VARCHAR(50) NOT NULL DEFAULT 'casier',
    `quantity` DECIMAL(10,2) NOT NULL,
    `unit_price` DECIMAL(15,2) NOT NULL,
    `total_price` DECIMAL(15,2) NOT NULL,
    `stock_equivalent` DECIMAL(10,2) NOT NULL,
    `is_returnable` TINYINT(1) NOT NULL DEFAULT 1,
    `crates_out` INT NOT NULL DEFAULT 0,
    `bottles_out` INT NOT NULL DEFAULT 0,
    `crates_returned` INT NOT NULL DEFAULT 0,
    `bottles_returned` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_sale_items_sid` (`sale_id`),
    FOREIGN KEY (`sale_id`) REFERENCES `sales`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
    `unit_price` DECIMAL(15,2) NOT NULL,
    `reference` VARCHAR(255),
    `user_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_stock_source` (`source_type`, `source_id`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`format_id`) REFERENCES `formats`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`movement_type_id`) REFERENCES `movement_types`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 7. REGLEMENTS & DETTES
-- --------------------------------------------------------

DROP TABLE IF EXISTS `client_payments`;
CREATE TABLE `client_payments` (
    `id` VARCHAR(50) PRIMARY KEY,
    `payment_date` DATE NOT NULL,
    `client_id` INT NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `payment_method_id` INT NOT NULL,
    `cash_account_id` INT NOT NULL,
    `reference` VARCHAR(100),
    `notes` TEXT,
    `user_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`cash_account_id`) REFERENCES `cash_accounts`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `supplier_payments`;
CREATE TABLE `supplier_payments` (
    `id` VARCHAR(50) PRIMARY KEY,
    `payment_date` DATE NOT NULL,
    `supplier_id` INT NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `payment_method_id` INT NOT NULL,
    `cash_account_id` INT NOT NULL,
    `reference` VARCHAR(100),
    `notes` TEXT,
    `user_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`cash_account_id`) REFERENCES `cash_accounts`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 8. DEPENSES, PAIE & CAISSE CENTRALE (GRAND LIVRE)
-- --------------------------------------------------------

DROP TABLE IF EXISTS `expenses`;
CREATE TABLE `expenses` (
    `id` VARCHAR(50) PRIMARY KEY,
    `expense_date` DATE NOT NULL,
    `category_id` INT NOT NULL,
    `description` TEXT,
    `amount` DECIMAL(15,2) NOT NULL,
    `payment_method_id` INT NOT NULL,
    `beneficiary` VARCHAR(150),
    `reference` VARCHAR(100),
    `user_id` INT NOT NULL,
    `status` ENUM('Paid', 'Cancelled') NOT NULL DEFAULT 'Paid',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `expense_categories`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `role` VARCHAR(100) NOT NULL,
    `base_salary` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `payroll_payments`;
CREATE TABLE `payroll_payments` (
    `id` VARCHAR(50) PRIMARY KEY,
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
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`cash_account_id`) REFERENCES `cash_accounts`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `cash_transactions`;
CREATE TABLE `cash_transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `transaction_date` DATETIME NOT NULL,
    `transaction_type` ENUM('Sale', 'Purchase', 'Expense', 'ClientPayment', 'SupplierPayment', 'Deposit', 'Transfer', 'Withdrawal', 'Payroll') NOT NULL,
    `source_id` VARCHAR(50),
    `description` TEXT,
    `cash_account_id` INT NOT NULL,
    `amount_in` DECIMAL(15,2) DEFAULT 0.00,
    `amount_out` DECIMAL(15,2) DEFAULT 0.00,
    `user_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`cash_account_id`) REFERENCES `cash_accounts`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
    `billetage` JSON NULL,
    `notes` TEXT DEFAULT NULL,
    `closed_by` INT NOT NULL,
    `status` ENUM('Closed', 'Validated') NOT NULL DEFAULT 'Closed',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`cash_account_id`) REFERENCES `cash_accounts`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`closed_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 9. JOURNAL D'AUDIT SYSTEME & SECURITE (DIFFS AVANT/APRES)
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
    `old_values` JSON NULL,
    `new_values` JSON NULL,
    `reason` VARCHAR(255) NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_module` (`module`),
    INDEX `idx_audit_user` (`user_id`),
    INDEX `idx_audit_action` (`action`),
    INDEX `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
