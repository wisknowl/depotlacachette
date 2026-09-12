<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance()->getConnection();

echo "=== TOURNÉES ===\n";
$stmt = $db->query("SELECT id, reference, tournee_date, status, total_sold_amount, total_cash_collected, cash_deposited, cash_shortage, notes FROM tournees");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== SALES ===\n";
$stmt = $db->query("SELECT id, sale_date, client_id, total_amount, amount_paid, amount_due, status, tournee_id FROM sales");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== CLIENT DELPHINE (1004) PAYMENTS (client_payments) ===\n";
$stmt = $db->query("SELECT * FROM client_payments WHERE client_id = 1004");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== CASH TRANSACTIONS FOR DELPHINE OR RC00002 ===\n";
$stmt = $db->query("SELECT * FROM cash_transactions WHERE description LIKE '%Delphine%' OR source_id LIKE '%RC%'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== CLIENT EMBALLAGE DEBTS FOR DELPHINE ===\n";
$stmt = $db->query("SELECT * FROM client_emballage_debts WHERE client_id = 1004");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== EMBALLAGE MOVEMENTS FOR DELPHINE ===\n";
$stmt = $db->query("SELECT id, packaging_type_id, movement_type, reference_id, client_id, crates_in, crates_out, bottles_in, bottles_out, notes FROM emballage_movements WHERE client_id = 1004");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
