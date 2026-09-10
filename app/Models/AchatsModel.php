<?php
namespace App\Models;
use App\Core\Database;
use PDO;

class AchatsModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getAll() {
        return $this->getFiltered([]);
    }

    public function getFiltered($filters = []) {
        $sql = "
            SELECT 
                a.*, 
                s.name as supplier_name, 
                pm.name as payment_method_name,
                pm.type as payment_method_type,
                ca.name as cash_account_name,
                u.username,
                COUNT(pi.id) as item_count,
                COALESCE(GROUP_CONCAT(CONCAT(p.name, ' (', pi.quantity, ' ', CASE WHEN pi.format_type = 'demi' THEN 'Demi' ELSE 'Casier' END, ')') SEPARATOR ', '), 'Aucun article') as products_summary
            FROM purchases a
            LEFT JOIN suppliers s ON a.supplier_id = s.id
            LEFT JOIN payment_methods pm ON a.payment_method_id = pm.id
            LEFT JOIN cash_accounts ca ON a.cash_account_id = ca.id
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN purchase_items pi ON a.id = pi.purchase_id
            LEFT JOIN products p ON pi.product_id = p.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (a.id LIKE ? OR a.reference LIKE ? OR s.name LIKE ? OR a.notes LIKE ?)";
            $term = '%' . trim($filters['search']) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['supplier_id'])) {
            $sql .= " AND a.supplier_id = ?";
            $params[] = intval($filters['supplier_id']);
        }

        if (!empty($filters['payment_mode'])) {
            if ($filters['payment_mode'] === 'cash') {
                $sql .= " AND a.payment_method_id != 5";
            } elseif ($filters['payment_mode'] === 'credit') {
                $sql .= " AND a.payment_method_id = 5";
            }
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND a.purchase_date >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND a.purchase_date <= ?";
            $params[] = $filters['end_date'];
        }

        $sql .= "
            GROUP BY a.id, a.purchase_date, a.supplier_invoice_date, a.supplier_id, a.total_amount, a.payment_method_id, a.cash_account_id, a.reference, a.notes, a.user_id, a.status, a.created_at, s.name, pm.name, pm.type, ca.name, u.username
            ORDER BY a.purchase_date DESC, a.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getPurchaseWithItems($id) {
        $stmt = $this->db->prepare("
            SELECT 
                a.*, 
                s.name as supplier_name, 
                s.phone as supplier_phone,
                s.address as supplier_address,
                pm.name as payment_method_name,
                pm.type as payment_method_type,
                ca.name as cash_account_name,
                u.username
            FROM purchases a
            LEFT JOIN suppliers s ON a.supplier_id = s.id
            LEFT JOIN payment_methods pm ON a.payment_method_id = pm.id
            LEFT JOIN cash_accounts ca ON a.cash_account_id = ca.id
            LEFT JOIN users u ON a.user_id = u.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        $purchase = $stmt->fetch();
        if (!$purchase) return null;

        $stmtItems = $this->db->prepare("
            SELECT 
                pi.*,
                p.name as product_name,
                p.packaging_type_id,
                p.is_returnable,
                p.factor,
                f.name as format_name,
                c.name as category_name
            FROM purchase_items pi
            LEFT JOIN products p ON pi.product_id = p.id
            LEFT JOIN formats f ON p.format_id = f.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE pi.purchase_id = ?
            ORDER BY pi.id ASC
        ");
        $stmtItems->execute([$id]);
        $purchase['items'] = $stmtItems->fetchAll();

        return $purchase;
    }

    public function getSuppliers() { 
        return $this->db->query("SELECT * FROM suppliers ORDER BY name ASC")->fetchAll(); 
    }

    public function getProducts() { 
        $sql = "
            SELECT 
                p.*, 
                f.name as format_name, 
                c.name as category_name,
                pt.name as packaging_name,
                pt.company as packaging_company,
                pt.color as packaging_color,
                pt.bottles_per_crate,
                COALESCE(es.empty_crates, 0) as available_empty_crates,
                COALESCE(es.loose_bottles, 0) as available_loose_bottles,
                COALESCE(SUM(CASE 
                    WHEN mt.direction = 'IN' THEN m.stock_equivalent 
                    WHEN mt.direction = 'OUT' THEN -m.stock_equivalent 
                    ELSE 0 
                END), 0) as current_stock
            FROM products p 
            LEFT JOIN formats f ON p.format_id = f.id 
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN packaging_types pt ON p.packaging_type_id = pt.id
            LEFT JOIN emballage_stock es ON p.packaging_type_id = es.packaging_type_id
            LEFT JOIN stock_movements m ON p.id = m.product_id
            LEFT JOIN movement_types mt ON m.movement_type_id = mt.id
            GROUP BY p.id, p.name, p.short_code, p.slug, p.category_id, p.format_id, p.is_returnable, p.cout_emballage, p.packaging_type_id, p.purchase_price, p.price_casier, p.price_demi, p.price_unite, p.alert_stock, p.factor, p.created_at, f.name, c.name, pt.name, pt.company, pt.color, pt.bottles_per_crate, es.empty_crates, es.loose_bottles
            ORDER BY p.name ASC
        ";
        return $this->db->query($sql)->fetchAll(); 
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

    private function generatePurchaseId() {
        $stmt = $this->db->query("SELECT id FROM purchases ORDER BY id DESC LIMIT 1");
        $last = $stmt->fetchColumn();
        if ($last && preg_match('/A(\d+)/', $last, $matches)) {
            $num = intval($matches[1]) + 1;
            return 'A' . str_pad($num, 5, '0', STR_PAD_LEFT);
        }
        return 'A00001';
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
            throw new \Exception("Veuillez ajouter au moins un produit au bon d'approvisionnement.");
        }

        $processedItems = [];
        $totalAmount = 0;

        foreach ($rawItems as $idx => $item) {
            $prodId = intval($item['product_id'] ?? 0);
            $qty = floatval($item['quantity'] ?? 0);
            $hasDemi = !empty($item['has_demi']) ? 1 : 0;
            $unitPrice = floatval($item['unit_price'] ?? 0);
            $demiUnitPrice = floatval($item['demi_unit_price'] ?? ($unitPrice / 2));

            if ($prodId <= 0) continue;
            if ($qty <= 0 && !$hasDemi) {
                throw new \Exception("La quantité approvisionnée à la ligne " . ($idx + 1) . " doit être supérieure à 0.");
            }
            if ($unitPrice <= 0 && $demiUnitPrice <= 0) {
                throw new \Exception("Le prix d'achat unitaire à la ligne " . ($idx + 1) . " doit être supérieur à 0 FCFA.");
            }

            $prodStmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
            $prodStmt->execute([$prodId]);
            $product = $prodStmt->fetch();
            if (!$product) {
                throw new \Exception("Article à la ligne " . ($idx + 1) . " introuvable.");
            }

            $isReturnable = intval($product['is_returnable'] ?? 1);
            $emptiesReturned = $isReturnable ? (isset($item['empties_returned']) ? intval($item['empties_returned']) : 0) : 0;
            $emballageCost = $isReturnable ? (isset($item['emballage_cost']) ? floatval($item['emballage_cost']) : floatval($product['cout_emballage'] ?? 3600)) : 0.00;
            $deficitCrates = max(0, intval($qty) - $emptiesReturned);
            $packagingMode = $item['packaging_mode'] ?? 'charge';
            
            // If deficit exists, check if billed/charged or recorded as supplier crate debt
            $emballageTotal = ($isReturnable && $deficitCrates > 0 && $packagingMode === 'charge') ? ($deficitCrates * $emballageCost) : 0.00;

            $stockEquivalent = $qty + ($hasDemi ? 0.5 : 0.0);
            $drinkTotal = ($qty * $unitPrice) + ($hasDemi ? $demiUnitPrice : 0.0);
            $lineTotal = $drinkTotal + $emballageTotal;
            $ristourneUnit = floatval($item['ristourne_unit'] ?? 0);
            $totalRistourne = ($qty + ($hasDemi ? 0.5 : 0.0)) * $ristourneUnit;
            $totalAmount += $lineTotal;
            $totalEmballageAmount = ($totalEmballageAmount ?? 0) + $emballageTotal;

            $formatType = ($qty == 0 && $hasDemi) ? 'demi' : 'casier';

            $processedItems[] = [
                'product_id' => $product['id'],
                'product_name' => $product['name'],
                'packaging_type_id' => $product['packaging_type_id'],
                'format_id' => $product['format_id'],
                'format_type' => $formatType,
                'has_demi' => $hasDemi,
                'is_returnable' => $isReturnable,
                'empties_returned' => $emptiesReturned,
                'emballage_cost' => $emballageCost,
                'emballage_total' => $emballageTotal,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'demi_unit_price' => $demiUnitPrice,
                'ristourne_unit' => $ristourneUnit,
                'total_ristourne' => $totalRistourne,
                'total_price' => $lineTotal,
                'stock_equivalent' => $stockEquivalent
            ];
        }

        if (empty($processedItems)) {
            throw new \Exception("Aucun article valide dans le bon d'achat.");
        }

        // Empty crates stock validation per packaging model
        $emptiesByPkg = [];
        foreach ($processedItems as $it) {
            if ($it['is_returnable'] && !empty($it['packaging_type_id'])) {
                $pkgId = intval($it['packaging_type_id']);
                $emptiesByPkg[$pkgId] = ($emptiesByPkg[$pkgId] ?? 0) + intval($it['empties_returned']);
            }
        }

        foreach ($emptiesByPkg as $pkgId => $totalReturned) {
            if ($totalReturned > 0) {
                $stmtCheck = $this->db->prepare("SELECT es.empty_crates, pt.name FROM emballage_stock es JOIN packaging_types pt ON es.packaging_type_id = pt.id WHERE es.packaging_type_id = ?");
                $stmtCheck->execute([$pkgId]);
                $curEmb = $stmtCheck->fetch();
                $avail = intval($curEmb['empty_crates'] ?? 0);
                if ($totalReturned > $avail) {
                    $pkgName = $curEmb['name'] ?? "Modèle #$pkgId";
                    throw new \Exception("Stock d'emballages insuffisant pour '{$pkgName}' : Vous tentez de remettre {$totalReturned} casier(s) vide(s) au camion alors que le dépôt n'en possède que {$avail} en stock.");
                }
            }
        }

        $additionalFees = floatval($data['additional_fees'] ?? 0);
        $totalAmount += $additionalFees;

        // Check Supplier
        $suppStmt = $this->db->prepare("SELECT name FROM suppliers WHERE id = ?");
        $suppStmt->execute([$data['supplier_id']]);
        $supplierName = $suppStmt->fetchColumn() ?: 'Fournisseur';

        $isCredit = (($data['settlement_type'] ?? '') === 'credit' || (isset($data['payment_method_id']) && $data['payment_method_id'] == 5));
        $paymentMethodId = 5;
        $cashAccountId = null;

        if (!$isCredit) {
            $cashAccountId = intval($data['cash_account_id'] ?? 0);
            $accountInfo = $this->getCashAccount($cashAccountId);
            if (!$accountInfo) {
                throw new \Exception("Veuillez sélectionner un compte de décaissement (caisse) valide pour un achat au comptant.");
            }
            $currentBalance = floatval($accountInfo['current_balance']);
            if ($totalAmount > $currentBalance) {
                throw new \Exception("Solde insuffisant dans " . htmlspecialchars($accountInfo['name']) . " (Solde disponible : " . number_format($currentBalance, 0, ',', ' ') . " FCFA, Total achat : " . number_format($totalAmount, 0, ',', ' ') . " FCFA).");
            }
            $paymentMethodId = $accountInfo['payment_method_id'] ?: 1;
        }

        $id = $this->generatePurchaseId();
        $userId = $data['user_id'] ?? 1;

        $this->db->beginTransaction();
        try {
            // 1. Insert Purchases Header
            $stmt = $this->db->prepare("
                INSERT INTO purchases (id, purchase_date, supplier_invoice_date, supplier_id, total_amount, additional_fees, total_emballage_amount, payment_method_id, cash_account_id, reference, notes, user_id, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Valid')
            ");
            $stmt->execute([
                $id,
                $data['purchase_date'],
                !empty($data['supplier_invoice_date']) ? $data['supplier_invoice_date'] : $data['purchase_date'],
                $data['supplier_id'],
                $totalAmount,
                $additionalFees,
                $totalEmballageAmount ?? 0,
                $paymentMethodId,
                $cashAccountId,
                $data['reference'] ?: $id,
                $data['notes'] ?? '',
                $userId
            ]);

            // 2. Insert Purchase Items and Stock Movements (Entrée Stock)
            $stmtItem = $this->db->prepare("
                INSERT INTO purchase_items (purchase_id, product_id, format_type, has_demi, quantity, unit_price, demi_unit_price, is_returnable, empties_returned, emballage_cost, emballage_total, ristourne_unit, total_ristourne, total_price, stock_equivalent)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmtStock = $this->db->prepare("
                INSERT INTO stock_movements (movement_date, product_id, format_id, format_type, movement_type_id, source_type, source_id, quantity, stock_equivalent, unit_price, reference, user_id)
                VALUES (?, ?, ?, ?, 1, 'Purchase', ?, ?, ?, ?, ?, ?)
            ");

            foreach ($processedItems as $item) {
                $stmtItem->execute([
                    $id,
                    $item['product_id'],
                    $item['format_type'],
                    $item['has_demi'],
                    $item['quantity'],
                    $item['unit_price'],
                    $item['demi_unit_price'],
                    $item['is_returnable'],
                    $item['empties_returned'],
                    $item['emballage_cost'],
                    $item['emballage_total'],
                    $item['ristourne_unit'],
                    $item['total_ristourne'],
                    $item['total_price'],
                    $item['stock_equivalent']
                ]);

                $stmtStock->execute([
                    $data['purchase_date'],
                    $item['product_id'],
                    $item['format_id'],
                    $item['format_type'],
                    $id,
                    $item['quantity'],
                    $item['stock_equivalent'],
                    $item['unit_price'],
                    'Achat ' . $id,
                    $userId
                ]);

                // Update product catalog purchase price to the latest supplier invoice price
                $latestFullPrice = ($item['unit_price'] > 0) ? $item['unit_price'] : ($item['demi_unit_price'] * 2);
                $stmtUpdateCost = $this->db->prepare("UPDATE products SET purchase_price = ? WHERE id = ?");
                $stmtUpdateCost->execute([$latestFullPrice, $item['product_id']]);

                // Emballage handling
                if ($item['is_returnable'] && !empty($item['packaging_type_id'])) {
                    $pkgTypeId = intval($item['packaging_type_id']);
                    $cratesHandedOver = intval($item['empties_returned']);
                    $missingCrates = max(0, intval($item['quantity']) - $cratesHandedOver);

                    // If empty crates were given to the truck: deduct from depot empty stock & log movement
                    if ($cratesHandedOver > 0) {
                        $this->db->prepare("
                            UPDATE emballage_stock 
                            SET empty_crates = GREATEST(0, empty_crates - ?) 
                            WHERE packaging_type_id = ?
                        ")->execute([$cratesHandedOver, $pkgTypeId]);

                        $this->db->prepare("
                            INSERT INTO emballage_movements (packaging_type_id, movement_type, reference_id, supplier_id, crates_out, notes, created_by)
                            VALUES (?, 'Purchase_Drink', ?, ?, ?, ?, ?)
                        ")->execute([
                            $pkgTypeId,
                            $id,
                            $data['supplier_id'],
                            $cratesHandedOver,
                            "Casiers vides remis au camion (Achat $id)",
                            $userId
                        ]);
                    }

                    // If missing crates were charged on invoice (bought as extra packaging):
                    if ($missingCrates > 0 && $item['emballage_total'] > 0) {
                        $this->db->prepare("
                            INSERT INTO emballage_movements (packaging_type_id, movement_type, reference_id, supplier_id, crates_in, unit_cost, total_cost, notes, created_by)
                            VALUES (?, 'Purchase_Empty', ?, ?, ?, ?, ?, ?, ?)
                        ")->execute([
                            $pkgTypeId,
                            $id,
                            $data['supplier_id'],
                            $missingCrates,
                            $item['emballage_cost'],
                            $item['emballage_total'],
                            "Consigne/Achat casiers déficitaires (Achat $id)",
                            $userId
                        ]);
                    } elseif ($missingCrates > 0 && $item['emballage_total'] == 0) {
                        // Unpaid packaging deficit -> record supplier packaging debt
                        $this->db->prepare("
                            INSERT INTO supplier_emballage_debts (supplier_id, packaging_type_id, crates_due)
                            VALUES (?, ?, ?)
                            ON DUPLICATE KEY UPDATE crates_due = crates_due + VALUES(crates_due)
                        ")->execute([$data['supplier_id'], $pkgTypeId, $missingCrates]);
                    }
                }
            }

            // 3. Log into cash_transactions ONLY if Cash Purchase
            if (!$isCredit && $cashAccountId) {
                $stmtCash = $this->db->prepare("
                    INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id)
                    VALUES (?, 'Purchase', ?, ?, ?, 0.00, ?, ?)
                ");
                $stmtCash->execute([
                    $data['purchase_date'] . ' ' . date('H:i:s'),
                    $id,
                    'Achat Comptant ' . $id . ' (' . $supplierName . ')',
                    $cashAccountId,
                    $totalAmount,
                    $userId
                ]);
            }

            $this->db->commit();
            return $id;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Audit-Compliant Cancellation of a Purchase
     */
    public function cancelPurchase($id, $userId = 1, $reason = '') {
        $purchase = $this->getPurchaseWithItems($id);
        if (!$purchase) {
            throw new \Exception("Bon d'achat introuvable.");
        }
        if ($purchase['status'] === 'Cancelled') {
            throw new \Exception("Ce bon d'achat est déjà annulé.");
        }

        $this->db->beginTransaction();
        try {
            // 1. Mark status as Cancelled
            $stmt = $this->db->prepare("UPDATE purchases SET status = 'Cancelled' WHERE id = ?");
            $stmt->execute([$id]);

            // 2. Append Reversing Stock Movements (Déstockage Achat / Retour Fournisseur)
            $stmtReversalStock = $this->db->prepare("
                INSERT INTO stock_movements (movement_date, product_id, format_id, format_type, movement_type_id, source_type, source_id, quantity, stock_equivalent, unit_price, reference, user_id)
                VALUES (CURRENT_DATE, ?, ?, ?, 8, 'PurchaseCancellation', ?, ?, ?, ?, ?, ?)
            ");

            foreach ($purchase['items'] as $item) {
                $stmtReversalStock->execute([
                    $item['product_id'],
                    $item['format_id'] ?? 1,
                    $item['format_type'] ?? 'casier',
                    $id,
                    $item['quantity'],
                    $item['stock_equivalent'],
                    $item['unit_price'],
                    'Annulation Achat ' . $id . ($reason ? ' (' . $reason . ')' : ''),
                    $userId
                ]);
            }

            // 3. Append Reversing Cash Transaction (Credit back into Cash Account if was Cash)
            $isCredit = ($purchase['payment_method_id'] == 5);
            if (!$isCredit && !empty($purchase['cash_account_id'])) {
                $stmtContraCash = $this->db->prepare("
                    INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id)
                    VALUES (NOW(), 'Purchase', ?, ?, ?, ?, 0.00, ?)
                ");
                $stmtContraCash->execute([
                    $id,
                    'Annulation & Réintégration Décaissement Achat ' . $id . ($reason ? ' - ' . $reason : ''),
                    $purchase['cash_account_id'],
                    $purchase['total_amount'],
                    $userId
                ]);
            }

            // 4. Reverse Packaging Stock & Supplier Packaging Debts
            $stmtRestoreEmpty = $this->db->prepare("
                UPDATE emballage_stock 
                SET empty_crates = empty_crates + ? 
                WHERE packaging_type_id = ?
            ");

            $stmtReduceSupplierDebt = $this->db->prepare("
                UPDATE supplier_emballage_debts 
                SET crates_due = GREATEST(0, crates_due - ?) 
                WHERE supplier_id = ? AND packaging_type_id = ?
            ");

            $stmtAuditEmbPurchase = $this->db->prepare("
                INSERT INTO emballage_movements (packaging_type_id, movement_type, reference_id, supplier_id, crates_in, crates_out, notes, created_by)
                VALUES (?, 'Purchase_Cancellation', ?, ?, ?, ?, ?, ?)
            ");

            foreach ($purchase['items'] as $item) {
                if (!empty($item['is_returnable']) && !empty($item['packaging_type_id'])) {
                    $pkgTypeId = intval($item['packaging_type_id']);
                    $qty = floatval($item['quantity'] ?? 0);
                    $emptiesHandedOver = floatval($item['empties_returned'] ?? 0);
                    $missingCrates = max(0, $qty - $emptiesHandedOver);
                    $embTotal = floatval($item['emballage_total'] ?? 0);

                    // A. Restore empty crates that were given to truck back into depot stock
                    if ($emptiesHandedOver > 0) {
                        $stmtRestoreEmpty->execute([$emptiesHandedOver, $pkgTypeId]);
                        $stmtAuditEmbPurchase->execute([
                            $pkgTypeId,
                            $id,
                            $purchase['supplier_id'],
                            $emptiesHandedOver, // Empty crates returned back to depot
                            0,
                            "Annulation achat $id : réintégration des vides remis au camion" . ($reason ? ' (' . $reason . ')' : ''),
                            $userId
                        ]);
                    }

                    // B. Deduct supplier packaging debt if unpaid missing crates existed
                    if ($missingCrates > 0 && $embTotal == 0) {
                        $stmtReduceSupplierDebt->execute([$missingCrates, $purchase['supplier_id'], $pkgTypeId]);
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
        return $this->cancelPurchase($id);
    }
}
