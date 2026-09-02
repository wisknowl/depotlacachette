<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = \App\Core\Database::getInstance()->getConnection();
try {
    $db->exec("ALTER TABLE sales ADD COLUMN discount_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER total_amount");
    echo "Column discount_amount added successfully!\n";
} catch (\PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Column discount_amount already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
