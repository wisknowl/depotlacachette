<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\UsersModel;

class UsersController extends Controller {
    private $model;
    public function __construct() { $this->model = new UsersModel(); }

    public function index() {
        $users = $this->model->getAll();
        $this->view('pages/users/index', [
            'title' => 'Gestion des Utilisateurs & Droits',
            'active_menu' => 'users',
            'users' => $users
        ]);
    }

    public function form($id = null) {
        $user = null;
        if ($id) {
            $user = $this->model->getById($id);
        }
        $this->view('pages/users/form', [
            'title' => $user ? 'Modifier l\'Utilisateur' : 'Créer un Nouvel Utilisateur',
            'active_menu' => 'users',
            'user' => $user
        ]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = !empty($_POST['id']) ? intval($_POST['id']) : null;
            $data = [
                'username' => trim($_POST['username']),
                'full_name' => trim($_POST['full_name']),
                'password' => $_POST['password'] ?? '',
                'role' => $_POST['role'],
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            if ($id) {
                $oldUser = $this->model->getById($id);
                unset($oldUser['password_hash']);
                $cleanData = $data;
                unset($cleanData['password']);

                $this->model->update($id, $data);

                $desc = "Mise à jour du compte utilisateur '{$data['username']}' (Rôle: {$data['role']}, Actif: {$data['is_active']})";
                \App\Core\Helper::logAudit('UPDATE', 'Utilisateurs', $id, $desc, $oldUser, $cleanData);

                if (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $id) {
                    $_SESSION['user']['full_name'] = $data['full_name'];
                    $_SESSION['user']['username'] = $data['username'];
                    $_SESSION['user']['role'] = $data['role'];
                }
            } else {
                $this->model->add($data);
                $cleanData = $data;
                unset($cleanData['password']);
                \App\Core\Helper::logAudit('CREATE', 'Utilisateurs', $data['username'], "Création du compte utilisateur '{$data['username']}' ({$data['role']})", null, $cleanData);
            }
            $this->redirect('users');
        }
    }

    public function delete($id = null) {
        if ($id) {
            $oldUser = $this->model->getById($id);
            if ($oldUser) {
                unset($oldUser['password_hash']);
            }
            $this->model->delete($id);
            \App\Core\Helper::logAudit('DELETE', 'Utilisateurs', $id, "Suppression de l'utilisateur '" . ($oldUser['username'] ?? $id) . "'", $oldUser, null);
        }
        $this->redirect('users');
    }


    public function profile() {
        $currentUserId = $_SESSION['user']['id'] ?? 1;
        $user = $this->model->getById($currentUserId);
        $message = $_SESSION['profile_message'] ?? null;
        unset($_SESSION['profile_message']);

        $this->view('pages/users/profile', [
            'title' => 'Mon Profil & Sécurité',
            'active_menu' => 'users',
            'user' => $user,
            'message' => $message
        ]);
    }

    public function saveProfile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentUserId = $_SESSION['user']['id'] ?? 1;
            $fullName = trim($_POST['full_name'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (!empty($password) && $password !== $confirmPassword) {
                $_SESSION['profile_message'] = "Les mots de passe ne correspondent pas.";
                $this->redirect('users/profile');
            }

            $data = [
                'full_name' => $fullName,
                'password' => $password
            ];

            $this->model->updateProfile($currentUserId, $data);
            $_SESSION['user']['full_name'] = $fullName;
            $_SESSION['profile_message'] = "Profil mis à jour avec succès !";
            $this->redirect('users/profile');
        }
    }
}
