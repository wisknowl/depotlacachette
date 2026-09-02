<?php
namespace App\Models;
use App\Core\Database;

class StockModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getStockStatus() {
        return $this->getFilteredStockStatus([]);
    }

    public function getFilteredStockStatus($filters = []) {
        $sql = "
            SELECT * FROM (
                SELECT 
                    p.id,
                    p.name,
                    p.short_code,
                    p.slug,
                    p.category_id,
                    p.format_id,
                    p.purchase_price,
                    p.price_casier,
                    p.price_demi,
                    p.price_unite,
                    p.alert_stock,
                    p.factor,
                    c.name as category_name,
                    f.name as format_name,
                    COALESCE(SUM(CASE 
                        WHEN mt.direction = 'IN' THEN m.stock_equivalent 
                        WHEN mt.direction = 'OUT' THEN -m.stock_equivalent 
                        ELSE 0 
                    END), 0) as current_stock
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN formats f ON p.format_id = f.id
                LEFT JOIN stock_movements m ON p.id = m.product_id
                LEFT JOIN movement_types mt ON m.movement_type_id = mt.id
                GROUP BY p.id, p.name, p.short_code, p.slug, p.category_id, p.format_id, p.purchase_price, p.price_casier, p.price_demi, p.price_unite, p.alert_stock, p.factor, c.name, f.name
            ) stock_sub
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (name LIKE ? OR category_name LIKE ? OR format_name LIKE ?)";
            $term = '%' . trim($filters['search']) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND category_id = ?";
            $params[] = intval($filters['category_id']);
        }

        if (!empty($filters['format_id'])) {
            $sql .= " AND format_id = ?";
            $params[] = intval($filters['format_id']);
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'instock') {
                $sql .= " AND current_stock > alert_stock";
            } elseif ($filters['status'] === 'alert') {
                $sql .= " AND current_stock > 0 AND current_stock <= alert_stock";
            } elseif ($filters['status'] === 'rupture') {
                $sql .= " AND current_stock <= 0";
            } elseif ($filters['status'] === 'positive') {
                $sql .= " AND current_stock > 0";
            }
        }

        $sql .= " ORDER BY name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getCategories() {
        return $this->db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
    }

    public function getFormats() {
        return $this->db->query("SELECT * FROM formats ORDER BY name ASC")->fetchAll();
    }

    public function getMovements($limit = 100, $filters = []) {
        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(p.name LIKE ? OR m.reference LIKE ? OR m.source_id LIKE ? OR c.name LIKE ?)";
            $term = '%' . trim($filters['search']) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }
        if (!empty($filters['product_id'])) {
            $where[] = "m.product_id = ?";
            $params[] = intval($filters['product_id']);
        }
        if (!empty($filters['movement_type_id'])) {
            $where[] = "m.movement_type_id = ?";
            $params[] = intval($filters['movement_type_id']);
        }
        if (!empty($filters['start_date'])) {
            $where[] = "m.movement_date >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where[] = "m.movement_date <= ?";
            $params[] = $filters['end_date'];
        }

        $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        $sql = "
            SELECT 
                m.*,
                p.name as product_name,
                p.short_code,
                c.name as category_name,
                f.name as format_name,
                t.name as movement_type,
                t.direction,
                u.username,
                COALESCE((
                    SELECT COUNT(*) 
                    FROM stock_movements c 
                    WHERE c.source_type = 'AdjustmentCancellation' AND c.source_id = CAST(m.id AS CHAR)
                ), 0) as is_cancelled
            FROM stock_movements m
            LEFT JOIN products p ON m.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN formats f ON m.format_id = f.id
            LEFT JOIN movement_types t ON m.movement_type_id = t.id
            LEFT JOIN users u ON m.user_id = u.id
            {$whereSql}
            ORDER BY m.movement_date DESC, m.id DESC
            LIMIT " . intval($limit);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function cancelMovement($id, $userId = 1, $reason = '') {
        $stmt = $this->db->prepare("
            SELECT m.*, t.direction 
            FROM stock_movements m
            JOIN movement_types t ON m.movement_type_id = t.id
            WHERE m.id = ?
        ");
        $stmt->execute([$id]);
        $orig = $stmt->fetch();

        if (!$orig) {
            throw new \Exception("Mouvement de stock #{$id} introuvable.");
        }

        if ($orig['source_type'] === 'Sale') {
            throw new \Exception("Pour annuler un mouvement issu d'une vente, veuillez annuler directement la facture de vente dans le module Ventes.");
        }
        if ($orig['source_type'] === 'Purchase') {
            throw new \Exception("Pour annuler un mouvement issu d'un achat, veuillez annuler directement le bon d'achat dans le module Achats.");
        }
        if ($orig['source_type'] === 'SaleCancellation' || $orig['source_type'] === 'PurchaseCancellation' || $orig['source_type'] === 'AdjustmentCancellation') {
            throw new \Exception("Une contre-passation d'annulation ne peut pas être annulée.");
        }

        // Check if already cancelled
        $checkStmt = $this->db->prepare("
            SELECT COUNT(*) FROM stock_movements 
            WHERE source_type = 'AdjustmentCancellation' AND source_id = ?
        ");
        $checkStmt->execute([strval($id)]);
        if ($checkStmt->fetchColumn() > 0) {
            throw new \Exception("Ce mouvement de stock a déjà été annulé précédemment.");
        }

        $this->db->beginTransaction();
        try {
            // Determine reversing movement type
            // If original was IN (5) -> reverse with OUT (6: Ajustement Négatif)
            // If original was OUT (3, 4, 6) -> reverse with IN (5: Ajustement Positif)
            $isIncoming = ($orig['direction'] === 'IN');
            $reversalTypeId = $isIncoming ? 6 : 5;
            $reversalSign = $isIncoming ? '(-)' : '(+)';
            $actionLabel = $isIncoming ? 'Déstockage d\'annulation' : 'Restitution de stock';

            $detailRef = "[ANNULATION Mvt #{$id}] " . ($reason ? $reason . ' - ' : '') . ($orig['reference'] ?? '');

            $stmtReversal = $this->db->prepare("
                INSERT INTO stock_movements (
                    movement_date, product_id, format_id, format_type, 
                    movement_type_id, source_type, source_id, 
                    quantity, stock_equivalent, unit_price, reference, user_id
                ) VALUES (CURRENT_DATE, ?, ?, ?, ?, 'AdjustmentCancellation', ?, ?, ?, ?, ?, ?)
            ");

            $stmtReversal->execute([
                $orig['product_id'],
                $orig['format_id'],
                $orig['format_type'],
                $reversalTypeId,
                strval($id),
                $orig['quantity'],
                $orig['stock_equivalent'],
                $orig['unit_price'],
                $detailRef,
                $userId
            ]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getProducts() {
        return $this->db->query("
            SELECT 
                p.*, 
                f.name as format_name, 
                c.name as category_name,
                COALESCE((
                    SELECT SUM(CASE WHEN mt.direction = 'IN' THEN sm.stock_equivalent WHEN mt.direction = 'OUT' THEN -sm.stock_equivalent ELSE 0 END)
                    FROM stock_movements sm
                    JOIN movement_types mt ON sm.movement_type_id = mt.id
                    WHERE sm.product_id = p.id
                ), 0) as current_stock
            FROM products p 
            LEFT JOIN formats f ON p.format_id = f.id 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.name ASC
        ")->fetchAll();
    }

    public function getMovementTypes() {
        return $this->db->query("SELECT * FROM movement_types WHERE id IN (3, 4, 5, 6) ORDER BY id ASC")->fetchAll();
    }

    public function getAllMovementTypes() {
        return $this->db->query("SELECT * FROM movement_types ORDER BY id ASC")->fetchAll();
    }

    public function addAdjustment($data) {
        $productStmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
        $productStmt->execute([$data['product_id']]);
        $product = $productStmt->fetch();
        if (!$product) throw new \Exception("Produit introuvable.");

        $qty = floatval($data['quantity']);
        if ($qty <= 0) {
            throw new \Exception("La quantité saisie doit être supérieure à zéro.");
        }

        $inputMode = $data['input_mode'] ?? 'casier';
        $factor = max(1, intval($product['factor'] ?: 24));
        $userId = $data['user_id'] ?? 1;

        // Check direction of movement type
        $movTypeStmt = $this->db->prepare("SELECT * FROM movement_types WHERE id = ?");
        $movTypeStmt->execute([$data['movement_type_id']]);
        $movType = $movTypeStmt->fetch();
        if (!$movType) throw new \Exception("Type de mouvement invalide.");
        $isIncoming = ($movType['direction'] === 'IN');

        if ($inputMode === 'bouteille') {
            $formatType = 'demi'; // Use demi/unit equivalent in DB
            $stockEquivalent = round($qty / $factor, 4);
            $unitPrice = round(floatval($product['purchase_price']) / $factor, 2);
            $financialVal = round($qty * $unitPrice, 0);
            
            $dir = $isIncoming ? '+' : '-';
            $actionLabel = $isIncoming ? 'Ajout' : 'Casse/Perte';

            $detailRef = "{$actionLabel} ({$dir}): {$qty} btl(s) (Valeur: " . number_format($financialVal, 0, ',', ' ') . " FCFA) - " . ($data['reference'] ?? '');
        } else {
            $formatType = in_array($data['format_type'] ?? '', ['casier', 'demi']) ? $data['format_type'] : 'casier';
            $stockEquivalent = ($formatType === 'demi') ? round($qty * 0.5, 4) : $qty;
            $unitPrice = floatval($product['purchase_price']);
            $detailRef = $data['reference'] ?? 'ADJ-' . date('YmdHis');
        }

        // Strict OUT validation: cannot deduce more than available stock
        if (!$isIncoming) {
            $stkStmt = $this->db->prepare("
                SELECT COALESCE(SUM(CASE WHEN mt.direction = 'IN' THEN sm.stock_equivalent WHEN mt.direction = 'OUT' THEN -sm.stock_equivalent ELSE 0 END), 0) as current_stock
                FROM stock_movements sm
                JOIN movement_types mt ON sm.movement_type_id = mt.id
                WHERE sm.product_id = ?
            ");
            $stkStmt->execute([$product['id']]);
            $currentStock = floatval($stkStmt->fetchColumn() ?? 0);

            if ($stockEquivalent > $currentStock) {
                $fmtCurrent = number_format($currentStock, ($currentStock == intval($currentStock) ? 0 : 2), ',', ' ');
                $fmtReq = number_format($stockEquivalent, ($stockEquivalent == intval($stockEquivalent) ? 0 : 2), ',', ' ');
                throw new \Exception("Déstockage impossible : vous tentez de déduire {$fmtReq} casier(s) alors que le stock disponible en magasin pour " . htmlspecialchars($product['name']) . " est de {$fmtCurrent} casier(s).");
            }
        }

        $stmt = $this->db->prepare("
            INSERT INTO stock_movements (movement_date, product_id, format_id, format_type, movement_type_id, source_type, source_id, quantity, stock_equivalent, unit_price, reference, user_id)
            VALUES (?, ?, ?, ?, ?, 'Adjustment', ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['movement_date'],
            $product['id'],
            $product['format_id'],
            $formatType,
            $data['movement_type_id'],
            $detailRef,
            $qty,
            $stockEquivalent,
            $unitPrice,
            $detailRef,
            $userId
        ]);
    }
}
