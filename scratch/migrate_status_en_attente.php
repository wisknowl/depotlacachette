<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    echo "Connected to database.\n";
    
    $db->exec("ALTER TABLE sales MODIFY COLUMN status ENUM('Valid', 'Cancelled', 'En_Route', 'En_Attente') NOT NULL DEFAULT 'Valid'");
    echo "Table 'sales' updated successfully.\n";
    
    $db->exec("ALTER TABLE purchases MODIFY COLUMN status ENUM('Valid', 'Cancelled', 'En_Attente') NOT NULL DEFAULT 'Valid'");
    echo "Table 'purchases' updated successfully.\n";
    
    echo "Migration completed successfully!\n";
} catch (\Throwable $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
    exit(1);
}
