<?php
namespace App\Models;
use App\Core\Database;
use PDO;

class VentesModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getAll() {
        return $this->getFiltered([]);
    }

    public function getFiltered($filters = []) {
        $sql = "
            SELECT 
                v.*, 
                c.name as client_name, 
                pm.name as payment_method_name,
                pm.type as payment_method_type,
                ca.name as cash_account_name,
                u.username,
                t.reference as tournee_reference,
                t.status as tournee_status,
                COUNT(si.id) as item_count,
                COALESCE(GROUP_CONCAT(CONCAT(p.name, ' (', si.quantity, ' ', CASE WHEN si.format_type = 'demi' THEN 'Demi' ELSE 'Casier' END, ')') SEPARATOR ', '), 'Aucun article') as products_summary
            FROM sales v
            LEFT JOIN clients c ON v.client_id = c.id
            LEFT JOIN payment_methods pm ON v.payment_method_id = pm.id
            LEFT JOIN cash_accounts ca ON v.cash_account_id = ca.id
            LEFT JOIN users u ON v.user_id = u.id
            LEFT JOIN tournees t ON v.tournee_id = t.id
            LEFT JOIN sale_items si ON v.id = si.sale_id
            LEFT JOIN products p ON si.product_id = p.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (v.id LIKE ? OR v.reference LIKE ? OR c.name LIKE ? OR v.notes LIKE ? OR t.reference LIKE ?)";
            $term = '%' . trim($filters['search']) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['client_id'])) {
            $sql .= " AND v.client_id = ?";
            $params[] = intval($filters['client_id']);
        }

        if (!empty($filters['sale_type'])) {
            $sql .= " AND v.sale_type = ?";
            $params[] = trim($filters['sale_type']);
        }

        if (!empty($filters['tournee_id'])) {
            $sql .= " AND v.tournee_id = ?";
            $params[] = intval($filters['tournee_id']);
        }

        if (!empty($filters['payment_mode'])) {
            if ($filters['payment_mode'] === 'cash') {
                $sql .= " AND v.payment_method_id != 5";
            } elseif ($filters['payment_mode'] === 'credit') {
                $sql .= " AND v.payment_method_id = 5";
            }
        }

        if (!empty($filters['cash_account_id'])) {
            $sql .= " AND v.cash_account_id = ?";
            $params[] = intval($filters['cash_account_id']);
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND v.sale_date >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND v.sale_date <= ?";
            $params[] = $filters['end_date'];
        }

        $sql .= "
            GROUP BY v.id, v.sale_date, v.client_id, v.total_amount, v.payment_method_id, v.cash_account_id, v.reference, v.notes, v.user_id, v.tournee_id, v.sale_type, v.status, v.created_at, c.name, pm.name, pm.type, ca.name, u.username, t.reference, t.status
            ORDER BY v.sale_date DESC, v.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getSaleWithItems($id) {
        $stmt = $this->db->prepare("
            SELECT 
                v.*, 
                c.name as client_name, 
                c.phone as client_phone, 
                c.address as client_address,
                pm.name as payment_method_name,
                pm.type as payment_method_type,
                ca.name as cash_account_name,
                u.username,
                t.reference as tournee_reference,
                t.status as tournee_status
            FROM sales v
            LEFT JOIN clients c ON v.client_id = c.id
            LEFT JOIN payment_methods pm ON v.payment_method_id = pm.id
            LEFT JOIN cash_accounts ca ON v.cash_account_id = ca.id
            LEFT JOIN users u ON v.user_id = u.id
            LEFT JOIN tournees t ON v.tournee_id = t.id
            WHERE v.id = ?
        ");
        $stmt->execute([$id]);
        $sale = $stmt->fetch();
        if (!$sale) return null;

        $stmtItems = $this->db->prepare("
            SELECT 
                si.*,
                p.name as product_name,
                p.short_code,
                p.packaging_type_id,
                p.factor,
                f.name as format_name,
                c.name as category_name
            FROM sale_items si
            LEFT JOIN products p ON si.product_id = p.id
            LEFT JOIN formats f ON p.format_id = f.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE si.sale_id = ?
            ORDER BY si.id ASC
        ");
        $stmtItems->execute([$id]);
        $sale['items'] = $stmtItems->fetchAll();

        return $sale;
    }

    public function getClients() {
        return $this->db->query("
            SELECT 
                c.*,
                COALESCE((SELECT SUM(CASE WHEN v.amount_due > 0 THEN v.amount_due WHEN v.payment_method_id = 5 THEN v.total_amount ELSE 0 END) FROM sales v WHERE v.client_id = c.id AND v.status = 'Valid'), 0) -
                COALESCE((SELECT SUM(p.amount) FROM client_payments p WHERE p.client_id = c.id), 0) as current_debt
            FROM clients c
            ORDER BY c.name ASC
        ")->fetchAll();
    }

    public function getClient($clientId) {
        $stmt = $this->db->prepare("
            SELECT 
                c.*,
                COALESCE((SELECT SUM(CASE WHEN v.amount_due > 0 THEN v.amount_due WHEN v.payment_method_id = 5 THEN v.total_amount ELSE 0 END) FROM sales v WHERE v.client_id = c.id AND v.status = 'Valid'), 0) -
                COALESCE((SELECT SUM(p.amount) FROM client_payments p WHERE p.client_id = c.id), 0) as current_debt
            FROM clients c
            WHERE c.id = ?
        ");
        $stmt->execute([$clientId]);
        return $stmt->fetch();
    }

    public function getProducts() { 
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

    public function getProductStock($productId) {
        $stmt = $this->db->prepare("
            SELECT 
                p.*, 
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
            WHERE p.id = ?
            GROUP BY p.id, p.name, p.short_code, p.slug, p.category_id, p.format_id, p.is_returnable, p.cout_emballage, p.packaging_type_id, p.purchase_price, p.price_casier, p.price_demi, p.price_unite, p.alert_stock, p.factor, p.created_at, f.name, c.name, pt.name, pt.company, pt.color, pt.bottles_per_crate
        ");
        $stmt->execute([$productId]);
        return $stmt->fetch();
    }

    public function getCashAccountsWithBalances() {
        $sql = "
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
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getCashAccount($accountId) {
        $stmt = $this->db->prepare("
            SELECT 
                a.id,
                a.name,
                a.payment_method_id,
                pm.name as payment_method_name,
                COALESCE(SUM(t.amount_in), 0) - COALESCE(SUM(t.amount_out), 0) as current_balance
            FROM cash_accounts a
            LEFT JOIN payment_methods pm ON a.payment_method_id = pm.id
            LEFT JOIN cash_transactions t ON a.id = t.cash_account_id
            WHERE a.id = ?
            GROUP BY a.id, a.name, a.payment_method_id, pm.name
        ");
        $stmt->execute([$accountId]);
        return $stmt->fetch();
    }

    private function generateSaleId() {
        $stmt = $this->db->query("SELECT id FROM sales ORDER BY id DESC LIMIT 1");
        $last = $stmt->fetchColumn();
        if ($last && preg_match('/V(\d+)/', $last, $matches)) {
            $num = intval($matches[1]) + 1;
            return 'V' . str_pad($num, 5, '0', STR_PAD_LEFT);
        }
        return 'V00001';
    }

    private function generateClientPaymentId() {
        $stmt = $this->db->query("SELECT id FROM client_payments ORDER BY id DESC LIMIT 1");
        $last = $stmt->fetchColumn();
        if ($last && preg_match('/RC(\d+)/', $last, $matches)) {
            $num = intval($matches[1]) + 1;
            return 'RC' . str_pad($num, 5, '0', STR_PAD_LEFT);
        }
        return 'RC00001';
    }

    public function add($data) {
        $userId = intval($data['user_id'] ?? ($_SESSION['user']['id'] ?? 1));
        
        // 1. Extract Items List (Multi-item support with single item fallback)
        $rawItems = $data['items'] ?? [];
        if (empty($rawItems) && !empty($data['product_id'])) {
            $rawItems = [[
                'product_id' => $data['product_id'],
                'format_type' => $data['format_type'] ?? 'casier',
                'quantity' => $data['quantity'] ?? 1,
                'unit_price' => $data['unit_price'] ?? 0
            ]];
        }

        if (empty($rawItems)) {
            throw new \Exception("Veuillez ajouter au moins un produit à la facture de vente.");
        }

        // Validate each item and calculate stock equivalents & total
        $processedItems = [];
        $totalAmount = 0;
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
                $product = $this->getProductStock($prodId);
                if (!$product) {
                    throw new \Exception("Article à la ligne " . ($idx + 1) . " introuvable.");
                }
                $productsStockMap[$prodId] = $product;
            }
            $product = $productsStockMap[$prodId];

            if ($hasDemi) {
                if ($demiUnitPrice <= 0) {
                    $demiUnitPrice = floatval($product['price_demi'] ?? ($unitPrice > 0 ? round($unitPrice / 2) : 0));
                } elseif ($unitPrice != floatval($product['price_casier'] ?? 0) && $demiUnitPrice == floatval($product['price_demi'] ?? 0)) {
                    $demiUnitPrice = round($unitPrice / 2);
                }
            }

            if ($qty > 0 && $unitPrice <= 0) {
                throw new \Exception("Le prix unitaire du casier à la ligne " . ($idx + 1) . " doit être supérieur à 0 FCFA.");
            }
            if ($qty == 0 && $hasDemi && $demiUnitPrice <= 0) {
                throw new \Exception("Le prix unitaire du demi à la ligne " . ($idx + 1) . " doit être supérieur à 0 FCFA.");
            }

            $stockEquivalent = $qty + ($hasDemi ? 0.5 : 0.0);
            $requestedStockByProduct[$prodId] = ($requestedStockByProduct[$prodId] ?? 0) + $stockEquivalent;

            $lineTotal = ($qty * $unitPrice) + ($hasDemi ? $demiUnitPrice : 0.0);
            $totalAmount += $lineTotal;

            $isReturnable = intval($product['is_returnable'] ?? 1);
            $factor = max(1, intval($product['factor'] ?: 12));
            $cratesOut = 0;
            $bottlesOut = 0;
            $cratesReturned = 0;
            $bottlesReturned = 0;
            $netCratesDue = 0;
            $netLooseBottlesDue = 0;

            if ($isReturnable) {
                $cratesOut = intval($qty);
                $bottlesOut = (intval($qty) * $factor) + ($hasDemi ? intval($factor / 2) : 0);
                $cratesReturned = isset($item['crates_returned']) ? intval($item['crates_returned']) : 0;
                $bottlesReturned = isset($item['bottles_returned']) ? intval($item['bottles_returned']) : 0;
                $totalBottlesRestituted = ($cratesReturned * $factor) + $bottlesReturned;
                $missingBottles = max(0, $bottlesOut - $totalBottlesRestituted);
                $netCratesDue = floor($missingBottles / $factor);
                $netLooseBottlesDue = $missingBottles % $factor;
            }

            $processedItems[] = [
                'product_id' => $product['id'],
                'product_name' => $product['name'],
                'packaging_type_id' => $product['packaging_type_id'],
                'format_id' => $product['format_id'],
                'format_type' => $hasDemi ? ($qty > 0 ? 'mixte' : 'demi') : 'casier',
                'has_demi' => $hasDemi,
                'is_returnable' => $isReturnable,
                'crates_out' => $cratesOut,
                'bottles_out' => $bottlesOut,
                'crates_returned' => $cratesReturned,
                'bottles_returned' => $bottlesReturned,
                'net_crates_due' => $netCratesDue,
                'net_bottles_due' => $netLooseBottlesDue,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'demi_unit_price' => $demiUnitPrice,
                'total_price' => $lineTotal,
                'stock_equivalent' => $stockEquivalent,
                'purchase_price' => $product['purchase_price'],
                'update_catalog_price' => !empty($item['update_catalog_price'])
            ];
        }

        if (empty($processedItems)) {
            throw new \Exception("Aucun article valide dans la facture de vente.");
        }

        $tourneeId = !empty($data['tournee_id']) ? intval($data['tournee_id']) : null;
        $saleType = (!empty($data['sale_type']) && in_array($data['sale_type'], ['comptoir', 'route'])) ? $data['sale_type'] : ($tourneeId ? 'route' : 'comptoir');

        // Validate aggregated stock per product across all rows
        if ($tourneeId) {
            // Check truck loaded stock
            foreach ($requestedStockByProduct as $pId => $totalReq) {
                $stmtTourneeItem = $this->db->prepare("
                    SELECT ti.qty_loaded, ti.has_demi,
                           COALESCE((
                               SELECT SUM(si.stock_equivalent) 
                               FROM sale_items si 
                               JOIN sales s ON si.sale_id = s.id 
                               WHERE s.tournee_id = ti.tournee_id 
                                 AND s.status IN ('En_Route', 'Valid') 
                                 AND si.product_id = ti.product_id
                           ), 0) as already_sold
                    FROM tournee_items ti
                    WHERE ti.tournee_id = ? AND ti.product_id = ?
                ");
                $stmtTourneeItem->execute([$tourneeId, $pId]);
                $tItem = $stmtTourneeItem->fetch();
                if (!$tItem) {
                    throw new \Exception("Le produit '" . htmlspecialchars($productsStockMap[$pId]['name']) . "' n'a pas été chargé sur le camion de cette tournée.");
                }
                $loadedEquiv = floatval($tItem['qty_loaded']) + (!empty($tItem['has_demi']) ? 0.5 : 0.0);
                $truckAvailable = $loadedEquiv - floatval($tItem['already_sold']);
                if ($totalReq > $truckAvailable) {
                    throw new \Exception("Stock camion insuffisant pour '" . htmlspecialchars($productsStockMap[$pId]['name']) . "'. Demandé sur cette facture : {$totalReq} casier(s), Restant sur camion : {$truckAvailable} casier(s).");
                }
            }
        } else {
            // Check warehouse stock for regular counter sales
            foreach ($requestedStockByProduct as $pId => $totalReq) {
                $curStock = floatval($productsStockMap[$pId]['current_stock']);
                if ($curStock <= 0) {
                    throw new \Exception("Rupture de stock pour '" . htmlspecialchars($productsStockMap[$pId]['name']) . "' (Stock: 0).");
                }
                if ($totalReq > $curStock) {
                    throw new \Exception("Stock magasin insuffisant pour '" . htmlspecialchars($productsStockMap[$pId]['name']) . "'. Demandé au total : {$totalReq} casier(s), Disponible : {$curStock} casier(s).");
                }
            }
        }

        $client = $this->getClient($data['client_id']);
        if (!$client) {
            throw new \Exception("Client sélectionné introuvable.");
        }

        $discountAmount = max(0, floatval($data['discount_amount'] ?? 0));
        $subTotal = $totalAmount;
        $grossNet = max(0, $subTotal - $discountAmount);

        // Check client's existing avoir
        $currentDebt = floatval($client['current_debt'] ?? 0);
        $availableAvoir = ($currentDebt < 0) ? abs($currentDebt) : 0.0;
        $requestedAvoir = max(0, floatval($data['avoir_used'] ?? 0));
        $avoirUsed = min($requestedAvoir, $availableAvoir, $grossNet);

        $netPayableCash = max(0, $grossNet - $avoirUsed);

        $settlementType = $data['settlement_type'] ?? 'cash';
        $isFullCredit = ($settlementType === 'credit' || (isset($data['payment_method_id']) && $data['payment_method_id'] == 5));

        if ($isFullCredit) {
            $amountPaid = 0.0;
            $amountDue = $grossNet;
            $paymentMethodId = 5;
            $cashAccountId = null;
        } else {
            $rawAmountPaid = is_null($data['amount_paid'] ?? null) ? $netPayableCash : max(0, floatval($data['amount_paid']));
            $amountPaid = min($netPayableCash, $rawAmountPaid);
            $excessCash = max(0, $rawAmountPaid - $netPayableCash);
            $cashShortage = max(0, $netPayableCash - $amountPaid);
            // In ledger math: amount_due = avoirUsed + cashShortage
            // (solde_du = amount_due - previous_payments = cashShortage - remaining_avoir)
            $amountDue = $avoirUsed + $cashShortage;
            $cashAccountId = intval($data['cash_account_id'] ?? 0);
            $accountInfo = $this->getCashAccount($cashAccountId);
            if (!$accountInfo && ($amountPaid > 0 || $excessCash > 0) && empty($tourneeId)) throw new \Exception("Compte d'encaissement invalide.");
            $paymentMethodId = ($cashShortage > 0) ? ($amountPaid > 0 ? 1 : 5) : ($accountInfo['payment_method_id'] ?? 1);
        }

        if ($isFullCredit || ($amountPaid < $netPayableCash)) {
            $realCashDebt = $isFullCredit ? $netPayableCash : max(0, $netPayableCash - $amountPaid);
            if (floatval($client['max_credit']) <= 0 && $realCashDebt > 0) throw new \Exception("Client en 'Comptant Uniquement'.");
            if (((max(0, $currentDebt)) + $realCashDebt) > floatval($client['max_credit'])) throw new \Exception("Plafond de crédit dépassé.");
        }

        $this->db->beginTransaction();
        try {
            $id = $this->generateSaleId();

            $initialStatus = !empty($tourneeId) ? 'En_Route' : 'Valid';

            $stmt = $this->db->prepare("
                INSERT INTO sales (id, sale_date, client_id, total_amount, discount_amount, avoir_used, amount_paid, amount_due, excess_amount, payment_method_id, cash_account_id, reference, notes, user_id, tournee_id, sale_type, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id, $data['sale_date'], $client['id'], $subTotal, $discountAmount, $avoirUsed, $amountPaid, $amountDue, $excessCash,
                $paymentMethodId, $cashAccountId, !empty($data['reference']) ? $data['reference'] : $id, $data['notes'] ?? '', $userId,
                $tourneeId, $saleType, $initialStatus
            ]);


            $stmtItem = $this->db->prepare("
                INSERT INTO sale_items (sale_id, product_id, format_type, has_demi, quantity, unit_price, demi_unit_price, total_price, stock_equivalent, is_returnable, crates_out, bottles_out, crates_returned, bottles_returned)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmtStockMov = $this->db->prepare("
                INSERT INTO stock_movements (movement_date, product_id, format_id, format_type, movement_type_id, source_type, source_id, quantity, stock_equivalent, unit_price, reference, user_id)
                VALUES (?, ?, ?, ?, 2, 'Sale', ?, ?, ?, ?, ?, ?)
            ");

            $stmtFetchCurDebt = $this->db->prepare("SELECT crates_due, loose_bottles_due FROM client_emballage_debts WHERE client_id = ? AND packaging_type_id = ?");
            $stmtUpsertDebt = $this->db->prepare("
                INSERT INTO client_emballage_debts (client_id, packaging_type_id, crates_due, loose_bottles_due)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE crates_due = VALUES(crates_due), loose_bottles_due = VALUES(loose_bottles_due)
            ");

            foreach ($processedItems as $item) {
                $stmtItem->execute([
                    $id, $item['product_id'], $item['format_type'], $item['has_demi'], $item['quantity'],
                    $item['unit_price'], $item['demi_unit_price'], $item['total_price'], $item['stock_equivalent'], $item['is_returnable'],
                    $item['crates_out'], $item['bottles_out'], $item['crates_returned'], $item['bottles_returned']
                ]);

                // Deduct from warehouse stock movement ONLY if NOT a route sale
                // (For route sales, stock already left the warehouse during morning truck loading)
                if (empty($tourneeId)) {
                    $stmtStockMov->execute([
                        $data['sale_date'], $item['product_id'], $item['format_id'] ?? 1, $item['format_type'],
                        $id, $item['quantity'], $item['stock_equivalent'], $item['unit_price'], 'Vente ' . $id, $userId
                    ]);
                }

                if (!empty($item['update_catalog_price'])) {
                    if ($item['unit_price'] > 0) {
                        $this->db->prepare("UPDATE products SET price_casier = ? WHERE id = ?")->execute([$item['unit_price'], $item['product_id']]);
                    }
                    if ($item['demi_unit_price'] > 0) {
                        $this->db->prepare("UPDATE products SET price_demi = ? WHERE id = ?")->execute([$item['demi_unit_price'], $item['product_id']]);
                    }
                }

                if ($item['is_returnable'] && !empty($item['packaging_type_id'])) {
                    $pkgTypeId = intval($item['packaging_type_id']);
                    $productStock = $this->getProductStock($item['product_id']);
                    $factor = max(1, intval($productStock['factor'] ?: 12));

                    // For counter sales, returned empties go directly into depot emballage stock
                    if (empty($tourneeId) && ($item['crates_returned'] > 0 || $item['bottles_returned'] > 0)) {
                        $this->db->prepare("UPDATE emballage_stock SET empty_crates = empty_crates + ?, loose_bottles = loose_bottles + ? WHERE packaging_type_id = ?")
                                 ->execute([$item['crates_returned'], $item['bottles_returned'], $pkgTypeId]);
                        $this->db->prepare("INSERT INTO emballage_movements (packaging_type_id, movement_type, reference_id, client_id, crates_in, bottles_in, notes, created_by) VALUES (?, 'Client_Return', ?, ?, ?, ?, ?, ?)")
                                 ->execute([$pkgTypeId, $id, $client['id'], $item['crates_returned'], $item['bottles_returned'], "Entrée casiers vides rapportés au comptoir (Vente $id)", $userId]);
                    }

                    // Client packaging debt: only commit immediately for COUNTER sales
                    // (For route sales, packaging debt stays staged in sale_items until tournee décharge/clôture)
                    if (empty($tourneeId) && ($item['net_crates_due'] > 0 || $item['net_bottles_due'] > 0)) {
                        $stmtFetchCurDebt->execute([$client['id'], $pkgTypeId]);
                        $curDebt = $stmtFetchCurDebt->fetch();
                        $curTotalBtls = (intval($curDebt['crates_due'] ?? 0) * $factor) + intval($curDebt['loose_bottles_due'] ?? 0);
                        $itemTotalDue = ($item['net_crates_due'] * $factor) + $item['net_bottles_due'];
                        $finalTotalBtls = $curTotalBtls + $itemTotalDue;
                        $stmtUpsertDebt->execute([$client['id'], $pkgTypeId, floor($finalTotalBtls / $factor), $finalTotalBtls % $factor]);

                        $this->db->prepare("INSERT INTO emballage_movements (packaging_type_id, movement_type, reference_id, client_id, crates_out, bottles_out, notes, created_by) VALUES (?, 'Sale_Drink', ?, ?, ?, ?, ?, ?)")
                                 ->execute([$pkgTypeId, $id, $client['id'], $item['net_crates_due'], $item['net_bottles_due'], "Sortie emballages non restitués (Dette Vente $id)", $userId]);
                    }
                }
            }

            // Cash transaction for counter sales: deposited directly
            // (For route sales, cash collected by driver is deposited during the evening Décharge)
            if (empty($tourneeId) && $amountPaid > 0 && $cashAccountId) {
                $this->db->prepare("INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id) VALUES (?, 'Sale', ?, ?, ?, ?, 0.00, ?)")
                         ->execute([$data['sale_date'] . ' ' . date('H:i:s'), $id, 'Vente ' . $id . ' (' . $client['name'] . ')', $cashAccountId, $amountPaid, $userId]);
            }

            // Automatic Avoir creation if client paid excess cash above net payable
            if (empty($tourneeId) && !empty($excessCash) && $excessCash > 0 && $cashAccountId) {
                $advId = $this->generateClientPaymentId();
                $advNotes = "Trop-perçu vente {$id} crédité en Avoir Client (Net Facture: " . number_format($netPayableCash, 0, ',', ' ') . " FCFA, Reçu: " . number_format($rawAmountPaid, 0, ',', ' ') . " FCFA)";
                $this->db->prepare("
                    INSERT INTO client_payments (id, payment_date, client_id, amount, payment_method_id, cash_account_id, reference, notes, user_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ")->execute([
                    $advId, $data['sale_date'], $client['id'], $excessCash,
                    $paymentMethodId, $cashAccountId, $id, $advNotes, $userId
                ]);

                $this->db->prepare("INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id) VALUES (?, 'ClientPayment', ?, ?, ?, ?, 0.00, ?)")
                         ->execute([$data['sale_date'] . ' ' . date('H:i:s'), $advId, "Avoir Client automatique - Trop-perçu Vente {$id} ({$client['name']})", $cashAccountId, $excessCash, $userId]);
            }

            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function addRetailSale($data) {
        $userId = intval($data['user_id'] ?? ($_SESSION['user']['id'] ?? 1));
        $rawItems = $data['items'] ?? [];
        if (empty($rawItems)) {
            throw new \Exception("Veuillez ajouter au moins une boisson au panier de vente au détail.");
        }

        $processedItems = [];
        $totalAmount = 0;

        foreach ($rawItems as $idx => $item) {
            $prodId = intval($item['product_id'] ?? 0);
            $qty = floatval($item['quantity'] ?? 0);
            $unitPrice = floatval($item['unit_price'] ?? 0);
            $emballageMode = $item['emballage_mode'] ?? 'sur_place';

            if ($prodId <= 0) continue;
            if ($qty <= 0) {
                throw new \Exception("La quantité de bouteilles à la ligne " . ($idx + 1) . " doit être supérieure à 0.");
            }
            if ($unitPrice <= 0) {
                throw new \Exception("Le prix unitaire par bouteille à la ligne " . ($idx + 1) . " doit être supérieur à 0 FCFA.");
            }

            $product = $this->getProductStock($prodId);
            if (!$product) {
                throw new \Exception("Article à la ligne " . ($idx + 1) . " introuvable.");
            }

            $factor = max(1, intval($product['factor'] ?: 12));
            $stockEquivalent = round($qty / $factor, 4);
            $currentStock = floatval($product['current_stock']);

            if ($currentStock <= 0) {
                throw new \Exception("Rupture de stock pour '" . htmlspecialchars($product['name']) . "' (Stock: 0).");
            }
            if ($stockEquivalent > $currentStock) {
                $fmtCur = number_format($currentStock, 2, ',', ' ');
                $fmtReq = number_format($stockEquivalent, 2, ',', ' ');
                throw new \Exception("Stock insuffisant pour '" . htmlspecialchars($product['name']) . "'. Demandé : {$qty} btl(s) ({$fmtReq} casier), Disponible : {$fmtCur} casier(s).");
            }

            $lineTotal = $qty * $unitPrice;
            $totalAmount += $lineTotal;

            $isReturnable = intval($product['is_returnable'] ?? 1);
            $cratesOut = 0;
            $bottlesOut = 0;
            $cratesReturned = 0;
            $bottlesReturned = 0;
            $netCratesDue = 0;
            $netLooseBottlesDue = 0;

            if ($isReturnable) {
                $bottlesOut = intval($qty);
                if ($emballageMode === 'sur_place') {
                    $bottlesReturned = intval($qty);
                } elseif ($emballageMode === 'echange') {
                    $bottlesReturned = min(intval($qty), max(0, intval($item['bottles_returned'] ?? $qty)));
                    $netLooseBottlesDue = max(0, intval($qty) - $bottlesReturned);
                } else {
                    $bottlesReturned = 0;
                    $netLooseBottlesDue = intval($qty);
                }
            }

            $processedItems[] = [
                'product_id' => $product['id'],
                'product_name' => $product['name'],
                'packaging_type_id' => $product['packaging_type_id'],
                'format_id' => $product['format_id'],
                'format_type' => 'unite',
                'is_returnable' => $isReturnable,
                'factor' => $factor,
                'crates_out' => $cratesOut,
                'bottles_out' => $bottlesOut,
                'crates_returned' => $cratesReturned,
                'bottles_returned' => $bottlesReturned,
                'net_crates_due' => $netCratesDue,
                'net_loose_bottles_due' => $netLooseBottlesDue,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total_price' => $lineTotal,
                'stock_equivalent' => $stockEquivalent,
                'purchase_price' => $product['purchase_price'],
                'update_catalog_price' => !empty($item['update_catalog_price'])
            ];
        }

        if (empty($processedItems)) {
            throw new \Exception("Aucun article valide dans la vente au détail.");
        }

        $client = $this->getClient($data['client_id']);
        if (!$client) {
            throw new \Exception("Client sélectionné introuvable.");
        }

        $subTotal = $totalAmount;
        $discountAmount = max(0, floatval($data['discount_amount'] ?? 0));
        if ($discountAmount > $subTotal) $discountAmount = $subTotal;
        $grossNet = max(0, $subTotal - $discountAmount);

        $clientDebtBalance = floatval($client['solde_du'] ?? 0);
        $clientAvailableAvoir = ($clientDebtBalance < 0) ? abs($clientDebtBalance) : 0.00;

        $requestedAvoir = max(0, floatval($data['avoir_used'] ?? 0));
        $avoirUsed = min($requestedAvoir, $clientAvailableAvoir, $grossNet);

        $netPayableCash = max(0, $grossNet - $avoirUsed);

        $isCredit = (($data['settlement_type'] ?? '') === 'credit' || (isset($data['payment_method_id']) && $data['payment_method_id'] == 5));
        $paymentMethodId = $isCredit ? 5 : intval($data['payment_method_id'] ?? 1);

        if ($isCredit) {
            $amountPaid = 0.00;
            $amountDue = $grossNet;
            $excessCash = 0.00;
        } else {
            $cashAccountId = !empty($data['cash_account_id']) ? intval($data['cash_account_id']) : 1;
            $rawAmountPaid = is_null($data['amount_paid'] ?? null) ? $netPayableCash : max(0, floatval($data['amount_paid']));
            $amountPaid = min($netPayableCash, $rawAmountPaid);
            $excessCash = max(0, $rawAmountPaid - $netPayableCash);
            $cashShortage = max(0, $netPayableCash - $amountPaid);

            $amountDue = $avoirUsed + $cashShortage;
        }

        $futureClientDebt = $isCredit ? ($clientDebtBalance + $grossNet) : ($clientDebtBalance + ($amountDue - $avoirUsed));
        $maxCredit = floatval($client['max_credit'] ?? 0);
        if ($isCredit && $maxCredit > 0 && $futureClientDebt > $maxCredit) {
            $fmtDebt = number_format($futureClientDebt, 0, ',', ' ');
            $fmtMax = number_format($maxCredit, 0, ',', ' ');
            throw new \Exception("Plafond de crédit dépassé : Cette vente porterait la dette de " . htmlspecialchars($client['name']) . " à {$fmtDebt} FCFA (Plafond : {$fmtMax} FCFA).");
        }

        $id = $this->generateSaleId();
        $userId = $data['user_id'] ?? 1;

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO sales (id, sale_date, client_id, total_amount, discount_amount, avoir_used, amount_paid, amount_due, excess_amount, payment_method_id, cash_account_id, reference, notes, user_id, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Valid')
            ");
            $stmt->execute([
                $id, $data['sale_date'], $client['id'], $subTotal, $discountAmount, $avoirUsed, $amountPaid, $amountDue, $excessCash,
                $paymentMethodId, $cashAccountId, !empty($data['reference']) ? $data['reference'] : $id, $data['notes'] ?? 'Vente au détail / Bouteilles', $userId
            ]);

            $stmtItem = $this->db->prepare("
                INSERT INTO sale_items (sale_id, product_id, format_type, quantity, unit_price, total_price, stock_equivalent, is_returnable, crates_out, bottles_out, crates_returned, bottles_returned)
                VALUES (?, ?, 'unite', ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmtStockMov = $this->db->prepare("
                INSERT INTO stock_movements (movement_date, product_id, format_id, format_type, movement_type_id, source_type, source_id, quantity, stock_equivalent, unit_price, reference, user_id)
                VALUES (?, ?, ?, 'unite', 2, 'Sale', ?, ?, ?, ?, ?, ?)
            ");

            $stmtFetchCurDebt = $this->db->prepare("SELECT crates_due, loose_bottles_due FROM client_emballage_debts WHERE client_id = ? AND packaging_type_id = ?");
            $stmtUpsertDebt = $this->db->prepare("
                INSERT INTO client_emballage_debts (client_id, packaging_type_id, crates_due, loose_bottles_due)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE crates_due = VALUES(crates_due), loose_bottles_due = VALUES(loose_bottles_due)
            ");

            foreach ($processedItems as $item) {
                $stmtItem->execute([
                    $id, $item['product_id'], $item['quantity'],
                    $item['unit_price'], $item['total_price'], $item['stock_equivalent'], $item['is_returnable'],
                    $item['crates_out'], $item['bottles_out'], $item['crates_returned'], $item['bottles_returned']
                ]);

                $stmtStockMov->execute([
                    $data['sale_date'], $item['product_id'], $item['format_id'] ?? 1,
                    $id, $item['quantity'], $item['stock_equivalent'], $item['unit_price'], "Vente Détail {$id} ({$item['quantity']} btls)", $userId
                ]);

                if (!empty($item['update_catalog_price'])) {
                    $this->db->prepare("UPDATE products SET price_unite = ? WHERE id = ?")
                             ->execute([$item['unit_price'], $item['product_id']]);
                }

                if ($item['is_returnable'] && !empty($item['packaging_type_id'])) {
                    $pkgTypeId = intval($item['packaging_type_id']);

                    if ($item['bottles_returned'] > 0) {
                        $this->db->prepare("UPDATE emballage_stock SET loose_bottles = loose_bottles + ? WHERE packaging_type_id = ?")
                                 ->execute([$item['bottles_returned'], $pkgTypeId]);
                        $this->db->prepare("INSERT INTO emballage_movements (packaging_type_id, movement_type, reference_id, client_id, bottles_in, notes, created_by) VALUES (?, 'Client_Return', ?, ?, ?, ?, ?)")
                                 ->execute([$pkgTypeId, $id, $client['id'], $item['bottles_returned'], "Entrée {$item['bottles_returned']} bouteilles vides vrac (Vente Détail $id)", $userId]);
                    }

                    if ($item['net_loose_bottles_due'] > 0) {
                        $stmtFetchCurDebt->execute([$client['id'], $pkgTypeId]);
                        $curDebt = $stmtFetchCurDebt->fetch();
                        $curCrates = intval($curDebt['crates_due'] ?? 0);
                        $curLoose = intval($curDebt['loose_bottles_due'] ?? 0);
                        $newLoose = $curLoose + $item['net_loose_bottles_due'];

                        $stmtUpsertDebt->execute([$client['id'], $pkgTypeId, $curCrates, $newLoose]);

                        $this->db->prepare("INSERT INTO emballage_movements (packaging_type_id, movement_type, reference_id, client_id, bottles_out, notes, created_by) VALUES (?, 'Sale_Drink', ?, ?, ?, ?, ?)")
                                 ->execute([$pkgTypeId, $id, $client['id'], $item['net_loose_bottles_due'], "Sortie bouteilles emportées non restituées (Vente Détail $id)", $userId]);
                    }
                }
            }

            if ($amountPaid > 0 && $cashAccountId) {
                $this->db->prepare("INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id) VALUES (?, 'Sale', ?, ?, ?, ?, 0.00, ?)")
                         ->execute([$data['sale_date'] . ' ' . date('H:i:s'), $id, 'Vente Détail ' . $id . ' (' . $client['name'] . ')', $cashAccountId, $amountPaid, $userId]);
            }

            // Automatic Avoir creation if client paid excess cash above net payable
            if (!empty($excessCash) && $excessCash > 0 && $cashAccountId) {
                $advId = $this->generateClientPaymentId();
                $advNotes = "Trop-perçu vente détail {$id} crédité en Avoir Client (Net Facture: " . number_format($netPayableCash, 0, ',', ' ') . " FCFA, Reçu: " . number_format($rawAmountPaid, 0, ',', ' ') . " FCFA)";
                $this->db->prepare("
                    INSERT INTO client_payments (id, payment_date, client_id, amount, payment_method_id, cash_account_id, reference, notes, user_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ")->execute([
                    $advId, $data['sale_date'], $client['id'], $excessCash,
                    $paymentMethodId, $cashAccountId, $id, $advNotes, $userId
                ]);

                $this->db->prepare("INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id) VALUES (?, 'ClientPayment', ?, ?, ?, ?, 0.00, ?)")
                         ->execute([$data['sale_date'] . ' ' . date('H:i:s'), $advId, "Avoir Client automatique - Trop-perçu Vente Détail {$id} ({$client['name']})", $cashAccountId, $excessCash, $userId]);
            }

            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function cancelSale($id, $userId = 1, $reason = '', $isCascadeFromTourneeCancellation = false) {
        $sale = $this->getSaleWithItems($id);
        if (!$sale || $sale['status'] === 'Cancelled') throw new \Exception("Facture invalide ou déjà annulée.");

        // Guard: Route sale from a closed tournee CANNOT be cancelled individually
        if (!empty($sale['tournee_id']) && !$isCascadeFromTourneeCancellation) {
            $tStatus = strtolower($sale['tournee_status'] ?? '');
            if (in_array($tStatus, ['cloturee', 'clôturée', 'closed'])) {
                $ref = !empty($sale['tournee_reference']) ? " (" . $sale['tournee_reference'] . ")" : "";
                throw new \Exception("Impossible d'annuler cette facture {$id} : elle est issue de la tournée{$ref} qui a déjà été clôturée, soldée et archivée. Les régularisations ultérieures doivent être traitées au dépôt par retour de marchandise ou avoir.");
            }
        }

        $alreadyInTx = $this->db->inTransaction();
        if (!$alreadyInTx) {
            $this->db->beginTransaction();
        }
        try {
            $this->db->prepare("UPDATE sales SET status = 'Cancelled' WHERE id = ?")->execute([$id]);

            $isTourneeSale = !empty($sale['tournee_id']);

            // Only reinstate warehouse stock if this was NOT a route sale
            // (For route sales, stock remains on the truck / managed by the tournee lifecycle)
            if (!$isTourneeSale) {
                $stmtReversalStock = $this->db->prepare("INSERT INTO stock_movements (movement_date, product_id, format_id, format_type, movement_type_id, source_type, source_id, quantity, stock_equivalent, unit_price, reference, user_id) VALUES (CURRENT_DATE, ?, ?, ?, 7, 'SaleCancellation', ?, ?, ?, ?, ?, ?)");
                foreach ($sale['items'] as $item) {
                    $stmtReversalStock->execute([$item['product_id'], $item['format_id'] ?? 1, $item['format_type'] ?? 'casier', $id, $item['quantity'], $item['stock_equivalent'], $item['unit_price'], 'Annulation Vente ' . $id, $userId]);
                }
            }

            // Only refund from depot cash account if this was NOT a route sale
            // (For route sales, cash is with the driver and reconciled during décharge)
            if (!$isTourneeSale && floatval($sale['amount_paid'] ?? 0) > 0 && !empty($sale['cash_account_id'])) {
                $this->db->prepare("INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id) VALUES (NOW(), 'Sale', ?, ?, ?, 0.00, ?, ?)")
                         ->execute([$id, 'Annulation & Remboursement Vente ' . $id, $sale['cash_account_id'], floatval($sale['amount_paid']), $userId]);
            }

            // Reverse any automatic excess payment (Avoir) linked to this sale
            $stmtExcessPay = $this->db->prepare("SELECT * FROM client_payments WHERE reference = ?");
            $stmtExcessPay->execute([$id]);
            $excessPay = $stmtExcessPay->fetch();
            if ($excessPay) {
                $this->db->prepare("DELETE FROM client_payments WHERE id = ?")->execute([$excessPay['id']]);
                if (!empty($excessPay['cash_account_id']) && floatval($excessPay['amount']) > 0) {
                    $this->db->prepare("INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id) VALUES (NOW(), 'ClientPayment', ?, ?, ?, 0.00, ?, ?)")
                             ->execute([$excessPay['id'], 'Annulation & Remboursement Avoir Trop-perçu Vente ' . $id, $excessPay['cash_account_id'], floatval($excessPay['amount']), $userId]);
                }
            }

            $stmtReduceEmpty = $this->db->prepare("UPDATE emballage_stock SET empty_crates = GREATEST(0, empty_crates - ?), loose_bottles = GREATEST(0, loose_bottles - ?) WHERE packaging_type_id = ?");
            $stmtFetchDebt = $this->db->prepare("SELECT crates_due, loose_bottles_due FROM client_emballage_debts WHERE client_id = ? AND packaging_type_id = ?");
            $stmtSetDebt = $this->db->prepare("UPDATE client_emballage_debts SET crates_due = ?, loose_bottles_due = ? WHERE client_id = ? AND packaging_type_id = ?");

            foreach ($sale['items'] as $item) {
                if (!empty($item['is_returnable']) && !empty($item['packaging_type_id'])) {
                    $pkgTypeId = intval($item['packaging_type_id']);
                    $factor = max(1, intval($item['factor'] ?: 12));
                    $missingBottles = max(0, intval($item['bottles_out']) - (intval($item['crates_returned']) * $factor + intval($item['bottles_returned'])));

                    // Relief client packaging debt: only if this was NOT a route sale OR if the sale was already Valid (committed)
                    if ($missingBottles > 0 && (!$isTourneeSale || $sale['status'] === 'Valid')) {
                        $stmtFetchDebt->execute([$sale['client_id'], $pkgTypeId]);
                        if ($curDebt = $stmtFetchDebt->fetch()) {
                            $newTotalBtls = max(0, (intval($curDebt['crates_due']) * $factor + intval($curDebt['loose_bottles_due'])) - $missingBottles);
                            $stmtSetDebt->execute([floor($newTotalBtls / $factor), $newTotalBtls % $factor, $sale['client_id'], $pkgTypeId]);
                        }
                    }

                    // Empties returned: only reduce depot emballage stock if NOT a route sale
                    // (For route sales, empties returned stay on the truck until evening décharge)
                    if (!$isTourneeSale) {
                        $cratesRet = intval($item['crates_returned'] ?? 0);
                        $bottlesRet = intval($item['bottles_returned'] ?? 0);

                        if ($cratesRet > 0 || $bottlesRet > 0) {
                            $stmtReduceEmpty->execute([$cratesRet, $bottlesRet, $pkgTypeId]);
                            $this->db->prepare("
                                INSERT INTO emballage_movements (packaging_type_id, movement_type, reference_id, client_id, crates_in, crates_out, bottles_in, bottles_out, notes, created_by) 
                                VALUES (?, 'Sale_Cancellation', ?, ?, 0, ?, 0, ?, ?, ?)
                            ")->execute([
                                $pkgTypeId,
                                $id,
                                $sale['client_id'],
                                $cratesRet,
                                $bottlesRet,
                                "Annulation Vente $id : restitution des vides déposés",
                                $userId
                            ]);
                        }
                    }
                }
            }

            \App\Core\Helper::logAudit('CANCEL', 'Ventes', $id, "Annulation de la facture $id" . (!empty($reason) ? " ($reason)" : ""));

            if (!$alreadyInTx) {
                $this->db->commit();
            }
            return true;
        } catch (\Throwable $e) {
            if (!$alreadyInTx) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function delete($id) {
        return $this->cancelSale($id);
    }
}
