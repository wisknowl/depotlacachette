<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SESSION['user'] = ['id' => 1, 'username' => 'admin', 'full_name' => 'Administrateur', 'role' => 'Admin'];

require 'config/config.php';
require 'app/Core/Database.php';
require 'app/Core/Helper.php';
require 'app/Models/StockModel.php';

$model = new \App\Models\StockModel();
$movements = $model->getMovements(50);

echo "=== MOVEMENTS #20 TO #29 (ADJUSTMENTS) ===" . PHP_EOL;
foreach ($movements as $m) {
    if ($m['id'] >= 20 && $m['id'] <= 29) {
        $isCancelled = !empty($m['is_cancelled']);
        $isPurch = ($m['source_type'] === 'Purchase');
        $isSale = ($m['source_type'] === 'Sale');
        $isAdmin = \App\Core\Helper::isAdmin();
        
        echo sprintf("[%d] %-25s | Type: %-25s | Source: %-12s | Action: ",
            $m['id'], $m['product_name'], $m['movement_type'], $m['source_type']
        );
        if ($isPurch) {
            echo "Géré via Achats\n";
        } elseif ($isSale) {
            echo "Géré via Ventes\n";
        } elseif ($isAdmin && !$isCancelled) {
            echo "[🔴 BOUTON ANNULER ACTIF] -> /stock/cancel/{$m['id']}\n";
        } else {
            echo "-\n";
        }
    }
}
