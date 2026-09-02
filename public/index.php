<?php
/**
 * DEPOT LA CACHETTE - FRONT CONTROLLER
 */

session_start();

// Define base paths
define('ROOT', dirname(__DIR__));
define('APP', ROOT . '/app');
define('CORE', APP . '/Core');
define('CONTROLLERS', APP . '/Controllers');
define('MODELS', APP . '/Models');
define('VIEWS', APP . '/Views');
define('CONFIG', ROOT . '/config');

// Require configuration
require_once CONFIG . '/config.php';

// Simple Autoloader
spl_autoload_register(function ($class) {
    // Convert namespace to full file path
    // e.g., App\Core\Router -> app/Core/Router.php
    $prefix = 'App\\';
    $base_dir = APP . '/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Load the Router
use App\Core\Router;

$router = new Router();
$router->dispatch($_SERVER['REQUEST_URI']);
