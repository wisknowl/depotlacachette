<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Helper.php';
require_once __DIR__ . '/../app/Models/VentesModel.php';
require_once __DIR__ . '/../app/Models/ClientsModel.php';
require_once __DIR__ . '/../app/Models/EmballagesModel.php';

$vm = new App\Models\VentesModel();
$cm = new App\Models\ClientsModel();
$em = new App\Models\EmballagesModel();

echo "=== 1. V00001 INVOICE ===\n";
$sale = $vm->getSaleWithItems('V00001');
echo "Global Client Debt Badge on Invoice:\n";
foreach ($sale['client_packaging_debts'] as $cpd) {
    $lbl = $cpd['packaging_company'] . ($cpd['bottles_per_crate'] == 12 ? ' GM' : ' PM');
    echo " -> " . \App\Core\Helper::formatEmballageDebt($cpd['crates_due'], $cpd['loose_bottles_due'], $lbl, $cpd['bottles_per_crate']) . "\n";
}
echo "Line Items:\n";
foreach ($sale['items'] as $it) {
    $factor = max(1, intval($it['factor'] ?: 12));
    $cDue = max(0, $it['crates_out'] - $it['crates_returned']);
    $bDue = max(0, $it['bottles_out'] - ($it['crates_returned'] * $factor + $it['bottles_returned']));
    echo " -> {$it['product_name']}: " . \App\Core\Helper::formatEmballageDebt($cDue, $bDue, '', $factor) . "\n";
}

echo "\n=== 2. CLIENTS PAGE (DELPHINE) ===\n";
$clients = $cm->getFiltered(['search' => 'Delphine']);
echo "Dette Emballages on Clients Table: " . ($clients[0]['packaging_debts_detail'] ?? 'None') . "\n";

echo "\n=== 3. EMBALLAGES PAGE (DELPHINE) ===\n";
$debts = $em->getClientDebts(1004);
foreach ($debts as $d) {
    echo "Packaging: {$d['packaging_name']}\n";
    echo " -> Emb. Dûs: {$d['crates_due']} emb.\n";
    echo " -> Btls Dues: +{$d['loose_bottles_due']} btls\n";
    echo " -> Equivalent: " . \App\Core\Helper::formatEmballageDebt($d['crates_due'], $d['loose_bottles_due'], '', $d['bottles_per_crate']) . "\n";
    echo " -> Source Invoices:\n";
    foreach ($d['source_invoices'] as $inv) {
        echo "    * Facture {$inv['sale_id']} ({$inv['sale_date']}): +{$inv['crates_due']} emb. + {$inv['bottles_due']} btls\n";
    }
}
