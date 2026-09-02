<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Database;

class HomeController extends Controller {
    public function index() {
        $db = Database::getInstance()->getConnection();

        // 1. Total Chiffre Affaires
        $caStmt = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM sales WHERE status = 'Valid'");
        $totalCA = $caStmt->fetchColumn();

        // 2. Total Depenses
        $depStmt = $db->query("SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE status = 'Paid'");
        $totalDepenses = $depStmt->fetchColumn();

        // 3. Solde Caisse Global
        $caisseStmt = $db->query("SELECT COALESCE(SUM(amount_in), 0) - COALESCE(SUM(amount_out), 0) FROM cash_transactions");
        $soldeCaisse = $caisseStmt->fetchColumn();

        // 4. Total Creances Clients
        $clientDebtStmt = $db->query("
            SELECT 
                COALESCE((SELECT SUM(CASE WHEN s.amount_due > 0 THEN s.amount_due WHEN s.payment_method_id = 5 THEN s.total_amount ELSE 0 END) FROM sales s WHERE s.status = 'Valid'), 0) - 
                COALESCE((SELECT SUM(p.amount) FROM client_payments p), 0)
        ");
        $totalCreancesClients = max(0, $clientDebtStmt->fetchColumn());

        // 5. Total Dettes Fournisseurs
        $suppDebtStmt = $db->query("
            SELECT 
                COALESCE((SELECT SUM(a.total_amount) FROM purchases a WHERE a.payment_method_id = 5 AND a.status = 'Valid'), 0) - 
                COALESCE((SELECT SUM(p.amount) FROM supplier_payments p), 0)
        ");
        $totalDettesFournisseurs = max(0, $suppDebtStmt->fetchColumn());

        // 6. Ristournes / Bonus Fournisseurs (Trimestre en cours)
        $currentMonth = intval(date('n'));
        $currentQuarter = ceil($currentMonth / 3);
        $quarterStartMonth = ($currentQuarter - 1) * 3 + 1;
        $quarterEndMonth = $currentQuarter * 3;
        $quarterStartDate = date('Y') . '-' . str_pad($quarterStartMonth, 2, '0', STR_PAD_LEFT) . '-01';
        $quarterEndDate = date('Y-m-t', strtotime(date('Y') . '-' . str_pad($quarterEndMonth, 2, '0', STR_PAD_LEFT) . '-01'));

        $ristourneStmt = $db->prepare("
            SELECT COALESCE(SUM(pi.total_ristourne), 0) 
            FROM purchase_items pi
            JOIN purchases a ON pi.purchase_id = a.id
            WHERE a.status = 'Valid'
              AND a.purchase_date BETWEEN ? AND ?
        ");
        $ristourneStmt->execute([$quarterStartDate, $quarterEndDate]);
        $trimesterRistournes = $ristourneStmt->fetchColumn();

        // Ristournes Breakdown per Supplier (Current Trimester)
        $suppRistourneStmt = $db->prepare("
            SELECT s.name as supplier_name,
                   COALESCE(SUM(pi.total_ristourne), 0) as total_ristourne,
                   COALESCE(SUM(pi.stock_equivalent), 0) as total_casiers
            FROM purchase_items pi
            JOIN purchases a ON pi.purchase_id = a.id
            JOIN suppliers s ON a.supplier_id = s.id
            WHERE a.status = 'Valid'
              AND a.purchase_date BETWEEN ? AND ?
              AND pi.total_ristourne > 0
            GROUP BY s.id, s.name
            ORDER BY total_ristourne DESC
        ");
        $suppRistourneStmt->execute([$quarterStartDate, $quarterEndDate]);
        $supplierRistournes = $suppRistourneStmt->fetchAll();

        // 7. Stock Alert Count (100% event-sourced from stock_movements)
        $stockStmt = $db->query("
            SELECT COUNT(*) FROM products p 
            WHERE COALESCE((
                SELECT SUM(CASE WHEN mt.direction = 'IN' THEN m.stock_equivalent WHEN mt.direction = 'OUT' THEN -m.stock_equivalent ELSE 0 END)
                FROM stock_movements m 
                JOIN movement_types mt ON m.movement_type_id = mt.id
                WHERE m.product_id = p.id
            ), 0) <= p.alert_stock
        ");
        $alertStockCount = $stockStmt->fetchColumn();

        // 8. Recent Sales (with multi-item support)
        $recentSales = $db->query("
            SELECT s.*, c.name as client_name, pm.name as payment_method_name,
                   COUNT(si.id) as item_count,
                   COALESCE(GROUP_CONCAT(CONCAT(p.name, ' (', si.quantity, ' ', CASE WHEN si.format_type = 'demi' THEN 'Demi' ELSE 'Casier' END, ')') SEPARATOR ', '), 'Article') as product_name
            FROM sales s 
            LEFT JOIN clients c ON s.client_id = c.id 
            LEFT JOIN payment_methods pm ON s.payment_method_id = pm.id 
            LEFT JOIN sale_items si ON s.id = si.sale_id
            LEFT JOIN products p ON si.product_id = p.id
            GROUP BY s.id, s.sale_date, s.client_id, s.total_amount, s.payment_method_id, s.cash_account_id, s.reference, s.notes, s.user_id, s.status, s.created_at, c.name, pm.name
            ORDER BY s.sale_date DESC, s.created_at DESC 
            LIMIT 5
        ")->fetchAll();

        // 9. Recent Cash Transactions
        $recentTransactions = $db->query("
            SELECT t.*, a.name as account_name 
            FROM cash_transactions t 
            LEFT JOIN cash_accounts a ON t.cash_account_id = a.id 
            ORDER BY t.transaction_date DESC, t.id DESC 
            LIMIT 5
        ")->fetchAll();

        // 10. Rentabilite & Benefice Net (Mois en cours)
        $rentabiliteModel = new \App\Models\RentabiliteModel();
        $monthMetrics = $rentabiliteModel->getSummaryMetrics(date('Y-m-01'), date('Y-m-t'));

        $data = [
            'title' => 'Tableau de Bord',
            'active_menu' => 'dashboard',
            'totalCA' => $totalCA,
            'totalDepenses' => $totalDepenses,
            'soldeCaisse' => $soldeCaisse,
            'totalCreancesClients' => $totalCreancesClients,
            'totalDettesFournisseurs' => $totalDettesFournisseurs,
            'currentQuarter' => $currentQuarter,
            'trimesterRistournes' => $trimesterRistournes,
            'supplierRistournes' => $supplierRistournes,
            'alertStockCount' => $alertStockCount,
            'recentSales' => $recentSales,
            'recentTransactions' => $recentTransactions,
            'netProfitMonth' => $monthMetrics['benefice_net'],
            'netMarginPctMonth' => $monthMetrics['taux_marge_nette'],
            'grossMarginMonth' => $monthMetrics['marge_brute']
        ];
        
        $this->view('pages/home/index', $data);
    }
}
