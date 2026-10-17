<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance()->getConnection();

$db->beginTransaction();
try {
    // 1. Update sale_items item 1 (EXP on V00001) to crates_out = 4
    $db->prepare("UPDATE sale_items SET crates_out = 4 WHERE id = 1 AND sale_id = 'V00001'")->execute();

    // 2. Decrement empty crates in stock by 1
    $db->prepare("UPDATE emballage_stock SET empty_crates = GREATEST(0, empty_crates - 1) WHERE packaging_type_id = 1")->execute();

    // 3. Update Delphine's debt: crates_due = 1, loose_bottles_due = 6
    $db->prepare("UPDATE client_emballage_debts SET crates_due = 1, loose_bottles_due = 6 WHERE client_id = 1004 AND packaging_type_id = 1")->execute();

    // 4. Update emballage_movements: for V00001 movement 8
    $db->prepare("UPDATE emballage_movements SET crates_out = 1, notes = 'Sortie emballages non restitués (Dette Vente V00001)' WHERE id = 8 AND reference_id = 'V00001'")->execute();

    $db->commit();
    echo "Successfully updated V00001 to crates_out = 4 and Delphine debt to crates_due = 1, loose_bottles_due = 6.\n";
} catch (\Throwable $e) {
    $db->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
