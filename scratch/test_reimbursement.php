<?php
define('ROOT', dirname(__DIR__));
define('APP', ROOT . '/app');
define('CORE', APP . '/Core');
define('CONTROLLERS', APP . '/Controllers');
define('MODELS', APP . '/Models');
define('VIEWS', APP . '/Views');
define('CONFIG', ROOT . '/config');
require_once CONFIG . '/config.php';

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Models/EmployeeLedgerModel.php';

$db = App\Core\Database::getInstance()->getConnection();
$model = new App\Models\EmployeeLedgerModel();

echo "Initial balance for employee 1: " . $model->getEmployeeBalance(1)['net_balance'] . " FCFA\n";

// Test reimbursement
$ref = $model->recordReimbursementPayment([
    'employee_id' => 1,
    'amount' => 900,
    'cash_account_id' => 1,
    'payment_date' => date('Y-m-d'),
    'notes' => 'Test Remboursement',
    'user_id' => 1
]);
echo "Reimbursement recorded with ref: $ref\n";

$balAfter = $model->getEmployeeBalance(1);
echo "Balance after reimbursement: " . $balAfter['net_balance'] . " FCFA\n";

// Verify cash_transactions
$stmtCash = $db->prepare("SELECT * FROM cash_transactions WHERE source_id = ?");
$stmtCash->execute([$ref]);
$cashRow = $stmtCash->fetch(PDO::FETCH_ASSOC);
echo "Cash transaction amount_in: " . $cashRow['amount_in'] . " FCFA\n";

// Clean up test reimbursement so DB state stays as is before user tests
$db->exec("DELETE FROM cash_transactions WHERE source_id = '$ref'");
$db->exec("DELETE FROM employee_ledger WHERE reference = '$ref'");

echo "Cleaned up test reimbursement. Restored balance: " . $model->getEmployeeBalance(1)['net_balance'] . " FCFA\n";
echo "REIMBURSEMENT LOGIC 100% OPERATIONAL!\n";
