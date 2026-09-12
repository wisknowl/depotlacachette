<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = \App\Core\Database::getInstance()->getConnection();

echo "=== TOURNEE TR00002 ===\n";
$stmt = $db->query("SELECT * FROM tournees WHERE reference = 'TR00002' OR id = 2");
$tournee = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($tournee);

echo "\n=== SALES FOR TR00002 ===\n";
$stmtSales = $db->query("SELECT id, sale_date, client_id, total_amount, amount_paid, amount_due, payment_method_id, tournee_id, status FROM sales WHERE tournee_id = " . intval($tournee['id'] ?? 2) . " OR id = 'V00004'");
$sales = $stmtSales->fetchAll(PDO::FETCH_ASSOC);
print_r($sales);

echo "\n=== SALE ITEMS FOR V00004 ===\n";
$stmtItems = $db->query("SELECT * FROM sale_items WHERE sale_id = 'V00004'");
print_r($stmtItems->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== CLIENT DELPHINE ===\n";
$stmtClient = $db->query("SELECT * FROM clients WHERE name LIKE '%Delphine%' OR id = " . intval($sales[0]['client_id'] ?? 0));
$client = $stmtClient->fetch(PDO::FETCH_ASSOC);
print_r($client);

if ($client) {
    echo "\n=== CLIENT EMBALLAGE DEBTS FOR DELPHINE (client_id = {$client['id']}) ===\n";
    $stmtEmbDebt = $db->query("SELECT * FROM client_emballage_debts WHERE client_id = {$client['id']}");
    print_r($stmtEmbDebt->fetchAll(PDO::FETCH_ASSOC));

    echo "\n=== EMBALLAGE MOVEMENTS FOR DELPHINE ===\n";
    $stmtEmbMov = $db->query("SELECT * FROM emballage_movements WHERE client_id = {$client['id']}");
    print_r($stmtEmbMov->fetchAll(PDO::FETCH_ASSOC));

    echo "\n=== CLIENT PAYMENTS FOR DELPHINE ===\n";
    $stmtPay = $db->query("SELECT * FROM client_payments WHERE client_id = {$client['id']}");
    print_r($stmtPay->fetchAll(PDO::FETCH_ASSOC));
}

echo "\n=== DETAIL RC00002 & CT ===\n";
$stmtP = $db->query("SELECT * FROM client_payments WHERE id = 'RC00002'");
print_r($stmtP->fetch(PDO::FETCH_ASSOC));

$stmtCt = $db->query("SELECT * FROM cash_transactions WHERE source_id = 'RC00002' OR description LIKE '%RC00002%'");
print_r($stmtCt->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== DETAIL MOVEMENT 49 ===\n";
$stmtM49 = $db->query("SELECT * FROM emballage_movements WHERE id = 49");
print_r($stmtM49->fetch(PDO::FETCH_ASSOC));






