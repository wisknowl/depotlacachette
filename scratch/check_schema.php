<?php
require 'config/config.php';
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();

$stmt = $db->query('DESCRIBE stock_movements');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . ' | ' . $row['Type'] . ' | ' . $row['Null'] . ' | ' . ($row['Default'] ?? 'NULL') . PHP_EOL;
}
