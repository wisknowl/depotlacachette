<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Database;

class AuthController extends Controller {
    
    public function index() {
        $this->login();
    }

    public function login() {
        if (isset($_SESSION['user'])) {
            $this->redirect('home');
        }
        
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        // Render standalone login page without main.php layout wrapper
        $viewFile = VIEWS . '/pages/auth/login.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            die("Login view missing");
        }
    }

    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $_SESSION['login_error'] = "Veuillez renseigner votre identifiant et votre mot de passe.";
                $this->redirect('auth/login');
            }

            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                if (isset($user['is_active']) && $user['is_active'] == 0) {
                    \App\Core\Helper::logAudit('AUTH_FAIL', 'Authentification', $user['id'], "Tentative de connexion refusée : compte utilisateur '{$username}' désactivé.");
                    $_SESSION['login_error'] = "Ce compte utilisateur est désactivé. Veuillez contacter l'administrateur.";
                    $this->redirect('auth/login');
                }

                // Start authenticated session
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'full_name' => $user['full_name'] ?: $user['username'],
                    'role' => $user['role']
                ];

                \App\Core\Helper::logAudit('LOGIN', 'Authentification', $user['id'], "Connexion réussie de l'utilisateur '{$user['username']}' ({$user['role']})");

                $this->redirect('home');
            } else {
                \App\Core\Helper::logAudit('AUTH_FAIL', 'Authentification', null, "Échec de connexion : Identifiant ou mot de passe incorrect pour '{$username}'.");
                $_SESSION['login_error'] = "Identifiant ou mot de passe incorrect.";
                $this->redirect('auth/login');
            }
        }
    }

    public function logout() {
        if (isset($_SESSION['user'])) {
            \App\Core\Helper::logAudit('LOGOUT', 'Authentification', $_SESSION['user']['id'], "Déconnexion de l'utilisateur '{$_SESSION['user']['username']}'");
        }
        unset($_SESSION['user']);
        session_destroy();
        session_start();
        $this->redirect('auth/login');
    }
}

