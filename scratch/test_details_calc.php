<?php
require 'config/config.php';
require 'app/Core/Database.php';
require 'app/Models/TourneesModel.php';

$m = new App\Models\TourneesModel();
$sales = $m->getSales(8);

$totalSoldFromSales = 0;
$totalCashFromSales = 0;
$totalCreditFromSales = 0;

foreach ($sales as $s) {
    if (in_array($s['status'], ['En_Route', 'Valid'])) {
        $totalSoldFromSales += floatval($s['total_amount']);
        $totalCreditFromSales += ($s['payment_method_id'] == 5) ? floatval($s['total_amount']) : floatval($s['amount_due']);
        if ($s['payment_method_id'] != 5) {
            $totalCashFromSales += floatval($s['amount_paid'] ?: $s['total_amount']) + floatval($s['excess_amount'] ?? 0);
        }
    }
}

echo "CALCULATION RESULT FOR TR00002:\n";
echo "- Total Sold: " . number_format($totalSoldFromSales, 0, ',', ' ') . " FCFA\n";
echo "- Total Cash (Encaissé): " . number_format($totalCashFromSales, 0, ',', ' ') . " FCFA\n";
echo "- Total Credit: " . number_format($totalCreditFromSales, 0, ',', ' ') . " FCFA\n";
echo "- Net Cash Attendu (Section 6): " . number_format($totalCashFromSales, 0, ',', ' ') . " FCFA\n";
