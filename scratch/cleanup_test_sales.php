<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance()->getConnection();

$sids = ['V00006', 'V00007'];
foreach ($sids as $sid) {
    $db->prepare("DELETE FROM stock_movements WHERE reference = ?")->execute([$sid]);
    $db->prepare("DELETE FROM sale_items WHERE sale_id = ?")->execute([$sid]);
    $db->prepare("DELETE FROM sales WHERE id = ?")->execute([$sid]);
}

$db->prepare("DELETE FROM emballage_movements WHERE reference_id IN ('V00006', 'V00007')")->execute();
$db->prepare("DELETE FROM emballage_movements WHERE id IN (18, 19, 20, 21)")->execute();

$db->prepare("DELETE FROM client_emballage_debts WHERE client_id = 1004")->execute();
$db->prepare("INSERT INTO client_emballage_debts (client_id, packaging_type_id, crates_due, loose_bottles_due) VALUES (1004, 1, 1, 18)")->execute();

echo "Cleaned up V00006 and V00007 and restored Delphine to crates_due=1, loose_bottles_due=18.\n";
