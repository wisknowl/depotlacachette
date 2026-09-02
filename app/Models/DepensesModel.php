<?php
namespace App\Models;
use App\Core\Database;
use PDO;

class DepensesModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getAll() {
        return $this->getFiltered([]);
    }

    public function getFiltered($filters = []) {
        $sql = "
            SELECT e.*, c.name as category_name, p.name as payment_method, ca.name as cash_account_name, u.username 
            FROM expenses e 
            LEFT JOIN expense_categories c ON e.category_id = c.id 
            LEFT JOIN payment_methods p ON e.payment_method_id = p.id 
            LEFT JOIN cash_transactions ct ON e.id = ct.source_id AND ct.transaction_type = 'Expense'
            LEFT JOIN cash_accounts ca ON ct.cash_account_id = ca.id
            LEFT JOIN users u ON e.user_id = u.id 
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (e.id LIKE ? OR e.description LIKE ? OR e.beneficiary LIKE ? OR e.reference LIKE ? OR c.name LIKE ?)";
            $term = '%' . trim($filters['search']) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND e.category_id = ?";
            $params[] = intval($filters['category_id']);
        }

        if (!empty($filters['cash_account_id'])) {
            $sql .= " AND ct.cash_account_id = ?";
            $params[] = intval($filters['cash_account_id']);
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND e.expense_date >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND e.expense_date <= ?";
            $params[] = $filters['end_date'];
        }

        $sql .= "
            GROUP BY e.id, e.expense_date, e.category_id, e.description, e.amount, e.payment_method_id, e.beneficiary, e.reference, e.user_id, e.status, e.created_at, c.name, p.name, ca.name, u.username
            ORDER BY e.expense_date DESC, e.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getCategories() {
        return $this->db->query("SELECT * FROM expense_categories ORDER BY name ASC")->fetchAll();
    }

    public function getPaymentMethods() {
        return $this->db->query("SELECT * FROM payment_methods WHERE id != 5 ORDER BY id ASC")->fetchAll();
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

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM expenses WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getExpenseDetails($id) {
        $stmt = $this->db->prepare("
            SELECT 
                e.*, 
                c.name as category_name, 
                p.name as payment_method_name, 
                ca.name as cash_account_name, 
                COALESCE(u.full_name, u.username) as user_name 
            FROM expenses e 
            LEFT JOIN expense_categories c ON e.category_id = c.id 
            LEFT JOIN payment_methods p ON e.payment_method_id = p.id 
            LEFT JOIN cash_transactions ct ON e.id = ct.source_id AND ct.transaction_type = 'Expense'
            LEFT JOIN cash_accounts ca ON ct.cash_account_id = ca.id
            LEFT JOIN users u ON e.user_id = u.id 
            WHERE e.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    private function generateExpenseId() {
        $stmt = $this->db->query("SELECT id FROM expenses ORDER BY id DESC LIMIT 1");
        $last = $stmt->fetchColumn();
        if ($last && preg_match('/DEP(\d+)/', $last, $matches)) {
            $num = intval($matches[1]) + 1;
            return 'DEP' . str_pad($num, 5, '0', STR_PAD_LEFT);
        }
        return 'DEP00001';
    }

    public function add($data) {
        $amount = floatval($data['amount'] ?? 0);
        if ($amount <= 0) {
            throw new \Exception("Le montant de la dépense doit être strictement supérieur à 0 FCFA.");
        }

        $cashAccountId = intval($data['cash_account_id'] ?? 0);
        $account = $this->getCashAccount($cashAccountId);
        if (!$account) {
            throw new \Exception("Veuillez sélectionner un compte source (caisse débitrice) valide.");
        }

        $currentBalance = floatval($account['current_balance']);
        if ($currentBalance <= 0) {
            throw new \Exception("Dépense impossible : Le compte '" . htmlspecialchars($account['name']) . "' est vide (Solde disponible : 0 FCFA). Veuillez choisir une caisse approvisionnée.");
        }

        if ($amount > $currentBalance) {
            throw new \Exception("Dépense impossible : Le montant demandé (" . number_format($amount, 0, ',', ' ') . " FCFA) dépasse le solde disponible dans " . htmlspecialchars($account['name']) . " (" . number_format($currentBalance, 0, ',', ' ') . " FCFA).");
        }

        $paymentMethodId = $account['payment_method_id'] ?: intval($data['payment_method_id'] ?? 1);
        $id = $this->generateExpenseId();
        $userId = $data['user_id'] ?? 1;

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO expenses (id, expense_date, category_id, description, amount, payment_method_id, beneficiary, reference, user_id, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Paid')
            ");
            $stmt->execute([
                $id,
                $data['expense_date'],
                $data['category_id'],
                $data['description'],
                $amount,
                $paymentMethodId,
                $data['beneficiary'],
                $data['reference'] ?: $id,
                $userId
            ]);

            // Log in cash_transactions to decrease cash balance
            $stmtCash = $this->db->prepare("
                INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id)
                VALUES (?, 'Expense', ?, ?, ?, 0.00, ?, ?)
            ");
            $stmtCash->execute([
                $data['expense_date'] . ' ' . date('H:i:s'),
                $id,
                'Dépense ' . $id . ': ' . ($data['description'] ?: 'Frais divers') . (!empty($data['beneficiary']) ? ' (' . $data['beneficiary'] . ')' : ''),
                $cashAccountId,
                $amount,
                $userId
            ]);

            $this->db->commit();
            return $id;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete($id) {
        $this->db->beginTransaction();
        try {
            $stmtCash = $this->db->prepare("DELETE FROM cash_transactions WHERE source_id = ? AND transaction_type = 'Expense'");
            $stmtCash->execute([$id]);

            $stmt = $this->db->prepare("DELETE FROM expenses WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}