<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = \App\Core\Database::getInstance()->getConnection();
$col = $db->query("SHOW COLUMNS FROM emballage_movements LIKE 'reference_id'")->fetch(PDO::FETCH_ASSOC);
print_r($col);
