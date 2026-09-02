<?php
require 'config/config.php';
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();

echo "=== PURCHASES TABLE SCHEMA ===" . PHP_EOL;
$stmt = $db->query('DESCRIBE purchases');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . ' | ' . $row['Type'] . ' | ' . $row['Null'] . ' | ' . ($row['Default'] ?? 'NULL') . PHP_EOL;
}

echo PHP_EOL . "=== SALES TABLE SCHEMA ===" . PHP_EOL;
$stmt2 = $db->query('DESCRIBE sales');
while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . ' | ' . $row['Type'] . ' | ' . $row['Null'] . ' | ' . ($row['Default'] ?? 'NULL') . PHP_EOL;
}
