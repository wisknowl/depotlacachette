<?php
namespace App\Models;
use App\Core\Database;
use PDO;

class EmballagesModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getPackagingTypes() {
        return $this->db->query("
            SELECT 
                pt.*,
                COALESCE(es.empty_crates, 0) as empty_crates,
                COALESCE(es.loose_bottles, 0) as loose_bottles,
                COALESCE((
                    SELECT SUM((
                        SELECT COALESCE(SUM(CASE WHEN mt.direction = 'IN' THEN sm.stock_equivalent WHEN mt.direction = 'OUT' THEN -sm.stock_equivalent ELSE 0 END), 0)
                        FROM stock_movements sm
                        JOIN movement_types mt ON sm.movement_type_id = mt.id
                        WHERE sm.product_id = p.id
                    ))
                    FROM products p
                    WHERE p.packaging_type_id = pt.id AND p.is_returnable = 1
                ), 0) as full_crates_stock,
                COALESCE((
                    SELECT SUM(ced.crates_due) FROM client_emballage_debts ced WHERE ced.packaging_type_id = pt.id
                ), 0) as client_crates_due,
                COALESCE((
                    SELECT SUM(ced.loose_bottles_due) FROM client_emballage_debts ced WHERE ced.packaging_type_id = pt.id
                ), 0) as client_bottles_due,
                COALESCE((
                    SELECT SUM(sed.crates_due) FROM supplier_emballage_debts sed WHERE sed.packaging_type_id = pt.id
                ), 0) as supplier_crates_due
            FROM packaging_types pt
            LEFT JOIN emballage_stock es ON pt.id = es.packaging_type_id
            ORDER BY pt.company ASC, pt.name ASC
        ")->fetchAll();
    }

    public function getGlobalSummary() {
        $types = $this->getPackagingTypes();
        $totalFullCrates = 0;
        $totalEmptyCrates = 0;
        $totalLooseBottles = 0;
        $totalClientCrates = 0;
        $totalClientBottles = 0;
        $totalSupplierCrates = 0;
        $totalAssetValue = 0;

        foreach ($types as $t) {
            $totalFullCrates += floatval($t['full_crates_stock']);
            $totalEmptyCrates += intval($t['empty_crates']);
            $totalLooseBottles += intval($t['loose_bottles']);
            $totalClientCrates += intval($t['client_crates_due']);
            $totalClientBottles += intval($t['client_bottles_due']);
            $totalSupplierCrates += intval($t['supplier_crates_due']);

            $cratesOwned = floatval($t['full_crates_stock']) + floatval($t['empty_crates']) + floatval($t['client_crates_due']) - floatval($t['supplier_crates_due']);
            $totalAssetValue += max(0, $cratesOwned) * floatval($t['default_cost']);
        }

        return [
            'total_full_crates' => $totalFullCrates,
            'total_empty_crates' => $totalEmptyCrates,
            'total_loose_bottles' => $totalLooseBottles,
            'total_client_crates' => $totalClientCrates,
            'total_client_bottles' => $totalClientBottles,
            'total_supplier_crates' => $totalSupplierCrates,
            'total_asset_value' => $totalAssetValue,
            'packaging_types' => $types
        ];
    }

    public function getClientDebts($clientId = null) {
        $sql = "
            SELECT 
                ced.*,
                c.name as client_name,
                c.phone as client_phone,
                pt.name as packaging_name,
                pt.company as packaging_company,
                pt.color as packaging_color,
                pt.bottles_per_crate,
                pt.default_cost,
                (
                    SELECT GROUP_CONCAT(DISTINCT COALESCE(p.short_code, p.name) ORDER BY p.name ASC SEPARATOR ', ')
                    FROM sale_items si
                    JOIN sales s ON si.sale_id = s.id
                    JOIN products p ON si.product_id = p.id
                    WHERE s.client_id = c.id 
                      AND s.status = 'Valid'
                      AND p.packaging_type_id = pt.id
                ) as product_sigles
            FROM client_emballage_debts ced
            JOIN clients c ON ced.client_id = c.id
            JOIN packaging_types pt ON ced.packaging_type_id = pt.id
            WHERE (ced.crates_due > 0 OR ced.loose_bottles_due > 0)
        ";
        $params = [];
        if ($clientId) {
            $sql .= " AND ced.client_id = ?";
            $params[] = intval($clientId);
        }
        $sql .= " ORDER BY c.name ASC, pt.company ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getSupplierDebts($supplierId = null) {
        $sql = "
            SELECT 
                sed.*,
                s.name as supplier_name,
                s.phone as supplier_phone,
                pt.name as packaging_name,
                pt.company as packaging_company,
                pt.color as packaging_color,
                pt.bottles_per_crate,
                pt.default_cost
            FROM supplier_emballage_debts sed
            JOIN suppliers s ON sed.supplier_id = s.id
            JOIN packaging_types pt ON sed.packaging_type_id = pt.id
            WHERE (sed.crates_due != 0 OR sed.loose_bottles_due != 0)
        ";
        $params = [];
        if ($supplierId) {
            $sql .= " AND sed.supplier_id = ?";
            $params[] = intval($supplierId);
        }
        $sql .= " ORDER BY s.name ASC, pt.company ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getMovements($filters = []) {
        $sql = "
            SELECT 
                em.*,
                pt.name as packaging_name,
                pt.company as packaging_company,
                pt.color as packaging_color,
                c.name as client_name,
                s.name as supplier_name,
                u.username
            FROM emballage_movements em
            JOIN packaging_types pt ON em.packaging_type_id = pt.id
            LEFT JOIN clients c ON em.client_id = c.id
            LEFT JOIN suppliers s ON em.supplier_id = s.id
            LEFT JOIN users u ON em.created_by = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['packaging_type_id'])) {
            $sql .= " AND em.packaging_type_id = ?";
            $params[] = intval($filters['packaging_type_id']);
        }
        if (!empty($filters['movement_type'])) {
            $sql .= " AND em.movement_type = ?";
            $params[] = $filters['movement_type'];
        }
        if (!empty($filters['client_id'])) {
            $sql .= " AND em.client_id = ?";
            $params[] = intval($filters['client_id']);
        }
        if (!empty($filters['supplier_id'])) {
            $sql .= " AND em.supplier_id = ?";
            $params[] = intval($filters['supplier_id']);
        }
        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(em.created_at) >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(em.created_at) <= ?";
            $params[] = $filters['end_date'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (pt.name LIKE ? OR c.name LIKE ? OR s.name LIKE ? OR em.notes LIKE ?)";
            $term = '%' . trim($filters['search']) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY em.id DESC LIMIT 100";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function recordPurchaseEmpty($data, $userId = 1) {
        $this->db->beginTransaction();
        try {
            $pkgTypeId = intval($data['packaging_type_id']);
            $supplierId = intval($data['supplier_id'] ?? 0);
            $cratesQty = intval($data['crates_quantity']);
            $unitCost = floatval($data['unit_cost']);
            $totalCost = $cratesQty * $unitCost;
            $notes = trim($data['notes'] ?? '');
            $paymentMethodId = intval($data['payment_method_id'] ?? 1);
            $cashAccountId = intval($data['cash_account_id'] ?? 1);

            if ($cratesQty <= 0) {
                throw new \Exception("La quantité de casiers achetés doit être supérieure à zéro.");
            }

            // 1. Log movement
            $stmtMov = $this->db->prepare("
                INSERT INTO emballage_movements (packaging_type_id, movement_type, supplier_id, crates_in, unit_cost, total_cost, notes, created_by)
                VALUES (?, 'Purchase_Empty', ?, ?, ?, ?, ?, ?)
            ");
            $stmtMov->execute([$pkgTypeId, $supplierId, $cratesQty, $unitCost, $totalCost, $notes, $userId]);
            $movementId = $this->db->lastInsertId();

            // 2. Update emballage_stock
            $this->db->prepare("
                INSERT INTO emballage_stock (packaging_type_id, empty_crates, loose_bottles)
                VALUES (?, ?, 0)
                ON DUPLICATE KEY UPDATE empty_crates = empty_crates + VALUES(empty_crates)
            ")->execute([$pkgTypeId, $cratesQty]);

            // 3. Financial Settlement
            if ($paymentMethodId != 5 && $totalCost > 0) {
                // Deduct from cash account
                $stmtCash = $this->db->prepare("
                    INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id)
                    VALUES (NOW(), 'Expense', ?, ?, ?, 0.00, ?, ?)
                ");
                $cashDesc = "Achat de " . $cratesQty . " casiers vides - " . $notes;
                $stmtCash->execute([$movementId, $cashDesc, $cashAccountId, $totalCost, $userId]);
            }

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function recordClientReturn($clientId, $packagingTypeId, $cratesReturned, $bottlesReturned, $notes = '', $userId = 1) {
        $this->db->beginTransaction();
        try {
            $clientId = intval($clientId);
            $packagingTypeId = intval($packagingTypeId);
            $cratesReturned = max(0, intval($cratesReturned));
            $bottlesReturned = max(0, intval($bottlesReturned));

            if ($cratesReturned == 0 && $bottlesReturned == 0) {
                $this->db->rollBack();
                return false;
            }

            // Get packaging factor
            $stmtPkg = $this->db->prepare("SELECT bottles_per_crate FROM packaging_types WHERE id = ?");
            $stmtPkg->execute([$packagingTypeId]);
            $pkg = $stmtPkg->fetch();
            $factor = max(1, intval($pkg['bottles_per_crate'] ?? 12));

            // 1. Log movement
            $stmtMov = $this->db->prepare("
                INSERT INTO emballage_movements (packaging_type_id, movement_type, client_id, crates_in, bottles_in, notes, created_by)
                VALUES (?, 'Client_Return', ?, ?, ?, ?, ?)
            ");
            $stmtMov->execute([$packagingTypeId, $clientId, $cratesReturned, $bottlesReturned, $notes ?: 'Restitution casiers client', $userId]);

            // 2. Increment warehouse empty stock
            $this->db->prepare("
                INSERT INTO emballage_stock (packaging_type_id, empty_crates, loose_bottles)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE empty_crates = empty_crates + VALUES(empty_crates), loose_bottles = loose_bottles + VALUES(loose_bottles)
            ")->execute([$packagingTypeId, $cratesReturned, $bottlesReturned]);

            // 3. Decrement client debt modularly
            $stmtDebt = $this->db->prepare("SELECT crates_due, loose_bottles_due FROM client_emballage_debts WHERE client_id = ? AND packaging_type_id = ?");
            $stmtDebt->execute([$clientId, $packagingTypeId]);
            $curDebt = $stmtDebt->fetch();

            if ($curDebt) {
                $curCrates = intval($curDebt['crates_due']);
                $curLoose = intval($curDebt['loose_bottles_due']);
                $curTotalBtls = ($curCrates * $factor) + $curLoose;

                $returnedTotalBtls = ($cratesReturned * $factor) + $bottlesReturned;
                $remainingTotalBtls = max(0, $curTotalBtls - $returnedTotalBtls);

                $newCrates = floor($remainingTotalBtls / $factor);
                $newLoose = $remainingTotalBtls % $factor;

                $this->db->prepare("
                    UPDATE client_emballage_debts 
                    SET crates_due = ?, loose_bottles_due = ? 
                    WHERE client_id = ? AND packaging_type_id = ?
                ")->execute([$newCrates, $newLoose, $clientId, $packagingTypeId]);
            }

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function recordSupplierReturn($supplierId, $packagingTypeId, $cratesReturned, $bottlesReturned, $notes = '', $userId = 1) {
        $this->db->beginTransaction();
        try {
            $supplierId = intval($supplierId);
            $packagingTypeId = intval($packagingTypeId);
            $cratesReturned = max(0, intval($cratesReturned));
            $bottlesReturned = max(0, intval($bottlesReturned));

            if ($cratesReturned == 0 && $bottlesReturned == 0) {
                $this->db->rollBack();
                return false;
            }

            // Get packaging factor
            $stmtPkg = $this->db->prepare("SELECT bottles_per_crate FROM packaging_types WHERE id = ?");
            $stmtPkg->execute([$packagingTypeId]);
            $pkg = $stmtPkg->fetch();
            $factor = max(1, intval($pkg['bottles_per_crate'] ?? 12));

            // 1. Log movement
            $stmtMov = $this->db->prepare("
                INSERT INTO emballage_movements (packaging_type_id, movement_type, supplier_id, crates_out, bottles_out, notes, created_by)
                VALUES (?, 'Supplier_Return', ?, ?, ?, ?, ?)
            ");
            $stmtMov->execute([$packagingTypeId, $supplierId, $cratesReturned, $bottlesReturned, $notes ?: 'Restitution casiers fournisseur', $userId]);

            // 2. Decrement warehouse empty stock
            $this->db->prepare("
                UPDATE emballage_stock 
                SET empty_crates = GREATEST(0, empty_crates - ?), loose_bottles = GREATEST(0, loose_bottles - ?)
                WHERE packaging_type_id = ?
            ")->execute([$cratesReturned, $bottlesReturned, $packagingTypeId]);

            // 3. Decrement supplier debt modularly
            $stmtDebt = $this->db->prepare("SELECT crates_due, loose_bottles_due FROM supplier_emballage_debts WHERE supplier_id = ? AND packaging_type_id = ?");
            $stmtDebt->execute([$supplierId, $packagingTypeId]);
            $curDebt = $stmtDebt->fetch();

            if ($curDebt) {
                $curCrates = intval($curDebt['crates_due']);
                $curLoose = intval($curDebt['loose_bottles_due']);
                $curTotalBtls = ($curCrates * $factor) + $curLoose;

                $returnedTotalBtls = ($cratesReturned * $factor) + $bottlesReturned;
                $remainingTotalBtls = max(0, $curTotalBtls - $returnedTotalBtls);

                $newCrates = floor($remainingTotalBtls / $factor);
                $newLoose = $remainingTotalBtls % $factor;

                $this->db->prepare("
                    UPDATE supplier_emballage_debts 
                    SET crates_due = ?, loose_bottles_due = ? 
                    WHERE supplier_id = ? AND packaging_type_id = ?
                ")->execute([$newCrates, $newLoose, $supplierId, $packagingTypeId]);
            }

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function recordAdjustment($data, $userId = 1) {
        $this->db->beginTransaction();
        try {
            $pkgTypeId = intval($data['packaging_type_id']);
            $movType = trim($data['movement_type'] ?? 'Initial_Stock');
            $crates = max(0, intval($data['crates_quantity'] ?? 0));
            $bottles = max(0, intval($data['bottles_quantity'] ?? 0));
            $notes = trim($data['notes'] ?? '');
            $movDate = !empty($data['movement_date']) ? $data['movement_date'] : date('Y-m-d');
            $timestamp = $movDate . ' ' . date('H:i:s');

            if ($crates == 0 && $bottles == 0) {
                throw new \Exception("Veuillez saisir au moins une quantité de casiers ou de bouteilles supérieure à zéro.");
            }

            // Determine flow direction based on movement type
            $isIncoming = in_array($movType, ['Initial_Stock', 'Adjustment_Plus']);

            if ($isIncoming) {
                // 1. Log incoming movement
                $stmtMov = $this->db->prepare("
                    INSERT INTO emballage_movements (packaging_type_id, movement_type, crates_in, bottles_in, notes, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmtMov->execute([$pkgTypeId, $movType, $crates, $bottles, $notes, $userId, $timestamp]);

                // 2. Increment emballage_stock
                $this->db->prepare("
                    INSERT INTO emballage_stock (packaging_type_id, empty_crates, loose_bottles)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        empty_crates = empty_crates + VALUES(empty_crates),
                        loose_bottles = loose_bottles + VALUES(loose_bottles)
                ")->execute([$pkgTypeId, $crates, $bottles]);

            } else {
                // Validate sufficient empty stock before deduction
                $stkStmt = $this->db->prepare("
                    SELECT COALESCE(empty_crates, 0) as empty_crates, COALESCE(loose_bottles, 0) as loose_bottles 
                    FROM emballage_stock 
                    WHERE packaging_type_id = ?
                ");
                $stkStmt->execute([$pkgTypeId]);
                $curStk = $stkStmt->fetch() ?: ['empty_crates' => 0, 'loose_bottles' => 0];

                $availCrates = intval($curStk['empty_crates']);
                $availBottles = intval($curStk['loose_bottles']);

                if ($crates > $availCrates) {
                    throw new \Exception("Stock de vides insuffisant : vous tentez de déduire {$crates} casier(s) vide(s) alors qu'il n'y en a que {$availCrates} disponible(s) au dépôt.");
                }
                if ($bottles > $availBottles) {
                    throw new \Exception("Stock de bouteilles vrac insuffisant : vous tentez de déduire {$bottles} bouteille(s) alors qu'il n'y en a que {$availBottles} disponible(s) au dépôt.");
                }

                // 1. Log outgoing movement
                $stmtMov = $this->db->prepare("
                    INSERT INTO emballage_movements (packaging_type_id, movement_type, crates_out, bottles_out, notes, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmtMov->execute([$pkgTypeId, $movType, $crates, $bottles, $notes, $userId, $timestamp]);

                // 2. Decrement emballage_stock
                $this->db->prepare("
                    UPDATE emballage_stock 
                    SET empty_crates = GREATEST(0, empty_crates - ?),
                        loose_bottles = GREATEST(0, loose_bottles - ?)
                    WHERE packaging_type_id = ?
                ")->execute([$crates, $bottles, $pkgTypeId]);
            }

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getSuppliers() {
        return $this->db->query("SELECT * FROM suppliers ORDER BY name ASC")->fetchAll();
    }

    public function getClients() {
        return $this->db->query("SELECT * FROM clients ORDER BY name ASC")->fetchAll();
    }

    public function getCashAccountsWithBalances() {
        return $this->db->query("
            SELECT 
                a.id, 
                a.name, 
                a.payment_method_id,
                pm.name as payment_method_name,
                COALESCE(SUM(t.amount_in), 0) - COALESCE(SUM(t.amount_out), 0) as current_balance
            FROM cash_accounts a
            LEFT JOIN payment_methods pm ON a.payment_method_id = pm.id
            LEFT JOIN cash_transactions t ON a.id = t.cash_account_id
            GROUP BY a.id, a.name, a.payment_method_id, pm.name
            ORDER BY a.id ASC
        ")->fetchAll();
    }

    public function getPaymentMethods() {
        return $this->db->query("SELECT * FROM payment_methods ORDER BY id ASC")->fetchAll();
    }
}

