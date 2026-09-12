<?php
require 'config/config.php';
require 'app/Core/Database.php';

$db = App\Core\Database::getInstance()->getConnection();
$sql = file_get_contents('resetdb.sql');

// Validate parsing of statements
$statements = array_filter(array_map('trim', explode(';', $sql)));
echo "Found " . count($statements) . " statements in resetdb.sql.\n";
foreach ($statements as $i => $stmt) {
    if (empty($stmt) || str_starts_with($stmt, '--')) continue;
    try {
        $db->prepare($stmt);
        echo "Statement " . ($i + 1) . ": VALID SYNTAX\n";
    } catch (PDOException $e) {
        echo "Statement " . ($i + 1) . ": ERROR: " . $e->getMessage() . "\n";
    }
}
