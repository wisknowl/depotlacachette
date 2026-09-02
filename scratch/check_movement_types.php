<?php
require 'config/config.php';
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();

$stmt = $db->query('SELECT * FROM movement_types');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['id'] . ' | ' . $row['name'] . ' | ' . $row['direction'] . PHP_EOL;
}
