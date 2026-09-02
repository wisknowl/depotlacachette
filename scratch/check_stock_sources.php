<?php
require 'config/config.php';
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();

$stmt = $db->query('SELECT id, movement_date, product_id, movement_type_id, source_type, source_id, reference FROM stock_movements ORDER BY id DESC LIMIT 30');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("[%2d] TypeID: %d | SourceType: %-20s | SourceID: %-15s | Ref: %s\n",
        $row['id'], $row['movement_type_id'], $row['source_type'] ?: 'NULL', $row['source_id'] ?: 'NULL', $row['reference']
    );
}
