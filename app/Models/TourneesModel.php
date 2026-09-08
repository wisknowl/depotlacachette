<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class TourneesModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($filters = []) {
        $sql = "
            SELECT 
                t.*,
                e.name as driver_name,
                e.phone as driver_phone,
                u.username as creator_name,
                ca.name as cash_account_name,
                COUNT(DISTINCT ti.id) as item_count,
                COUNT(DISTINCT s.id) as sales_count,
                COALESCE(SUM(DISTINCT s.total_amount), 0) as calculated_sales_total
            FROM tournees t
            LEFT JOIN employees e ON t.driver_id = e.id
            LEFT JOIN users u ON t.created_by = u.id
            LEFT JOIN cash_accounts ca ON t.cash_account_id = ca.id
            LEFT JOIN tournee_items ti ON t.id = ti.tournee_id
            LEFT JOIN sales s ON t.id = s.tournee_id AND s.status = 'Valid'
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (t.reference LIKE ? OR e.name LIKE ? OR t.vehicle_name LIKE ? OR t.notes LIKE ?)";
            $term = '%' . trim($filters['search']) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['driver_id'])) {
            $sql .= " AND t.driver_id = ?";
            $params[] = intval($filters['driver_id']);
        }

        if (!empty($filters['status'])) {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND t.tournee_date >= ?";
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND t.tournee_date <= ?";
            $params[] = $filters['end_date'];
        }

        $sql .= "
            GROUP BY t.id, t.reference, t.tournee_date, t.driver_id, t.vehicle_name, t.status, 
                     t.total_loaded_amount, t.total_sold_amount, t.total_cash_collected, t.total_expenses, 
                     t.cash_deposited, t.cash_account_id, t.cash_shortage, t.notes, t.created_by, 
                     t.created_at, t.closed_at, e.name, e.phone, u.username, ca.name
            ORDER BY t.tournee_date DESC, t.id DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT 
                t.*,
                e.name as driver_name,
                e.phone as driver_phone,
                e.role as driver_role,
                u.username as creator_name,
                ca.name as cash_account_name
            FROM tournees t
            LEFT JOIN employees e ON t.driver_id = e.id
            LEFT JOIN users u ON t.created_by = u.id
            LEFT JOIN cash_accounts ca ON t.cash_account_id = ca.id
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getItems($tourneeId) {
        $stmt = $this->db->prepare("
            SELECT 
                ti.*,
                p.name as product_name,
                p.short_code,
                p.is_returnable,
                p.cout_emballage,
                p.packaging_type_id,
                p.factor,
                p.purchase_price,
                f.name as format_name,
                c.name as category_name,
                pt.name as packaging_name,
                pt.color as packaging_color,
                COALESCE((
                    SELECT SUM(si.quantity) 
                    FROM sale_items si 
                    JOIN sales s ON si.sale_id = s.id 
                    WHERE s.tournee_id = ti.tournee_id 
                      AND s.status = 'Valid' 
                      AND si.product_id = ti.product_id 
                      AND si.format_type = ti.format_type
                ), 0) as calculated_qty_sold
            FROM tournee_items ti
            JOIN products p ON ti.product_id = p.id
            LEFT JOIN formats f ON p.format_id = f.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN packaging_types pt ON p.packaging_type_id = pt.id
            WHERE ti.tournee_id = ?
            ORDER BY p.name ASC
        ");
        $stmt->execute([$tourneeId]);
        return $stmt->fetchAll();
    }

    public function getSales($tourneeId) {
        $stmt = $this->db->prepare("
            SELECT 
                s.*,
                c.name as client_name,
                c.phone as client_phone,
                pm.name as payment_method_name,
                pm.type as payment_method_type,
                ca.name as cash_account_name,
                u.username as seller_name,
                COUNT(si.id) as item_count,
                COALESCE(GROUP_CONCAT(CONCAT(p.name, ' (', si.quantity, ')') SEPARATOR ', '), '') as items_summary
            FROM sales s
            LEFT JOIN clients c ON s.client_id = c.id
            LEFT JOIN payment_methods pm ON s.payment_method_id = pm.id
            LEFT JOIN cash_accounts ca ON s.cash_account_id = ca.id
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN sale_items si ON s.id = si.sale_id
            LEFT JOIN products p ON si.product_id = p.id
            WHERE s.tournee_id = ?
            GROUP BY s.id, s.sale_date, s.client_id, s.total_amount, s.payment_method_id, s.cash_account_id,
                     s.reference, s.notes, s.user_id, s.status, s.created_at, c.name, c.phone, pm.name, pm.type, ca.name, u.username
            ORDER BY s.id ASC
        ");
        $stmt->execute([$tourneeId]);
        return $stmt->fetchAll();
    }

    public function getExpenses($tourneeId) {
        $stmt = $this->db->prepare("
            SELECT 
                e.*,
                ec.name as category_name,
                pm.name as payment_method_name,
                ca.name as cash_account_name,
                u.username as creator_name
            FROM expenses e
            LEFT JOIN expense_categories ec ON e.category_id = ec.id
            LEFT JOIN payment_methods pm ON e.payment_method_id = pm.id
            LEFT JOIN cash_transactions ct ON e.id = ct.source_id AND ct.transaction_type = 'Expense'
            LEFT JOIN cash_accounts ca ON ct.cash_account_id = ca.id
            LEFT JOIN users u ON e.user_id = u.id
            WHERE e.tournee_id = ?
            ORDER BY e.id ASC
        ");
        $stmt->execute([$tourneeId]);
        return $stmt->fetchAll();
    }

    public function getDrivers() {
        return $this->db->query("
            SELECT id, name, phone, role, status 
            FROM employees 
            WHERE status = 'Active' 
            ORDER BY name ASC
        ")->fetchAll();
    }

    public function getCashAccounts() {
        $sql = "
            SELECT 
                a.id, 
                a.name, 
                a.payment_method_id,
                pm.name as payment_method_name,
                pm.type as payment_method_type,
                COALESCE(SUM(t.amount_in), 0) - COALESCE(SUM(t.amount_out), 0) as current_balance
            FROM cash_accounts a
            LEFT JOIN payment_methods pm ON a.payment_method_id = pm.id
            LEFT JOIN cash_transactions t ON a.id = t.cash_account_id
            GROUP BY a.id, a.name, a.payment_method_id, pm.name, pm.type
            ORDER BY a.id ASC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getExpenseCategories() {
        return $this->db->query("
            SELECT id, name 
            FROM expense_categories 
            ORDER BY name ASC
        ")->fetchAll();
    }

    public function getPackagingTypes() {
        return $this->db->query("
            SELECT pt.*, COALESCE(es.empty_crates, 0) as empty_crates, COALESCE(es.loose_bottles, 0) as loose_bottles
            FROM packaging_types pt
            LEFT JOIN emballage_stock es ON pt.id = es.packaging_type_id
            ORDER BY pt.id ASC
        ")->fetchAll();
    }

    public function getAvailableProducts() {
        $sql = "
            SELECT 
                p.*, 
                IF(p.price_unite > 0, p.price_unite, ROUND(p.price_casier / GREATEST(1, p.factor), 0)) as calculated_price_unite,
                f.name as format_name,
                c.name as category_name,
                pt.name as packaging_name,
                pt.company as packaging_company,
                pt.color as packaging_color,
                pt.bottles_per_crate,
                COALESCE(SUM(CASE 
                    WHEN mt.direction = 'IN' THEN m.stock_equivalent 
                    WHEN mt.direction = 'OUT' THEN -m.stock_equivalent 
                    ELSE 0 
                END), 0) as current_stock
            FROM products p 
            LEFT JOIN formats f ON p.format_id = f.id 
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN packaging_types pt ON p.packaging_type_id = pt.id
            LEFT JOIN stock_movements m ON p.id = m.product_id
            LEFT JOIN movement_types mt ON m.movement_type_id = mt.id
            GROUP BY p.id, p.name, p.short_code, p.slug, p.category_id, p.format_id, p.is_returnable, p.cout_emballage, p.packaging_type_id, p.purchase_price, p.price_casier, p.price_demi, p.price_unite, p.alert_stock, p.factor, p.created_at, f.name, c.name, pt.name, pt.company, pt.color, pt.bottles_per_crate
            ORDER BY p.name ASC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getTourneeProductsForSale($tourneeId) {
        $stmt = $this->db->prepare("
            SELECT 
                p.id,
                p.name,
                p.short_code,
                p.slug,
                p.category_id,
                p.format_id,
                p.is_returnable,
                p.cout_emballage,
                p.packaging_type_id,
                p.purchase_price,
                p.price_casier,
                p.price_demi,
                p.price_unite,
                p.factor,
                f.name as format_name,
                c.name as category_name,
                pt.name as packaging_name,
                pt.company as packaging_company,
                pt.color as packaging_color,
                pt.bottles_per_crate,
                SUM(CASE WHEN ti.format_type = 'demi' THEN ti.qty_loaded * 0.5 ELSE ti.qty_loaded END) as total_loaded_equiv,
                COALESCE((
                    SELECT SUM(CASE WHEN si.format_type = 'demi' THEN si.quantity * 0.5 ELSE si.quantity END)
                    FROM sale_items si 
                    JOIN sales s ON si.sale_id = s.id 
                    WHERE s.tournee_id = ti.tournee_id 
                      AND s.status = 'Valid' 
                      AND si.product_id = p.id
                ), 0) as total_sold_equiv
            FROM tournee_items ti
            JOIN products p ON ti.product_id = p.id
            LEFT JOIN formats f ON p.format_id = f.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN packaging_types pt ON p.packaging_type_id = pt.id
            WHERE ti.tournee_id = ?
            GROUP BY p.id, p.name, p.short_code, p.slug, p.category_id, p.format_id, p.is_returnable, 
                     p.cout_emballage, p.packaging_type_id, p.purchase_price, p.price_casier, p.price_demi, 
                     p.price_unite, p.factor, f.name, c.name, pt.name, pt.company, pt.color, pt.bottles_per_crate, ti.tournee_id
            ORDER BY p.name ASC
        ");
        $stmt->execute([$tourneeId]);
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $r) {
            $rem = max(0, floatval($r['total_loaded_equiv']) - floatval($r['total_sold_equiv']));
            $r['current_stock'] = $rem;
            $r['truck_stock'] = $rem;
            $r['is_tournee'] = true;
            $result[] = $r;
        }
        return $result;
    }

    public function createTournee($data, $userId = 1) {
        $this->db->beginTransaction();
        try {
            $driverId = intval($data['driver_id'] ?? 0);
            if ($driverId <= 0) {
                throw new \Exception("Veuillez sélectionner un chauffeur / livreur.");
            }

            $tourneeDate = !empty($data['tournee_date']) ? $data['tournee_date'] : date('Y-m-d');
            $vehicleName = trim($data['vehicle_name'] ?? '');
            $notes = trim($data['notes'] ?? '');

            $rawItems = $data['items'] ?? [];
            if (empty($rawItems)) {
                throw new \Exception("Veuillez ajouter au moins un produit à charger sur le camion.");
            }

            // Generate Reference TR00001
            $lastRef = $this->db->query("SELECT reference FROM tournees ORDER BY id DESC LIMIT 1")->fetchColumn();
            $nextNum = 1;
            if ($lastRef && preg_match('/TR(\d+)/', $lastRef, $matches)) {
                $nextNum = intval($matches[1]) + 1;
            }
            $reference = 'TR' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

            // Validate each item stock in warehouse
            $processedItems = [];
            $totalLoadedAmount = 0;

            $requestedStockByProduct = [];
            $productsStockMap = [];

            foreach ($rawItems as $idx => $item) {
                $prodId = intval($item['product_id'] ?? 0);
                $qty = floatval($item['quantity'] ?? 0);
                $formatType = in_array($item['format_type'] ?? '', ['casier', 'demi', 'unite']) ? $item['format_type'] : 'casier';
                $unitPrice = floatval($item['unit_price'] ?? 0);

                if ($prodId <= 0 || $qty <= 0) continue;

                if (!isset($productsStockMap[$prodId])) {
                    // Check warehouse stock
                    $stmtStk = $this->db->prepare("
                        SELECT p.id, p.name, p.format_id, p.price_casier, p.price_demi,
                               COALESCE(SUM(CASE 
                                   WHEN mt.direction = 'IN' THEN m.stock_equivalent 
                                   WHEN mt.direction = 'OUT' THEN -m.stock_equivalent 
                                   ELSE 0 
                               END), 0) as current_stock
                        FROM products p
                        LEFT JOIN stock_movements m ON p.id = m.product_id
                        LEFT JOIN movement_types mt ON m.movement_type_id = mt.id
                        WHERE p.id = ?
                        GROUP BY p.id, p.name, p.format_id, p.price_casier, p.price_demi
                    ");
                    $stmtStk->execute([$prodId]);
                    $product = $stmtStk->fetch();

                    if (!$product) {
                        throw new \Exception("Produit à la ligne " . ($idx + 1) . " introuvable.");
                    }
                    $productsStockMap[$prodId] = $product;
                }

                $product = $productsStockMap[$prodId];
                $equiv = ($formatType === 'demi') ? ($qty * 0.5) : $qty;
                $requestedStockByProduct[$prodId] = ($requestedStockByProduct[$prodId] ?? 0) + $equiv;

                if ($unitPrice <= 0) {
                    $unitPrice = ($formatType === 'demi') ? floatval($product['price_demi'] ?: ($product['price_casier'] / 2)) : floatval($product['price_casier']);
                }

                $lineTotal = $qty * $unitPrice;
                $totalLoadedAmount += $lineTotal;

                $processedItems[] = [
                    'product_id' => $prodId,
                    'format_id' => $product['format_id'] ?? null,
                    'format_type' => $formatType,
                    'qty_loaded' => $qty,
                    'stock_equivalent' => $equiv,
                    'unit_price' => $unitPrice,
                    'product_name' => $product['name']
                ];
            }

            if (empty($processedItems)) {
                throw new \Exception("Aucun article valide sélectionné pour le chargement.");
            }

            // Verify aggregated stock for each product across all rows
            foreach ($requestedStockByProduct as $pId => $totalReq) {
                $curStock = floatval($productsStockMap[$pId]['current_stock']);
                if ($totalReq > $curStock) {
                    $pName = $productsStockMap[$pId]['name'];
                    throw new \Exception("Stock magasin insuffisant pour '" . htmlspecialchars($pName) . "'. Demandé au total : {$totalReq} casier(s), Disponible en magasin : {$curStock} casier(s).");
                }
            }

            // Insert Tournée Header
            $stmtInsert = $this->db->prepare("
                INSERT INTO tournees (reference, tournee_date, driver_id, vehicle_name, status, total_loaded_amount, notes, created_by)
                VALUES (?, ?, ?, ?, 'En_Route', ?, ?, ?)
            ");
            $stmtInsert->execute([$reference, $tourneeDate, $driverId, $vehicleName, $totalLoadedAmount, $notes, $userId]);
            $tourneeId = $this->db->lastInsertId();

            // Insert Items and Stock Movements (OUT from warehouse)
            $stmtItem = $this->db->prepare("
                INSERT INTO tournee_items (tournee_id, product_id, format_type, qty_loaded, unit_price)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmtMov = $this->db->prepare("
                INSERT INTO stock_movements (movement_date, product_id, format_id, format_type, movement_type_id, source_type, source_id, quantity, stock_equivalent, unit_price, reference, user_id)
                VALUES (?, ?, ?, ?, 9, 'Tournee', ?, ?, ?, ?, ?, ?)
            ");

            foreach ($processedItems as $it) {
                $stmtItem->execute([$tourneeId, $it['product_id'], $it['format_type'], $it['qty_loaded'], $it['unit_price']]);
                
                // Movement OUT (Chargement Tournée Route)
                $stmtMov->execute([
                    $tourneeDate,
                    $it['product_id'],
                    $it['format_id'],
                    $it['format_type'],
                    $tourneeId,
                    $it['qty_loaded'],
                    $it['stock_equivalent'],
                    $it['unit_price'],
                    $reference,
                    $userId
                ]);
            }

            // Log in system_audit_logs using Helper
            \App\Core\Helper::logAudit('CREATE_TOURNEE', 'Tournees', $tourneeId, "Ouverture et chargement tournée $reference ($totalLoadedAmount FCFA)");

            $this->db->commit();
            return $tourneeId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function saveDecharge($tourneeId, $data, $userId = 1) {
        $this->db->beginTransaction();
        try {
            $tournee = $this->getById($tourneeId);
            if (!$tournee) {
                throw new \Exception("Tournée introuvable.");
            }
            if ($tournee['status'] !== 'En_Route') {
                throw new \Exception("Cette tournée a déjà été clôturée ou annulée.");
            }

            $reference = $tournee['reference'];
            $items = $this->getItems($tourneeId);

            // 1. Calculate actual sales made per product from linked sales
            $sales = $this->getSales($tourneeId);
            $soldQuantities = [];
            $totalSalesAmount = 0;
            $totalCashCollectedFromSales = 0;

            foreach ($sales as $s) {
                if ($s['status'] === 'Valid') {
                    $totalSalesAmount += floatval($s['total_amount']);
                    if ($s['payment_method_id'] != 5) { // Not credit
                        $totalCashCollectedFromSales += floatval($s['amount_paid'] ?: $s['total_amount']);
                    }

                    // Get items of this sale
                    $stmtSaleItems = $this->db->prepare("SELECT product_id, format_type, quantity FROM sale_items WHERE sale_id = ?");
                    $stmtSaleItems->execute([$s['id']]);
                    $sItems = $stmtSaleItems->fetchAll();
                    foreach ($sItems as $si) {
                        $key = $si['product_id'] . '_' . $si['format_type'];
                        $soldQuantities[$key] = ($soldQuantities[$key] ?? 0) + floatval($si['quantity']);
                    }
                }
            }

            // 2. Process Physical Returns of Full Drinks (Invendus)
            $returnsInput = $data['returns'] ?? [];
            $stmtUpdateItem = $this->db->prepare("
                UPDATE tournee_items 
                SET qty_sold = ?, qty_returned = ?, qty_shortage = ?
                WHERE tournee_id = ? AND product_id = ? AND format_type = ?
            ");

            $stmtReturnMov = $this->db->prepare("
                INSERT INTO stock_movements (movement_date, product_id, format_id, format_type, movement_type_id, source_type, source_id, quantity, stock_equivalent, unit_price, reference, user_id)
                VALUES (CURRENT_DATE, ?, ?, ?, 10, 'TourneeReturn', ?, ?, ?, ?, ?, ?)
            ");

            foreach ($items as $it) {
                $key = $it['product_id'] . '_' . $it['format_type'];
                $qtySold = floatval($soldQuantities[$key] ?? 0);
                $qtyReturned = floatval($returnsInput[$it['id']] ?? 0);
                $qtyLoaded = floatval($it['qty_loaded']);
                
                // Shortage = Loaded - Sold - Returned
                $qtyShortage = max(0, $qtyLoaded - ($qtySold + $qtyReturned));

                $stmtUpdateItem->execute([
                    $qtySold,
                    $qtyReturned,
                    $qtyShortage,
                    $tourneeId,
                    $it['product_id'],
                    $it['format_type']
                ]);

                // If unsold drinks returned, reintegrate into warehouse stock (IN)
                if ($qtyReturned > 0) {
                    $equiv = ($it['format_type'] === 'demi') ? ($qtyReturned * 0.5) : $qtyReturned;
                    $stmtReturnMov->execute([
                        $it['product_id'],
                        $it['format_id'] ?? null,
                        $it['format_type'],
                        $tourneeId,
                        $qtyReturned,
                        $equiv,
                        $it['unit_price'],
                        $reference,
                        $userId
                    ]);
                }
            }

            // 3. Process Empty Crates Returned from Truck
            $emptyCratesReturned = $data['empty_crates_returned'] ?? [];
            $stmtEmbMov = $this->db->prepare("
                INSERT INTO emballage_movements (packaging_type_id, movement_type, reference_id, crates_in, notes, created_by)
                VALUES (?, 'Route_Return', ?, ?, ?, ?)
            ");
            $stmtEmbStock = $this->db->prepare("
                INSERT INTO emballage_stock (packaging_type_id, empty_crates, loose_bottles)
                VALUES (?, ?, 0)
                ON DUPLICATE KEY UPDATE empty_crates = empty_crates + VALUES(empty_crates)
            ");

            foreach ($emptyCratesReturned as $pkgTypeId => $cCount) {
                $crates = intval($cCount);
                if ($crates > 0) {
                    $stmtEmbMov->execute([$pkgTypeId, $tourneeId, $crates, "Retour casiers vides de tournée $reference", $userId]);
                    $stmtEmbStock->execute([$pkgTypeId, $crates]);
                }
            }

            // 4. Process Road Expenses (Frais de route / Carburant / Ration)
            $expensesInput = $data['expenses'] ?? [];
            $totalExpenses = 0;
            
            $stmtLastExp = $this->db->query("SELECT id FROM expenses WHERE id LIKE 'EXP%' ORDER BY id DESC LIMIT 1");
            $lastExp = $stmtLastExp->fetchColumn();
            $nextExpNum = 1;
            if ($lastExp && preg_match('/EXP(\d+)/', $lastExp, $m)) {
                $nextExpNum = intval($m[1]) + 1;
            }

            $stmtExp = $this->db->prepare("
                INSERT INTO expenses (id, expense_date, category_id, description, amount, payment_method_id, beneficiary, reference, user_id, tournee_id, status)
                VALUES (?, CURRENT_DATE, ?, ?, ?, 1, ?, ?, ?, ?, 'Paid')
            ");

            $cashAccountId = !empty($data['cash_account_id']) ? intval($data['cash_account_id']) : null;

            foreach ($expensesInput as $exp) {
                $expAmount = floatval($exp['amount'] ?? 0);
                $expCatId = intval($exp['category_id'] ?? 1);
                $expNotes = trim($exp['notes'] ?? 'Frais de route');

                if ($expAmount > 0) {
                    $totalExpenses += $expAmount;
                    $expId = 'EXP' . str_pad($nextExpNum++, 5, '0', STR_PAD_LEFT);
                    $stmtExp->execute([
                        $expId,
                        $expCatId,
                        "[$reference] " . $expNotes,
                        $expAmount,
                        $tournee['driver_name'] ?? 'Chauffeur Tournée',
                        $reference,
                        $userId,
                        $tourneeId
                    ]);
                }
            }

            // 5. Cash Reconciliation & Deposit into Main Cash Register
            $cashDeposited = floatval($data['cash_deposited'] ?? 0);
            $netTheoreticalCash = max(0, $totalCashCollectedFromSales - $totalExpenses);
            $cashShortage = max(0, $netTheoreticalCash - $cashDeposited);

            if ($cashDeposited > 0 && $cashAccountId) {
                // Record cash transaction in cash_transactions
                $this->db->prepare("
                    INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id)
                    VALUES (NOW(), 'Deposit', ?, ?, ?, ?, 0.00, ?)
                ")->execute([
                    $tourneeId,
                    "Versement net décharge tournée $reference (Chauffeur: {$tournee['driver_name']})",
                    $cashAccountId,
                    $cashDeposited,
                    $userId
                ]);
            }

            // 6. Update Tournée Header Status to 'Cloturee'
            $this->db->prepare("
                UPDATE tournees 
                SET status = 'Cloturee',
                    total_sold_amount = ?,
                    total_cash_collected = ?,
                    total_expenses = ?,
                    cash_deposited = ?,
                    cash_account_id = ?,
                    cash_shortage = ?,
                    closed_at = NOW()
                WHERE id = ?
            ")->execute([
                $totalSalesAmount,
                $totalCashCollectedFromSales,
                $totalExpenses,
                $cashDeposited,
                $cashAccountId,
                $cashShortage,
                $tourneeId
            ]);

            // Audit log
            \App\Core\Helper::logAudit('CLOSE_TOURNEE', 'Tournees', $tourneeId, "Clôture et décharge tournée $reference : Ventes = $totalSalesAmount F, Cash versé = $cashDeposited F, Manquant = $cashShortage F");

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function cancelTournee($tourneeId, $userId = 1, $reason = '') {
        $this->db->beginTransaction();
        try {
            $tournee = $this->getById($tourneeId);
            if (!$tournee) {
                throw new \Exception("Tournée introuvable.");
            }
            if ($tournee['status'] !== 'En_Route') {
                throw new \Exception("Seule une tournée en cours peut être annulée.");
            }

            // Check if any sales are linked
            $salesCount = $this->db->query("SELECT COUNT(*) FROM sales WHERE tournee_id = $tourneeId AND status = 'Valid'")->fetchColumn();
            if ($salesCount > 0) {
                throw new \Exception("Impossible d'annuler cette tournée car $salesCount vente(s) y sont rattachée(s). Veuillez d'abord annuler les ventes correspondantes.");
            }

            $reference = $tournee['reference'];
            $items = $this->getItems($tourneeId);

            // Reintegrate all loaded items back to warehouse stock (IN)
            $stmtReturnMov = $this->db->prepare("
                INSERT INTO stock_movements (movement_date, product_id, format_id, format_type, movement_type_id, source_type, source_id, quantity, stock_equivalent, unit_price, reference, user_id)
                VALUES (CURRENT_DATE, ?, ?, ?, 10, 'TourneeCancellation', ?, ?, ?, ?, ?, ?)
            ");

            foreach ($items as $it) {
                $equiv = ($it['format_type'] === 'demi') ? ($it['qty_loaded'] * 0.5) : $it['qty_loaded'];
                $stmtReturnMov->execute([
                    $it['product_id'],
                    $it['format_id'] ?? null,
                    $it['format_type'],
                    $tourneeId,
                    $it['qty_loaded'],
                    $equiv,
                    $it['unit_price'],
                    $reference,
                    $userId
                ]);
            }

            // Update status to Annulee
            $this->db->prepare("
                UPDATE tournees 
                SET status = 'Annulee',
                    notes = CONCAT(COALESCE(notes, ''), ' [Annulée: ', ?, ']')
                WHERE id = ?
            ")->execute([$reason, $tourneeId]);

            // Audit log
            \App\Core\Helper::logAudit('CANCEL_TOURNEE', 'Tournees', $tourneeId, "Annulation tournée $reference ($reason)");

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
