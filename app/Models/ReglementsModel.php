<?php
namespace App\Models;
use App\Core\Database;
use PDO;

class ReglementsModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getAll() {
        return $this->getFiltered([]);
    }

    public function getFiltered($filters = []) {
        $sql = "
            SELECT 
                p.*,
                c.name as client_name,
                pm.name as payment_method_name,
                a.name as cash_account_name
            FROM client_payments p
            LEFT JOIN clients c ON p.client_id = c.id
            LEFT JOIN payment_methods pm ON p.payment_method_id = pm.id
            LEFT JOIN cash_accounts a ON p.cash_account_id = a.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (p.id LIKE ? OR p.reference LIKE ? OR c.name LIKE ? OR p.notes LIKE ?)";
            $term = '%' . trim($filters['search']) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['client_id'])) {
            $sql .= " AND p.client_id = ?";
            $params[] = intval($filters['client_id']);
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

    public function getClients() {
        return $this->db->query("SELECT * FROM clients ORDER BY name ASC")->fetchAll();
    }

    public function getClientsWithDebt() {
        $sql = "
            SELECT 
                c.id, 
                c.name, 
                c.slug,
                c.phone,
                c.max_credit,
                ct.name as type_name,
                COALESCE((SELECT SUM(CASE WHEN v.amount_due > 0 THEN v.amount_due WHEN v.payment_method_id = 5 THEN v.total_amount ELSE 0 END) FROM sales v WHERE v.client_id = c.id AND v.status = 'Valid'), 0) as total_credit_sales,
                COALESCE((SELECT SUM(p.amount) FROM client_payments p WHERE p.client_id = c.id), 0) as total_paid,
                (COALESCE((SELECT SUM(CASE WHEN v.amount_due > 0 THEN v.amount_due WHEN v.payment_method_id = 5 THEN v.total_amount ELSE 0 END) FROM sales v WHERE v.client_id = c.id AND v.status = 'Valid'), 0) - 
                 COALESCE((SELECT SUM(p.amount) FROM client_payments p WHERE p.client_id = c.id), 0)) as solde_du
            FROM clients c
            LEFT JOIN client_types ct ON c.client_type_id = ct.id
            ORDER BY solde_du DESC, c.name ASC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getClientDebt($clientId) {
        $stmt = $this->db->prepare("
            SELECT 
                c.id,
                c.name,
                c.max_credit,
                (COALESCE((SELECT SUM(CASE WHEN v.amount_due > 0 THEN v.amount_due WHEN v.payment_method_id = 5 THEN v.total_amount ELSE 0 END) FROM sales v WHERE v.client_id = c.id AND v.status = 'Valid'), 0) - 
                 COALESCE((SELECT SUM(p.amount) FROM client_payments p WHERE p.client_id = c.id), 0)) as solde_du
            FROM clients c
            WHERE c.id = ?
        ");
        $stmt->execute([$clientId]);
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

    private function generatePaymentId() {
        $stmt = $this->db->query("SELECT id FROM client_payments ORDER BY id DESC LIMIT 1");
        $last = $stmt->fetchColumn();
        if ($last && preg_match('/RC(\d+)/', $last, $matches)) {
            $num = intval($matches[1]) + 1;
            return 'RC' . str_pad($num, 5, '0', STR_PAD_LEFT);
        }
        return 'RC00001';
    }

    public function add($data) {
        $amount = floatval($data['amount']);
        if ($amount <= 0) {
            throw new \Exception("Le montant du versement doit être strictement supérieur à 0 FCFA.");
        }

        // 1. Validate Client & Debt
        $clientInfo = $this->getClientDebt($data['client_id']);
        if (!$clientInfo) {
            throw new \Exception("Client sélectionné invalide ou introuvable.");
        }
        $debt = floatval($clientInfo['solde_du']);
        $isAdvance = !empty($data['is_advance']) || !empty($data['allow_advance']);

        if (!$isAdvance) {
            if ($debt <= 0) {
                // If not explicitly flagged as advance but client has no debt, ask or treat as advance
                throw new \Exception("Ce client n'a aucune dette en cours (" . htmlspecialchars($clientInfo['name']) . " : 0 FCFA). Cochez 'Acompte / Avoir' pour enregistrer un crédit client.");
            }
        }

        // 2. Validate Cash Account & Auto-infer payment method
        $accountInfo = $this->getCashAccount($data['cash_account_id']);
        if (!$accountInfo) {
            throw new \Exception("Compte d'encaissement (caisse) invalide.");
        }
        $paymentMethodId = $accountInfo['payment_method_id'] ?: 1;

        $id = $this->generatePaymentId();
        $userId = intval($data['user_id'] ?? ($_SESSION['user']['id'] ?? 1));
        $this->db->beginTransaction();
        try {
            // Insert into client_payments
            $stmt = $this->db->prepare("
                INSERT INTO client_payments (id, payment_date, client_id, amount, payment_method_id, cash_account_id, reference, notes, user_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id,
                $data['payment_date'],
                $data['client_id'],
                $amount,
                $paymentMethodId,
                $data['cash_account_id'],
                $data['reference'] ?: $id,
                $data['notes'] ?? '',
                $userId
            ]);

            // Log into cash_transactions (Encaissement : +Montant)
            $desc = ($debt <= 0 || $isAdvance) 
                ? ('Avoir / Reliquat Monnaie Client ' . $id . ' (' . $clientInfo['name'] . ')')
                : ('Règlement Dette Client ' . $id . ' (' . $clientInfo['name'] . ')');

            $stmtCash = $this->db->prepare("
                INSERT INTO cash_transactions (transaction_date, transaction_type, source_id, description, cash_account_id, amount_in, amount_out, user_id)
                VALUES (?, 'ClientPayment', ?, ?, ?, ?, 0.00, ?)
            ");
            $stmtCash->execute([
                $data['payment_date'] . ' ' . date('H:i:s'),
                $id,
                $desc,
                $data['cash_account_id'],
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

    public function getPaymentDetails($id) {
        $stmt = $this->db->prepare("
            SELECT 
                p.*,
                c.name as client_name,
                c.phone as client_phone,
                c.address as client_address,
                ct.name as client_type_name,
                pm.name as payment_method_name,
                a.name as cash_account_name,
                COALESCE(u.full_name, u.username) as user_name,
                (COALESCE((SELECT SUM(v.total_amount) FROM sales v WHERE v.client_id = c.id AND v.payment_method_id = 5 AND v.status = 'Valid'), 0) - 
                 COALESCE((SELECT SUM(cp.amount) FROM client_payments cp WHERE cp.client_id = c.id), 0)) as current_remaining_debt
            FROM client_payments p
            LEFT JOIN clients c ON p.client_id = c.id
            LEFT JOIN client_types ct ON c.client_type_id = ct.id
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
            $this->db->prepare("DELETE FROM cash_transactions WHERE source_id = ? AND transaction_type IN ('ClientPayment', 'Client Payment')")->execute([$id]);
            $this->db->prepare("DELETE FROM client_payments WHERE id = ?")->execute([$id]);
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
