<?php
namespace App\Models;
use App\Core\Database;
use PDO;

class PaiementsFournisseursModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getAll() {
        return $this->getFiltered([]);
    }

    public function getFiltered($filters = []) {
        $sql = "
            SELECT 
                p.*,
                s.name as supplier_name,
                pm.name as payment_method_name,
                a.name as cash_account_name
            FROM supplier_payments p
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            LEFT JOIN payment_methods pm ON p.payment_method_id = pm.id
            LEFT JOIN cash_accounts a ON p.cash_account_id = a.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (p.id LIKE ? OR p.reference LIKE ? OR s.name LIKE ? OR p.notes LIKE ?)";
            $term = '%' . trim($filters['search']) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['supplier_id'])) {
            $sql .= " AND p.supplier_id = ?";
            $params[] = intval($filters['supplier_id']);
        }

        if (!empty($filters['cash_account_id'])) {
            $sql .= " AND p.cash_account_id = ?";
            $params[] = intval($filters['cash_account_id']);
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND p.payment_date >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND p.payment_date <= ?";
            $params[] = $filters['end_date'];
        }

        $sql .= " ORDER BY p.payment_date DESC, p.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getSuppliers() {
        return $this->db->query("SELECT * FROM suppliers ORDER BY name ASC")->fetchAll();
    }

    public function getSuppliersWithDebts() {
        $sql = "
            SELECT 
                s.id, 
                s.name, 
                s.slug,
                s.phone,
                s.contact_name,
                COALESCE((SELECT SUM(a.total_amount) FROM purchases a WHERE a.supplier_id = s.id AND a.payment_method_id = 5 AND a.status = 'Valid'), 0) as total_credit_purchases,
                COALESCE((SELECT SUM(p.amount) FROM supplier_payments p WHERE p.supplier_id = s.id), 0) as total_paid,
                (COALESCE((SELECT SUM(a.total_amount) FROM purchases a WHERE a.supplier_id = s.id AND a.payment_method_id = 5 AND a.status = 'Valid'), 0) - 
                 COALESCE((SELECT SUM(p.amount) FROM supplier_payments p WHERE p.supplier_id = s.id), 0)) as solde_du
            FROM suppliers s
            ORDER BY solde_du DESC, s.name ASC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getSupplierDebt($supplierId) {
        $stmt = $this->db->prepare("
            SELECT 
                s.id,
                s.name,
                (COALESCE((SELECT SUM(a.total_amount) FROM purchases a WHERE a.supplier_id = s.id AND a.payment_method_id = 5 AND a.status = 'Valid'), 0) - 
                 COALESCE((SELECT SUM(p.amount) FROM supplier_payments p WHERE p.supplier_id = s.id), 0)) as solde_du
            FROM suppliers s
            WHERE s.id = ?
        ");
        $stmt->execute([$supplierId]);
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

    public function getCashAccountBalance($accountId) {
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

    private function generatePaymentId() {
        $stmt = $this->db->query("SELECT id FROM supplier_payments ORDER BY id DESC LIMIT 1");
        $last = $stmt->fetchColumn();
        if ($last && preg_match('/PF(\d+)/', $last, $matches)) {
            $num = intval($matches[1]) + 1;
            return 'PF' . str_pad($num, 5, '0', STR_PAD_LEFT);
        }
        return 'PF00001';
    }

    public function add($data) {
        $amount = floatval($data['amount']);
        if ($amount <= 0) {
            throw new \Exception("Le montant à régler doit être strictement supérieur à 0 FCFA.");
        }

        // 1. Validate Supplier & Debt
        $supplierInfo = $this->getSupplierDebt($data['supplier_id']);
        if (!$supplierInfo) {
            throw new \Exception("Fournisseur sélectionné invalide ou introuvable.");
        }
        $debt = floatval($supplierInfo['solde_du']);
        if ($debt <= 0) {
            throw new \Exception("Ce fournisseur n'a aucune dette en cours (" . htmlspecialchars($supplierInfo['name']) . " : 0 FCFA).");
        }
        if ($amount > $debt) {
            throw new \Exception("Le montant saisi (" . number_format($amount, 0, ',', ' ') . " FCFA) dépasse la dette due au fournisseur (" . number_format($debt, 0, ',', ' ') . " FCFA).");
        }

        // 2. Validate Cash Account & Overdraft Prevention
        $accountInfo = $this->getCashAccountBalance($data['cash_account_id']);
        if (!$accountInfo) {
            throw new \Exception("Compte de caisse / trésorerie invalide.");
        }
        $cashBalance = floatval($accountInfo['current_balance']);
        if ($amount > $cashBalance) {
            throw new \Exception("Solde insuffisant dans " . htmlspecialchars($accountInfo['name']) . " (Solde disponible : " . number_format($cashBalance, 0, ',', ' ') . " FCFA, Montant requis : " . number_format($amount, 0, ',', ' ') . " FCFA).");
        }

        // 3. Auto-infer payment_method_id from cash account
        $paymentMethodId = $accountInfo['payment_method_id'] ?: 1;

        $id = $this->generatePaymentId();
        $this->db->beginTransaction();
        try {
            // Insert into supplier_payments
            $stmt = $this->db->prepare("
                INSERT INTO supplier_payments (id, payment_date, supplier_id, amount, payment_method_id, cash_account_id, reference, notes, user_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([
                $id,
                $data['payment_date'],
                $data['supplier_id'],
                $amount,
                $paymentMethodId,
                $data['cash_account_id'],
                $data['reference'] ?: $id,
                $data['notes']
            ]);

            // Log into cash_transactions (Sortie / Décaissement dette fournisseur)
            $stmtCash = $this->db->prepare("
                INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id)
                VALUES (?, 'SupplierPayment', ?, ?, ?, 0.00, ?, 1)
            ");
            $stmtCash->execute([
                $data['payment_date'] . ' ' . date('H:i:s'),
                $id,
                'Paiement Dette Fournisseur ' . $id . ' (' . $supplierInfo['name'] . ')',
                $data['cash_account_id'],
                $amount
            ]);

            $this->db->commit();
            return $id;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getPaiementDetails($id) {
        $stmt = $this->db->prepare("
            SELECT 
                p.*,
                s.name as supplier_name,
                s.phone as supplier_phone,
                s.address as supplier_address,
                pm.name as payment_method_name,
                a.name as cash_account_name,
                COALESCE(u.full_name, u.username) as user_name,
                (COALESCE((SELECT SUM(a.total_amount) FROM purchases a WHERE a.supplier_id = s.id AND a.payment_method_id = 5 AND a.status = 'Valid'), 0) - 
                 COALESCE((SELECT SUM(sp.amount) FROM supplier_payments sp WHERE sp.supplier_id = s.id), 0)) as current_remaining_debt
            FROM supplier_payments p
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            LEFT JOIN payment_methods pm ON p.payment_method_id = pm.id
            LEFT JOIN cash_accounts a ON p.cash_account_id = a.id
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function delete($id) {
        $this->db->beginTransaction();
        try {
            $this->db->prepare("DELETE FROM cash_transactions WHERE source_id = ? AND transaction_type IN ('SupplierPayment', 'Supplier Payment')")->execute([$id]);
            $this->db->prepare("DELETE FROM supplier_payments WHERE id = ?")->execute([$id]);
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
