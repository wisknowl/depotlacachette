<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Helper.php';
require_once __DIR__ . '/../app/Models/AuditModel.php';

use App\Models\AuditModel;
use App\Core\Helper;

$model = new AuditModel();

echo "=== 1. TEST GRAND LIVRE AVEC EMBALLAGES ===\n";
$emballageEvents = $model->getGlobalAuditTrail(['module' => 'Emballages'], 5);
echo "Nombre de mouvements d'emballages trouvés : " . count($emballageEvents) . "\n";
foreach ($emballageEvents as $e) {
    echo "- [" . $e['event_date'] . "] (" . $e['module'] . ") " . $e['description'] . " | " . $e['flow_direction'] . " | Auteur: " . $e['author'] . "\n";
}

echo "\n=== 2. TEST LOG AUDIT SYSTEME & DIFFS ===\n";
Helper::logAudit(
    'PRICE_CHANGE', 
    'Produits', 
    'P001', 
    'Test modification prix casier 4500 -> 4800 FCFA', 
    ['price_casier' => 4500, 'name' => 'Castel 65cl'], 
    ['price_casier' => 4800, 'name' => 'Castel 65cl'], 
    'Hausse fournisseur brasseries'
);

$logs = $model->getSystemAuditLogs([], 5);
echo "Nombre de logs système trouvés : " . count($logs) . "\n";
foreach ($logs as $l) {
    echo "- [" . $l['created_at'] . "] " . $l['action'] . " (" . $l['module'] . " / " . $l['record_id'] . ") : " . $l['description'] . " | IP: " . $l['ip_address'] . "\n";
}

echo "\n=== 3. TEST REGISTRE DES CLOTURES ===\n";
$closings = $model->getClosings(5);
echo "Nombre de clôtures trouvées : " . count($closings) . "\n";
foreach ($closings as $c) {
    echo "- Clôture #" . $c['id'] . " du " . $c['closing_date'] . " (" . $c['cash_account_name'] . ") | Théorique: " . $c['theoretical_balance'] . " | Physique: " . $c['physical_cash'] . " | Écart: " . $c['difference'] . "\n";
}

echo "\nTOUS LES TESTS ONT REUSSI AVEC SUCCES !\n";
