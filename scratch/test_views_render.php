<?php
session_start();
$_SESSION['user'] = ['id' => 1, 'username' => 'admin', 'role' => 'Admin'];

define('ROOT', dirname(__DIR__));
define('APP', ROOT . '/app');
define('CORE', APP . '/Core');
define('CONTROLLERS', APP . '/Controllers');
define('MODELS', APP . '/Models');
define('VIEWS', APP . '/Views');
define('CONFIG', ROOT . '/config');
require_once CONFIG . '/config.php';

require_once __DIR__ . '/../app/Core/Controller.php';
require_once __DIR__ . '/../app/Core/Helper.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Models/PayrollModel.php';
require_once __DIR__ . '/../app/Models/EmployeeLedgerModel.php';
require_once __DIR__ . '/../app/Controllers/PayrollController.php';

$controller = new App\Controllers\PayrollController();

echo "Testing index()...\n";
ob_start();
$controller->index();
$outputIndex = ob_get_clean();
echo "Index rendered successfully, length: " . strlen($outputIndex) . "\n";

echo "Testing employees()...\n";
ob_start();
$controller->employees();
$outputEmployees = ob_get_clean();
echo "Employees rendered successfully, length: " . strlen($outputEmployees) . "\n";

echo "Testing ledger()...\n";
ob_start();
$controller->ledger();
$outputLedger = ob_get_clean();
echo "Ledger rendered successfully, length: " . strlen($outputLedger) . "\n";

echo "Testing ledger(1) [filtered on Sebastiene]...\n";
ob_start();
$controller->ledger(1);
$outputLedger1 = ob_get_clean();
echo "Ledger(1) rendered successfully, length: " . strlen($outputLedger1) . "\n";

echo "ALL TESTS PASSED!\n";
