<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Helper.php';
require_once __DIR__ . '/../app/Models/VentesModel.php';
require_once __DIR__ . '/../app/Models/EmballagesModel.php';
require_once __DIR__ . '/../app/Models/ClientsModel.php';

$db = App\Core\Database::getInstance()->getConnection();
$vm = new App\Models\VentesModel();
$em = new App\Models\EmballagesModel();
$cm = new App\Models\ClientsModel();

$initialDebts = $db->query("SELECT * FROM client_emballage_debts WHERE client_id = 1004")->fetchAll(PDO::FETCH_ASSOC);
$createdSaleIds = [];

try {
    // Pick an existing client or test client
    $clientId = 1004; // Delphine
    $products = $vm->getProducts();
    $chosenProd = null;
    foreach ($products as $p) {
        if ($p['is_returnable'] && $p['current_stock'] >= 2) {
            $chosenProd = $p;
            break;
        }
    }
    if (!$chosenProd) {
        throw new Exception("No returnable product with stock found!");
    }
    $prodId = $chosenProd['id'];
    $pkgTypeId = $chosenProd['packaging_type_id'];
    echo "Using Product: {$chosenProd['name']} (ID: $prodId, Stock: {$chosenProd['current_stock']})\n";

    // Reset Delphine packaging debt to 0
    $db->prepare("DELETE FROM client_emballage_debts WHERE client_id = ?")->execute([$clientId]);

    echo "--- Test 1: Demi wholesale with Sorti avec emb. ---\n";
    // Simulate addSale with 0 whole casiers + 1 demi (has_demi = 1, has_demi_emb = 1)
    // Product 1 (EXP)
    $saleData = [
        'sale_date' => date('Y-m-d'),
        'client_id' => $clientId,
        'payment_method_id' => 1,
        'cash_account_id' => 1,
        'items' => [
            [
                'product_id' => $prodId,
                'quantity' => 0,
                'unit_price' => 7655,
                'has_demi' => 1,
                'has_demi_emb' => 1,
                'crates_returned' => 0,
                'bottles_returned' => 0
            ]
        ]
    ];
    $saleId = $vm->add($saleData);
    $createdSaleIds[] = $saleId;
    $clients = $cm->getFiltered(['search' => 'Delphine']);
    $delphine = $clients[0] ?? null;
    echo "Sale ID: " . $saleId . "\n";
    echo "Client Packaging Debt: " . ($delphine['packaging_debts_detail'] ?? 'None') . "\n";

    echo "\n--- Test 2: Client returns 6 bottles, keeps 1 crate shell ---\n";
    $em->recordClientReturn($clientId, $pkgTypeId, 0, 6, "Retour 6 bouteilles sans casier");
    $clients = $cm->getFiltered(['search' => 'Delphine']);
    $delphine = $clients[0] ?? null;
    echo "Client Packaging Debt: " . ($delphine['packaging_debts_detail'] ?? 'None') . "\n";

    echo "\n--- Test 3: Client returns 1 crate shell ---\n";
    $em->recordClientReturn($clientId, $pkgTypeId, 1, 0, "Retour 1 casier vide");
    $clients = $cm->getFiltered(['search' => 'Delphine']);
    $delphine = $clients[0] ?? null;
    echo "Client Packaging Debt: " . ($delphine['packaging_debts_detail'] ?? 'None (Dette soldée)') . "\n";

    echo "\n--- Test 4: Retail sale with 6 bottles and Sorti avec emb. ---\n";
    $retailData = [
        'sale_date' => date('Y-m-d'),
        'client_id' => $clientId,
        'payment_method_id' => 1,
        'cash_account_id' => 1,
        'items' => [
            [
                'product_id' => $prodId,
                'quantity' => 6,
                'unit_price' => 650,
                'emballage_mode' => 'dette',
                'has_emb' => 1
            ]
        ]
    ];
    $retailSaleId = $vm->addRetailSale($retailData);
    $createdSaleIds[] = $retailSaleId;
    $clients = $cm->getFiltered(['search' => 'Delphine']);
    $delphine = $clients[0] ?? null;
    echo "Retail Sale ID: " . $retailSaleId . "\n";
    echo "Client Packaging Debt: " . ($delphine['packaging_debts_detail'] ?? 'None') . "\n";

    echo "\n>>> SUCCESS: All operations tested! <<<\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
} finally {
    // Clean up created test sales
    foreach ($createdSaleIds as $sid) {
        $db->prepare("DELETE FROM stock_movements WHERE reference = ?")->execute([$sid]);
        $db->prepare("DELETE FROM emballage_movements WHERE reference_id = ?")->execute([$sid]);
        $db->prepare("DELETE FROM sale_items WHERE sale_id = ?")->execute([$sid]);
        $db->prepare("DELETE FROM sales WHERE id = ?")->execute([$sid]);
    }
    // Restore initial debts
    $db->prepare("DELETE FROM client_emballage_debts WHERE client_id = 1004")->execute();
    foreach ($initialDebts as $d) {
        $db->prepare("INSERT INTO client_emballage_debts (client_id, packaging_type_id, crates_due, loose_bottles_due) VALUES (?, ?, ?, ?)")
           ->execute([$d['client_id'], $d['packaging_type_id'], $d['crates_due'], $d['loose_bottles_due']]);
    }
    // Clean up packaging restitution movements for client 1004 during test
    $db->prepare("DELETE FROM emballage_movements WHERE notes LIKE '%Retour 6 bouteilles sans casier%' OR notes LIKE '%Retour 1 casier vide%'")->execute();
    echo "\nCleaned up all test artifacts and restored database state.\n";
}
