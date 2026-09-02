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
    WHERE p.packaging_type_id = 1
    ORDER BY p.id ASC
");

echo "=== PRODUCTS ASSIGNED TO PACKAGING TYPE 1 (Casier SABC GM 12 Btls) ===" . PHP_EOL;
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("[%2d] %-25s | Cat: %-15s | Fmt: %-10s | Fct: %2d | is_returnable: %d | PkgID: %s (%s)\n",
        $r['id'], $r['name'], $r['category_name'], $r['format_name'], $r['factor'], $r['is_returnable'], $r['packaging_type_id'], $r['packaging_name']
    );
}
