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
ob_start();
$controller->employees();
$html = ob_get_clean();

// Check with DOMDocument for any malformed HTML
libxml_use_internal_errors(true);
$doc = new DOMDocument();
$doc->loadHTML($html);
$errors = libxml_get_errors();
libxml_clear_errors();

echo "Libxml errors count: " . count($errors) . "\n";
foreach (array_slice($errors, 0, 10) as $e) {
    echo "Line {$e->line}: {$e->message}\n";
}

// Find table and grid in HTML
if (preg_match('/<div class="dashboard-grid"[^>]*>/', $html, $m)) {
    echo "Found grid: " . $m[0] . "\n";
}
