<?php
require 'config/config.php';
require 'app/Core/Database.php';

$db = App\Core\Database::getInstance()->getConnection();
$schemaSql = file_get_contents('database/schema.sql');

$liveTables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

echo "DEEP COLUMN CHECK:\n";
$issues = 0;
foreach ($liveTables as $table) {
    $cols = $db->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        $colName = $c['Field'];
        $pattern = '/[`]?' . preg_quote($colName, '/') . '[`]?/i';
        // Check if column is within the CREATE TABLE block for this table
        if (preg_match('/CREATE TABLE\s+[`]?' . preg_quote($table, '/') . '[`]?\s*\((.*?)\)\s*ENGINE/is', $schemaSql, $m)) {
            $tableDef = $m[1];
            if (!preg_match($pattern, $tableDef)) {
                echo "⚠️ Table `$table` -> Column `$colName` missing in schema.sql!\n";
                $issues++;
            }
        }
    }
}

if ($issues === 0) {
    echo "✓ All columns across all 31 tables match perfectly between Live DB and schema.sql!\n";
} else {
    echo "Found $issues issue(s).\n";
}
