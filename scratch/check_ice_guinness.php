<?php
require 'config/config.php';
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();

$stmt = $db->query("
    SELECT p.id, p.name, p.format_id, f.name as format_name, p.factor, p.is_returnable, p.packaging_type_id, pt.name as packaging_name,
    COALESCE((
        SELECT SUM(CASE WHEN mt.direction = 'IN' THEN sm.stock_equivalent WHEN mt.direction = 'OUT' THEN -sm.stock_equivalent ELSE 0 END)
        FROM stock_movements sm
        JOIN movement_types mt ON sm.movement_type_id = mt.id
        WHERE sm.product_id = p.id
    ), 0) as current_stock
    FROM products p
    LEFT JOIN formats f ON p.format_id = f.id
    LEFT JOIN packaging_types pt ON p.packaging_type_id = pt.id
    ORDER BY p.packaging_type_id ASC, p.id ASC
");

echo "=== ALL PRODUCTS GROUPED BY PACKAGING MODEL ===" . PHP_EOL;
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("[%2d] %-30s | Fmt: %-15s | Fct: %2d | PkgID: %-4s (%-35s) | Stock: %.2f\n",
        $r['id'], $r['name'], $r['format_name'], $r['factor'], $r['packaging_type_id'] ?: 'NULL', $r['packaging_name'] ?: 'NON ASSIGNE', $r['current_stock']
    );
}
