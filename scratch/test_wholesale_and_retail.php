<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Helper.php';
require_once __DIR__ . '/../app/Models/VentesModel.php';
require_once __DIR__ . '/../app/Models/ClientsModel.php';
require_once __DIR__ . '/../app/Models/EmballagesModel.php';

$vm = new App\Models\VentesModel();
$cm = new App\Models\ClientsModel();
$em = new App\Models\EmballagesModel();
$db = App\Core\Database::getInstance()->getConnection();

$initialDebts = $db->query("SELECT * FROM client_emballage_debts WHERE client_id = 1004")->fetchAll(PDO::FETCH_ASSOC);
$createdSaleIds = [];

try {
    echo "=== TEST 1: WHOLESALE (Simulation of user's sale with has_demi_emb = 1) ===\n";
    $saleData = [
        'sale_date' => date('Y-m-d'),
        'client_id' => 1004,
        'payment_method_id' => 1,
        'cash_account_id' => 1,
        'items' => [
            [
                'product_id' => 21, // EXP
                'quantity' => 3,
                'has_demi' => 1,
                'has_demi_emb' => 1,
                'unit_price' => 7800,
                'demi_unit_price' => 3900,
                'crates_returned' => 3,
                'bottles_returned' => 0
            ],
            [
                'product_id' => 27, // BFT
                'quantity' => 5,
                'has_demi' => 0,
                'unit_price' => 7200,
                'crates_returned' => 5,
                'bottles_returned' => 0
            ]
        ]
    ];

    $saleId = $vm->add($saleData);
    $createdSaleIds[] = $saleId;

    $items = $db->query("SELECT id, sale_id, product_id, quantity, crates_out, bottles_out, crates_returned, bottles_returned FROM sale_items WHERE sale_id = '$saleId'")->fetchAll(PDO::FETCH_ASSOC);
    echo "Created Sale ID: $saleId\n";
    echo "Sale Items:\n";
    print_r($items);

    $debts = $db->query("SELECT * FROM client_emballage_debts WHERE client_id = 1004")->fetchAll(PDO::FETCH_ASSOC);
    echo "Client Debts:\n";
    print_r($debts);

    $clients = $cm->getFiltered(['search' => 'Delphine']);
    echo "Clients Table Debt Display: " . ($clients[0]['packaging_debts_detail'] ?? 'None') . "\n";

    echo "\n=== TEST 2: RETAIL (Simulation of retail sale with has_emb = 1) ===\n";
    $retailData = [
        'sale_date' => date('Y-m-d'),
        'client_id' => 1004,
        'payment_method_id' => 1,
        'cash_account_id' => 1,
        'items' => [
            [
                'product_id' => 21, // EXP
                'quantity' => 6,
                'unit_price' => 650,
                'emballage_mode' => 'dette',
                'has_emb' => 1,
                'bottles_returned' => 0
            ]
        ]
    ];
    $retailSaleId = $vm->addRetailSale($retailData);
    $createdSaleIds[] = $retailSaleId;

    $retailItems = $db->query("SELECT id, sale_id, product_id, quantity, crates_out, bottles_out FROM sale_items WHERE sale_id = '$retailSaleId'")->fetchAll(PDO::FETCH_ASSOC);
    echo "Created Retail Sale ID: $retailSaleId\n";
    echo "Retail Items:\n";
    print_r($retailItems);

    $clients2 = $cm->getFiltered(['search' => 'Delphine']);
    echo "Clients Table Debt Display: " . ($clients2[0]['packaging_debts_detail'] ?? 'None') . "\n";

    echo "\n>>> SUCCESS: All tests passed! <<<\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
} finally {
    foreach ($createdSaleIds as $sid) {
        $db->prepare("DELETE FROM stock_movements WHERE reference = ?")->execute([$sid]);
        $db->prepare("DELETE FROM emballage_movements WHERE reference_id = ?")->execute([$sid]);
        $db->prepare("DELETE FROM sale_items WHERE sale_id = ?")->execute([$sid]);
        $db->prepare("DELETE FROM sales WHERE id = ?")->execute([$sid]);
    }
    $db->prepare("DELETE FROM client_emballage_debts WHERE client_id = 1004")->execute();
    foreach ($initialDebts as $d) {
        $db->prepare("INSERT INTO client_emballage_debts (client_id, packaging_type_id, crates_due, loose_bottles_due) VALUES (?, ?, ?, ?)")
           ->execute([$d['client_id'], $d['packaging_type_id'], $d['crates_due'], $d['loose_bottles_due']]);
    }
    echo "\nCleaned up all test artifacts and restored database state.\n";
}
