<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Models/EmployeeLedgerModel.php';

$db = App\Core\Database::getInstance()->getConnection();
$empModel = new App\Models\EmployeeLedgerModel();

// Find closed tournees with shortages
$stmt = $db->query("SELECT * FROM tournees WHERE status = 'Cloturee' AND cash_shortage > 0 AND driver_id > 0");
$tournees = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($tournees as $t) {
    if (!$empModel->entryExists('Tournee', $t['id'], 'Tournee_Shortage')) {
        echo "Adding shortage for Tournee {$t['reference']} (Driver {$t['driver_id']}, Amount {$t['cash_shortage']})...\n";
        $empModel->recordShortageDebit(
            $t['driver_id'],
            'Tournee',
            $t['id'],
            $t['reference'],
            $t['cash_shortage'],
            "Manquant de caisse décharge tournée {$t['reference']} (Attendu: " . number_format($t['total_cash_collected'] - $t['total_expenses'], 0, ',', ' ') . " FCFA, Versé: " . number_format($t['cash_deposited'], 0, ',', ' ') . " FCFA)",
            $t['created_by'] ?? 1
        );
    } else {
        echo "Tournee {$t['reference']} already recorded.\n";
    }
}
echo "Done.\n";
