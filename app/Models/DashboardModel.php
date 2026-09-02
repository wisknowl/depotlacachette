<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class DashboardModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getDailyMetrics() {
        $today = date('Y-m-d');
        
        // Ventes du jour
        $stmt = $this->db->prepare("SELECT SUM(total_amount) as total_ventes FROM sales WHERE sale_date = ? AND status = 'Valid'");
        $stmt->execute([$today]);
        $ventes = $stmt->fetchColumn() ?: 0;

        // Achats du jour
        $stmt = $this->db->prepare("SELECT SUM(total_amount) as total_achats FROM purchases WHERE purchase_date = ? AND status = 'Valid'");
        $stmt->execute([$today]);
        $achats = $stmt->fetchColumn() ?: 0;

        // Solde en Caisse Principale (ID 1 par defaut)
        $stmt = $this->db->query("
            SELECT SUM(amount_in) - SUM(amount_out) as solde 
            FROM cash_transactions 
            WHERE cash_account_id = 1
        ");
        $solde = $stmt->fetchColumn() ?: 0;

        // Produits en alerte stock
        // On calcule le stock actuel: initial_stock + TM_IN - TM_OUT
        // En vrai, il faudrait précalculer ça ou faire une jointure.
        // Simplification pour le dashboard: on récupère le count brut
        $stmt = $this->db->query("
            SELECT COUNT(*) FROM products p
            WHERE (
                COALESCE((SELECT SUM(stock_equivalent) FROM stock_movements sm JOIN movement_types mt ON sm.movement_type_id = mt.id WHERE sm.product_id = p.id AND mt.direction = 'IN'), 0) -
                COALESCE((SELECT SUM(stock_equivalent) FROM stock_movements sm JOIN movement_types mt ON sm.movement_type_id = mt.id WHERE sm.product_id = p.id AND mt.direction = 'OUT'), 0)
            ) <= p.alert_stock
        ");
        $alertes = $stmt->fetchColumn() ?: 0;

        return [
            'ventes' => $ventes,
            'achats' => $achats,
            'solde' => $solde,
            'alertes' => $alertes
        ];
    }
    
    public function getRecentSales() {
        $stmt = $this->db->query("
            SELECT s.id, s.sale_date, c.name as client_name, s.total_amount, u.username
            FROM sales s
            JOIN clients c ON s.client_id = c.id
            JOIN users u ON s.user_id = u.id
            ORDER BY s.created_at DESC
            LIMIT 5
        ");
        return $stmt->fetchAll();
    }
}
