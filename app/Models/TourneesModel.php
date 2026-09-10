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
                p.format_id,
                p.factor,
                p.purchase_price,
                f.name as format_name,
                c.name as category_name,
                pt.name as packaging_name,
                pt.color as packaging_color,
                COALESCE((
                    SELECT SUM(si.stock_equivalent) 
                    FROM sale_items si 
                    JOIN sales s ON si.sale_id = s.id 
                    WHERE s.tournee_id = ti.tournee_id 
                      AND s.status = 'Valid' 
                      AND si.product_id = ti.product_id
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
                SUM(ti.qty_loaded + (CASE WHEN ti.has_demi = 1 OR ti.format_type = 'demi' THEN 0.5 ELSE 0.0 END)) as total_loaded_equiv,
                COALESCE((
                    SELECT SUM(si.stock_equivalent)
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
                $hasDemi = !empty($item['has_demi']) ? 1 : 0;
                $unitPrice = floatval($item['unit_price'] ?? 0);
                $demiUnitPrice = floatval($item['demi_unit_price'] ?? 0);

                if ($prodId <= 0 || ($qty <= 0 && !$hasDemi)) continue;

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
                $equiv = $qty + ($hasDemi ? 0.5 : 0.0);
                $requestedStockByProduct[$prodId] = ($requestedStockByProduct[$prodId] ?? 0) + $equiv;

                if ($unitPrice <= 0) {
                    $unitPrice = floatval($product['price_casier'] ?? 0);
                }
                if ($hasDemi) {
                    if ($demiUnitPrice <= 0) {
                        $demiUnitPrice = floatval($product['price_demi'] ?: round($unitPrice / 2));
                    } elseif ($unitPrice != floatval($product['price_casier'] ?? 0) && $demiUnitPrice == floatval($product['price_demi'] ?? 0)) {
                        $demiUnitPrice = round($unitPrice / 2);
                    }
                }

                $lineTotal = ($qty * $unitPrice) + ($hasDemi ? $demiUnitPrice : 0.0);
                $totalLoadedAmount += $lineTotal;

                $processedItems[] = [
                    'product_id' => $prodId,
                    'format_id' => $product['format_id'] ?? null,
                    'format_type' => $hasDemi ? ($qty > 0 ? 'casier' : 'demi') : 'casier',
                    'has_demi' => $hasDemi,
                    'qty_loaded' => $qty,
                    'stock_equivalent' => $equiv,
                    'unit_price' => $unitPrice,
                    'demi_unit_price' => $demiUnitPrice,
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
                INSERT INTO tournee_items (tournee_id, product_id, format_type, has_demi, stock_equivalent, qty_loaded, unit_price, demi_unit_price)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmtMov = $this->db->prepare("
                INSERT INTO stock_movements (movement_date, product_id, format_id, format_type, movement_type_id, source_type, source_id, quantity, stock_equivalent, unit_price, reference, user_id)
                VALUES (?, ?, ?, ?, 9, 'Tournee', ?, ?, ?, ?, ?, ?)
            ");

            foreach ($processedItems as $it) {
                $stmtItem->execute([$tourneeId, $it['product_id'], $it['format_type'], $it['has_demi'], $it['stock_equivalent'], $it['qty_loaded'], $it['unit_price'], $it['demi_unit_price']]);
                
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

    public function getTourneeReturnedEmballagesSummary($tourneeId) {
        $stmt = $this->db->prepare("
            SELECT 
                pt.id as packaging_type_id,
                pt.name as packaging_name,
                pt.company,
                pt.color,
                pt.bottles_per_crate,
                COALESCE(SUM(si.crates_returned), 0) as total_crates_returned,
                COALESCE(SUM(si.bottles_returned), 0) as total_bottles_returned
            FROM sales s
            JOIN sale_items si ON s.id = si.sale_id
            JOIN products p ON si.product_id = p.id
            JOIN packaging_types pt ON p.packaging_type_id = pt.id
            WHERE s.tournee_id = ? AND s.status = 'Valid' AND si.is_returnable = 1
            GROUP BY pt.id, pt.name, pt.company, pt.color, pt.bottles_per_crate
        ");
        $stmt->execute([$tourneeId]);
        return $stmt->fetchAll();
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

            // 1. Calculate actual sales made per product from linked sales (in stock_equivalent)
            $sales = $this->getSales($tourneeId);
            $soldStockEquivByProduct = [];
            $totalSalesAmount = 0;
            $totalCashCollectedFromSales = 0;

            foreach ($sales as $s) {
                if ($s['status'] === 'Valid') {
                    $totalSalesAmount += floatval($s['total_amount']);
                    if ($s['payment_method_id'] != 5) { // Not credit
                        $totalCashCollectedFromSales += floatval($s['amount_paid'] ?: $s['total_amount']) + floatval($s['excess_amount'] ?? 0);
                    }

                    // Get items of this sale
                    $stmtSaleItems = $this->db->prepare("SELECT product_id, stock_equivalent FROM sale_items WHERE sale_id = ?");
                    $stmtSaleItems->execute([$s['id']]);
                    $sItems = $stmtSaleItems->fetchAll();
                    foreach ($sItems as $si) {
                        $pId = intval($si['product_id']);
                        $soldStockEquivByProduct[$pId] = ($soldStockEquivByProduct[$pId] ?? 0) + floatval($si['stock_equivalent']);
                    }
                }
            }

            // 2. Process Physical Returns of Full Drinks (Invendus)
            $returnsInput = $data['returns'] ?? [];
            $stmtUpdateItem = $this->db->prepare("
                UPDATE tournee_items 
                SET qty_sold = ?, qty_returned = ?, qty_shortage = ?
                WHERE id = ?
            ");

            $stmtReturnMov = $this->db->prepare("
                INSERT INTO stock_movements (movement_date, product_id, format_id, format_type, movement_type_id, source_type, source_id, quantity, stock_equivalent, unit_price, reference, user_id)
                VALUES (CURRENT_DATE, ?, ?, ?, 10, 'TourneeReturn', ?, ?, ?, ?, ?, ?)
            ");

            foreach ($items as $it) {
                $pId = intval($it['product_id']);
                $qtySold = floatval($soldStockEquivByProduct[$pId] ?? 0);
                
                $retVal = $returnsInput[$it['id']] ?? 0;
                $retCrates = 0;
                $retHasDemi = 0;
                if (is_array($retVal)) {
                    $retCrates = floatval($retVal['crates'] ?? 0);
                    $retHasDemi = !empty($retVal['has_demi']) ? 1 : 0;
                } else {
                    $retCrates = floatval($retVal);
                }

                $qtyReturned = $retCrates + ($retHasDemi ? 0.5 : 0.0);
                $qtyLoaded = floatval($it['qty_loaded']) + (!empty($it['has_demi']) ? 0.5 : 0.0);
                
                // Shortage = Loaded - Sold - Returned
                $qtyShortage = max(0, $qtyLoaded - ($qtySold + $qtyReturned));

                $stmtUpdateItem->execute([
                    $qtySold,
                    $qtyReturned,
                    $qtyShortage,
                    $it['id']
                ]);

                // If unsold drinks returned, reintegrate into warehouse stock (IN)
                if ($qtyReturned > 0) {
                    $stmtReturnMov->execute([
                        $it['product_id'],
                        intval($it['format_id'] ?: 1),
                        $it['format_type'],
                        $tourneeId,
                        $retCrates,
                        $qtyReturned,
                        $it['unit_price'],
                        $reference,
                        $userId
                    ]);
                }
            }

            // 3. Process Empty Crates and Loose Bottles Returned from Truck
            $emptyCratesReturned = $data['empty_crates_returned'] ?? [];
            $looseBottlesReturned = $data['loose_bottles_returned'] ?? [];
            
            $stmtEmbMov = $this->db->prepare("
                INSERT INTO emballage_movements (packaging_type_id, movement_type, reference_id, crates_in, bottles_in, notes, created_by)
                VALUES (?, 'Route_Return', ?, ?, ?, ?, ?)
            ");
            $stmtEmbStock = $this->db->prepare("
                INSERT INTO emballage_stock (packaging_type_id, empty_crates, loose_bottles)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE empty_crates = empty_crates + VALUES(empty_crates), loose_bottles = loose_bottles + VALUES(loose_bottles)
            ");

            $allPkgIds = array_unique(array_merge(array_keys($emptyCratesReturned), array_keys($looseBottlesReturned)));
            foreach ($allPkgIds as $pkgTypeId) {
                $pkgId = intval($pkgTypeId);
                $crates = intval($emptyCratesReturned[$pkgId] ?? 0);
                $bottles = intval($looseBottlesReturned[$pkgId] ?? 0);
                if ($crates > 0 || $bottles > 0) {
                    $stmtEmbMov->execute([$pkgId, $tourneeId, $crates, $bottles, "Retour casiers ($crates) & bouteilles vrac ($bottles) tournée $reference", $userId]);
                    $stmtEmbStock->execute([$pkgId, $crates, $bottles]);
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
                    VALUES (NOW(), 'Tournee', ?, ?, ?, ?, 0.00, ?)
                ")->execute([
                    $tourneeId,
                    "Versement net décharge tournée $reference (Chauffeur: {$tournee['driver_name']})",
                    $cashAccountId,
                    $cashDeposited,
                    $userId
                ]);
            }

            // 5b. Generate official Client Avoirs (client_payments) for any excess cash collected by driver on this tournee
            $ventesModel = new \App\Models\VentesModel();
            foreach ($sales as $s) {
                $excess = floatval($s['excess_amount'] ?? 0);
                if ($s['status'] === 'Valid' && $excess > 0) {
                    $stmtCheck = $this->db->prepare("SELECT id FROM client_payments WHERE reference = ?");
                    $stmtCheck->execute([$s['id']]);
                    if (!$stmtCheck->fetch()) {
                        $advId = $ventesModel->generateClientPaymentId();
                        $advNotes = "Avoir Client tournée {$reference} - Trop-perçu Vente {$s['id']} (Validé à la décharge)";
                        $this->db->prepare("
                            INSERT INTO client_payments (id, payment_date, client_id, amount, payment_method_id, cash_account_id, reference, notes, user_id)
                            VALUES (?, CURRENT_DATE, ?, ?, 1, ?, ?, ?, ?)
                        ")->execute([
                            $advId, $s['client_id'], $excess,
                            $cashAccountId, $s['id'], $advNotes, $userId
                        ]);
                    }
                }
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

    public function reopenTournee($tourneeId, $userId = 1, $reason = '') {
        $this->db->beginTransaction();
        try {
            $tournee = $this->getById($tourneeId);
            if (!$tournee) {
                throw new \Exception("Tournée introuvable.");
            }
            if ($tournee['status'] !== 'Cloturee') {
                throw new \Exception("Seule une tournée déjà clôturée peut être réouverte.");
            }

            $reference = $tournee['reference'];

            // 1. Invert Net Cash Deposit (Storno: Withdrawal from cash register for net deposited)
            $stmtNetCash = $this->db->prepare("
                SELECT cash_account_id,
                       SUM(amount_in - amount_out) as net_cash
                FROM cash_transactions
                WHERE source_id = ? AND transaction_type IN ('Tournee', 'Deposit', 'Withdrawal')
                GROUP BY cash_account_id
                HAVING net_cash > 0
            ");
            $stmtNetCash->execute([$tourneeId]);
            $cashRows = $stmtNetCash->fetchAll();

            foreach ($cashRows as $cr) {
                $netAmount = floatval($cr['net_cash']);
                if ($netAmount > 0) {
                    $this->db->prepare("
                        INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id)
                        VALUES (NOW(), 'Tournee', ?, ?, ?, 0.00, ?, ?)
                    ")->execute([
                        $tourneeId,
                        "Annulation versement décharge tournée $reference (Réouverture)" . (!empty($reason) ? " - Motif: $reason" : ""),
                        $cr['cash_account_id'],
                        $netAmount,
                        $userId
                    ]);
                }
            }

            // 2. Invert Net Unsold Drinks Returned to Warehouse Stock (Movement OUT)
            $stmtNetStock = $this->db->prepare("
                SELECT product_id, format_id, format_type, unit_price,
                       SUM(CASE WHEN source_type = 'TourneeReturn' THEN stock_equivalent ELSE -stock_equivalent END) as net_returned_equiv,
                       SUM(CASE WHEN source_type = 'TourneeReturn' THEN quantity ELSE -quantity END) as net_returned_qty
                FROM stock_movements
                WHERE source_id = ? AND source_type IN ('TourneeReturn', 'TourneeReturnReversal')
                GROUP BY product_id, format_id, format_type, unit_price
                HAVING net_returned_equiv > 0
            ");
            $stmtNetStock->execute([$tourneeId]);
            $stockRows = $stmtNetStock->fetchAll();

            $stmtReversalStock = $this->db->prepare("
                INSERT INTO stock_movements (movement_date, product_id, format_id, format_type, movement_type_id, source_type, source_id, quantity, stock_equivalent, unit_price, reference, user_id)
                VALUES (CURRENT_DATE, ?, ?, ?, 6, 'TourneeReturnReversal', ?, ?, ?, ?, ?, ?)
            ");

            foreach ($stockRows as $sr) {
                $stmtReversalStock->execute([
                    $sr['product_id'],
                    intval($sr['format_id'] ?: 1),
                    $sr['format_type'],
                    $tourneeId,
                    $sr['net_returned_qty'],
                    $sr['net_returned_equiv'],
                    $sr['unit_price'],
                    "Annulation retour invendus décharge $reference",
                    $userId
                ]);
            }

            // 3. Invert Net Returned Empty Crates & Loose Bottles from Depot Emballage Stock
            $stmtNetEmb = $this->db->prepare("
                SELECT packaging_type_id,
                       SUM(crates_in - crates_out) as net_crates,
                       SUM(bottles_in - bottles_out) as net_bottles
                FROM emballage_movements
                WHERE reference_id = ? AND movement_type IN ('Route_Return', 'Adjustment_Minus')
                GROUP BY packaging_type_id
                HAVING net_crates > 0 OR net_bottles > 0
            ");
            $stmtNetEmb->execute([$tourneeId]);
            $embRows = $stmtNetEmb->fetchAll();

            $stmtReduceEmpty = $this->db->prepare("
                UPDATE emballage_stock 
                SET empty_crates = GREATEST(0, empty_crates - ?),
                    loose_bottles = GREATEST(0, loose_bottles - ?)
                WHERE packaging_type_id = ?
            ");

            $stmtEmbCompensate = $this->db->prepare("
                INSERT INTO emballage_movements (packaging_type_id, movement_type, reference_id, crates_in, crates_out, bottles_in, bottles_out, notes, created_by)
                VALUES (?, 'Adjustment_Minus', ?, 0, ?, 0, ?, ?, ?)
            ");

            foreach ($embRows as $er) {
                $pkgId = intval($er['packaging_type_id']);
                $netCrates = intval($er['net_crates']);
                $netBottles = intval($er['net_bottles']);
                if ($netCrates > 0 || $netBottles > 0) {
                    $stmtReduceEmpty->execute([$netCrates, $netBottles, $pkgId]);
                    $stmtEmbCompensate->execute([
                        $pkgId,
                        $tourneeId,
                        $netCrates,
                        $netBottles,
                        "Annulation retour emballages décharge $reference (Réouverture)",
                        $userId
                    ]);
                }
            }

            // 4. Clean up road expenses created during this décharge
            $this->db->prepare("
                DELETE FROM expenses 
                WHERE tournee_id = ?
            ")->execute([$tourneeId]);

            // 4b. Reverse any Avoirs created during décharge of this tournee
            $stmtTourneeSales = $this->db->prepare("SELECT id FROM sales WHERE tournee_id = ?");
            $stmtTourneeSales->execute([$tourneeId]);
            $tSaleIds = $stmtTourneeSales->fetchAll(\PDO::FETCH_COLUMN);
            if (!empty($tSaleIds)) {
                $inClause = implode(',', array_fill(0, count($tSaleIds), '?'));
                $this->db->prepare("DELETE FROM client_payments WHERE reference IN ($inClause)")->execute($tSaleIds);
            }

            // 5. Update Tournée Header: revert to 'En_Route'
            $this->db->prepare("
                UPDATE tournees 
                SET status = 'En_Route',
                    total_sold_amount = 0,
                    total_cash_collected = 0,
                    total_expenses = 0,
                    cash_deposited = 0,
                    cash_shortage = 0,
                    closed_at = NULL,
                    notes = CONCAT(COALESCE(notes, ''), ' [Décharge réouverte le ', NOW(), ' par User #', ?, ' : ', ?, ']')
                WHERE id = ?
            ")->execute([$userId, $reason, $tourneeId]);

            // 5b. Reset items counters in tournee_items
            $this->db->prepare("
                UPDATE tournee_items 
                SET qty_sold = 0,
                    qty_returned = 0,
                    qty_shortage = 0
                WHERE tournee_id = ?
            ")->execute([$tourneeId]);

            // 6. Audit Log
            \App\Core\Helper::logAudit('REOPEN_TOURNEE', 'Tournees', $tourneeId, "Réouverture de la décharge $reference : $reason");

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function cancelTourneeComplete($tourneeId, $userId = 1, $reason = '') {
        $this->db->beginTransaction();
        try {
            $tournee = $this->getById($tourneeId);
            if (!$tournee) {
                throw new \Exception("Tournée introuvable.");
            }
            if ($tournee['status'] === 'Annulee') {
                throw new \Exception("Cette tournée est déjà annulée.");
            }

            $reference = $tournee['reference'];

            // 1. If tournee was closed, undo all evening decharge movements (using net active balance)
            if ($tournee['status'] === 'Cloturee') {
                // Invert Net Cash Deposit
                $stmtNetCash = $this->db->prepare("
                    SELECT cash_account_id,
                           SUM(amount_in - amount_out) as net_cash
                    FROM cash_transactions
                    WHERE source_id = ? AND transaction_type IN ('Deposit', 'Withdrawal')
                    GROUP BY cash_account_id
                    HAVING net_cash > 0
                ");
                $stmtNetCash->execute([$tourneeId]);
                $cashRows = $stmtNetCash->fetchAll();

                foreach ($cashRows as $cr) {
                    $netAmount = floatval($cr['net_cash']);
                    if ($netAmount > 0) {
                        $this->db->prepare("
                            INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id)
                            VALUES (NOW(), 'Withdrawal', ?, ?, ?, 0.00, ?, ?)
                        ")->execute([
                            $tourneeId,
                            "Annulation versement décharge tournée $reference (Annulation totale)" . (!empty($reason) ? " - Motif: $reason" : ""),
                            $cr['cash_account_id'],
                            $netAmount,
                            $userId
                        ]);
                    }
                }

                // Invert Net Unsold Drinks Returned to Warehouse
                $stmtNetStock = $this->db->prepare("
                    SELECT product_id, format_id, format_type, unit_price,
                           SUM(CASE WHEN source_type = 'TourneeReturn' THEN stock_equivalent ELSE -stock_equivalent END) as net_returned_equiv,
                           SUM(CASE WHEN source_type = 'TourneeReturn' THEN quantity ELSE -quantity END) as net_returned_qty
                    FROM stock_movements
                    WHERE source_id = ? AND source_type IN ('TourneeReturn', 'TourneeReturnReversal')
                    GROUP BY product_id, format_id, format_type, unit_price
                    HAVING net_returned_equiv > 0
                ");
                $stmtNetStock->execute([$tourneeId]);
                $stockRows = $stmtNetStock->fetchAll();

                $stmtReversalStock = $this->db->prepare("
                    INSERT INTO stock_movements (movement_date, product_id, format_id, format_type, movement_type_id, source_type, source_id, quantity, stock_equivalent, unit_price, reference, user_id)
                    VALUES (CURRENT_DATE, ?, ?, ?, 6, 'TourneeReturnReversal', ?, ?, ?, ?, ?, ?)
                ");
                foreach ($stockRows as $sr) {
                    $stmtReversalStock->execute([
                        $sr['product_id'],
                        intval($sr['format_id'] ?: 1),
                        $sr['format_type'],
                        $tourneeId,
                        $sr['net_returned_qty'],
                        $sr['net_returned_equiv'],
                        $sr['unit_price'],
                        "Annulation retour invendus décharge $reference",
                        $userId
                    ]);
                }

                // Invert Net Returned Empty Crates
                $stmtNetEmb = $this->db->prepare("
                    SELECT packaging_type_id,
                           SUM(crates_in - crates_out) as net_crates,
                           SUM(bottles_in - bottles_out) as net_bottles
                    FROM emballage_movements
                    WHERE reference_id = ? AND movement_type IN ('Route_Return', 'Adjustment_Minus')
                    GROUP BY packaging_type_id
                    HAVING net_crates > 0 OR net_bottles > 0
                ");
                $stmtNetEmb->execute([$tourneeId]);
                $embRows = $stmtNetEmb->fetchAll();

                $stmtReduceEmpty = $this->db->prepare("
                    UPDATE emballage_stock 
                    SET empty_crates = GREATEST(0, empty_crates - ?),
                        loose_bottles = GREATEST(0, loose_bottles - ?)
                    WHERE packaging_type_id = ?
                ");

                $stmtEmbCompensate = $this->db->prepare("
                    INSERT INTO emballage_movements (packaging_type_id, movement_type, reference_id, crates_in, crates_out, bottles_in, bottles_out, notes, created_by)
                    VALUES (?, 'Adjustment_Minus', ?, 0, ?, 0, ?, ?, ?)
                ");
                foreach ($embRows as $er) {
                    $pkgId = intval($er['packaging_type_id']);
                    $netCrates = intval($er['net_crates']);
                    $netBottles = intval($er['net_bottles']);
                    if ($netCrates > 0 || $netBottles > 0) {
                        $stmtReduceEmpty->execute([$netCrates, $netBottles, $pkgId]);
                        $stmtEmbCompensate->execute([$pkgId, $tourneeId, $netCrates, $netBottles, "Annulation retour emballages tournée $reference (Annulation totale)", $userId]);
                    }
                }

                // Delete road expenses
                $this->db->prepare("DELETE FROM expenses WHERE tournee_id = ?")->execute([$tourneeId]);
            }

            // 2. Cascade cancel all linked carnet sales (using route-safe logic: no depot stock/cash movement)
            $stmtSales = $this->db->prepare("SELECT id FROM sales WHERE tournee_id = ? AND status = 'Valid'");
            $stmtSales->execute([$tourneeId]);
            $validSales = $stmtSales->fetchAll();

            $vModel = new \App\Models\VentesModel();
            foreach ($validSales as $s) {
                $vModel->cancelSale($s['id'], $userId, "Annulation automatique suite à annulation totale tournée $reference : $reason", true);
            }

            // 3. Reintegrate all items loaded in the morning back into warehouse stock (IN)
            $items = $this->getItems($tourneeId);
            $stmtReturnMov = $this->db->prepare("
                INSERT INTO stock_movements (movement_date, product_id, format_id, format_type, movement_type_id, source_type, source_id, quantity, stock_equivalent, unit_price, reference, user_id)
                VALUES (CURRENT_DATE, ?, ?, ?, 10, 'TourneeCancellation', ?, ?, ?, ?, ?, ?)
            ");

            foreach ($items as $it) {
                $hasDemi = !empty($it['has_demi']) || ($it['format_type'] === 'demi');
                $equiv = floatval($it['stock_equivalent'] ?: ($it['qty_loaded'] + ($hasDemi ? 0.5 : 0.0)));
                $stmtReturnMov->execute([
                    $it['product_id'],
                    intval($it['format_id'] ?: 1),
                    $it['format_type'],
                    $tourneeId,
                    $it['qty_loaded'],
                    $equiv,
                    $it['unit_price'],
                    "Restitution chargement matin tournée $reference (Annulation totale)",
                    $userId
                ]);
            }

            // 4. Update Tournée Header to 'Annulee'
            $this->db->prepare("
                UPDATE tournees 
                SET status = 'Annulee',
                    total_sold_amount = 0,
                    total_cash_collected = 0,
                    total_expenses = 0,
                    cash_deposited = 0,
                    cash_shortage = 0,
                    notes = CONCAT(COALESCE(notes, ''), ' [Annulation totale le ', NOW(), ' par User #', ?, ' : ', ?, ']')
                WHERE id = ?
            ")->execute([$userId, $reason, $tourneeId]);

            // 5. Audit Log
            \App\Core\Helper::logAudit('CANCEL_TOURNEE_FULL', 'Tournees', $tourneeId, "Annulation totale tournée $reference : $reason");

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function cancelTournee($tourneeId, $userId = 1, $reason = '') {
        return $this->cancelTourneeComplete($tourneeId, $userId, $reason);
    }
}
