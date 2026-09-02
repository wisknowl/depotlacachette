<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\RentabiliteModel;
use App\Core\Helper;

class RentabiliteController extends Controller {
    private $model;

    public function __construct() {
        $this->model = new RentabiliteModel();
    }

    public function index() {
        if (!Helper::isAdmin()) {
            $_SESSION['flash_error'] = "Accès restreint : Le tableau de rentabilité et compte de résultat est réservé à l'administrateur.";
            $this->redirect('');
            return;
        }

        $period = $_GET['period'] ?? 'month';
        $today = date('Y-m-d');

        switch ($period) {
            case 'today':
                $startDate = $today;
                $endDate = $today;
                $periodLabel = "Aujourd'hui (" . date('d/m/Y') . ")";
                break;
            case 'week':
                $startOfWeek = (new \DateTime())->setISODate(intval(date('o')), intval(date('W')))->format('Y-m-d');
                $endOfWeek = (new \DateTime())->setISODate(intval(date('o')), intval(date('W')))->modify('+6 days')->format('Y-m-d');
                $startDate = $startOfWeek;
                $endDate = $endOfWeek;
                $periodLabel = "Cette Semaine (" . date('d/m', strtotime($startDate)) . " au " . date('d/m/Y', strtotime($endDate)) . ")";
                break;
            case 'last_month':
                $startDate = (new \DateTime('first day of last month'))->format('Y-m-d');
                $endDate = (new \DateTime('last day of last month'))->format('Y-m-d');
                $periodLabel = "Mois Dernier (" . date('m/Y', strtotime($startDate)) . ")";
                break;
            case 'year':
                $startDate = date('Y-01-01');
                $endDate = date('Y-12-31');
                $periodLabel = "Année " . date('Y');
                break;
            case 'custom':
                $startDate = !empty($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
                $endDate = !empty($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
                $periodLabel = "Période du " . date('d/m/Y', strtotime($startDate)) . " au " . date('d/m/Y', strtotime($endDate));
                break;
            case 'month':
            default:
                $period = 'month';
                $startDate = date('Y-m-01');
                $endDate = date('Y-m-t');
                $periodLabel = "Ce Mois (" . date('m/Y') . ")";
                break;
        }

        $metrics = $this->model->getSummaryMetrics($startDate, $endDate);
        $monthlyEvolution = $this->model->getMonthlyEvolution(6);
        $costBreakdown = $this->model->getCostBreakdown($startDate, $endDate);
        $productRanking = $this->model->getProductProfitabilityRanking($startDate, $endDate, 15);

        $this->view('pages/rentabilite/index', [
            'title' => 'Compte de Résultat & Analyse de Rentabilité',
            'active_menu' => 'rentabilite',
            'hide_sidebar' => true,
            'period' => $period,
            'periodLabel' => $periodLabel,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'metrics' => $metrics,
            'monthlyEvolution' => $monthlyEvolution,
            'costBreakdown' => $costBreakdown,
            'productRanking' => $productRanking
        ]);
    }
}
