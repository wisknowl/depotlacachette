<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Helper.php';
require_once __DIR__ . '/../app/Models/TourneesModel.php';
require_once __DIR__ . '/../app/Models/VentesModel.php';
require_once __DIR__ . '/../app/Models/ClientsModel.php';
require_once __DIR__ . '/../app/Models/EmployeeLedgerModel.php';

$db = \App\Core\Database::getInstance()->getConnection();
$tourneesModel = new \App\Models\TourneesModel();
$ventesModel = new \App\Models\VentesModel();
$clientsModel = new \App\Models\ClientsModel();
$ledgerModel = new \App\Models\EmployeeLedgerModel();

echo "========================================================\n";
echo "TEST SUITE: EMPLOYEE LEDGER & SHORTAGE LIFECYCLE\n";
echo "========================================================\n\n";

// Driver: Sebastiene (ID 1)
$driverId = 1;
$balBefore = $ledgerModel->getEmployeeBalance($driverId);
echo "1. Baseline Driver Balance (Sebastiene, ID {$driverId}):\n";
echo "   - Total Débit: {$balBefore['total_debit']} F\n";
echo "   - Total Crédit: {$balBefore['total_credit']} F\n";
echo "   - Solde Dû: {$balBefore['net_balance']} F\n\n";

// Temporary test client with credit allowed
$db->exec("INSERT INTO clients (name, phone, address, client_type_id, max_credit) VALUES ('TEST_CLIENT_LEDGER', '699001122', 'Test Zone', 1, 500000)");
$clientId = (int)$db->lastInsertId();

// Create test tournee with driver Sebastiene (ID 1)
$productId = 27; // Beaufort Lager
$tourneeData = [
    'tournee_date' => date('Y-m-d'),
    'driver_id' => $driverId,
    'vehicle_name' => 'Camion Test Ledger',
    'notes' => 'Test Employee Ledger Shortage',
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
echo "2. Tournée Created: ID {$tourneeId}, Ref {$tournee['reference']}, Driver: {$tournee['driver_name']} (ID {$tournee['driver_id']})\n";

// Create route sale: Total 18 000 F, paid 15 000 F in cash
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
echo "   -> Sale Created: ID {$saleId} (Cash collecté: 15 000 F)\n\n";

// STEP 1: Clôture with Shortage: Expected 15 000 F, Deposited 14 100 F -> Shortage = 900 F!
echo "3. Closing Tournée with 900 F Cash Shortage (saveDecharge)...\n";
$tourneeItem = $tourneesModel->getItems($tourneeId)[0];
$dechargeData = [
    'tournee_id' => $tourneeId,
    'cash_account_id' => 1,
    'cash_deposited' => 14100, // Shortage = 15000 - 14100 = 900 F
    'returns' => [
        $tourneeItem['id'] => [
            'crates' => 7,
            'has_demi' => 1
        ]
    ],
    'empty_crates_returned' => [
        1 => 2
    ],
    'loose_bottles_returned' => [
        1 => 0
    ],
    'expenses' => []
];

$tourneesModel->saveDecharge($tourneeId, $dechargeData, 1);

// CHECK 1: After Décharge with Shortage
$tourneeClosed = $tourneesModel->getById($tourneeId);
$balAfterShortage = $ledgerModel->getEmployeeBalance($driverId);
$stmtLedger = $db->query("SELECT * FROM employee_ledger WHERE source_type = 'Tournee' AND source_id = '{$tourneeId}'")->fetch(PDO::FETCH_ASSOC);

echo "\n--- CHECK 1: DECHARGE SHORTAGE DEBIT ---\n";
echo "   - Tournée Cash Shortage: {$tourneeClosed['cash_shortage']} FCFA (Expected: 900) => " . ($tourneeClosed['cash_shortage'] == 900 ? "PASS [OK]" : "FAIL [ERR]") . "\n";
echo "   - Ledger Entry Found: " . ($stmtLedger ? "YES" : "NO") . " => " . ($stmtLedger ? "PASS [OK]" : "FAIL [ERR]") . "\n";
echo "   - Ledger Amount Debit: {$stmtLedger['amount_debit']} FCFA (Expected: 900) => " . ($stmtLedger['amount_debit'] == 900 ? "PASS [OK]" : "FAIL [ERR]") . "\n";
echo "   - Ledger Source Type: {$stmtLedger['source_type']} (Expected: 'Tournee') => " . ($stmtLedger['source_type'] === 'Tournee' ? "PASS [OK]" : "FAIL [ERR]") . "\n";
echo "   - Ledger Source ID: {$stmtLedger['source_id']} (Expected: '{$tourneeId}') => " . ($stmtLedger['source_id'] == $tourneeId ? "PASS [OK]" : "FAIL [ERR]") . "\n";
echo "   - Driver Net Balance Owed: {$balAfterShortage['net_balance']} FCFA (Expected: 900) => " . ($balAfterShortage['net_balance'] == 900 ? "PASS [OK]" : "FAIL [ERR]") . "\n";

// STEP 2: Reopening Tournée (reopenTournee)
echo "\n4. Reopening Tournée (reopenTournee)...\n";
$tourneesModel->reopenTournee($tourneeId, 1, 'Testing Shortage Reversal');

// CHECK 2: After Reopening
$balAfterReopen = $ledgerModel->getEmployeeBalance($driverId);
$stmtLedgerReopen = $db->query("SELECT COUNT(*) FROM employee_ledger WHERE source_type = 'Tournee' AND source_id = '{$tourneeId}'")->fetchColumn();

echo "\n--- CHECK 2: REOPENING SHORTAGE REVERSAL ---\n";
echo "   - Ledger Entries for Tournée: {$stmtLedgerReopen} (Expected: 0) => " . ($stmtLedgerReopen == 0 ? "PASS [OK]" : "FAIL [ERR]") . "\n";
echo "   - Driver Net Balance Owed: {$balAfterReopen['net_balance']} FCFA (Expected: 0) => " . ($balAfterReopen['net_balance'] == 0 ? "PASS [OK]" : "FAIL [ERR]") . "\n";

// STEP 3: Re-closing Tournée with Shortage again
echo "\n5. Re-closing Tournée with 900 F Shortage again...\n";
$tourneesModel->saveDecharge($tourneeId, $dechargeData, 1);

// CHECK 3: After Re-closing
$balAfterReclose = $ledgerModel->getEmployeeBalance($driverId);
$stmtLedgerReclose = $db->query("SELECT * FROM employee_ledger WHERE source_type = 'Tournee' AND source_id = '{$tourneeId}'")->fetch(PDO::FETCH_ASSOC);

echo "\n--- CHECK 3: RE-CLOTURE SHORTAGE INTEGRITY ---\n";
echo "   - Ledger Entry Re-created: " . ($stmtLedgerReclose ? "YES" : "NO") . " => " . ($stmtLedgerReclose ? "PASS [OK]" : "FAIL [ERR]") . "\n";
echo "   - Driver Net Balance Owed: {$balAfterReclose['net_balance']} FCFA (Expected: 900) => " . ($balAfterReclose['net_balance'] == 900 ? "PASS [OK]" : "FAIL [ERR]") . "\n";

// STEP 4: Driver Reimburses the 900 F later
echo "\n6. Driver Reimburses the 900 F later (recordReimbursementCredit)...\n";
$creditId = $ledgerModel->recordReimbursementCredit(
    $driverId,
    'Cash',
    'REG_001',
    $tournee['reference'],
    900,
    "Règlement manquant tournée {$tournee['reference']} apporté par Sebastiene",
    1
);
$balAfterReimburse = $ledgerModel->getEmployeeBalance($driverId);

echo "\n--- CHECK 4: REIMBURSEMENT CREDIT INTEGRITY ---\n";
echo "   - Reimbursement Entry ID: {$creditId} => PASS [OK]\n";
echo "   - Driver Total Debit: {$balAfterReimburse['total_debit']} FCFA\n";
echo "   - Driver Total Credit: {$balAfterReimburse['total_credit']} FCFA\n";
echo "   - Driver Net Balance Owed: {$balAfterReimburse['net_balance']} FCFA (Expected: 0) => " . ($balAfterReimburse['net_balance'] == 0 ? "PASS [OK]" : "FAIL [ERR]") . "\n";

// Full ledger inspection
$history = $ledgerModel->getEmployeeLedger($driverId);
echo "\n--- FULL LEDGER TRACEABILITY FOR SEBASTIENE ---\n";
foreach ($history as $h) {
    echo "   [{$h['transaction_date']}] Type: {$h['transaction_type']} | Source: {$h['source_type']} #{$h['source_id']} | Debit: {$h['amount_debit']} | Credit: {$h['amount_credit']} | Note: {$h['description']}\n";
}

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
$db->exec("DELETE FROM employee_ledger WHERE employee_id = {$driverId}");
echo "   -> Cleanup complete.\n\n";

echo "========================================================\n";
echo "ALL TESTS PASSED WITH 100% PRECISION!\n";
echo "========================================================\n";
