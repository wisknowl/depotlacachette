<?php
namespace App\Core;

class Helper {
    public static function slugify($text) {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }

    public static function getPackagingUnit($categoryName = '', $formatName = '') {
        $cat = mb_strtolower($categoryName ?? '');
        $fmt = mb_strtolower($formatName ?? '');

        if (strpos($cat, 'eau') !== false || strpos($fmt, 'pack') !== false || strpos($fmt, 'fardeau') !== false || strpos($fmt, '1.5l') !== false) {
            return [
                'full' => 'Pack / Fardeau Entier',
                'demi' => 'Demi-Pack (0.5)',
                'full_short' => 'Pack',
                'demi_short' => 'Demi-Pack',
                'unit_name' => 'Pack(s)'
            ];
        } elseif (strpos($fmt, 'canette') !== false || strpos($fmt, 'carton') !== false || strpos($cat, 'vin') !== false || strpos($cat, 'spirit') !== false) {
            return [
                'full' => 'Carton Entier',
                'demi' => 'Demi-Carton (0.5)',
                'full_short' => 'Carton',
                'demi_short' => 'Demi-Carton',
                'unit_name' => 'Carton(s)'
            ];
        } else {
            return [
                'full' => 'Casier Entier',
                'demi' => 'Demi-Casier (0.5)',
                'full_short' => 'Casier',
                'demi_short' => 'Demi-Casier',
                'unit_name' => 'Casier(s)'
            ];
        }
    }

    public static function formatPackagingLabel($categoryName, $formatName, $formatType = 'casier') {
        if ($formatType === 'unite' || $formatType === 'bouteille') {
            return 'Bouteille(s)';
        }
        $units = self::getPackagingUnit($categoryName, $formatName);
        return ($formatType === 'demi') ? $units['demi_short'] : $units['full_short'];
    }

    public static function isAdmin() {
        if (!isset($_SESSION['user'])) {
            return false;
        }
        $role = strtolower(trim($_SESSION['user']['role'] ?? ''));
        return ($role === 'admin');
    }

    public static function logAudit($action, $module, $recordId, $description, $old = null, $new = null, $reason = null) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO system_audit_logs 
                (user_id, user_name, user_role, action, module, record_id, description, old_values, new_values, reason, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $user = $_SESSION['user'] ?? ['id' => null, 'username' => 'Système', 'role' => 'System'];
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'CLI / Background Process', 0, 255);

            $stmt->execute([
                $user['id'] ?? null,
                $user['username'] ?? 'Système',
                $user['role'] ?? 'System',
                $action,
                $module,
                $recordId ? strval($recordId) : null,
                $description,
                $old !== null ? json_encode($old, JSON_UNESCAPED_UNICODE) : null,
                $new !== null ? json_encode($new, JSON_UNESCAPED_UNICODE) : null,
                $reason,
                $ip,
                $userAgent
            ]);
        } catch (\Throwable $e) {
            error_log("Audit log failed: " . $e->getMessage());
        }
    }
}

