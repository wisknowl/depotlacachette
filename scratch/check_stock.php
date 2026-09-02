<?php
require 'config/config.php';
require 'app/Core/Database.php';

$db = \App\Core\Database::getInstance()->getConnection();

echo "=== 1. ALL RECENT STOCK MOVEMENTS (LAST 15) ===" . PHP_EOL;
$stmt = $db->query("
    SELECT sm.id, sm.movement_date, sm.product_id, p.name as product_name, sm.format_type, sm.movement_type_id, mt.name as mov_type_name, mt.direction, sm.quantity, sm.stock_equivalent, sm.reference, sm.created_at
    FROM stock_movements sm
    LEFT JOIN products p ON sm.product_id = p.id
    LEFT JOIN movement_types mt ON sm.movement_type_id = mt.id
    ORDER BY sm.id DESC
    LIMIT 15
");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("[%d] %s | %s | %s (%s) | Qty: %.2f | Equiv: %.2f | Ref: %s\n", 
        $r['id'], $r['movement_date'], $r['product_name'], $r['mov_type_name'], $r['direction'], $r['quantity'], $r['stock_equivalent'], $r['reference']
    );
}

echo PHP_EOL . "=== 2. CASTLE MILK MOVEMENTS ===" . PHP_EOL;
$stmt = $db->query("
    SELECT sm.id, sm.movement_date, sm.product_id, p.name as product_name, mt.name as mov_type_name, mt.direction, sm.quantity, sm.stock_equivalent, sm.reference, sm.created_at
    FROM stock_movements sm
    LEFT JOIN products p ON sm.product_id = p.id
    LEFT JOIN movement_types mt ON sm.movement_type_id = mt.id
    WHERE p.name LIKE '%castle%' OR p.name LIKE '%milk%'
    ORDER BY sm.id ASC
");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("[%d] %s | %s | %s (%s) | Qty: %.2f | Equiv: %.2f | Ref: %s\n", 
        $r['id'], $r['movement_date'], $r['product_name'], $r['mov_type_name'], $r['direction'], $r['quantity'], $r['stock_equivalent'], $r['reference']
    );
}

echo PHP_EOL . "=== 3. PRODUCTS BREAKDOWN & PACKAGING ATTACHMENT ===" . PHP_EOL;
$stmt = $db->query("
    SELECT 
        p.id, 
        p.name, 
        p.is_returnable, 
        p.packaging_type_id, 
        pt.name as pkg_name, 
        f.name as format_name,
        COALESCE(SUM(CASE WHEN mt.direction = 'IN' THEN sm.stock_equivalent WHEN mt.direction = 'OUT' THEN -sm.stock_equivalent ELSE 0 END), 0) as current_stock
    FROM products p
    LEFT JOIN formats f ON p.format_id = f.id
    LEFT JOIN packaging_types pt ON p.packaging_type_id = pt.id
    LEFT JOIN stock_movements sm ON p.id = sm.product_id
    LEFT JOIN movement_types mt ON sm.movement_type_id = mt.id
    GROUP BY p.id, p.name, p.is_returnable, p.packaging_type_id, pt.name, f.name
    ORDER BY current_stock DESC
");

$totalLiquid = 0;
$totalWithPackaging = 0;
$totalWithoutPackaging = 0;

while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $stk = floatval($r['current_stock']);
    $totalLiquid += $stk;
    if (!empty($r['packaging_type_id'])) {
        $totalWithPackaging += $stk;
    } else {
        $totalWithoutPackaging += $stk;
    }
    echo sprintf("[%2d] %-32s | Fmt: %-10s | Ret: %d | PkgID: %-4s (%-15s) | Stock: %6.2f\n", 
        $r['id'], $r['name'], $r['format_name'], $r['is_returnable'], 
        $r['packaging_type_id'] ?: 'NULL', $r['pkg_name'] ?: 'NON CONSIGNE', $stk
    );
}

echo PHP_EOL . "=== 4. SUMMARY COMPARISON ===" . PHP_EOL;
echo "Total Liquid Stock in Depot (All Products): " . $totalLiquid . " casiers/packs" . PHP_EOL;
echo "Total Liquid Stock WITH packaging_type_id (Consigné/Returnable to a crate model): " . $totalWithPackaging . " casiers" . PHP_EOL;
echo "Total Liquid Stock WITHOUT packaging_type_id (Canettes, Eau/Packs perdus): " . $totalWithoutPackaging . " packs/cartons" . PHP_EOL;

echo PHP_EOL . "=== 5. EMBALLAGE MODEL GLOBAL SUMMARY ===" . PHP_EOL;
require_once 'app/Models/EmballagesModel.php';
$embModel = new \App\Models\EmballagesModel();
$embSummary = $embModel->getGlobalSummary();
echo "Emballage Summary total_full_crates: " . $embSummary['total_full_crates'] . PHP_EOL;
echo "Emballage Summary total_empty_crates: " . $embSummary['total_empty_crates'] . PHP_EOL;
foreach ($embSummary['packaging_types'] as $pt) {
    echo sprintf("  - Model [%d] %-20s: Full Crates = %d | Empty Crates = %d | Client Due = %d | Supplier Due = %d\n",
        $pt['id'], $pt['name'], $pt['full_crates_stock'], $pt['empty_crates'], $pt['client_crates_due'], $pt['supplier_crates_due']
    );
}
