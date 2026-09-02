<?php
namespace App\Core;

class Router {
    protected $routes = [];

    public function add($route, $controller, $action) {
        $this->routes[$route] = ['controller' => $controller, 'action' => $action];
    }

    public function dispatch($url) {
        // Strip query string and base url path
        $url = parse_url($url, PHP_URL_PATH);
        
        // Remove BASE_URL prefix from url if it exists
        $scriptNameDir = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptNameDir !== '/' && strpos($url, $scriptNameDir) === 0) {
            $url = substr($url, strlen($scriptNameDir));
        }
        
        $url = trim($url, '/');
        if ($url === '') {
            $url = 'home';
        }

        $parts = explode('/', $url);
        
        // Normalize controller name (supports both kebab-case and camelCase)
        $controllerClean = str_replace(' ', '', ucwords(str_replace('-', ' ', $parts[0])));
        $controllerName = $controllerClean . 'Controller';
        
        $actionRaw = isset($parts[1]) ? $parts[1] : 'index';
        $actionName = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $actionRaw))));

        // AUTHENTICATION GUARD: Protect all routes except 'auth' or 'login'
        $isAuthRoute = (strtolower($parts[0]) === 'auth' || strtolower($parts[0]) === 'login');
        if (!$isAuthRoute && !isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit();
        }

        // Redirect 'login' route directly to AuthController
        if (strtolower($parts[0]) === 'login') {
            $controllerClass = 'App\\Controllers\\AuthController';
            $controllerInstance = new $controllerClass();
            $controllerInstance->login();
            return;
        }

        $controllerClass = 'App\\Controllers\\' . $controllerName;

        if (class_exists($controllerClass)) {
            $controllerInstance = new $controllerClass();
            if (method_exists($controllerInstance, $actionName)) {
                $params = array_slice($parts, 2);
                call_user_func_array([$controllerInstance, $actionName], $params);
            } else {
                $this->sendNotFound("Action '" . $actionName . "' not found in controller '" . $controllerName . "'");
            }
        } else {
            $this->sendNotFound("Controller '" . $controllerName . "' not found");
        }
    }

    private function sendNotFound($message) {
        header("HTTP/1.0 404 Not Found");
        echo "<h1>404 Not Found</h1>";
        echo "<p>" . htmlspecialchars($message) . "</p>";
    }
}
