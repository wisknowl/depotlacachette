<?php
namespace App\Models;
use App\Core\Database;

class UsersModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getAll() {
        return $this->db->query("SELECT * FROM users ORDER BY id ASC")->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function add($data) {
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("
            INSERT INTO users (username, full_name, password_hash, role, is_active)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['username'],
            $data['full_name'],
            $passwordHash,
            $data['role'],
            isset($data['is_active']) ? intval($data['is_active']) : 1
        ]);
    }

    public function update($id, $data) {
        if (!empty($data['password'])) {
            $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("
                UPDATE users 
                SET username = ?, full_name = ?, password_hash = ?, role = ?, is_active = ?
                WHERE id = ?
            ");
            return $stmt->execute([
                $data['username'],
                $data['full_name'],
                $passwordHash,
                $data['role'],
                intval($data['is_active']),
                $id
            ]);
        } else {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET username = ?, full_name = ?, role = ?, is_active = ?
                WHERE id = ?
            ");
            return $stmt->execute([
                $data['username'],
                $data['full_name'],
                $data['role'],
                intval($data['is_active']),
                $id
            ]);
        }
    }

    public function updateProfile($id, $data) {
        if (!empty($data['password'])) {
            $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("
                UPDATE users 
                SET full_name = ?, password_hash = ?
                WHERE id = ?
            ");
            return $stmt->execute([$data['full_name'], $passwordHash, $id]);
        } else {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET full_name = ?
                WHERE id = ?
            ");
            return $stmt->execute([$data['full_name'], $id]);
        }
    }

    public function delete($id) {
        // Prevent deleting admin id 1 or currently logged-in user
        if ($id == 1 || (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $id)) {
            return false;
        }
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
