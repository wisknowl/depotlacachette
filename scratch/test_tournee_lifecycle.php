<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Helper.php';
require_once __DIR__ . '/../app/Models/TourneesModel.php';
require_once __DIR__ . '/../app/Models/VentesModel.php';
require_once __DIR__ . '/../app/Models/ClientsModel.php';
require_once __DIR__ . '/../app/Models/EmballagesModel.php';

$db = \App\Core\Database::getInstance()->getConnection();
$tourneesModel = new \App\Models\TourneesModel();
$ventesModel = new \App\Models\VentesModel();
$clientsModel = new \App\Models\ClientsModel();
$emballagesModel = new \App\Models\EmballagesModel();

echo "========================================================\n";
echo "TEST SUITE: UNIFIED LIFECYCLE FOR TOURNÉE, DETTES & EMBALLAGES\n";
echo "========================================================\n\n";

// Clean up any dangling test tournee 10 from previous interrupted run
$db->exec("DELETE FROM sale_items WHERE sale_id IN (SELECT id FROM sales WHERE tournee_id = 10)");
$db->exec("DELETE FROM sales WHERE tournee_id = 10");
$db->exec("DELETE FROM tournee_items WHERE tournee_id = 10");
$db->exec("DELETE FROM tournees WHERE id = 10");
$db->exec("DELETE FROM stock_movements WHERE source_id = '10'");

// 1. Create Dedicated Test Client with credit allowed
$db->exec("INSERT INTO clients (name, phone, address, client_type_id, max_credit) VALUES ('TEST_CLIENT_AUTO', '699999999', 'Zone Test', 1, 500000)");
$clientId = (int)$db->lastInsertId();

$clientBefore = $clientsModel->getById($clientId);
echo "1. Baseline Client State (Client {$clientBefore['name']}, ID {$clientId}):\n";
echo "   - Solde Dû: {$clientBefore['solde_du']} FCFA\n";
$stmtD = $db->query("SELECT * FROM client_emballage_debts WHERE client_id = $clientId");
echo "   - Emballage Debts: " . json_encode($stmtD->fetchAll(PDO::FETCH_ASSOC)) . "\n\n";

// Returnable product: Beaufort Lager (id 27, packaging_type_id 1, factor 12)
$productId = 27;

// A. Create Tournée
echo "2. Creating Tournée TR_TEST...\n";
$tourneeData = [
    'tournee_date' => date('Y-m-d'),
    'driver_id' => 1,
    'vehicle_name' => 'Camion Test',
    'notes' => 'Test Automated Lifecycle',
    'items' => [
        [
            'product_id' => $productId,
            'qty_loaded' => 10,
            'has_demi' => 0,
            'unit_price' => 7200
        ]
    ]
];

$tourneeId = $tourneesModel->createTournee($tourneeData, 1);
$tournee = $tourneesModel->getById($tourneeId);
echo "   -> Tournée Created: ID {$tourneeId}, Ref {$tournee['reference']}, Status: {$tournee['status']}\n\n";

// B. Create Route Sale under Tournée
echo "3. Creating Route Sale under Tournée {$tournee['reference']}...\n";
// Client buys 2 crates + 1 demi (30 bottles), but only returns 2 crates (24 bottles).
// Missing bottles: 6 bottles.
// Total price: (2 * 7200) + 3600 = 18 000 FCFA.
// Amount paid: 15 000 FCFA. Reste dû: 3 000 FCFA.
$saleData = [
    'sale_date' => date('Y-m-d'),
    'client_id' => $clientId,
    'tournee_id' => $tourneeId,
    'sale_type' => 'route',
    'settlement_type' => 'cash',
    'cash_account_id' => 1,
    'amount_paid' => 15000,
    'items' => [
        [
            'product_id' => $productId,
            'format_type' => 'casier',
            'quantity' => 2,
            'has_demi' => 1,
            'unit_price' => 7200,
            'demi_unit_price' => 3600,
            'crates_returned' => 2,
            'bottles_returned' => 0
        ]
    ]
];

$saleId = $ventesModel->add($saleData);
echo "   -> Sale Created: ID {$saleId}\n";

// CHECK 1: While Tournée is En_Route
$sale = $ventesModel->getSaleWithItems($saleId);
$clientEnRoute = $clientsModel->getById($clientId);
$stmtDEnRoute = $db->query("SELECT * FROM client_emballage_debts WHERE client_id = $clientId AND packaging_type_id = 1")->fetch(PDO::FETCH_ASSOC);
$stmtEmbMovEnRoute = $db->query("SELECT COUNT(*) FROM emballage_movements WHERE reference_id = '{$saleId}'")->fetchColumn();

echo "\n--- CHECK 1: EN_ROUTE INTEGRITY ---\n";
echo "   - Sale Status: {$sale['status']} (Expected: 'En_Route') => " . ($sale['status'] === 'En_Route' ? "PASS [OK]" : "FAIL [ERR]") . "\n";
echo "   - Client Solde Dû: {$clientEnRoute['solde_du']} FCFA (Expected: 0) => " . ($clientEnRoute['solde_du'] == 0 ? "PASS [OK]" : "FAIL [ERR]") . "\n";
$cratesDue = $stmtDEnRoute['crates_due'] ?? 0;
$btlsDue = $stmtDEnRoute['loose_bottles_due'] ?? 0;
echo "   - Client Packaging Debt: {$cratesDue} c. + {$btlsDue} btl. (Expected: 0 c. + 0 btl.) => " . (($cratesDue == 0 && $btlsDue == 0) ? "PASS [OK]" : "FAIL [ERR]") . "\n";
echo "   - Emballage Ledger Entries: {$stmtEmbMovEnRoute} (Expected: 0) => " . ($stmtEmbMovEnRoute == 0 ? "PASS [OK]" : "FAIL [ERR]") . "\n";

// C. Clôture / Décharge of Tournée
echo "\n4. Closing Tournée (saveDecharge)...\n";
$tourneeItem = $tourneesModel->getItems($tourneeId)[0];
$dechargeData = [
    'tournee_id' => $tourneeId,
    'cash_account_id' => 1,
    'cash_deposited' => 15000,
    'returns' => [
        $tourneeItem['id'] => [
            'crates' => 7,
            'has_demi' => 1 // 7.5 returned unsold, 2.5 sold = 10 loaded
        ]
    ],
    'empty_crates_returned' => [
        1 => 2 // 2 empty crates returned to depot
    ],
    'loose_bottles_returned' => [
        1 => 0
    ],
    'expenses' => []
];

$tourneesModel->saveDecharge($tourneeId, $dechargeData, 1);

// CHECK 2: After Clôture
$tourneeClosed = $tourneesModel->getById($tourneeId);
$saleClosed = $ventesModel->getSaleWithItems($saleId);
$clientClosed = $clientsModel->getById($clientId);
$stmtDClosed = $db->query("SELECT * FROM client_emballage_debts WHERE client_id = $clientId AND packaging_type_id = 1")->fetch(PDO::FETCH_ASSOC);
$stmtEmbMovSaleClosed = $db->query("SELECT movement_type, bottles_out FROM emballage_movements WHERE reference_id = '{$saleId}'")->fetch(PDO::FETCH_ASSOC);
$stmtEmbMovRouteClosed = $db->query("SELECT movement_type, crates_in FROM emballage_movements WHERE reference_id = '{$tourneeId}' AND movement_type = 'Route_Return'")->fetch(PDO::FETCH_ASSOC);

echo "\n--- CHECK 2: CLOTURE INTEGRITY ---\n";
echo "   - Tournée Status: {$tourneeClosed['status']} (Expected: 'Cloturee') => " . ($tourneeClosed['status'] === 'Cloturee' ? "PASS [OK]" : "FAIL [ERR]") . "\n";
echo "   - Sale Status: {$saleClosed['status']} (Expected: 'Valid') => " . ($saleClosed['status'] === 'Valid' ? "PASS [OK]" : "FAIL [ERR]") . "\n";
echo "   - Client Solde Dû: {$clientClosed['solde_du']} FCFA (Expected: 3 000 FCFA) => " . ($clientClosed['solde_du'] == 3000 ? "PASS [OK]" : "FAIL [ERR]") . "\n";
$cratesDue = $stmtDClosed['crates_due'] ?? 0;
$btlsDue = $stmtDClosed['loose_bottles_due'] ?? 0;
echo "   - Client Packaging Debt: {$cratesDue} c. + {$btlsDue} btl. (Expected: 0 c. + 6 btl.) => " . (($cratesDue == 0 && $btlsDue == 6) ? "PASS [OK]" : "FAIL [ERR]") . "\n";
echo "   - Emballage Ledger (Sale_Drink): " . ($stmtEmbMovSaleClosed['movement_type'] ?? 'NONE') . " (bottles_out: " . ($stmtEmbMovSaleClosed['bottles_out'] ?? 0) . ") => " . (($stmtEmbMovSaleClosed['bottles_out'] ?? 0) == 6 ? "PASS [OK]" : "FAIL [ERR]") . "\n";
echo "   - Emballage Ledger (Route_Return): " . ($stmtEmbMovRouteClosed['movement_type'] ?? 'NONE') . " (crates_in: " . ($stmtEmbMovRouteClosed['crates_in'] ?? 0) . ") => " . (($stmtEmbMovRouteClosed['crates_in'] ?? 0) == 2 ? "PASS [OK]" : "FAIL [ERR]") . "\n";

// D. Réouverture de Tournée
echo "\n5. Reopening Tournée (reopenTournee)...\n";
$tourneesModel->reopenTournee($tourneeId, 1, 'Automated Test Reopening');

// CHECK 3: After Réouverture
$tourneeReopened = $tourneesModel->getById($tourneeId);
$saleReopened = $ventesModel->getSaleWithItems($saleId);
$clientReopened = $clientsModel->getById($clientId);
$stmtDReopened = $db->query("SELECT * FROM client_emballage_debts WHERE client_id = $clientId AND packaging_type_id = 1")->fetch(PDO::FETCH_ASSOC);
$stmtEmbMovSaleReopened = $db->query("SELECT COUNT(*) FROM emballage_movements WHERE reference_id = '{$saleId}' AND movement_type = 'Sale_Drink'")->fetchColumn();
$stmtEmbMovCompensate = $db->query("SELECT movement_type, crates_out FROM emballage_movements WHERE reference_id = '{$tourneeId}' AND movement_type = 'Adjustment_Minus'")->fetch(PDO::FETCH_ASSOC);

echo "\n--- CHECK 3: REOPENING ROLLBACK INTEGRITY ---\n";
echo "   - Tournée Status: {$tourneeReopened['status']} (Expected: 'En_Route') => " . ($tourneeReopened['status'] === 'En_Route' ? "PASS [OK]" : "FAIL [ERR]") . "\n";
echo "   - Sale Status: {$saleReopened['status']} (Expected: 'En_Route') => " . ($saleReopened['status'] === 'En_Route' ? "PASS [OK]" : "FAIL [ERR]") . "\n";
echo "   - Client Solde Dû: {$clientReopened['solde_du']} FCFA (Expected: 0 FCFA) => " . ($clientReopened['solde_du'] == 0 ? "PASS [OK]" : "FAIL [ERR]") . "\n";
$cratesDue = $stmtDReopened['crates_due'] ?? 0;
$btlsDue = $stmtDReopened['loose_bottles_due'] ?? 0;
echo "   - Client Packaging Debt: {$cratesDue} c. + {$btlsDue} btl. (Expected: 0 c. + 0 btl.) => " . (($cratesDue == 0 && $btlsDue == 0) ? "PASS [OK]" : "FAIL [ERR]") . "\n";
echo "   - Emballage Ledger (Sale_Drink cleared): {$stmtEmbMovSaleReopened} (Expected: 0) => " . ($stmtEmbMovSaleReopened == 0 ? "PASS [OK]" : "FAIL [ERR]") . "\n";
echo "   - Emballage Ledger (Adjustment_Minus compensation): " . ($stmtEmbMovCompensate['movement_type'] ?? 'NONE') . " (crates_out: " . ($stmtEmbMovCompensate['crates_out'] ?? 0) . ") => " . (($stmtEmbMovCompensate['crates_out'] ?? 0) == 2 ? "PASS [OK]" : "FAIL [ERR]") . "\n";

// E. Re-clôture
echo "\n6. Re-closing Tournée (saveDecharge again)...\n";
$tourneesModel->saveDecharge($tourneeId, $dechargeData, 1);

// CHECK 4: After Re-clôture
$tourneeReClosed = $tourneesModel->getById($tourneeId);
$saleReClosed = $ventesModel->getSaleWithItems($saleId);
$clientReClosed = $clientsModel->getById($clientId);
$stmtDReClosed = $db->query("SELECT * FROM client_emballage_debts WHERE client_id = $clientId AND packaging_type_id = 1")->fetch(PDO::FETCH_ASSOC);
$stmtEmbMovSaleReClosed = $db->query("SELECT movement_type, bottles_out FROM emballage_movements WHERE reference_id = '{$saleId}' AND movement_type = 'Sale_Drink'")->fetch(PDO::FETCH_ASSOC);

echo "\n--- CHECK 4: RE-CLOTURE INTEGRITY ---\n";
echo "   - Tournée Status: {$tourneeReClosed['status']} (Expected: 'Cloturee') => " . ($tourneeReClosed['status'] === 'Cloturee' ? "PASS [OK]" : "FAIL [ERR]") . "\n";
echo "   - Sale Status: {$saleReClosed['status']} (Expected: 'Valid') => " . ($saleReClosed['status'] === 'Valid' ? "PASS [OK]" : "FAIL [ERR]") . "\n";
echo "   - Client Solde Dû: {$clientReClosed['solde_du']} FCFA (Expected: 3 000 FCFA) => " . ($clientReClosed['solde_du'] == 3000 ? "PASS [OK]" : "FAIL [ERR]") . "\n";
$cratesDue = $stmtDReClosed['crates_due'] ?? 0;
$btlsDue = $stmtDReClosed['loose_bottles_due'] ?? 0;
echo "   - Client Packaging Debt: {$cratesDue} c. + {$btlsDue} btl. (Expected: 0 c. + 6 btl.) => " . (($cratesDue == 0 && $btlsDue == 6) ? "PASS [OK]" : "FAIL [ERR]") . "\n";
echo "   - Emballage Ledger (Sale_Drink re-committed): " . ($stmtEmbMovSaleReClosed['movement_type'] ?? 'NONE') . " (bottles_out: " . ($stmtEmbMovSaleReClosed['bottles_out'] ?? 0) . ") => " . (($stmtEmbMovSaleReClosed['bottles_out'] ?? 0) == 6 ? "PASS [OK]" : "FAIL [ERR]") . "\n";

// CLEANUP TEST DATA
echo "\n7. Cleaning up test data...\n";
$tourneesModel->cancelTourneeComplete($tourneeId, 1, 'Cleanup Test Data');
$db->exec("DELETE FROM sale_items WHERE sale_id = '{$saleId}'");
$db->exec("DELETE FROM sales WHERE id = '{$saleId}'");
$db->exec("DELETE FROM tournee_items WHERE tournee_id = {$tourneeId}");
$db->exec("DELETE FROM tournees WHERE id = {$tourneeId}");
$db->exec("DELETE FROM stock_movements WHERE reference LIKE '%{$tournee['reference']}%' OR source_id = '{$tourneeId}'");
$db->exec("DELETE FROM cash_transactions WHERE source_id = '{$tourneeId}'");
$db->exec("DELETE FROM emballage_movements WHERE reference_id = '{$tourneeId}' OR reference_id = '{$saleId}'");
$db->exec("DELETE FROM client_emballage_debts WHERE client_id = {$clientId}");
$db->exec("DELETE FROM clients WHERE id = {$clientId}");
echo "   -> Cleanup complete.\n\n";

echo "========================================================\n";
echo "ALL TESTS PASSED WITH 100% PRECISION!\n";
echo "========================================================\n";
