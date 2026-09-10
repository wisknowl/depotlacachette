<?php
namespace App\Models;
use App\Core\Database;
use PDO;

class CaisseModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getAccounts() {
        return $this->db->query("
            SELECT a.*, pm.name as payment_method_name 
            FROM cash_accounts a 
            LEFT JOIN payment_methods pm ON a.payment_method_id = pm.id 
            ORDER BY a.id ASC
        ")->fetchAll();
    }

    public function getAccountsWithBalances() {
        $sql = "
            SELECT 
                a.id, 
                a.name, 
                a.payment_method_id,
                pm.name as payment_method_name,
                COALESCE(SUM(t.amount_in), 0) - COALESCE(SUM(t.amount_out), 0) as current_balance,
                COALESCE(SUM(t.amount_in), 0) as total_in,
                COALESCE(SUM(t.amount_out), 0) as total_out
            FROM cash_accounts a
            LEFT JOIN payment_methods pm ON a.payment_method_id = pm.id
            LEFT JOIN cash_transactions t ON a.id = t.cash_account_id
            GROUP BY a.id, a.name, a.payment_method_id, pm.name
            ORDER BY a.id ASC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getAccount($id) {
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
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getTransactions($limit = 100) {
        return $this->getFilteredTransactions(['limit' => $limit]);
    }

    public function getFilteredTransactions($filters = []) {
        $sql = "
            SELECT 
                t.*, 
                a.name as account_name,
                u.username,
                u.full_name as author_name,
                tour.reference as tournee_reference
            FROM cash_transactions t
            LEFT JOIN cash_accounts a ON t.cash_account_id = a.id
            LEFT JOIN users u ON t.user_id = u.id
            LEFT JOIN tournees tour ON (t.transaction_type = 'Tournee' AND t.source_id = CAST(tour.id AS CHAR))
            WHERE 1=1
        ";
        $params = [];

        // 1. Search Query
        if (!empty($filters['search'])) {
            $sql .= " AND (t.source_id LIKE ? OR t.description LIKE ? OR a.name LIKE ? OR u.username LIKE ?)";
            $term = '%' . trim($filters['search']) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        // 2. Date Range
        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(t.transaction_date) >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(t.transaction_date) <= ?";
            $params[] = $filters['end_date'];
        }

        // 3. Cash Account
        if (!empty($filters['account_id'])) {
            $sql .= " AND t.cash_account_id = ?";
            $params[] = intval($filters['account_id']);
        }

        // 4. Transaction Type
        if (!empty($filters['type'])) {
            if ($filters['type'] === 'Sale') {
                $sql .= " AND t.transaction_type = 'Sale'";
            } elseif ($filters['type'] === 'Purchase') {
                $sql .= " AND t.transaction_type = 'Purchase'";
            } elseif ($filters['type'] === 'Expense') {
                $sql .= " AND t.transaction_type = 'Expense'";
            } elseif ($filters['type'] === 'ClientPayment') {
                $sql .= " AND t.transaction_type IN ('ClientPayment', 'Client Payment')";
            } elseif ($filters['type'] === 'SupplierPayment') {
                $sql .= " AND t.transaction_type IN ('SupplierPayment', 'Supplier Payment')";
            } elseif ($filters['type'] === 'Deposit') {
                $sql .= " AND t.transaction_type = 'Deposit'";
            } elseif ($filters['type'] === 'Transfer') {
                $sql .= " AND t.transaction_type = 'Transfer'";
            } elseif ($filters['type'] === 'Payroll') {
                $sql .= " AND t.transaction_type = 'Payroll'";
            } elseif ($filters['type'] === 'Tournee') {
                $sql .= " AND t.transaction_type = 'Tournee'";
            } elseif ($filters['type'] === 'Withdrawal') {
                $sql .= " AND t.transaction_type = 'Withdrawal'";
            }
        }

        // 5. Flow Direction
        if (!empty($filters['direction'])) {
            if ($filters['direction'] === 'IN') {
                $sql .= " AND t.amount_in > 0";
            } elseif ($filters['direction'] === 'OUT') {
                $sql .= " AND t.amount_out > 0";
            }
        }

        $sql .= " ORDER BY t.transaction_date DESC, t.id DESC";

        $limit = !empty($filters['limit']) ? intval($filters['limit']) : 250;
        $sql .= " LIMIT " . $limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function addDeposit($data) {
        $amount = floatval($data['amount'] ?? 0);
        $accountId = intval($data['cash_account_id'] ?? 0);
        if ($amount <= 0) {
            throw new \Exception("Le montant de l'apport doit être supérieur à 0 FCFA.");
        }
        $account = $this->getAccount($accountId);
        if (!$account) {
            throw new \Exception("Compte récepteur de trésorerie invalide.");
        }

        $depositType = $data['deposit_type'] ?? 'Capital Initial / Fond de Caisse';
        $desc = 'Apport de Fonds : ' . $depositType;
        if (!empty($data['description'])) {
            $desc .= ' (' . $data['description'] . ')';
        }

        $ref = 'DEP' . date('YmdHis');
        $date = ($data['transaction_date'] ?: date('Y-m-d')) . ' ' . date('H:i:s');

        $stmt = $this->db->prepare("
            INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id)
            VALUES (?, 'Deposit', ?, ?, ?, ?, 0.00, 1)
        ");
        return $stmt->execute([$date, $ref, $desc, $accountId, $amount]);
    }

    public function addTransfer($data) {
        $amount = floatval($data['amount'] ?? 0);
        $sourceId = intval($data['from_account_id'] ?? 0);
        $destId = intval($data['to_account_id'] ?? 0);

        if ($amount <= 0) {
            throw new \Exception("Le montant du transfert doit être supérieur à 0 FCFA.");
        }
        if ($sourceId === $destId) {
            throw new \Exception("Le compte source et le compte destinataire doivent être différents.");
        }

        $sourceAcc = $this->getAccount($sourceId);
        $destAcc = $this->getAccount($destId);
        if (!$sourceAcc || !$destAcc) {
            throw new \Exception("Comptes de transfert invalides.");
        }

        $sourceBalance = floatval($sourceAcc['current_balance']);
        if ($amount > $sourceBalance) {
            throw new \Exception("Solde insuffisant dans " . htmlspecialchars($sourceAcc['name']) . " (Solde disponible : " . number_format($sourceBalance, 0, ',', ' ') . " FCFA, Montant à transférer : " . number_format($amount, 0, ',', ' ') . " FCFA).");
        }

        $this->db->beginTransaction();
        try {
            $transferRef = 'TRF' . date('YmdHis');
            $date = ($data['transaction_date'] ?: date('Y-m-d')) . ' ' . date('H:i:s');
            $memo = !empty($data['description']) ? ' - ' . $data['description'] : '';

            // 1. Sortie from Source Account
            $stmt1 = $this->db->prepare("
                INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id)
                VALUES (?, 'Transfer', ?, ?, ?, 0.00, ?, 1)
            ");
            $stmt1->execute([$date, $transferRef, 'Transfert Sortant Vers ' . $destAcc['name'] . $memo, $sourceId, $amount]);

            // 2. Entree into Destination Account
            $stmt2 = $this->db->prepare("
                INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id)
                VALUES (?, 'Transfer', ?, ?, ?, ?, 0.00, 1)
            ");
            $stmt2->execute([$date, $transferRef, 'Transfert Entrant Depuis ' . $sourceAcc['name'] . $memo, $destId, $amount]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function addWithdrawal($data) {
        $amount = floatval($data['amount'] ?? 0);
        $accountId = intval($data['cash_account_id'] ?? 0);

        if ($amount <= 0) {
            throw new \Exception("Le montant du retrait doit être supérieur à 0 FCFA.");
        }
        $account = $this->getAccount($accountId);
        if (!$account) {
            throw new \Exception("Compte sélectionné invalide.");
        }

        $balance = floatval($account['current_balance']);
        if ($amount > $balance) {
            throw new \Exception("Solde insuffisant dans " . htmlspecialchars($account['name']) . " (Solde disponible : " . number_format($balance, 0, ',', ' ') . " FCFA, Retrait demandé : " . number_format($amount, 0, ',', ' ') . " FCFA).");
        }

        $desc = 'Retrait de Caisse';
        if (!empty($data['description'])) {
            $desc .= ' (' . $data['description'] . ')';
        }

        $ref = 'RET' . date('YmdHis');
        $date = ($data['transaction_date'] ?: date('Y-m-d')) . ' ' . date('H:i:s');

        $stmt = $this->db->prepare("
            INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id)
            VALUES (?, 'Withdrawal', ?, ?, ?, 0.00, ?, 1)
        ");
        return $stmt->execute([$date, $ref, $desc, $accountId, $amount]);
    }
}
