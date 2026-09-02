<?php
require 'config/config.php';
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();

echo "=== RECENT SALES (LAST 10) ===" . PHP_EOL;
$stmt = $db->query('
    SELECT s.id, s.sale_date, s.client_id, c.name as client_name, s.total_amount, s.payment_method_id, s.status, s.created_at
    FROM sales s
    LEFT JOIN clients c ON s.client_id = c.id
    ORDER BY s.id DESC LIMIT 10
');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("[%s] Date: %s | Client: %-25s | Total: %8.2f | Status: %s\n",
        $row['id'], $row['sale_date'], $row['client_name'], $row['total_amount'], $row['status']
    );
}

echo PHP_EOL . "=== SALE ITEMS & EMBALLAGES FOR RECENT SALES ===" . PHP_EOL;
$stmtItems = $db->query('
    SELECT si.sale_id, si.product_id, p.name as prod_name, si.format_type, si.quantity, si.crates_out, si.bottles_out, si.crates_returned, si.bottles_returned
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    ORDER BY si.id DESC LIMIT 15
');
while ($item = $stmtItems->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("[%s] %-25s | Fmt: %-6s | Qty: %2.0f | Crates OUT: %2d | Btls OUT: %2d | Crates RET: %2d | Btls RET: %2d\n",
        $item['sale_id'], $item['prod_name'], $item['format_type'], $item['quantity'], $item['crates_out'], $item['bottles_out'], $item['crates_returned'], $item['bottles_returned']
    );
}

echo PHP_EOL . "=== CLIENT EMBALLAGE DEBTS CURRENT TABLE ===" . PHP_EOL;
$stmtDebts = $db->query('
    SELECT ced.client_id, c.name as client_name, ced.packaging_type_id, pt.name as packaging_name, ced.crates_due, ced.loose_bottles_due
    FROM client_emballage_debts ced
    JOIN clients c ON ced.client_id = c.id
    JOIN packaging_types pt ON ced.packaging_type_id = pt.id
    WHERE ced.crates_due > 0 OR ced.loose_bottles_due > 0
');
while ($d = $stmtDebts->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("Client [%d] %-25s | Pkg: %-35s | Crates Due: %2d | Btls Due: %2d\n",
        $d['client_id'], $d['client_name'], $d['packaging_name'], $d['crates_due'], $d['loose_bottles_due']
    );
}
