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
                COUNT(si.id) as item_count,
                COALESCE(GROUP_CONCAT(CONCAT(p.name, ' (', si.quantity, ' ', CASE WHEN si.format_type = 'demi' THEN 'Demi' ELSE 'Casier' END, ')') SEPARATOR ', '), 'Aucun article') as products_summary
            FROM sales v
            LEFT JOIN clients c ON v.client_id = c.id
            LEFT JOIN payment_methods pm ON v.payment_method_id = pm.id
            LEFT JOIN cash_accounts ca ON v.cash_account_id = ca.id
            LEFT JOIN users u ON v.user_id = u.id
            LEFT JOIN sale_items si ON v.id = si.sale_id
            LEFT JOIN products p ON si.product_id = p.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (v.id LIKE ? OR v.reference LIKE ? OR c.name LIKE ? OR v.notes LIKE ?)";
            $term = '%' . trim($filters['search']) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['client_id'])) {
            $sql .= " AND v.client_id = ?";
            $params[] = intval($filters['client_id']);
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
            GROUP BY v.id, v.sale_date, v.client_id, v.total_amount, v.payment_method_id, v.cash_account_id, v.reference, v.notes, v.user_id, v.status, v.created_at, c.name, pm.name, pm.type, ca.name, u.username
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
                u.username
            FROM sales v
            LEFT JOIN clients c ON v.client_id = c.id
            LEFT JOIN payment_methods pm ON v.payment_method_id = pm.id
            LEFT JOIN cash_accounts ca ON v.cash_account_id = ca.id
            LEFT JOIN users u ON v.user_id = u.id
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

    public function add($data) {
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

        foreach ($rawItems as $idx => $item) {
            $prodId = intval($item['product_id'] ?? 0);
            $qty = floatval($item['quantity'] ?? 0);
            $formatType = in_array($item['format_type'] ?? '', ['casier', 'demi']) ? $item['format_type'] : 'casier';
            $unitPrice = floatval($item['unit_price'] ?? 0);

            if ($prodId <= 0) continue;
            if ($qty <= 0) {
                throw new \Exception("La quantité du produit à la ligne " . ($idx + 1) . " doit être supérieure à 0.");
            }
            if ($unitPrice <= 0) {
                throw new \Exception("Le prix unitaire à la ligne " . ($idx + 1) . " doit être supérieur à 0 FCFA.");
            }

            $product = $this->getProductStock($prodId);
            if (!$product) {
                throw new \Exception("Article à la ligne " . ($idx + 1) . " introuvable.");
            }

            $currentStock = floatval($product['current_stock']);
            $stockEquivalent = ($formatType === 'demi') ? ($qty * 0.5) : $qty;

            if ($currentStock <= 0) {
                throw new \Exception("Rupture de stock pour '" . htmlspecialchars($product['name']) . "' (Stock: 0).");
            }
            if ($stockEquivalent > $currentStock) {
                throw new \Exception("Stock insuffisant pour '" . htmlspecialchars($product['name']) . "'. Demandé : {$stockEquivalent} casiers, Disponible : {$currentStock} casiers.");
            }

            $lineTotal = $qty * $unitPrice;
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
                if ($formatType === 'casier') {
                    $cratesOut = intval($qty);
                    $bottlesOut = intval($qty) * $factor;
                    $cratesReturned = isset($item['crates_returned']) ? intval($item['crates_returned']) : 0;
                    $bottlesReturned = isset($item['bottles_returned']) ? intval($item['bottles_returned']) : 0;
                    $totalBottlesRestituted = ($cratesReturned * $factor) + $bottlesReturned;
                    $missingBottles = max(0, $bottlesOut - $totalBottlesRestituted);
                    $netCratesDue = floor($missingBottles / $factor);
                    $netLooseBottlesDue = $missingBottles % $factor;
                } else { // Demi-casier
                    $cratesOut = 0;
                    $bottlesOut = intval($qty) * intval($factor / 2);
                    $cratesReturned = 0;
                    $bottlesReturned = isset($item['bottles_returned']) ? intval($item['bottles_returned']) : 0;
                    $missingBottles = max(0, $bottlesOut - $bottlesReturned);
                    $netCratesDue = floor($missingBottles / $factor);
                    $netLooseBottlesDue = $missingBottles % $factor;
                }
            }

            $processedItems[] = [
                'product_id' => $product['id'],
                'product_name' => $product['name'],
                'packaging_type_id' => $product['packaging_type_id'],
                'format_id' => $product['format_id'],
                'format_type' => $formatType,
                'is_returnable' => $isReturnable,
                'crates_out' => $cratesOut,
                'bottles_out' => $bottlesOut,
                'crates_returned' => $cratesReturned,
                'bottles_returned' => $bottlesReturned,
                'net_crates_due' => $netCratesDue,
                'net_bottles_due' => $netLooseBottlesDue,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total_price' => $lineTotal,
                'stock_equivalent' => $stockEquivalent,
                'purchase_price' => $product['purchase_price'],
                'update_catalog_price' => !empty($item['update_catalog_price'])
            ];
        }

        if (empty($processedItems)) {
            throw new \Exception("Aucun article valide dans la facture de vente.");
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
            $amountPaid = min($netPayableCash, max(0, floatval($data['amount_paid'] ?? $netPayableCash)));
            $cashShortage = max(0, $netPayableCash - $amountPaid);
            // In ledger math: amount_due = avoirUsed + cashShortage
            // (solde_du = amount_due - previous_payments = cashShortage - remaining_avoir)
            $amountDue = $avoirUsed + $cashShortage;
            $cashAccountId = intval($data['cash_account_id'] ?? 0);
            $accountInfo = $this->getCashAccount($cashAccountId);
            if (!$accountInfo && $amountPaid > 0) throw new \Exception("Compte d'encaissement invalide.");
            $paymentMethodId = ($cashShortage > 0) ? ($amountPaid > 0 ? 1 : 5) : ($accountInfo['payment_method_id'] ?: 1);
        }

        if ($isFullCredit || ($amountPaid < $netPayableCash)) {
            $realCashDebt = $isFullCredit ? $netPayableCash : max(0, $netPayableCash - $amountPaid);
            if (floatval($client['max_credit']) <= 0 && $realCashDebt > 0) throw new \Exception("Client en 'Comptant Uniquement'.");
            if (((max(0, $currentDebt)) + $realCashDebt) > floatval($client['max_credit'])) throw new \Exception("Plafond de crédit dépassé.");
        }

        $this->db->beginTransaction();
        try {
            $id = $this->generateSaleId();
            $userId = $data['user_id'] ?? 1;

            $stmt = $this->db->prepare("
                INSERT INTO sales (id, sale_date, client_id, total_amount, discount_amount, avoir_used, amount_paid, amount_due, payment_method_id, cash_account_id, reference, notes, user_id, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Valid')
            ");
            $stmt->execute([
                $id, $data['sale_date'], $client['id'], $subTotal, $discountAmount, $avoirUsed, $amountPaid, $amountDue,
                $paymentMethodId, $cashAccountId, $data['reference'] ?: $id, $data['notes'] ?? '', $userId
            ]);


            $stmtItem = $this->db->prepare("
                INSERT INTO sale_items (sale_id, product_id, format_type, quantity, unit_price, total_price, stock_equivalent, is_returnable, crates_out, bottles_out, crates_returned, bottles_returned)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
                    $id, $item['product_id'], $item['format_type'], $item['quantity'],
                    $item['unit_price'], $item['total_price'], $item['stock_equivalent'], $item['is_returnable'],
                    $item['crates_out'], $item['bottles_out'], $item['crates_returned'], $item['bottles_returned']
                ]);

                $stmtStockMov->execute([
                    $data['sale_date'], $item['product_id'], $item['format_id'] ?? 1, $item['format_type'],
                    $id, $item['quantity'], $item['stock_equivalent'], $item['unit_price'], 'Vente ' . $id, $userId
                ]);

                if ($item['is_returnable'] && !empty($item['packaging_type_id'])) {
                    $pkgTypeId = intval($item['packaging_type_id']);
                    $productStock = $this->getProductStock($item['product_id']);
                    $factor = max(1, intval($productStock['factor'] ?: 12));

                    if ($item['crates_returned'] > 0 || $item['bottles_returned'] > 0) {
                        $this->db->prepare("UPDATE emballage_stock SET empty_crates = empty_crates + ?, loose_bottles = loose_bottles + ? WHERE packaging_type_id = ?")
                                 ->execute([$item['crates_returned'], $item['bottles_returned'], $pkgTypeId]);
                        $this->db->prepare("INSERT INTO emballage_movements (packaging_type_id, movement_type, reference_id, client_id, crates_in, bottles_in, notes, created_by) VALUES (?, 'Client_Return', ?, ?, ?, ?, ?, ?)")
                                 ->execute([$pkgTypeId, $id, $client['id'], $item['crates_returned'], $item['bottles_returned'], "Entrée casiers vides rapportés au comptoir (Vente $id)", $userId]);
                    }

                    if ($item['net_crates_due'] > 0 || $item['net_bottles_due'] > 0) {
                        $stmtFetchCurDebt->execute([$client['id'], $pkgTypeId]);
                        $curDebt = $stmtFetchCurDebt->fetch();
                        $curTotalBtls = (intval($curDebt['crates_due'] ?? 0) * $factor) + intval($curDebt['loose_bottles_due'] ?? 0);
                        $itemTotalDue = ($item['net_crates_due'] * $factor) + $item['net_bottles_due'];
                        $finalTotalBtls = $curTotalBtls + $itemTotalDue;
                        $stmtUpsertDebt->execute([$client['id'], $pkgTypeId, floor($finalTotalBtls / $factor), $finalTotalBtls % $factor]);
                    }
                }
            }

            if ($amountPaid > 0 && $cashAccountId) {
                $this->db->prepare("INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id) VALUES (?, 'Sale', ?, ?, ?, ?, 0.00, ?)")
                         ->execute([$data['sale_date'] . ' ' . date('H:i:s'), $id, 'Vente ' . $id . ' (' . $client['name'] . ')', $cashAccountId, $amountPaid, $userId]);
            }

            $this->db->commit();
            return $id;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function addRetailSale($data) {
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
        $cashAccountId = null;

        if ($isCredit) {
            $amountPaid = 0.00;
            $amountDue = $grossNet;
        } else {
            $cashAccountId = !empty($data['cash_account_id']) ? intval($data['cash_account_id']) : 1;
            $actualCashPaid = isset($data['amount_paid']) ? floatval($data['amount_paid']) : $netPayableCash;
            if ($actualCashPaid > $netPayableCash) $actualCashPaid = $netPayableCash;
            $cashShortage = max(0, $netPayableCash - $actualCashPaid);

            $amountPaid = $actualCashPaid;
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
                INSERT INTO sales (id, sale_date, client_id, total_amount, discount_amount, avoir_used, amount_paid, amount_due, payment_method_id, cash_account_id, reference, notes, user_id, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Valid')
            ");
            $stmt->execute([
                $id, $data['sale_date'], $client['id'], $subTotal, $discountAmount, $avoirUsed, $amountPaid, $amountDue,
                $paymentMethodId, $cashAccountId, $data['reference'] ?: $id, $data['notes'] ?? 'Vente au détail / Bouteilles', $userId
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
                    }
                }
            }

            if ($amountPaid > 0 && $cashAccountId) {
                $this->db->prepare("INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id) VALUES (?, 'Sale', ?, ?, ?, ?, 0.00, ?)")
                         ->execute([$data['sale_date'] . ' ' . date('H:i:s'), $id, 'Vente Détail ' . $id . ' (' . $client['name'] . ')', $cashAccountId, $amountPaid, $userId]);
            }

            $this->db->commit();
            return $id;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function cancelSale($id, $userId = 1, $reason = '') {
        $sale = $this->getSaleWithItems($id);
        if (!$sale || $sale['status'] === 'Cancelled') throw new \Exception("Facture invalide ou déjà annulée.");

        $this->db->beginTransaction();
        try {
            $this->db->prepare("UPDATE sales SET status = 'Cancelled' WHERE id = ?")->execute([$id]);

            $stmtReversalStock = $this->db->prepare("INSERT INTO stock_movements (movement_date, product_id, format_id, format_type, movement_type_id, source_type, source_id, quantity, stock_equivalent, unit_price, reference, user_id) VALUES (CURRENT_DATE, ?, ?, ?, 7, 'SaleCancellation', ?, ?, ?, ?, ?, ?)");
            foreach ($sale['items'] as $item) {
                $stmtReversalStock->execute([$item['product_id'], $item['format_id'] ?? 1, $item['format_type'] ?? 'casier', $id, $item['quantity'], $item['stock_equivalent'], $item['unit_price'], 'Annulation Vente ' . $id, $userId]);
            }

            if (floatval($sale['amount_paid'] ?? 0) > 0 && !empty($sale['cash_account_id'])) {
                $this->db->prepare("INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id) VALUES (NOW(), 'Sale', ?, ?, ?, 0.00, ?, ?)")
                         ->execute([$id, 'Annulation & Remboursement Vente ' . $id, $sale['cash_account_id'], floatval($sale['amount_paid']), $userId]);
            }

            $stmtReduceEmpty = $this->db->prepare("UPDATE emballage_stock SET empty_crates = GREATEST(0, empty_crates - ?), loose_bottles = GREATEST(0, loose_bottles - ?) WHERE packaging_type_id = ?");
            $stmtFetchDebt = $this->db->prepare("SELECT crates_due, loose_bottles_due FROM client_emballage_debts WHERE client_id = ? AND packaging_type_id = ?");
            $stmtSetDebt = $this->db->prepare("UPDATE client_emballage_debts SET crates_due = ?, loose_bottles_due = ? WHERE client_id = ? AND packaging_type_id = ?");

            foreach ($sale['items'] as $item) {
                if (!empty($item['is_returnable']) && !empty($item['packaging_type_id'])) {
                    $pkgTypeId = intval($item['packaging_type_id']);
                    $factor = max(1, intval($item['factor'] ?: 12));
                    $missingBottles = max(0, intval($item['bottles_out']) - (intval($item['crates_returned']) * $factor + intval($item['bottles_returned'])));

                    if ($missingBottles > 0) {
                        $stmtFetchDebt->execute([$sale['client_id'], $pkgTypeId]);
                        if ($curDebt = $stmtFetchDebt->fetch()) {
                            $newTotalBtls = max(0, (intval($curDebt['crates_due']) * $factor + intval($curDebt['loose_bottles_due'])) - $missingBottles);
                            $stmtSetDebt->execute([floor($newTotalBtls / $factor), $newTotalBtls % $factor, $sale['client_id'], $pkgTypeId]);
                        }
                    }
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
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete($id) {
        return $this->cancelSale($id);
    }
}
