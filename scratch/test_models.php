<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Models/PayrollModel.php';
require_once __DIR__ . '/../app/Models/EmployeeLedgerModel.php';

$pModel = new App\Models\PayrollModel();
$employees = $pModel->getEmployees(false);
echo "=== EMPLOYEES WITH BALANCES ===\n";
foreach ($employees as $e) {
    echo "ID: {$e['id']} | {$e['name']} ({$e['role']}) | Debit: {$e['total_debit']} | Credit: {$e['total_credit']} | Net: {$e['net_balance']}\n";
}

echo "\n=== TOTAL OUTSTANDING DEBT ===\n";
echo $pModel->getTotalOutstandingDebt() . " FCFA\n";

$elModel = new App\Models\EmployeeLedgerModel();
echo "\n=== LEDGER STATS ===\n";
print_r($elModel->getLedgerStats());

echo "\n=== FILTERED ENTRIES (driver 1) ===\n";
print_r($elModel->getFilteredEntries(['employee_id' => 1]));
