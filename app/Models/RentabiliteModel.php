<?php
namespace App\Models;
use App\Core\Database;

class RentabiliteModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    /**
     * Compute core financial KPIs for a given period
     */
    public function getSummaryMetrics($startDate, $endDate) {
        // 1. Chiffre d'Affaires (CA)
        $stmtCA = $this->db->prepare("
            SELECT COALESCE(SUM(total_amount), 0) as total_ca
            FROM sales 
            WHERE status = 'Valid' AND sale_date BETWEEN ? AND ?
        ");
        $stmtCA->execute([$startDate, $endDate]);
        $ca = floatval($stmtCA->fetchColumn());

        // 2. Coût d'Achat des Marchandises Vendues (COGS)
        $stmtCOGS = $this->db->prepare("
            SELECT COALESCE(SUM(
                CASE 
                    WHEN si.format_type = 'demi' THEN (si.quantity * (p.purchase_price * 0.5))
                    ELSE (si.quantity * p.purchase_price)
                END
            ), 0) as total_cogs
            FROM sale_items si
            JOIN sales s ON si.sale_id = s.id
            JOIN products p ON si.product_id = p.id
            WHERE s.status = 'Valid' AND s.sale_date BETWEEN ? AND ?
        ");
        $stmtCOGS->execute([$startDate, $endDate]);
        $cogs = floatval($stmtCOGS->fetchColumn());

        // 3. Ristournes Fournisseurs Acquises
        $stmtRist = $this->db->prepare("
            SELECT COALESCE(SUM(pi.total_ristourne), 0) as total_ristourne
            FROM purchase_items pi
            JOIN purchases p ON pi.purchase_id = p.id
            WHERE p.status = 'Valid' AND p.purchase_date BETWEEN ? AND ?
        ");
        $stmtRist->execute([$startDate, $endDate]);
        $ristournes = floatval($stmtRist->fetchColumn());

        // 4. Dépenses d'Exploitation (Charges hors paie)
        $stmtExp = $this->db->prepare("
            SELECT COALESCE(SUM(amount), 0) as total_expenses
            FROM expenses
            WHERE expense_date BETWEEN ? AND ?
        ");
        $stmtExp->execute([$startDate, $endDate]);
        $depenses = floatval($stmtExp->fetchColumn());

        // 5. Masse Salariale (Paie du Personnel)
        $stmtPay = $this->db->prepare("
            SELECT COALESCE(SUM(amount), 0) as total_payroll
            FROM payroll_payments
            WHERE status = 'Valid' AND payment_date BETWEEN ? AND ?
        ");
        $stmtPay->execute([$startDate, $endDate]);
        $masseSalariale = floatval($stmtPay->fetchColumn());

        // 6. Pertes sur Casse & Démarque Inconnue (Exclut les mouvements annulés)
        $stmtLoss = $this->db->prepare("
            SELECT COALESCE(SUM(sm.stock_equivalent * sm.unit_price), 0) as total_loss
            FROM stock_movements sm
            WHERE sm.movement_type_id IN (3, 4, 6)
              AND (sm.source_type IS NULL OR sm.source_type != 'AdjustmentCancellation')
              AND sm.id NOT IN (
                  SELECT CAST(c.source_id AS UNSIGNED) 
                  FROM stock_movements c 
                  WHERE c.source_type = 'AdjustmentCancellation' AND c.source_id IS NOT NULL
              )
              AND sm.movement_date BETWEEN ? AND ?
        ");
        $stmtLoss->execute([$startDate, $endDate]);
        $pertesCasse = floatval($stmtLoss->fetchColumn());

        // 7. Écarts de Caisse (Clôtures)
        $stmtDisc = $this->db->prepare("
            SELECT COALESCE(SUM(difference), 0) as total_discrepancy
            FROM cash_closings
            WHERE closing_date BETWEEN ? AND ?
        ");
        $stmtDisc->execute([$startDate, $endDate]);
        $ecartsCaisse = floatval($stmtDisc->fetchColumn());

        // Calculations
        $margeBrute = $ca - $cogs;
        $tauxMargeBrute = ($ca > 0) ? round(($margeBrute / $ca) * 100, 2) : 0.0;
        $margeAjustee = $margeBrute + $ristournes;
        $totalCharges = $depenses + $masseSalariale + $pertesCasse;
        $beneficeNet = $margeAjustee - $totalCharges + $ecartsCaisse;
        $tauxMargeNette = ($ca > 0) ? round(($beneficeNet / $ca) * 100, 2) : 0.0;

        // Seuil de Rentabilité (Break-Even Revenue)
        $pointMort = ($tauxMargeBrute > 0) ? round($totalCharges / ($tauxMargeBrute / 100), 0) : 0;

        return [
            'ca' => $ca,
            'cogs' => $cogs,
            'marge_brute' => $margeBrute,
            'taux_marge_brute' => $tauxMargeBrute,
            'ristournes' => $ristournes,
            'marge_ajustee' => $margeAjustee,
            'depenses' => $depenses,
            'masse_salariale' => $masseSalariale,
            'pertes_casse' => $pertesCasse,
            'ecarts_caisse' => $ecartsCaisse,
            'total_charges' => $totalCharges,
            'benefice_net' => $beneficeNet,
            'taux_marge_nette' => $tauxMargeNette,
            'point_mort' => $pointMort
        ];
    }

    /**
     * Compute monthly progression for the last 6 months
     */
    public function getMonthlyEvolution($limitMonths = 6) {
        $months = [];
        for ($i = $limitMonths - 1; $i >= 0; $i--) {
            $date = new \DateTime("first day of -$i month");
            $start = $date->format('Y-m-01');
            $end = $date->format('Y-m-t');
            $label = $date->format('M Y'); // e.g. "Août 2026"
            
            $frenchMonths = [
                'Jan' => 'Jan', 'Feb' => 'Fév', 'Mar' => 'Mar', 'Apr' => 'Avr',
                'May' => 'Mai', 'Jun' => 'Juin', 'Jul' => 'Juil', 'Aug' => 'Août',
                'Sep' => 'Sept', 'Oct' => 'Oct', 'Nov' => 'Nov', 'Dec' => 'Déc'
            ];
            $monthShort = $date->format('M');
            $translatedLabel = ($frenchMonths[$monthShort] ?? $monthShort) . ' ' . $date->format('y');

            $metrics = $this->getSummaryMetrics($start, $end);
            $months[] = [
                'period' => $date->format('Y-m'),
                'label' => $translatedLabel,
                'ca' => $metrics['ca'],
                'marge_brute' => $metrics['marge_brute'],
                'charges' => $metrics['total_charges'],
                'benefice_net' => $metrics['benefice_net']
            ];
        }
        return $months;
    }

    /**
     * Breakdown of all operational costs (Expenses, Payroll, Breakages)
     */
    public function getCostBreakdown($startDate, $endDate) {
        $items = [];
        $total = 0;

        // 1. Operating Expenses by Category
        $stmt = $this->db->prepare("
            SELECT ec.name as category_name, COALESCE(SUM(e.amount), 0) as total_amount
            FROM expenses e
            JOIN expense_categories ec ON e.category_id = ec.id
            WHERE e.expense_date BETWEEN ? AND ?
            GROUP BY ec.id, ec.name
            ORDER BY total_amount DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        $expenses = $stmt->fetchAll();

        foreach ($expenses as $exp) {
            $amt = floatval($exp['total_amount']);
            if ($amt > 0) {
                $items[] = [
                    'label' => $exp['category_name'],
                    'type' => 'OPEX',
                    'amount' => $amt,
                    'color' => '#3B82F6'
                ];
                $total += $amt;
            }
        }

        // 2. Staff Payroll
        $stmtPay = $this->db->prepare("
            SELECT COALESCE(SUM(amount), 0) FROM payroll_payments 
            WHERE status = 'Valid' AND payment_date BETWEEN ? AND ?
        ");
        $stmtPay->execute([$startDate, $endDate]);
        $payrollAmt = floatval($stmtPay->fetchColumn());
        if ($payrollAmt > 0) {
            $items[] = [
                'label' => 'Masse Salariale & Primes',
                'type' => 'Payroll',
                'amount' => $payrollAmt,
                'color' => '#8B5CF6'
            ];
            $total += $payrollAmt;
        }

        // 3. Breakage & Inventory Losses (Exclut les mouvements annulés)
        $stmtLoss = $this->db->prepare("
            SELECT COALESCE(SUM(sm.stock_equivalent * sm.unit_price), 0)
            FROM stock_movements sm
            WHERE sm.movement_type_id IN (3, 4, 6)
              AND (sm.source_type IS NULL OR sm.source_type != 'AdjustmentCancellation')
              AND sm.id NOT IN (
                  SELECT CAST(c.source_id AS UNSIGNED) 
                  FROM stock_movements c 
                  WHERE c.source_type = 'AdjustmentCancellation' AND c.source_id IS NOT NULL
              )
              AND sm.movement_date BETWEEN ? AND ?
        ");
        $stmtLoss->execute([$startDate, $endDate]);
        $lossAmt = floatval($stmtLoss->fetchColumn());
        if ($lossAmt > 0) {
            $items[] = [
                'label' => 'Pertes & Casses Bouteilles',
                'type' => 'Shrinkage',
                'amount' => $lossAmt,
                'color' => '#EF4444'
            ];
            $total += $lossAmt;
        }

        // Calculate percentages
        foreach ($items as &$item) {
            $item['percentage'] = ($total > 0) ? round(($item['amount'] / $total) * 100, 1) : 0;
        }

        return [
            'total' => $total,
            'items' => $items
        ];
    }

    /**
     * Top product profitability and margin ranking
     */
    public function getProductProfitabilityRanking($startDate, $endDate, $limit = 15) {
        $stmt = $this->db->prepare("
            SELECT * FROM (
                SELECT 
                    p.id,
                    p.name as product_name,
                    p.purchase_price,
                    f.name as format_name,
                    c.name as category_name,
                    COALESCE(SUM(CASE WHEN si.format_type = 'demi' THEN si.quantity * 0.5 ELSE si.quantity END), 0) as total_casiers_sold,
                    COALESCE(SUM(si.total_price), 0) as total_revenue,
                    COALESCE(SUM(
                        CASE 
                            WHEN si.format_type = 'demi' THEN (si.quantity * (p.purchase_price * 0.5))
                            ELSE (si.quantity * p.purchase_price)
                        END
                    ), 0) as total_cogs
                FROM sale_items si
                JOIN sales s ON si.sale_id = s.id
                JOIN products p ON si.product_id = p.id
                LEFT JOIN formats f ON p.format_id = f.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE s.status = 'Valid' AND s.sale_date BETWEEN ? AND ?
                GROUP BY p.id, p.name, p.purchase_price, f.name, c.name
            ) sub
            ORDER BY (total_revenue - total_cogs) DESC
            LIMIT $limit
        ");
        $stmt->execute([$startDate, $endDate]);
        $rows = $stmt->fetchAll();

        $results = [];
        foreach ($rows as $r) {
            $rev = floatval($r['total_revenue']);
            $cogs = floatval($r['total_cogs']);
            $margin = $rev - $cogs;
            $marginPct = ($rev > 0) ? round(($margin / $rev) * 100, 1) : 0;

            $results[] = [
                'id' => $r['id'],
                'name' => $r['product_name'],
                'format_name' => $r['format_name'],
                'category_name' => $r['category_name'],
                'casiers_sold' => floatval($r['total_casiers_sold']),
                'revenue' => $rev,
                'cogs' => $cogs,
                'margin' => $margin,
                'margin_pct' => $marginPct
            ];
        }

        return $results;
    }
}
