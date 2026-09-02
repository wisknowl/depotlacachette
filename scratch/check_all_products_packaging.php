<?php
require 'config/config.php';
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();

$stmt = $db->query("
    SELECT p.id, p.name, p.category_id, c.name as category_name, p.format_id, f.name as format_name, p.factor, p.is_returnable, p.packaging_type_id, pt.name as packaging_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN formats f ON p.format_id = f.id
    LEFT JOIN packaging_types pt ON p.packaging_type_id = pt.id
    ORDER BY p.is_returnable ASC, p.id ASC
");

echo "=== ALL PRODUCTS (RETURNABLE STATUS VS PACKAGING TYPE) ===" . PHP_EOL;
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $flag = ($r['is_returnable'] == 0 && $r['packaging_type_id'] !== null) ? "⚠️ ANOMALIE (Perdu mais PkgID = {$r['packaging_type_id']})" : "✅ OK";
    echo sprintf("[%2d] %-25s | is_returnable: %d | Fct: %2d | PkgID: %-4s (%-35s) | %s\n",
        $r['id'], $r['name'], $r['is_returnable'], $r['factor'], $r['packaging_type_id'] ?: 'NULL', $r['packaging_name'] ?: 'AUCUN', $flag
    );
}
