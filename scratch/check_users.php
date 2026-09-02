<?php
require 'config/config.php';
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();

$stmt = $db->query('SELECT id, username, full_name, role FROM users');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['id'] . ' | ' . $row['username'] . ' | ' . $row['full_name'] . ' | ' . $row['role'] . PHP_EOL;
}
