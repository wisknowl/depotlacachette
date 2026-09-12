<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = \App\Core\Database::getInstance()->getConnection();
$db->exec("ALTER TABLE sales MODIFY COLUMN status ENUM('Valid', 'Cancelled', 'En_Route') NOT NULL DEFAULT 'Valid'");
echo "Status column modified successfully.\n";

$col = $db->query("SHOW COLUMNS FROM sales LIKE 'status'")->fetch(PDO::FETCH_ASSOC);
print_r($col);
