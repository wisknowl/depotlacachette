<?php

// Fuseau horaire du Cameroun (WAT / UTC+1)
date_default_timezone_set('Africa/Douala');

// Configuration de l'URL de base (à adapter selon le dossier)
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
define('BASE_URL', $scriptName);

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Mettre le mot de passe si existant (ex: sur WAMP c'est vide, sur MAMP c'est root)
define('DB_NAME', 'depot_la_cachette');

// Informations sur l'application
define('APP_NAME', 'Dépôt La Cachette');
define('APP_VERSION', '4.0 Web');
