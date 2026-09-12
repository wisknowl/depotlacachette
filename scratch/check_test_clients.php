<?php
require 'config/config.php';
require 'app/Core/Database.php';

$db = App\Core\Database::getInstance()->getConnection();
$tests = $db->query("SELECT id, name FROM clients WHERE name LIKE '%TEST%'")->fetchAll(PDO::FETCH_ASSOC);
print_r($tests);
