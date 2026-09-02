<?php
namespace App\Models;
use App\Core\Database;

class ProduitsModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getAll() {
        return $this->db->query("
            SELECT p.*, c.name as category_name, f.name as format_name, pt.name as packaging_name, pt.company as packaging_company, pt.color as packaging_color
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN formats f ON p.format_id = f.id 
            LEFT JOIN packaging_types pt ON p.packaging_type_id = pt.id
            ORDER BY p.name ASC
        ")->fetchAll();
    }

    public function getFiltered($search = '', $categoryId = null, $formatId = null, $factor = null, $isReturnable = null, $packagingTypeId = null) {
        $sql = "
            SELECT p.*, c.name as category_name, f.name as format_name, pt.name as packaging_name, pt.company as packaging_company, pt.color as packaging_color
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN formats f ON p.format_id = f.id 
            LEFT JOIN packaging_types pt ON p.packaging_type_id = pt.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (p.name LIKE ? OR p.short_code LIKE ? OR c.name LIKE ? OR f.name LIKE ?)";
            $searchTerm = '%' . trim($search) . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($categoryId)) {
            $sql .= " AND p.category_id = ?";
            $params[] = intval($categoryId);
        }

        if (!empty($formatId)) {
            $sql .= " AND p.format_id = ?";
            $params[] = intval($formatId);
        }

        if (!empty($factor)) {
            $sql .= " AND p.factor = ?";
            $params[] = intval($factor);
        }

        if ($isReturnable !== null && $isReturnable !== '') {
            $sql .= " AND p.is_returnable = ?";
            $params[] = intval($isReturnable);
        }

        if (!empty($packagingTypeId)) {
            $sql .= " AND p.packaging_type_id = ?";
            $params[] = intval($packagingTypeId);
        }

        $sql .= " ORDER BY p.name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getCategories() { return $this->db->query("SELECT * FROM categories ORDER BY name")->fetchAll(); }
    public function getFormats() { return $this->db->query("SELECT * FROM formats ORDER BY name")->fetchAll(); }
    public function getPackagingTypes() { return $this->db->query("SELECT * FROM packaging_types ORDER BY company ASC, name ASC")->fetchAll(); }
    public function getDistinctFactors() { return $this->db->query("SELECT DISTINCT factor FROM products WHERE factor IS NOT NULL ORDER BY factor ASC")->fetchAll(\PDO::FETCH_COLUMN); }

    public function getOrCreateFormatByName($formatInput) {
        $formatInput = trim($formatInput);
        if (empty($formatInput)) return 1;
        if (is_numeric($formatInput)) {
            $stmt = $this->db->prepare("SELECT id FROM formats WHERE id = ?");
            $stmt->execute([intval($formatInput)]);
            $id = $stmt->fetchColumn();
            if ($id) return intval($id);
        }
        $stmt = $this->db->prepare("SELECT id FROM formats WHERE LOWER(name) = LOWER(?)");
        $stmt->execute([$formatInput]);
        $existingId = $stmt->fetchColumn();
        if ($existingId) {
            return intval($existingId);
        }
        $stmtIns = $this->db->prepare("INSERT INTO formats (name) VALUES (?)");
        $stmtIns->execute([$formatInput]);
        return intval($this->db->lastInsertId());
    }

    public function getOrCreateCategoryByName($catInput) {
        $catInput = trim($catInput);
        if (empty($catInput)) return 1;
        if (is_numeric($catInput)) {
            $stmt = $this->db->prepare("SELECT id FROM categories WHERE id = ?");
            $stmt->execute([intval($catInput)]);
            $id = $stmt->fetchColumn();
            if ($id) return intval($id);
        }
        $stmt = $this->db->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(?)");
        $stmt->execute([$catInput]);
        $existingId = $stmt->fetchColumn();
        if ($existingId) {
            return intval($existingId);
        }
        $stmtIns = $this->db->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmtIns->execute([$catInput]);
        return intval($this->db->lastInsertId());
    }

    public function add($data) {
        $stmt = $this->db->prepare("
            INSERT INTO products (name, short_code, slug, category_id, format_id, is_returnable, cout_emballage, packaging_type_id, purchase_price, price_casier, price_demi, price_unite, alert_stock, factor) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['name'], 
            !empty($data['short_code']) ? trim(strtoupper($data['short_code'])) : null,
            $data['slug'], 
            $data['category_id'], 
            $data['format_id'], 
            $data['is_returnable'] ?? 1,
            $data['cout_emballage'] ?? 3600.00,
            $data['packaging_type_id'] ?? null,
            $data['purchase_price'], 
            $data['price_casier'], 
            $data['price_demi'], 
            $data['price_unite'], 
            $data['alert_stock'], 
            $data['factor']
        ]);
    }

    public function getBySlug($slug) {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, f.name as format_name, pt.name as packaging_name, pt.company as packaging_company, pt.color as packaging_color 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN formats f ON p.format_id = f.id 
            LEFT JOIN packaging_types pt ON p.packaging_type_id = pt.id
            WHERE p.slug = ? OR p.id = ?
        ");
        $stmt->execute([$slug, $slug]);
        return $stmt->fetch();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE products 
            SET name=?, short_code=?, slug=?, category_id=?, format_id=?, is_returnable=?, cout_emballage=?, packaging_type_id=?, purchase_price=?, price_casier=?, price_demi=?, price_unite=?, alert_stock=?, factor=? 
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['name'], 
            !empty($data['short_code']) ? trim(strtoupper($data['short_code'])) : null,
            $data['slug'], 
            $data['category_id'], 
            $data['format_id'], 
            $data['is_returnable'] ?? 1,
            $data['cout_emballage'] ?? 3600.00,
            $data['packaging_type_id'] ?? null,
            $data['purchase_price'], 
            $data['price_casier'], 
            $data['price_demi'], 
            $data['price_unite'], 
            $data['alert_stock'], 
            $data['factor'], 
            $id
        ]);
    }

    public function delete($identifier) {
        $stmt = $this->db->prepare("DELETE FROM products WHERE slug = ? OR id = ?");
        return $stmt->execute([$identifier, $identifier]);
    }
}
