<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance()->getConnection();

$db->beginTransaction();
try {
    // 1. Update sale_items for V00005 item 5 (EXP)
    $db->prepare("UPDATE sale_items SET crates_out = 1 WHERE id = 5 AND sale_id = 'V00005'")->execute();

    // 2. Update emballage_movements for V00005 item 16
    $db->prepare("UPDATE emballage_movements SET crates_out = 1 WHERE id = 16 AND reference_id = 'V00005'")->execute();

    // 3. Decrement empty crates in stock by 1
    $db->prepare("UPDATE emballage_stock SET empty_crates = GREATEST(0, empty_crates - 1) WHERE packaging_type_id = 1")->execute();

    // 4. Update Delphine's debt: crates_due = 2, loose_bottles_due = 18
    $db->prepare("UPDATE client_emballage_debts SET crates_due = 2, loose_bottles_due = 18 WHERE client_id = 1004 AND packaging_type_id = 1")->execute();

    $db->commit();
    echo "Successfully updated V00005 and Delphine's packaging debt!\n";
} catch (\Throwable $e) {
    $db->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
