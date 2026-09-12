<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = \App\Core\Database::getInstance()->getConnection();
$db->exec("ALTER TABLE emballage_movements MODIFY COLUMN reference_id VARCHAR(50) DEFAULT NULL");
echo "emballage_movements.reference_id modified to VARCHAR(50) successfully.\n";

$col = $db->query("SHOW COLUMNS FROM emballage_movements LIKE 'reference_id'")->fetch(PDO::FETCH_ASSOC);
print_r($col);
