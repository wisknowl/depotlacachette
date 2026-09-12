<?php
require 'config/config.php';
require 'app/Core/Database.php';

$db = App\Core\Database::getInstance()->getConnection();
$emps = $db->query('SELECT id, name, role, base_salary, status FROM employees')->fetchAll(PDO::FETCH_ASSOC);
print_r($emps);
