<?php
require 'config/config.php';
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();

$stmt = $db->query("
    SELECT sm.id, sm.movement_date, sm.product_id, p.name as product_name, sm.movement_type_id, mt.name as movement_name, sm.quantity, sm.stock_equivalent, sm.unit_price, (sm.stock_equivalent * sm.unit_price) as loss_amount, sm.reference
    FROM stock_movements sm
    JOIN movement_types mt ON sm.movement_type_id = mt.id
    JOIN products p ON sm.product_id = p.id
    WHERE sm.movement_type_id IN (3, 4, 6)
");

echo "=== ALL MOVEMENTS WITH LOSS (TYPE 3, 4, 6) ===" . PHP_EOL;
$total = 0;
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $total += $r['loss_amount'];
    echo sprintf("[%d] %s | %-25s | %-20s | Qty: %.2f | Equiv: %.2f | Price: %.2f | Loss: %.2f FCFA | Ref: %s\n",
        $r['id'], $r['movement_date'], $r['product_name'], $r['movement_name'], $r['quantity'], $r['stock_equivalent'], $r['unit_price'], $r['loss_amount'], $r['reference']
    );
}
echo "TOTAL LOSS: " . number_format($total, 2) . " FCFA" . PHP_EOL;
