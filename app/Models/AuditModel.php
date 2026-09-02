<?php
namespace App\Models;
use App\Core\Database;
use PDO;

class AuditModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getDailyCaisseSummary($date, $accountId) {
        $date = $date ?: date('Y-m-d');
        $accountId = intval($accountId);

        // 1. Opening balance before this day
        $stmtOpen = $this->db->prepare("
            SELECT (COALESCE(SUM(amount_in), 0) - COALESCE(SUM(amount_out), 0)) as opening_bal
            FROM cash_transactions
            WHERE cash_account_id = ?
              AND DATE(transaction_date) < ?
        ");
        $stmtOpen->execute([$accountId, $date]);
        $openingBalance = floatval($stmtOpen->fetchColumn() ?: 0);

        // 2. Day's Inflows and Outflows
        $stmtDay = $this->db->prepare("
            SELECT 
                COALESCE(SUM(amount_in), 0) as total_in,
                COALESCE(SUM(amount_out), 0) as total_out,
                COUNT(id) as trans_count
            FROM cash_transactions
            WHERE cash_account_id = ?
              AND DATE(transaction_date) = ?
        ");
        $stmtDay->execute([$accountId, $date]);
        $dayStats = $stmtDay->fetch();

        $totalIn = floatval($dayStats['total_in'] ?? 0);
        $totalOut = floatval($dayStats['total_out'] ?? 0);
        $theoreticalBalance = $openingBalance + $totalIn - $totalOut;

        // 3. Detailed breakdown of the day
        $stmtDetails = $this->db->prepare("
            SELECT 
                transaction_type,
                COALESCE(SUM(amount_in), 0) as sum_in,
                COALESCE(SUM(amount_out), 0) as sum_out,
                COUNT(id) as count_tx
            FROM cash_transactions
            WHERE cash_account_id = ?
              AND DATE(transaction_date) = ?
            GROUP BY transaction_type
        ");
        $stmtDetails->execute([$accountId, $date]);
        $details = $stmtDetails->fetchAll();

        // 4. Check if already closed today
        $stmtClosed = $this->db->prepare("
            SELECT * FROM cash_closings 
            WHERE cash_account_id = ? AND closing_date = ?
            LIMIT 1
        ");
        $stmtClosed->execute([$accountId, $date]);
        $existingClosing = $stmtClosed->fetch();

        return [
            'date' => $date,
            'cash_account_id' => $accountId,
            'opening_balance' => $openingBalance,
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'theoretical_balance' => $theoreticalBalance,
            'transaction_count' => intval($dayStats['trans_count'] ?? 0),
            'breakdown' => $details,
            'existing_closing' => $existingClosing
        ];
    }

    public function saveClosing($data) {
        $stmt = $this->db->prepare("
            INSERT INTO cash_closings (closing_date, cash_account_id, opening_balance, total_in, total_out, theoretical_balance, physical_cash, difference, billetage, notes, closed_by, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Closed')
            ON DUPLICATE KEY UPDATE
                opening_balance = VALUES(opening_balance),
                total_in = VALUES(total_in),
                total_out = VALUES(total_out),
                theoretical_balance = VALUES(theoretical_balance),
                physical_cash = VALUES(physical_cash),
                difference = VALUES(difference),
                billetage = VALUES(billetage),
                notes = VALUES(notes),
                closed_by = VALUES(closed_by),
                status = VALUES(status)
        ");
        $stmt->execute([
            $data['closing_date'],
            $data['cash_account_id'],
            $data['opening_balance'],
            $data['total_in'],
            $data['total_out'],
            $data['theoretical_balance'],
            $data['physical_cash'],
            $data['difference'],
            !empty($data['billetage']) ? json_encode($data['billetage']) : null,
            $data['notes'] ?? '',
            $data['closed_by']
        ]);
        return $this->db->lastInsertId() ?: $data['existing_id'] ?? 1;
    }

    public function getClosings($limit = 50) {
        $stmt = $this->db->prepare("
            SELECT 
                cc.*,
                ca.name as cash_account_name,
                u.username as closed_by_name
            FROM cash_closings cc
            JOIN cash_accounts ca ON cc.cash_account_id = ca.id
            JOIN users u ON cc.closed_by = u.id
            ORDER BY cc.closing_date DESC, cc.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getClosingById($id) {
        $stmt = $this->db->prepare("
            SELECT 
                cc.*,
                ca.name as cash_account_name,
                u.username as closed_by_name
            FROM cash_closings cc
            JOIN cash_accounts ca ON cc.cash_account_id = ca.id
            JOIN users u ON cc.closed_by = u.id
            WHERE cc.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    private function buildAuditSubquery() {
        return "
            SELECT 
                s.created_at as event_date,
                'Ventes' as module,
                s.id as reference_id,
                CONCAT('Vente à ', c.name, ' (', CASE WHEN s.payment_method_id = 5 THEN 'Crédit' ELSE 'Comptant' END, ')') as description,
                s.total_amount as amount,
                'IN' as flow_direction,
                s.status as status,
                u.username as author,
                s.user_id
            FROM sales s
            JOIN clients c ON s.client_id = c.id
            JOIN users u ON s.user_id = u.id

            UNION ALL

            SELECT 
                p.created_at as event_date,
                'Achats' as module,
                p.id as reference_id,
                CONCAT('Approvisionnement ', sup.name, ' (BL: ', COALESCE(p.reference, '-'), ')') as description,
                p.total_amount as amount,
                'OUT' as flow_direction,
                p.status as status,
                u.username as author,
                p.user_id
            FROM purchases p
            JOIN suppliers sup ON p.supplier_id = sup.id
            JOIN users u ON p.user_id = u.id

            UNION ALL

            SELECT 
                e.created_at as event_date,
                'Dépenses' as module,
                e.id as reference_id,
                CONCAT('Dépense: ', ec.name, ' - ', COALESCE(e.description, '')) as description,
                e.amount as amount,
                'OUT' as flow_direction,
                e.status as status,
                u.username as author,
                e.user_id
            FROM expenses e
            JOIN expense_categories ec ON e.category_id = ec.id
            JOIN users u ON e.user_id = u.id

            UNION ALL

            SELECT 
                cp.created_at as event_date,
                'Règlements' as module,
                cp.id as reference_id,
                CONCAT('Encaissement Dette Client - ', c.name) as description,
                cp.amount as amount,
                'IN' as flow_direction,
                'Valid' as status,
                u.username as author,
                cp.user_id
            FROM client_payments cp
            JOIN clients c ON cp.client_id = c.id
            JOIN users u ON cp.user_id = u.id

            UNION ALL

            SELECT 
                sp.created_at as event_date,
                'Paiements Fournisseurs' as module,
                sp.id as reference_id,
                CONCAT('Règlement Dette Fournisseur - ', sup.name) as description,
                sp.amount as amount,
                'OUT' as flow_direction,
                'Valid' as status,
                u.username as author,
                sp.user_id
            FROM supplier_payments sp
            JOIN suppliers sup ON sp.supplier_id = sup.id
            JOIN users u ON sp.user_id = u.id

            UNION ALL

            SELECT 
                pp.created_at as event_date,
                'Paie' as module,
                pp.id as reference_id,
                CONCAT('Paiement ', pp.payment_type, ' - ', emp.name, ' (', pp.period, ')') as description,
                pp.amount as amount,
                'OUT' as flow_direction,
                pp.status as status,
                u.username as author,
                pp.user_id
            FROM payroll_payments pp
            JOIN employees emp ON pp.employee_id = emp.id
            JOIN users u ON pp.user_id = u.id

            UNION ALL

            SELECT 
                sm.created_at as event_date,
                'Stock' as module,
                COALESCE(sm.source_id, CONCAT('MVT-', sm.id)) as reference_id,
                CONCAT(
                    CASE 
                        WHEN mt.direction = 'IN' THEN 'Entrée Stock: ' 
                        ELSE 'Sortie Stock: ' 
                    END,
                    prod.name, ' (', sm.quantity, ' ', 
                    CASE WHEN sm.format_type = 'demi' THEN 'Demi' ELSE 'Casier' END, 
                    ') [', mt.name, ']', 
                    CASE WHEN sm.reference IS NOT NULL AND sm.reference != '' THEN CONCAT(' - ', sm.reference) ELSE '' END
                ) as description,
                0.00 as amount,
                mt.direction as flow_direction,
                'Valid' as status,
                u.username as author,
                sm.user_id
            FROM stock_movements sm
            JOIN products prod ON sm.product_id = prod.id
            JOIN movement_types mt ON sm.movement_type_id = mt.id
            JOIN users u ON sm.user_id = u.id

            UNION ALL

            SELECT 
                em.created_at as event_date,
                'Emballages' as module,
                COALESCE(em.reference_id, CONCAT('EMB-', em.id)) as reference_id,
                CONCAT(
                    'Mvt Emballage (', em.movement_type, ') - ', pt.name, ' : ',
                    CASE 
                        WHEN (em.crates_in > 0 OR em.bottles_in > 0) THEN CONCAT('+', em.crates_in, ' casiers, +', em.bottles_in, ' btls')
                        ELSE CONCAT('-', em.crates_out, ' casiers, -', em.bottles_out, ' btls')
                    END,
                    CASE 
                        WHEN em.client_id IS NOT NULL THEN CONCAT(' [Client: ', COALESCE(c.name, ''), ']')
                        WHEN em.supplier_id IS NOT NULL THEN CONCAT(' [Fournisseur: ', COALESCE(sup.name, ''), ']')
                        ELSE ''
                    END,
                    CASE WHEN em.notes IS NOT NULL AND em.notes != '' THEN CONCAT(' - ', em.notes) ELSE '' END
                ) as description,
                COALESCE(em.total_cost, 0.00) as amount,
                CASE WHEN (em.crates_in > 0 OR em.bottles_in > 0) THEN 'IN' ELSE 'OUT' END as flow_direction,
                'Valid' as status,
                COALESCE(u.username, 'Système') as author,
                COALESCE(em.created_by, 1) as user_id
            FROM emballage_movements em
            JOIN packaging_types pt ON em.packaging_type_id = pt.id
            LEFT JOIN clients c ON em.client_id = c.id
            LEFT JOIN suppliers sup ON em.supplier_id = sup.id
            LEFT JOIN users u ON em.created_by = u.id
        ";
    }

    public function getGlobalAuditTrailCount($filters = []) {
        $whereConditions = [];
        $params = [];

        if (!empty($filters['start_date'])) {
            $whereConditions[] = "DATE(event_date) >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $whereConditions[] = "DATE(event_date) <= ?";
            $params[] = $filters['end_date'];
        }
        if (!empty($filters['user_id'])) {
            $whereConditions[] = "user_id = ?";
            $params[] = intval($filters['user_id']);
        }
        if (!empty($filters['module'])) {
            $whereConditions[] = "module = ?";
            $params[] = $filters['module'];
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        $sub = $this->buildAuditSubquery();

        $sql = "SELECT COUNT(*) FROM ({$sub}) as unified_ledger {$whereClause}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return intval($stmt->fetchColumn() ?: 0);
    }

    public function getGlobalAuditTrail($filters = [], $limit = 50, $offset = 0) {
        $whereConditions = [];
        $params = [];

        if (!empty($filters['start_date'])) {
            $whereConditions[] = "DATE(event_date) >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $whereConditions[] = "DATE(event_date) <= ?";
            $params[] = $filters['end_date'];
        }
        if (!empty($filters['user_id'])) {
            $whereConditions[] = "user_id = ?";
            $params[] = intval($filters['user_id']);
        }
        if (!empty($filters['module'])) {
            $whereConditions[] = "module = ?";
            $params[] = $filters['module'];
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        $sub = $this->buildAuditSubquery();

        $sql = "
            SELECT * FROM ({$sub}) as unified_ledger
            {$whereClause}
            ORDER BY event_date DESC
        ";

        if ($limit > 0) {
            $sql .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getSystemAuditLogsCount($filters = []) {
        $where = [];
        $params = [];

        if (!empty($filters['start_date'])) {
            $where[] = "DATE(created_at) >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where[] = "DATE(created_at) <= ?";
            $params[] = $filters['end_date'];
        }
        if (!empty($filters['user_id'])) {
            $where[] = "user_id = ?";
            $params[] = intval($filters['user_id']);
        }
        if (!empty($filters['module'])) {
            $where[] = "module = ?";
            $params[] = $filters['module'];
        }
        if (!empty($filters['action'])) {
            $where[] = "action = ?";
            $params[] = $filters['action'];
        }
        if (!empty($filters['search'])) {
            $where[] = "(description LIKE ? OR record_id LIKE ? OR user_name LIKE ? OR reason LIKE ?)";
            $term = '%' . trim($filters['search']) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM system_audit_logs {$whereClause}");
        $stmt->execute($params);
        return intval($stmt->fetchColumn() ?: 0);
    }

    public function getSystemAuditLogs($filters = [], $limit = 50, $offset = 0) {
        $where = [];
        $params = [];

        if (!empty($filters['start_date'])) {
            $where[] = "DATE(created_at) >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where[] = "DATE(created_at) <= ?";
            $params[] = $filters['end_date'];
        }
        if (!empty($filters['user_id'])) {
            $where[] = "user_id = ?";
            $params[] = intval($filters['user_id']);
        }
        if (!empty($filters['module'])) {
            $where[] = "module = ?";
            $params[] = $filters['module'];
        }
        if (!empty($filters['action'])) {
            $where[] = "action = ?";
            $params[] = $filters['action'];
        }
        if (!empty($filters['search'])) {
            $where[] = "(description LIKE ? OR record_id LIKE ? OR user_name LIKE ? OR reason LIKE ?)";
            $term = '%' . trim($filters['search']) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        $sql = "SELECT * FROM system_audit_logs {$whereClause} ORDER BY created_at DESC, id DESC";
        if ($limit > 0) {
            $sql .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getDistinctAuditActions() {
        return $this->db->query("SELECT DISTINCT action FROM system_audit_logs ORDER BY action ASC")->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getDistinctAuditModules() {
        return $this->db->query("SELECT DISTINCT module FROM system_audit_logs ORDER BY module ASC")->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getUsers() {
        return $this->db->query("SELECT id, username, full_name, role FROM users ORDER BY username ASC")->fetchAll();
    }

    public function getCashAccounts() {
        return $this->db->query("SELECT id, name FROM cash_accounts ORDER BY id ASC")->fetchAll();
    }
}
