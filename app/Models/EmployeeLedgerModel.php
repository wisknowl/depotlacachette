<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class EmployeeLedgerModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Record a new entry in the employee ledger (debit or credit).
     * 
     * @param array $data
     * @return int
     * @throws \Exception
     */
    public function recordEntry(array $data) {
        $employeeId = intval($data['employee_id'] ?? 0);
        if ($employeeId <= 0) {
            throw new \Exception("Identifiant employé invalide pour l'écriture au grand-livre.");
        }

        $transactionDate = !empty($data['transaction_date']) ? $data['transaction_date'] : date('Y-m-d H:i:s');
        $transactionType = trim($data['transaction_type'] ?? 'Adjustment');
        $sourceType = trim($data['source_type'] ?? 'Manual');
        $sourceId = trim((string)($data['source_id'] ?? '0'));
        $reference = trim($data['reference'] ?? $sourceId);
        $amountDebit = max(0, floatval($data['amount_debit'] ?? 0));
        $amountCredit = max(0, floatval($data['amount_credit'] ?? 0));
        $description = trim($data['description'] ?? '');
        $notes = trim($data['notes'] ?? '');
        $userId = intval($data['created_by'] ?? 1);

        if ($amountDebit <= 0 && $amountCredit <= 0) {
            throw new \Exception("Le montant du débit ou du crédit doit être supérieur à zéro.");
        }

        $stmt = $this->db->prepare("
            INSERT INTO employee_ledger 
                (employee_id, transaction_date, transaction_type, source_type, source_id, reference, amount_debit, amount_credit, description, notes, created_by)
            VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $employeeId,
            $transactionDate,
            $transactionType,
            $sourceType,
            $sourceId,
            $reference,
            $amountDebit,
            $amountCredit,
            $description,
            $notes,
            $userId
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Record a cash shortage debit against an employee (e.g. delivery driver).
     */
    public function recordShortageDebit($employeeId, $sourceType, $sourceId, $reference, $amount, $description, $userId, $notes = '') {
        return $this->recordEntry([
            'employee_id' => $employeeId,
            'transaction_type' => 'Tournee_Shortage',
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'reference' => $reference,
            'amount_debit' => $amount,
            'amount_credit' => 0.00,
            'description' => $description,
            'notes' => $notes,
            'created_by' => $userId
        ]);
    }

    /**
     * Record a reimbursement credit by an employee.
     */
    public function recordReimbursementCredit($employeeId, $sourceType, $sourceId, $reference, $amount, $description, $userId, $notes = '') {
        return $this->recordEntry([
            'employee_id' => $employeeId,
            'transaction_type' => 'Shortage_Reimbursement',
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'reference' => $reference,
            'amount_debit' => 0.00,
            'amount_credit' => $amount,
            'description' => $description,
            'notes' => $notes,
            'created_by' => $userId
        ]);
    }

    /**
     * Delete/revert ledger entries matching a specific source (e.g. during tournee reopening or cancellation).
     */
    public function deleteEntriesBySource($sourceType, $sourceId, $transactionType = null) {
        $sql = "DELETE FROM employee_ledger WHERE source_type = ? AND source_id = ?";
        $params = [$sourceType, (string)$sourceId];

        if ($transactionType !== null) {
            $sql .= " AND transaction_type = ?";
            $params[] = $transactionType;
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Check if a specific ledger entry already exists (idempotency guard).
     */
    public function entryExists($sourceType, $sourceId, $transactionType = null) {
        $sql = "SELECT COUNT(*) FROM employee_ledger WHERE source_type = ? AND source_id = ?";
        $params = [$sourceType, (string)$sourceId];

        if ($transactionType !== null) {
            $sql .= " AND transaction_type = ?";
            $params[] = $transactionType;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return ($stmt->fetchColumn() > 0);
    }

    /**
     * Calculate an employee's cumulative balance.
     * Net balance > 0 means the employee owes money to the company (debt/shortage).
     * 
     * @param int $employeeId
     * @return array
     */
    public function getEmployeeBalance($employeeId) {
        $stmt = $this->db->prepare("
            SELECT 
                COALESCE(SUM(amount_debit), 0) as total_debit,
                COALESCE(SUM(amount_credit), 0) as total_credit,
                (COALESCE(SUM(amount_debit), 0) - COALESCE(SUM(amount_credit), 0)) as net_balance
            FROM employee_ledger
            WHERE employee_id = ?
        ");
        $stmt->execute([intval($employeeId)]);
        $res = $stmt->fetch();

        return [
            'total_debit' => floatval($res['total_debit'] ?? 0),
            'total_credit' => floatval($res['total_credit'] ?? 0),
            'net_balance' => floatval($res['net_balance'] ?? 0)
        ];
    }

    /**
     * Get aggregate stats for the ledger (overall or for an employee).
     */
    public function getLedgerStats($employeeId = null) {
        $sql = "
            SELECT 
                COALESCE(SUM(amount_debit), 0) as total_debit,
                COALESCE(SUM(amount_credit), 0) as total_credit,
                (COALESCE(SUM(amount_debit), 0) - COALESCE(SUM(amount_credit), 0)) as net_balance,
                COUNT(*) as total_entries
            FROM employee_ledger
        ";
        $params = [];
        if (!empty($employeeId)) {
            $sql .= " WHERE employee_id = ?";
            $params[] = intval($employeeId);
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_debit' => floatval($res['total_debit'] ?? 0),
            'total_credit' => floatval($res['total_credit'] ?? 0),
            'net_balance' => floatval($res['net_balance'] ?? 0),
            'total_entries' => intval($res['total_entries'] ?? 0)
        ];
    }

    /**
     * Get filtered ledger entries with employee and creator info.
     */
    public function getFilteredEntries($filters = [], $limit = 250) {
        $sql = "
            SELECT 
                el.*,
                e.name as employee_name,
                e.role as employee_role,
                u.username as creator_name
            FROM employee_ledger el
            JOIN employees e ON el.employee_id = e.id
            LEFT JOIN users u ON el.created_by = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['employee_id'])) {
            $sql .= " AND el.employee_id = ?";
            $params[] = intval($filters['employee_id']);
        }

        if (!empty($filters['transaction_type'])) {
            $sql .= " AND el.transaction_type = ?";
            $params[] = $filters['transaction_type'];
        }

        if (!empty($filters['source_type'])) {
            $sql .= " AND el.source_type = ?";
            $params[] = $filters['source_type'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(el.transaction_date) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(el.transaction_date) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (el.reference LIKE ? OR el.description LIKE ? OR el.notes LIKE ? OR e.name LIKE ?)";
            $term = '%' . trim($filters['search']) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY el.transaction_date DESC, el.id DESC LIMIT " . intval($limit);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Record a cash reimbursement by an employee, entering cash into caisse
     * and crediting the employee ledger atomically.
     */
    public function recordReimbursementPayment(array $data) {
        $employeeId = intval($data['employee_id'] ?? 0);
        $amount = floatval($data['amount'] ?? 0);
        $cashAccountId = intval($data['cash_account_id'] ?? 0);
        $date = !empty($data['payment_date']) ? $data['payment_date'] : date('Y-m-d');
        $notes = trim($data['notes'] ?? '');
        $userId = intval($data['user_id'] ?? 1);

        if ($employeeId <= 0) {
            throw new \Exception("Veuillez sélectionner un collaborateur.");
        }
        if ($amount <= 0) {
            throw new \Exception("Le montant du remboursement doit être supérieur à 0 FCFA.");
        }
        if ($cashAccountId <= 0) {
            throw new \Exception("Veuillez sélectionner la caisse de destination du remboursement.");
        }

        $stmtEmp = $this->db->prepare("SELECT name, role FROM employees WHERE id = ?");
        $stmtEmp->execute([$employeeId]);
        $emp = $stmtEmp->fetch(PDO::FETCH_ASSOC);
        if (!$emp) {
            throw new \Exception("Employé introuvable.");
        }

        $ref = 'RMB' . date('YmdHis');
        $desc = "Remboursement manquant / régularisation par " . $emp['name'] . " (" . $emp['role'] . ")";
        if (!empty($notes)) {
            $desc .= " - " . $notes;
        }

        $this->db->beginTransaction();
        try {
            // 1. Enter into cash_transactions (Money in)
            $txDate = $date . ' ' . date('H:i:s');
            $stmtCash = $this->db->prepare("
                INSERT INTO cash_transactions 
                    (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id)
                VALUES (?, 'Reimbursement', ?, ?, ?, ?, 0.00, ?)
            ");
            $stmtCash->execute([$txDate, $ref, $desc, $cashAccountId, $amount, $userId]);
            $cashTxId = $this->db->lastInsertId();

            // 2. Credit employee ledger
            $stmtLedger = $this->db->prepare("
                INSERT INTO employee_ledger 
                    (employee_id, transaction_date, transaction_type, source_type, source_id, reference, amount_debit, amount_credit, description, notes, created_by)
                VALUES (?, ?, 'Shortage_Reimbursement', 'Caisse', ?, ?, 0.00, ?, ?, ?, ?)
            ");
            $stmtLedger->execute([
                $employeeId,
                $txDate,
                $cashTxId,
                $ref,
                $amount,
                $desc,
                $notes,
                $userId
            ]);

            $this->db->commit();
            return $ref;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get the full ledger history for an employee.
     * 
     * @param int $employeeId
     * @param int $limit
     * @return array
     */
    public function getEmployeeLedger($employeeId, $limit = 100) {
        $stmt = $this->db->prepare("
            SELECT 
                el.*,
                e.name as employee_name,
                e.role as employee_role,
                u.username as creator_name
            FROM employee_ledger el
            JOIN employees e ON el.employee_id = e.id
            LEFT JOIN users u ON el.created_by = u.id
            WHERE el.employee_id = ?
            ORDER BY el.transaction_date DESC, el.id DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, intval($employeeId), PDO::PARAM_INT);
        $stmt->bindValue(2, intval($limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
