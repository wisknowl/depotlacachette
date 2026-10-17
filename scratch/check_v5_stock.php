<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance()->getConnection();
echo "=== EMBALLAGE STOCK TYPE 1 ===\n";
print_r($db->query('SELECT * FROM emballage_stock WHERE packaging_type_id = 1')->fetch(PDO::FETCH_ASSOC));

echo "=== V00005 ITEMS ===\n";
print_r($db->query("SELECT * FROM sale_items WHERE sale_id = 'V00005'")->fetchAll(PDO::FETCH_ASSOC));

echo "=== DELPHINE DEBT ===\n";
print_r($db->query("SELECT * FROM client_emballage_debts WHERE client_id = 1004")->fetchAll(PDO::FETCH_ASSOC));
