<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance()->getConnection();

echo "=== RECENT SALES ===\n";
$sales = $db->query('SELECT id, sale_date, client_id, total_amount, status, created_at FROM sales ORDER BY id DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
print_r($sales);

echo "\n=== ALL SALE ITEMS FOR CLIENT 1004 (DELPHINE) ===\n";
$items = $db->query('SELECT si.id, si.sale_id, p.name, si.quantity, si.crates_out, si.bottles_out, si.crates_returned, si.bottles_returned 
                    FROM sale_items si 
                    JOIN sales s ON si.sale_id = s.id 
                    JOIN products p ON si.product_id = p.id 
                    WHERE s.client_id = 1004 
                    ORDER BY si.sale_id ASC, si.id ASC')->fetchAll(PDO::FETCH_ASSOC);
print_r($items);

echo "\n=== CLIENT EMBALLAGE DEBTS FOR DELPHINE (1004) ===\n";
$debts = $db->query('SELECT * FROM client_emballage_debts WHERE client_id = 1004')->fetchAll(PDO::FETCH_ASSOC);
print_r($debts);

echo "\n=== EMBALLAGE MOVEMENTS FOR DELPHINE (1004) ===\n";
$movs = $db->query('SELECT * FROM emballage_movements WHERE client_id = 1004 ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
print_r($movs);
