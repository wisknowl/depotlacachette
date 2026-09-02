<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\StockModel;

class StockController extends Controller {
    private $model;
    public function __construct() { $this->model = new StockModel(); }

    public function index() {
        $period = trim($_GET['period'] ?? '');
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        if ($period === 'today') {
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d');
        } elseif ($period === 'week') {
            $startDate = (new \DateTime())->setISODate(intval(date('o')), intval(date('W')))->format('Y-m-d');
            $endDate = (new \DateTime())->setISODate(intval(date('o')), intval(date('W')))->modify('+6 days')->format('Y-m-d');
        } elseif ($period === 'month') {
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
        }

        $search = trim($_GET['search'] ?? '');
        $filters = [
            'search' => $search,
            'product_id' => $_GET['product_id'] ?? '',
            'movement_type_id' => $_GET['movement_type_id'] ?? '',
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        $movements = $this->model->getMovements(200, $filters);
        $allMovementTypes = $this->model->getAllMovementTypes();
        $products = $this->model->getProducts();

        $totalIn = 0;
        $totalOut = 0;
        foreach ($movements as $m) {
            if ($m['direction'] === 'IN') {
                $totalIn += floatval($m['stock_equivalent']);
            } elseif ($m['direction'] === 'OUT') {
                $totalOut += floatval($m['stock_equivalent']);
            }
        }

        $this->view('pages/stock/index', [
            'title' => 'Grand Livre des Mouvements de Stock',
            'active_menu' => 'stock',
            'movements' => $movements,
            'allMovementTypes' => $allMovementTypes,
            'products' => $products,
            'filters' => $filters,
            'filterPeriod' => $period,
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'flash_success' => $_SESSION['flash_success'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function inventaire() {
        $search = trim($_GET['search'] ?? '');
        $categoryId = !empty($_GET['category_id']) ? intval($_GET['category_id']) : null;
        $formatId = !empty($_GET['format_id']) ? intval($_GET['format_id']) : null;
        $status = trim($_GET['status'] ?? 'all');

        // Global KPI counts across all products
        $allStockItems = $this->model->getStockStatus();
        $totalCasiers = 0;
        $totalValorisation = 0;
        $countTotal = count($allStockItems);
        $countInStock = 0;
        $countAlert = 0;
        $countRupture = 0;

        foreach ($allStockItems as $item) {
            $stock = floatval($item['current_stock']);
            $alert = floatval($item['alert_stock']);
            $totalCasiers += max(0, $stock);
            $totalValorisation += (max(0, $stock) * floatval($item['purchase_price']));

            if ($stock > $alert) {
                $countInStock++;
            } elseif ($stock > 0 && $stock <= $alert) {
                $countAlert++;
            } else {
                $countRupture++;
            }
        }

        // Filtered products list
        $filterParams = [
            'search' => $search,
            'category_id' => $categoryId,
            'format_id' => $formatId,
            'status' => ($status !== 'all') ? $status : null
        ];
        $filteredItems = $this->model->getFilteredStockStatus($filterParams);

        $filteredCasiers = 0;
        $filteredValorisation = 0;
        foreach ($filteredItems as $item) {
            $stock = floatval($item['current_stock']);
            $filteredCasiers += max(0, $stock);
            $filteredValorisation += (max(0, $stock) * floatval($item['purchase_price']));
        }

        $this->view('pages/stock/inventaire', [
            'title' => 'État Réel des Stocks & Niveaux d\'Inventaire',
            'active_menu' => 'stock',
            'stockItems' => $filteredItems,
            'categories' => $this->model->getCategories(),
            'formats' => $this->model->getFormats(),
            'totalCasiers' => $totalCasiers,
            'totalValorisation' => $totalValorisation,
            'countTotal' => $countTotal,
            'countInStock' => $countInStock,
            'countAlert' => $countAlert,
            'countRupture' => $countRupture,
            'filteredCasiers' => $filteredCasiers,
            'filteredValorisation' => $filteredValorisation,
            'filterSearch' => $search,
            'filterCategoryId' => $categoryId,
            'filterFormatId' => $formatId,
            'filterStatus' => $status,
            'flash_success' => $_SESSION['flash_success'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function form() {
        if (!\App\Core\Helper::isAdmin()) {
            $_SESSION['flash_error'] = "Accès refusé : Seul l'administrateur est autorisé à déclarer des ajustements manuels ou des casses.";
            $this->redirect('stock');
            return;
        }

        $this->view('pages/stock/form', [
            'title' => 'Ajustement Manuel / Déclaration Casse',
            'active_menu' => 'stock',
            'products' => $this->model->getProducts(),
            'types' => $this->model->getMovementTypes(),
            'flash_error' => $_SESSION['stock_error'] ?? null
        ]);
        unset($_SESSION['stock_error']);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!\App\Core\Helper::isAdmin()) {
                $_SESSION['flash_error'] = "Accès refusé : Seul l'administrateur est autorisé à déclarer des ajustements manuels ou des casses.";
                $this->redirect('stock');
                return;
            }

            $ref = trim($_POST['reference'] ?? '');
            if (empty($ref)) {
                $_SESSION['stock_error'] = "Le motif explicatif / référence est obligatoire (ex: Casse transport, Inventaire fin de mois...).";
                $this->redirect('stock/form');
                return;
            }

            try {
                $userId = $_SESSION['user']['id'] ?? 1;
                $data = [
                    'movement_date' => !empty($_POST['movement_date']) ? $_POST['movement_date'] : date('Y-m-d'),
                    'product_id' => intval($_POST['product_id'] ?? 0),
                    'input_mode' => $_POST['input_mode'] ?? 'casier',
                    'format_type' => in_array($_POST['format_type'] ?? '', ['casier', 'demi']) ? $_POST['format_type'] : 'casier',
                    'movement_type_id' => intval($_POST['movement_type_id'] ?? 0),
                    'quantity' => floatval($_POST['quantity'] ?? 0),
                    'reference' => $ref,
                    'user_id' => $userId
                ];
                $this->model->addAdjustment($data);
                \App\Core\Helper::logAudit('STOCK_ADJUST', 'Stock', $data['product_id'], "Ajustement de stock ({$data['input_mode']}): {$data['quantity']} - {$ref}", null, $data, $ref);
                $_SESSION['flash_success'] = "Mouvement d'ajustement de stock enregistré avec succès !";
                $this->redirect('stock');
            } catch (\Exception $e) {
                $_SESSION['stock_error'] = "Erreur lors de l'enregistrement : " . $e->getMessage();
                $this->redirect('stock/form');
            }
        }
    }

    public function cancel($id = null) {
        if (!\App\Core\Helper::isAdmin()) {
            $_SESSION['flash_error'] = "Accès refusé : Seul l'administrateur peut annuler un mouvement de stock.";
            $this->redirect('stock');
            return;
        }

        $id = intval($id);
        if ($id <= 0) {
            $_SESSION['flash_error'] = "Identifiant de mouvement invalide.";
            $this->redirect('stock');
            return;
        }

        try {
            $userId = $_SESSION['user']['id'] ?? 1;
            $this->model->cancelMovement($id, $userId, 'Annulation manuelle admin');
            \App\Core\Helper::logAudit('CANCEL', 'Stock', $id, "Annulation du mouvement de stock #{$id} par contre-passation", null, ['movement_id' => $id], 'Annulation manuelle admin');
            $_SESSION['flash_success'] = "Le mouvement de stock #{$id} a été annulé avec succès ! Une contre-passation a rééquilibré le stock.";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Impossible d'annuler ce mouvement : " . $e->getMessage();
        }

        $this->redirect('stock');
    }
}