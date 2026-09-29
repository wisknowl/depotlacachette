<?php
/**
 * Test Suite: Lifecycle of On-Hold (En_Attente) Sales and Purchases
 */

define('ROOT', 'c:/xampp/htdocs/webapp');
define('APP', ROOT . '/app');
define('CONFIG', ROOT . '/config');

require_once CONFIG . '/config.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = APP . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

use App\Core\Database;
use App\Models\VentesModel;
use App\Models\AchatsModel;

$db = Database::getInstance()->getConnection();
$ventesModel = new VentesModel();
$achatsModel = new AchatsModel();

function assertCheck($name, $condition, $details = '') {
    if ($condition) {
        echo "  [PASS] {$name}\n";
    } else {
        echo "  [FAIL] {$name} - {$details}\n";
        throw new Exception("Assertion failed: {$name} ({$details})");
    }
}

echo "=======================================================\n";
echo "BEGINNING ON-HOLD (EN ATTENTE) FACTURATION TEST SUITE\n";
echo "=======================================================\n\n";

// Get test product, client, supplier, cash account via model methods
$products = $ventesModel->getProducts();
usort($products, function($a, $b) {
    return floatval($b['current_stock']) <=> floatval($a['current_stock']);
});
$prod = null;
foreach ($products as $p) {
    if (floatval($p['current_stock']) >= 10 && floatval($p['price_casier']) > 0) {
        $prod = $p;
        break;
    }
}
if (!$prod) $prod = $products[0];

$clients = $ventesModel->getClients();
$client = $clients[0];
$suppliers = $achatsModel->getSuppliers();
$supplier = $suppliers[0];
$cashAccounts = $achatsModel->getCashAccountsWithBalances();
$cashAcc = $cashAccounts[0];

$productId = intval($prod['id']);
$clientId = intval($client['id']);
$supplierId = intval($supplier['id']);
$cashAccountId = intval($cashAcc['id']);

$initialStockInfo = $ventesModel->getProductStock($productId);
$initialStock = floatval($initialStockInfo['current_stock']);
$initialReserved = floatval($initialStockInfo['reserved_stock']);

echo "Test Fixtures:\n";
echo "  Product: [{$prod['id']}] {$prod['name']} (Stock: {$initialStock}, Reserved: {$initialReserved})\n";
echo "  Client: [{$client['id']}] {$client['name']}\n";
echo "  Supplier: [{$supplier['id']}] {$supplier['name']}\n";
echo "  Cash Account: [{$cashAcc['id']}] {$cashAcc['name']}\n\n";

// ==========================================
// TEST 1: SALE ON-HOLD CREATION
// ==========================================
echo "TEST 1: Creating a Sale on Hold (En_Attente)...\n";

$saleData = [
    'sale_date' => date('Y-m-d'),
    'client_id' => $clientId,
    'payment_method_id' => 1,
    'cash_account_id' => $cashAccountId,
    'reference' => 'TEST-HOLD-SALE-' . time(),
    'notes' => 'Test On Hold Sale',
    'user_id' => 1,
    'is_hold' => true,
    'items' => [
        [
            'product_id' => $productId,
            'quantity' => 1,
            'unit_price' => floatval($prod['price_casier']),
            'format_type' => 'casier',
            'has_demi' => 0,
            'crates_returned' => 1,
            'bottles_returned' => 0
        ]
    ]
];

$saleId = $ventesModel->add($saleData);
echo "  Created Sale ID: {$saleId}\n";

$saleRow = $db->query("SELECT * FROM sales WHERE id = '{$saleId}'")->fetch();
assertCheck("Sale status is En_Attente", $saleRow['status'] === 'En_Attente', "Got status: " . $saleRow['status']);

// Check 0 stock movements
$smCount = $db->query("SELECT COUNT(*) FROM stock_movements WHERE source_type = 'Sale' AND source_id = '{$saleId}'")->fetchColumn();
assertCheck("Zero stock movements created for on-hold sale", intval($smCount) === 0, "Got smCount: {$smCount}");

// Check 0 cash transactions
$ctCount = $db->query("SELECT COUNT(*) FROM cash_transactions WHERE source_id = '{$saleId}'")->fetchColumn();
assertCheck("Zero cash transactions created for on-hold sale", intval($ctCount) === 0, "Got ctCount: {$ctCount}");

// Check physical stock unchanged
$stockInfoAfterHold = $ventesModel->getProductStock($productId);
assertCheck("Physical warehouse stock unchanged", floatval($stockInfoAfterHold['current_stock']) === $initialStock, "Initial: {$initialStock}, Current: {$stockInfoAfterHold['current_stock']}");

// Check reserved stock in VentesModel::getProductStock
assertCheck("Reserved stock tracks the on-hold sale", floatval($stockInfoAfterHold['reserved_stock']) === ($initialReserved + 1), "Reserved: " . $stockInfoAfterHold['reserved_stock']);

// ==========================================
// TEST 2: UPDATE SALE ON-HOLD
// ==========================================
echo "\nTEST 2: Updating an On-Hold Sale (adjusting quantity to 2)...\n";
$updateSaleData = [
    'sale_date' => date('Y-m-d'),
    'client_id' => $clientId,
    'payment_method_id' => 1,
    'cash_account_id' => $cashAccountId,
    'reference' => $saleRow['reference'],
    'notes' => 'Updated notes while on hold',
    'items' => [
        [
            'product_id' => $productId,
            'quantity' => 2, // changed from 1 to 2
            'unit_price' => floatval($prod['price_casier']),
            'format_type' => 'casier',
            'has_demi' => 0,
            'crates_returned' => 2,
            'bottles_returned' => 0
        ]
    ]
];

$ventesModel->updateHoldSale($saleId, $updateSaleData, 1);
$updatedSaleRow = $db->query("SELECT * FROM sales WHERE id = '{$saleId}'")->fetch();
assertCheck("Sale status remains En_Attente after update", $updatedSaleRow['status'] === 'En_Attente');
$updatedItemQty = $db->query("SELECT quantity FROM sale_items WHERE sale_id = '{$saleId}'")->fetchColumn();
assertCheck("Item quantity updated to 2", intval($updatedItemQty) === 2, "Got: {$updatedItemQty}");

$smCountAfterUpdate = $db->query("SELECT COUNT(*) FROM stock_movements WHERE source_type = 'Sale' AND source_id = '{$saleId}'")->fetchColumn();
assertCheck("Still zero stock movements after update", intval($smCountAfterUpdate) === 0);

$stockInfoAfterUpdate = $ventesModel->getProductStock($productId);
assertCheck("Reserved stock updated to +2", floatval($stockInfoAfterUpdate['reserved_stock']) === ($initialReserved + 2));

// ==========================================
// TEST 3: VALIDATE SALE ON-HOLD
// ==========================================
echo "\nTEST 3: Validating On-Hold Sale (dock physical check passed)...\n";
$cashBalBefore = floatval($db->query("SELECT COALESCE(SUM(amount_in), 0) - COALESCE(SUM(amount_out), 0) FROM cash_transactions WHERE cash_account_id = {$cashAccountId}")->fetchColumn());

$ventesModel->validateHoldSale($saleId, $updateSaleData, 1);

$validatedSaleRow = $db->query("SELECT * FROM sales WHERE id = '{$saleId}'")->fetch();
assertCheck("Sale status transitioned to Valid", $validatedSaleRow['status'] === 'Valid', "Got status: " . $validatedSaleRow['status']);

// Stock movement must now exist
$smCountValid = $db->query("SELECT COUNT(*) FROM stock_movements WHERE source_type = 'Sale' AND source_id = '{$saleId}'")->fetchColumn();
assertCheck("Stock movement OUT created on validation", intval($smCountValid) >= 1, "Got: {$smCountValid}");

// Stock deducted by 2
$stockInfoAfterVal = $ventesModel->getProductStock($productId);
assertCheck("Physical warehouse stock deducted by 2", floatval($stockInfoAfterVal['current_stock']) === ($initialStock - 2), "Expected " . ($initialStock - 2) . ", got {$stockInfoAfterVal['current_stock']}");
assertCheck("Reserved stock released back to initial", floatval($stockInfoAfterVal['reserved_stock']) === $initialReserved, "Expected {$initialReserved}, got {$stockInfoAfterVal['reserved_stock']}");

// Cash transaction created
$ctCountValid = $db->query("SELECT COUNT(*) FROM cash_transactions WHERE source_id = '{$saleId}'")->fetchColumn();
assertCheck("Cash transaction IN created on validation", intval($ctCountValid) === 1, "Got: {$ctCountValid}");

$cashBalAfter = floatval($db->query("SELECT COALESCE(SUM(amount_in), 0) - COALESCE(SUM(amount_out), 0) FROM cash_transactions WHERE cash_account_id = {$cashAccountId}")->fetchColumn());
assertCheck("Cash account balance credited", $cashBalAfter > $cashBalBefore, "Initial: {$cashBalBefore}, After: {$cashBalAfter}");

// ==========================================
// TEST 4: ON-HOLD SALE CANCELLATION (LIGHTWEIGHT)
// ==========================================
echo "\nTEST 4: Creating and Cancelling an On-Hold Sale (No reversal storno)...\n";
$cancelSaleData = $saleData;
$cancelSaleData['reference'] = 'TEST-CANCEL-HOLD-' . time();
$cancelSaleId = $ventesModel->add($cancelSaleData);
assertCheck("New sale created on hold", $db->query("SELECT status FROM sales WHERE id = '{$cancelSaleId}'")->fetchColumn() === 'En_Attente');

$ventesModel->cancelSale($cancelSaleId, 1, 'Client abandoned queue');
$cancelledSaleRow = $db->query("SELECT * FROM sales WHERE id = '{$cancelSaleId}'")->fetch();
assertCheck("Sale status is Cancelled", $cancelledSaleRow['status'] === 'Cancelled');

// Verify zero movements existed and zero reversal movements created
$smCancelCount = $db->query("SELECT COUNT(*) FROM stock_movements WHERE source_type = 'Sale' AND source_id = '{$cancelSaleId}'")->fetchColumn();
assertCheck("Zero stock movements existed or generated during hold cancellation", intval($smCancelCount) === 0);

// ==========================================
// TEST 5: PURCHASE ON-HOLD CREATION
// ==========================================
echo "\nTEST 5: Creating a Supplier Purchase on Hold (En_Attente)...\n";
$stockPurchInfo = $ventesModel->getProductStock($productId);
$initialStockPurch = floatval($stockPurchInfo['current_stock']);

$purchaseData = [
    'purchase_date' => date('Y-m-d'),
    'supplier_invoice_date' => date('Y-m-d'),
    'supplier_id' => $supplierId,
    'settlement_type' => 'cash',
    'cash_account_id' => $cashAccountId,
    'reference' => 'PREV-TEST-' . time(),
    'notes' => 'Test Previsionnel Achat',
    'user_id' => 1,
    'is_hold' => true,
    'items' => [
        [
            'product_id' => $productId,
            'quantity' => 10,
            'unit_price' => floatval($prod['purchase_price']),
            'format_type' => 'casier',
            'has_demi' => 0,
            'empties_returned' => 10,
            'packaging_mode' => 'charge',
            'ristourne_unit' => 50
        ]
    ]
];

$purchaseId = $achatsModel->add($purchaseData);
echo "  Created Purchase ID: {$purchaseId}\n";

$purchaseRow = $db->query("SELECT * FROM purchases WHERE id = '{$purchaseId}'")->fetch();
assertCheck("Purchase status is En_Attente", $purchaseRow['status'] === 'En_Attente', "Got status: " . $purchaseRow['status']);

// Check 0 stock movements
$smPCount = $db->query("SELECT COUNT(*) FROM stock_movements WHERE source_type = 'Purchase' AND source_id = '{$purchaseId}'")->fetchColumn();
assertCheck("Zero stock movements created for on-hold purchase", intval($smPCount) === 0);

// Check 0 cash disbursements
$ctPCount = $db->query("SELECT COUNT(*) FROM cash_transactions WHERE source_id = '{$purchaseId}'")->fetchColumn();
assertCheck("Zero cash transactions created for on-hold purchase", intval($ctPCount) === 0);

// Check warehouse stock unchanged
$stockAfterHoldPurch = floatval($ventesModel->getProductStock($productId)['current_stock']);
assertCheck("Stock unchanged after on-hold purchase", $stockAfterHoldPurch === $initialStockPurch);

// ==========================================
// TEST 6: UPDATE PURCHASE ON-HOLD
// ==========================================
echo "\nTEST 6: Updating an On-Hold Purchase (truck arrived with 12 crates instead of 10)...\n";
$updatePurchaseData = [
    'purchase_date' => date('Y-m-d'),
    'supplier_invoice_date' => date('Y-m-d'),
    'supplier_id' => $supplierId,
    'settlement_type' => 'cash',
    'cash_account_id' => $cashAccountId,
    'reference' => 'BL-OFFICIEL-998877',
    'notes' => 'Ajuste apres dechargement camion',
    'items' => [
        [
            'product_id' => $productId,
            'quantity' => 12, // adjusted to 12
            'unit_price' => floatval($prod['purchase_price']),
            'format_type' => 'casier',
            'has_demi' => 0,
            'empties_returned' => 12,
            'packaging_mode' => 'charge',
            'ristourne_unit' => 50
        ]
    ]
];

$achatsModel->updateHoldPurchase($purchaseId, $updatePurchaseData, 1);
$updatedPurchRow = $db->query("SELECT * FROM purchases WHERE id = '{$purchaseId}'")->fetch();
assertCheck("Purchase status remains En_Attente after update", $updatedPurchRow['status'] === 'En_Attente');
assertCheck("Reference updated to BL-OFFICIEL-998877", $updatedPurchRow['reference'] === 'BL-OFFICIEL-998877');

$updatedPurchQty = $db->query("SELECT quantity FROM purchase_items WHERE purchase_id = '{$purchaseId}'")->fetchColumn();
assertCheck("Purchase item quantity updated to 12", intval($updatedPurchQty) === 12);

// ==========================================
// TEST 7: VALIDATE PURCHASE ON-HOLD
// ==========================================
echo "\nTEST 7: Validating On-Hold Purchase (truck unloaded, stock received)...\n";
$achatsModel->validateHoldPurchase($purchaseId, $updatePurchaseData, 1);

$validatedPurchRow = $db->query("SELECT * FROM purchases WHERE id = '{$purchaseId}'")->fetch();
assertCheck("Purchase status transitioned to Valid", $validatedPurchRow['status'] === 'Valid');

// Stock movement IN created
$smPCountValid = $db->query("SELECT COUNT(*) FROM stock_movements WHERE source_type = 'Purchase' AND source_id = '{$purchaseId}'")->fetchColumn();
assertCheck("Stock movement IN created on validation", intval($smPCountValid) >= 1);

// Stock incremented by 12
$stockAfterValPurch = floatval($ventesModel->getProductStock($productId)['current_stock']);
assertCheck("Physical warehouse stock increased by 12", $stockAfterValPurch === ($initialStockPurch + 12), "Expected " . ($initialStockPurch + 12) . ", got {$stockAfterValPurch}");

// Cash transaction OUT created
$ctPCountValid = $db->query("SELECT COUNT(*) FROM cash_transactions WHERE source_id = '{$purchaseId}'")->fetchColumn();
assertCheck("Cash transaction OUT created on validation", intval($ctPCountValid) === 1);

// ==========================================
// TEST 8: ON-HOLD PURCHASE CANCELLATION
// ==========================================
echo "\nTEST 8: Creating and Cancelling an On-Hold Purchase (No reversal needed)...\n";
$cancelPurchData = $purchaseData;
$cancelPurchData['reference'] = 'PREV-CANCEL-' . time();
$cancelPurchId = $achatsModel->add($cancelPurchData);
assertCheck("New purchase created on hold", $db->query("SELECT status FROM purchases WHERE id = '{$cancelPurchId}'")->fetchColumn() === 'En_Attente');

$achatsModel->cancelPurchase($cancelPurchId, 1, 'Delivery truck cancelled by brewery');
$cancelledPurchRow = $db->query("SELECT * FROM purchases WHERE id = '{$cancelPurchId}'")->fetch();
assertCheck("Purchase status is Cancelled", $cancelledPurchRow['status'] === 'Cancelled');

$smCancelPCount = $db->query("SELECT COUNT(*) FROM stock_movements WHERE source_type = 'Purchase' AND source_id = '{$cancelPurchId}'")->fetchColumn();
assertCheck("Zero stock movements existed or generated during hold purchase cancellation", intval($smCancelPCount) === 0);

echo "\n=======================================================\n";
echo "ALL TESTS PASSED SUCCESSFULLY (8/8)!\n";
echo "Database and business logic integrity 100% verified.\n";
echo "=======================================================\n";
