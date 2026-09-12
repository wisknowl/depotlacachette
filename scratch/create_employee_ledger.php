<?php
require 'config/config.php';
require 'app/Core/Database.php';

$db = App\Core\Database::getInstance()->getConnection();

$sql = "
CREATE TABLE IF NOT EXISTS `employee_ledger` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `transaction_date` DATETIME NOT NULL,
    `transaction_type` VARCHAR(50) NOT NULL,
    `source_type` VARCHAR(50) NOT NULL,
    `source_id` VARCHAR(50) NOT NULL,
    `reference` VARCHAR(100) DEFAULT NULL,
    `amount_debit` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `amount_credit` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `description` VARCHAR(255) NOT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_by` INT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `employee_id` (`employee_id`),
    KEY `idx_source` (`source_type`, `source_id`),
    KEY `transaction_date` (`transaction_date`),
    CONSTRAINT `employee_ledger_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
    CONSTRAINT `employee_ledger_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

$db->exec($sql);
echo "Table employee_ledger created successfully!\n";
