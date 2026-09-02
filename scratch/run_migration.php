<?php
require 'config/config.php';
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();

// 1. Add additional_fees to purchases if not exists
$cols = $db->query("SHOW COLUMNS FROM purchases LIKE 'additional_fees'")->fetchAll();
if (empty($cols)) {
    $db->exec("ALTER TABLE purchases ADD COLUMN additional_fees DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER total_amount");
    echo "Added additional_fees to purchases table." . PHP_EOL;
} else {
    echo "additional_fees already exists in purchases table." . PHP_EOL;
}

// 2. Add amount_paid and amount_due to sales if not exists
$cols2 = $db->query("SHOW COLUMNS FROM sales LIKE 'amount_paid'")->fetchAll();
if (empty($cols2)) {
    $db->exec("ALTER TABLE sales ADD COLUMN amount_paid DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER total_amount, ADD COLUMN amount_due DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER amount_paid");
    echo "Added amount_paid and amount_due to sales table." . PHP_EOL;
} else {
    echo "amount_paid and amount_due already exist in sales table." . PHP_EOL;
}
