<?php
namespace App\Models;
use App\Core\Database;
use PDO;

class PayrollModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function generatePaymentId() {
        $stmt = $this->db->query("SELECT MAX(CAST(SUBSTRING(id, 4) AS UNSIGNED)) as max_id FROM payroll_payments WHERE id LIKE 'PAY%'");
        $res = $stmt->fetch();
        $next = ($res['max_id'] ?? 0) + 1;
        return 'PAY' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    public function getEmployees($onlyActive = true) {
        $sql = "SELECT * FROM employees";
        if ($onlyActive) {
            $sql .= " WHERE status = 'Active'";
        }
        $sql .= " ORDER BY name ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getEmployeeById($id) {
        $stmt = $this->db->prepare("SELECT * FROM employees WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function addEmployee($data) {
        $stmt = $this->db->prepare("
            INSERT INTO employees (name, phone, role, base_salary, status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $data['phone'] ?? null,
            $data['role'],
            floatval($data['base_salary'] ?? 0),
            $data['status'] ?? 'Active'
        ]);
        return $this->db->lastInsertId();
    }

    public function updateEmployee($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE employees 
            SET name = ?, phone = ?, role = ?, base_salary = ?, status = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['name'],
            $data['phone'] ?? null,
            $data['role'],
            floatval($data['base_salary'] ?? 0),
            $data['status'] ?? 'Active',
            $id
        ]);
    }

    public function deleteEmployee($id) {
        // Soft delete / inactivate
        $stmt = $this->db->prepare("UPDATE employees SET status = 'Inactive' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getCashAccountsWithBalances() {
        return $this->db->query("
            SELECT 
                ca.id,
                ca.name,
                ca.payment_method_id,
                pm.name as payment_method_name,
                (COALESCE(SUM(ct.amount_in), 0) - COALESCE(SUM(ct.amount_out), 0)) as current_balance
            FROM cash_accounts ca
            LEFT JOIN payment_methods pm ON ca.payment_method_id = pm.id
            LEFT JOIN cash_transactions ct ON ca.id = ct.cash_account_id
            GROUP BY ca.id, ca.name, ca.payment_method_id, pm.name
            ORDER BY ca.id ASC
        ")->fetchAll();
    }

    public function getAll($limit = 100) {
        $stmt = $this->db->prepare("
            SELECT 
                pp.*,
                e.name as employee_name,
                e.phone as employee_phone,
                e.role as employee_role,
                ca.name as cash_account_name,
                u.username
            FROM payroll_payments pp
            JOIN employees e ON pp.employee_id = e.id
            JOIN cash_accounts ca ON pp.cash_account_id = ca.id
            JOIN users u ON pp.user_id = u.id
            ORDER BY pp.payment_date DESC, pp.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPaymentDetails($id) {
        $stmt = $this->db->prepare("
            SELECT 
                pp.*,
                e.name as employee_name,
                e.phone as employee_phone,
                e.role as employee_role,
                e.base_salary as employee_base_salary,
                ca.name as cash_account_name,
                u.username
            FROM payroll_payments pp
            JOIN employees e ON pp.employee_id = e.id
            JOIN cash_accounts ca ON pp.cash_account_id = ca.id
            JOIN users u ON pp.user_id = u.id
            WHERE pp.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function addPayment($data) {
        $amount = floatval($data['amount'] ?? 0);
        if ($amount <= 0) {
            throw new \Exception("Le montant du versement doit être strictement supérieur à 0 FCFA.");
        }

        $cashAccountId = intval($data['cash_account_id'] ?? 0);
        if ($cashAccountId <= 0) {
            throw new \Exception("Veuillez sélectionner un compte de caisse valide pour le décaissement.");
        }

        // Verify available cash balance
        $stmtBal = $this->db->prepare("
            SELECT (COALESCE(SUM(amount_in), 0) - COALESCE(SUM(amount_out), 0)) as balance
            FROM cash_transactions
            WHERE cash_account_id = ?
        ");
        $stmtBal->execute([$cashAccountId]);
        $currentBalance = floatval($stmtBal->fetchColumn() ?: 0);

        if ($amount > $currentBalance) {
            throw new \Exception("Solde insuffisant dans la caisse (" . number_format($currentBalance, 0, ',', ' ') . " FCFA disponibles). Impossible de décaisser " . number_format($amount, 0, ',', ' ') . " FCFA.");
        }

        $id = $this->generatePaymentId();
        $userId = $data['user_id'] ?? 1;
        $paymentDate = !empty($data['payment_date']) ? $data['payment_date'] : date('Y-m-d');
        $paymentType = $data['payment_type'] ?? 'Salary';
        $period = $data['period'] ?? date('F Y');
        $notes = $data['notes'] ?? '';
        $reference = $data['reference'] ?? $id;

        $emp = $this->getEmployeeById($data['employee_id']);
        $empName = $emp ? $emp['name'] : 'Employé #' . $data['employee_id'];

        $this->db->beginTransaction();
        try {
            // 1. Insert Payroll record
            $stmt = $this->db->prepare("
                INSERT INTO payroll_payments (id, payment_date, employee_id, payment_type, amount, cash_account_id, period, reference, notes, user_id, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Valid')
            ");
            $stmt->execute([
                $id,
                $paymentDate,
                $data['employee_id'],
                $paymentType,
                $amount,
                $cashAccountId,
                $period,
                $reference,
                $notes,
                $userId
            ]);

            // 2. Disburse from Caisse (cash_transactions)
            $typeLabel = ($paymentType === 'Advance') ? 'Avance sur Salaire' : (($paymentType === 'Bonus') ? 'Prime / Gratification' : 'Salaire');
            $desc = "Paiement {$typeLabel} - {$empName} (Période: {$period})";

            $stmtCaisse = $this->db->prepare("
                INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id)
                VALUES (NOW(), 'Payroll', ?, ?, ?, 0.00, ?, ?)
            ");
            $stmtCaisse->execute([
                $id,
                $desc,
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

    public function cancelPayment($id, $userId, $reason) {
        $payment = $this->getPaymentDetails($id);
        if (!$payment) {
            throw new \Exception("Paiement introuvable.");
        }
        if ($payment['status'] === 'Cancelled') {
            throw new \Exception("Ce paiement est déjà annulé.");
        }

        $this->db->beginTransaction();
        try {
            // 1. Update status
            $stmt = $this->db->prepare("UPDATE payroll_payments SET status = 'Cancelled', notes = CONCAT(COALESCE(notes, ''), '\n[ANNULÉ le ', NOW(), ' : ', ?, ']') WHERE id = ?");
            $stmt->execute([$reason, $id]);

            // 2. Re-credit cash account (inverse cash movement)
            $desc = "Annulation Paie {$id} ({$payment['employee_name']}) - Motif: {$reason}";
            $stmtCaisse = $this->db->prepare("
                INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id)
                VALUES (NOW(), 'Payroll', ?, ?, ?, ?, 0.00, ?)
            ");
            $stmtCaisse->execute([
                $id,
                $desc,
                $payment['cash_account_id'],
                $payment['amount'],
                $userId
            ]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getMonthlyPayrollStats($year = null, $month = null) {
        $year = $year ?: date('Y');
        $month = $month ?: date('m');

        $stmt = $this->db->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN payment_type = 'Salary' THEN amount ELSE 0 END), 0) as total_salaires,
                COALESCE(SUM(CASE WHEN payment_type = 'Advance' THEN amount ELSE 0 END), 0) as total_avances,
                COALESCE(SUM(CASE WHEN payment_type = 'Bonus' THEN amount ELSE 0 END), 0) as total_primes,
                COALESCE(SUM(amount), 0) as total_global,
                COUNT(id) as total_paiements
            FROM payroll_payments
            WHERE status = 'Valid'
              AND YEAR(payment_date) = ?
              AND MONTH(payment_date) = ?
        ");
        $stmt->execute([$year, $month]);
        return $stmt->fetch();
    }
}
