<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Models/ClientsModel.php';

$cModel = new \App\Models\ClientsModel();
$clients = $cModel->getFiltered(['search' => 'Delphine']);
print_r($clients);
