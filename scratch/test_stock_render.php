<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SESSION['user'] = ['id' => 1, 'username' => 'admin', 'full_name' => 'Administrateur', 'role' => 'Admin'];

require 'config/config.php';
require 'app/Core/Database.php';
require 'app/Core/Helper.php';
require 'app/Models/StockModel.php';

$model = new \App\Models\StockModel();
$movements = $model->getMovements(10);

echo "=== FIRST 5 MOVEMENTS RENDERED IN TABLE ===" . PHP_EOL;
foreach (array_slice($movements, 0, 5) as $m) {
    $isCancelled = !empty($m['is_cancelled']);
    $isPurch = ($m['source_type'] === 'Purchase');
    $isSale = ($m['source_type'] === 'Sale');
    $isAdj = ($m['source_type'] === 'Adjustment' || empty($m['source_type']));
    $isAdmin = \App\Core\Helper::isAdmin();
    
    echo sprintf("[%d] %s (%s) | Source: %s | is_cancelled: %d | isAdmin: %d\n",
        $m['id'], $m['product_name'], $m['movement_type'], $m['source_type'], $isCancelled, $isAdmin
    );
    if ($isPurch) {
        echo "   -> Action column displays: 'Géré via Achats'\n";
    } elseif ($isSale) {
        echo "   -> Action column displays: 'Géré via Ventes'\n";
    } elseif ($isAdmin && !$isCancelled) {
        echo "   -> Action column displays: [🔴 Annuler Button] (href=/stock/cancel/{$m['id']})\n";
    }
}
