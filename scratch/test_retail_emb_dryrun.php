<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Helper.php';
require_once __DIR__ . '/../app/Models/VentesModel.php';

$vm = new App\Models\VentesModel();
$db = App\Core\Database::getInstance()->getConnection();

$initialDebts = $db->query("SELECT * FROM client_emballage_debts WHERE client_id = 1004")->fetchAll(PDO::FETCH_ASSOC);

$testData = [
    'sale_date' => date('Y-m-d'),
    'client_id' => 1004,
    'payment_method_id' => 1,
    'cash_account_id' => 1,
    'items' => [
        [
            'product_id' => 21, // EXP
            'quantity' => 9,
            'unit_price' => 650,
            'emballage_mode' => 'dette',
            'has_emb' => 1
        ],
        [
            'product_id' => 27, // BFT BEAUFORT LAGER
            'quantity' => 3,
            'unit_price' => 600,
            'emballage_mode' => 'dette',
            'has_emb' => 0
        ]
    ]
];

$saleId = null;
try {
    $saleId = $vm->addRetailSale($testData);
    $items = $db->query("SELECT id, sale_id, product_id, quantity, crates_out, bottles_out FROM sale_items WHERE sale_id = '$saleId'")->fetchAll(PDO::FETCH_ASSOC);
    $debt = $db->query("SELECT * FROM client_emballage_debts WHERE client_id = 1004")->fetchAll(PDO::FETCH_ASSOC);
    echo "Created sale: $saleId\n";
    echo "Sale items:\n";
    print_r($items);
    echo "Client debt after sale:\n";
    print_r($debt);
    $formatted = \App\Core\Helper::formatEmballageDebt($debt[0]['crates_due'], $debt[0]['loose_bottles_due'], 'SABC GM', 12);
    echo "Formatted Debt: $formatted\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
} finally {
    if ($saleId) {
        $db->prepare("DELETE FROM stock_movements WHERE reference = ?")->execute([$saleId]);
        $db->prepare("DELETE FROM emballage_movements WHERE reference_id = ?")->execute([$saleId]);
        $db->prepare("DELETE FROM sale_items WHERE sale_id = ?")->execute([$saleId]);
        $db->prepare("DELETE FROM sales WHERE id = ?")->execute([$saleId]);
    }
    $db->prepare("DELETE FROM client_emballage_debts WHERE client_id = 1004")->execute();
    foreach ($initialDebts as $d) {
        $db->prepare("INSERT INTO client_emballage_debts (client_id, packaging_type_id, crates_due, loose_bottles_due) VALUES (?, ?, ?, ?)")
           ->execute([$d['client_id'], $d['packaging_type_id'], $d['crates_due'], $d['loose_bottles_due']]);
    }
    echo "Cleaned up and restored initial state.\n";
}
