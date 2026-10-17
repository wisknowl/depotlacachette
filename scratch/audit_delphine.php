<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance()->getConnection();

echo "=== DELPHINE SALES ITEMS ===\n";
$items = $db->query("
    SELECT s.id as sale_id, s.sale_date, si.id as item_id, p.name, si.quantity, si.crates_out, si.bottles_out, si.crates_returned, si.bottles_returned
    FROM sales s
    JOIN sale_items si ON s.id = si.sale_id
    JOIN products p ON si.product_id = p.id
    WHERE s.client_id = 1004
    ORDER BY s.id ASC, si.id ASC
")->fetchAll(PDO::FETCH_ASSOC);
print_r($items);

echo "=== DELPHINE MOVEMENTS ===\n";
$movs = $db->query("
    SELECT * FROM emballage_movements WHERE client_id = 1004 ORDER BY id ASC
")->fetchAll(PDO::FETCH_ASSOC);
print_r($movs);
