<?php
namespace App\Models;
use App\Core\Database;

class ClientsModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getAll() {
        return $this->getFiltered([]);
    }

    public function getFiltered($filters = []) {
        $sql = "
            SELECT * FROM (
                SELECT 
                    c.*, 
                    ct.name as type_name,
                    COALESCE((SELECT SUM(CASE WHEN s.amount_due > 0 THEN s.amount_due WHEN s.payment_method_id = 5 THEN s.total_amount ELSE 0 END) FROM sales s WHERE s.client_id = c.id AND s.status = 'Valid'), 0) as total_credit_sales,
                    COALESCE((SELECT SUM(p.amount) FROM client_payments p WHERE p.client_id = c.id), 0) as total_paid,
                    (COALESCE((SELECT SUM(CASE WHEN s.amount_due > 0 THEN s.amount_due WHEN s.payment_method_id = 5 THEN s.total_amount ELSE 0 END) FROM sales s WHERE s.client_id = c.id AND s.status = 'Valid'), 0) - 
                     COALESCE((SELECT SUM(p.amount) FROM client_payments p WHERE p.client_id = c.id), 0)) as solde_du,
                    COALESCE((SELECT SUM(ced.crates_due) FROM client_emballage_debts ced WHERE ced.client_id = c.id), 0) as total_crates_due,
                    COALESCE((SELECT SUM(ced.loose_bottles_due) FROM client_emballage_debts ced WHERE ced.client_id = c.id), 0) as total_bottles_due,
                    (
                        SELECT GROUP_CONCAT(
                            CONCAT(
                                IF(ced.crates_due > 0, CONCAT(ced.crates_due, ' c. '), ''),
                                IF(ced.crates_due > 0 AND ced.loose_bottles_due > 0, '+ ', ''),
                                IF(ced.loose_bottles_due > 0, CONCAT(ced.loose_bottles_due, ' btl. '), ''),
                                '[', pt.company, ' ', pt.bottles_per_crate, ']'
                            )
                            SEPARATOR ' '
                        )
                        FROM client_emballage_debts ced
                        JOIN packaging_types pt ON ced.packaging_type_id = pt.id
                        WHERE ced.client_id = c.id AND (ced.crates_due > 0 OR ced.loose_bottles_due > 0)
                    ) as packaging_debts_detail
                FROM clients c 
                LEFT JOIN client_types ct ON c.client_type_id = ct.id 
                WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (c.name LIKE ? OR c.phone LIKE ? OR c.address LIKE ?)";
            $term = '%' . trim($filters['search']) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['client_type_id'])) {
            $sql .= " AND c.client_type_id = ?";
            $params[] = intval($filters['client_type_id']);
        }

        $sql .= " ) as client_data WHERE 1=1";

        if (!empty($filters['debt_status'])) {
            if ($filters['debt_status'] === 'debt') {
                $sql .= " AND (solde_du > 0 OR total_crates_due > 0 OR total_bottles_due > 0)";
            } elseif ($filters['debt_status'] === 'no_debt') {
                $sql .= " AND (solde_du <= 0 AND total_crates_due = 0 AND total_bottles_due = 0)";
            } elseif ($filters['debt_status'] === 'limit_exceeded') {
                $sql .= " AND (max_credit > 0 AND solde_du >= max_credit)";
            }
        } elseif (!empty($filters['has_debt'])) {
            $sql .= " AND (solde_du > 0 OR total_crates_due > 0 OR total_bottles_due > 0)";
        }

        $sort = $filters['sort'] ?? 'debt_desc';
        if ($sort === 'name_asc') {
            $sql .= " ORDER BY name ASC";
        } elseif ($sort === 'limit_desc') {
            $sql .= " ORDER BY max_credit DESC, solde_du DESC";
        } else {
            $sql .= " ORDER BY solde_du DESC, name ASC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getTypes() { return $this->db->query("SELECT * FROM client_types ORDER BY name ASC")->fetchAll(); }
    public function add($data) {
        $stmt = $this->db->prepare("INSERT INTO clients (name, slug, address, phone, client_type_id, max_credit) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$data['name'], $data['slug'], $data['address'], $data['phone'], $data['client_type_id'], $data['max_credit']]);
    }
    public function getBySlug($slug) {
        $stmt = $this->db->prepare("
            SELECT 
                c.*, 
                ct.name as type_name,
                (COALESCE((SELECT SUM(CASE WHEN s.amount_due > 0 THEN s.amount_due WHEN s.payment_method_id = 5 THEN s.total_amount ELSE 0 END) FROM sales s WHERE s.client_id = c.id AND s.status = 'Valid'), 0) - 
                 COALESCE((SELECT SUM(p.amount) FROM client_payments p WHERE p.client_id = c.id), 0)) as solde_du
            FROM clients c 
            LEFT JOIN client_types ct ON c.client_type_id = ct.id 
            WHERE c.slug = ? OR c.id = ?
        ");
        $stmt->execute([$slug, $slug]);
        return $stmt->fetch();
    }
    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE clients SET name = ?, slug=?, address = ?, phone = ?, client_type_id = ?, max_credit = ? WHERE id = ?");
        return $stmt->execute([$data['name'], $data['slug'], $data['address'], $data['phone'], $data['client_type_id'], $data['max_credit'], $id]);
    }
    public function getById($id) {
        return $this->getBySlug($id);
    }

    public function ensureUniqueSlug($slug, $currentId = null) {
        $baseSlug = $slug;
        $counter = 1;
        while (true) {
            if ($currentId) {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM clients WHERE slug = ? AND id != ?");
                $stmt->execute([$slug, $currentId]);
            } else {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM clients WHERE slug = ?");
                $stmt->execute([$slug]);
            }
            if ($stmt->fetchColumn() == 0) {
                break;
            }
            $slug = $baseSlug . '-' . (++$counter);
        }
        return $slug;
    }

    public function delete($identifier) {
        $stmt = $this->db->prepare("DELETE FROM clients WHERE slug = ? OR id = ?");
        return $stmt->execute([$identifier, $identifier]);
    }
}
