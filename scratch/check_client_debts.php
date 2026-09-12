<?php
require 'config/config.php';
require 'app/Core/Database.php';

$db = App\Core\Database::getInstance()->getConnection();
$debts = $db->query("SELECT ced.*, c.name FROM client_emballage_debts ced JOIN clients c ON ced.client_id = c.id")->fetchAll(PDO::FETCH_ASSOC);
print_r($debts);
