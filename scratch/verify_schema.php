<?php
require 'config/config.php';
require 'app/Core/Database.php';

$db = App\Core\Database::getInstance()->getConnection();
$schemaSql = file_get_contents('database/schema.sql');

$liveTables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

echo "COMPARING DATABASE SCHEMA WITH LIVE DB:\n";
foreach ($liveTables as $table) {
    $pattern = '/CREATE TABLE\s+[`]?' . preg_quote($table, '/') . '[`]?/i';
    if (!preg_match($pattern, $schemaSql)) {
        echo "⚠️ Table '$table' NOT found in schema.sql!\n";
    } else {
        echo "✓ Table '$table' present in schema.sql\n";
    }
}
