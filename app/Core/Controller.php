<?php
namespace App\Core;

class Controller {
    public function view($view, $data = []) {
        extract($data);
        $contentView = VIEWS . '/' . $view . '.php';
        if (file_exists($contentView)) {
            require VIEWS . '/layout/main.php';
        } else {
            die("View does not exist: " . $view);
        }
    }
    
    public function redirect($url) {
        header("Location: " . BASE_URL . '/' . ltrim($url, '/'));
        exit();
    }
}