<?php
namespace App\Models;
use App\Core\Database;

class FournisseursModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getAll() {
        $sql = "
            SELECT 
                s.*,
                COALESCE((SELECT SUM(a.total_amount) FROM purchases a WHERE a.supplier_id = s.id AND a.payment_method_id = 5 AND a.status = 'Valid'), 0) as total_credit_purchases,
                COALESCE((SELECT SUM(p.amount) FROM supplier_payments p WHERE p.supplier_id = s.id), 0) as total_paid,
                (COALESCE((SELECT SUM(a.total_amount) FROM purchases a WHERE a.supplier_id = s.id AND a.payment_method_id = 5 AND a.status = 'Valid'), 0) - 
                 COALESCE((SELECT SUM(p.amount) FROM supplier_payments p WHERE p.supplier_id = s.id), 0)) as solde_du,
                COALESCE((SELECT SUM(sed.crates_due) FROM supplier_emballage_debts sed WHERE sed.supplier_id = s.id), 0) as total_crates_due,
                COALESCE((SELECT SUM(sed.loose_bottles_due) FROM supplier_emballage_debts sed WHERE sed.supplier_id = s.id), 0) as total_bottles_due
            FROM suppliers s 
            ORDER BY solde_du DESC, s.name ASC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function add($data) {
        $stmt = $this->db->prepare("INSERT INTO suppliers (name, slug, address, phone, contact_name) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$data['name'], $data['slug'], $data['address'], $data['phone'], $data['contact_name']]);
    }
    public function getBySlug($slug) {
        $stmt = $this->db->prepare("
            SELECT 
                s.*,
                (COALESCE((SELECT SUM(a.total_amount) FROM purchases a WHERE a.supplier_id = s.id AND a.payment_method_id = 5 AND a.status = 'Valid'), 0) - 
                 COALESCE((SELECT SUM(p.amount) FROM supplier_payments p WHERE p.supplier_id = s.id), 0)) as solde_du
            FROM suppliers s 
            WHERE s.slug = ? OR s.id = ?
        ");
        $stmt->execute([$slug, $slug]);
        return $stmt->fetch();
    }
    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE suppliers SET name = ?, slug=?, address = ?, phone = ?, contact_name = ? WHERE id = ?");
        return $stmt->execute([$data['name'], $data['slug'], $data['address'], $data['phone'], $data['contact_name'], $id]);
    }
    public function delete($identifier) {
        $stmt = $this->db->prepare("DELETE FROM suppliers WHERE slug = ? OR id = ?");
        return $stmt->execute([$identifier, $identifier]);
    }
}
