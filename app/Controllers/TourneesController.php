<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\TourneesModel;

class TourneesController extends Controller {
    private $model;

    public function __construct() {
        $this->model = new TourneesModel();
    }

    public function index() {
        $filters = [
            'search' => !empty($_GET['search']) ? trim($_GET['search']) : null,
            'driver_id' => !empty($_GET['driver_id']) ? intval($_GET['driver_id']) : null,
            'status' => !empty($_GET['status']) ? trim($_GET['status']) : null,
            'start_date' => !empty($_GET['start_date']) ? trim($_GET['start_date']) : null,
            'end_date' => !empty($_GET['end_date']) ? trim($_GET['end_date']) : null,
        ];

        $tournees = $this->model->getAll($filters);
        $drivers = $this->model->getDrivers();

        $this->view('pages/tournees/index', [
            'title' => 'Ventes Route & Tournées de Distribution',
            'active_menu' => 'tournees',
            'tournees' => $tournees,
            'drivers' => $drivers,
            'filters' => $filters,
            'flash_success' => $_SESSION['flash_success'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function nouveau() {
        $drivers = $this->model->getDrivers();
        $products = $this->model->getAvailableProducts();

        $this->view('pages/tournees/form', [
            'title' => 'Nouveau Bon de Chargement Camion (Départ Tournée)',
            'active_menu' => 'tournees',
            'drivers' => $drivers,
            'products' => $products,
            'flash_error' => $_SESSION['tournee_form_error'] ?? null,
            'flash_old' => $_SESSION['tournee_form_old'] ?? []
        ]);
        unset($_SESSION['tournee_form_error'], $_SESSION['tournee_form_old']);
    }

    public function saveNouveau() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $userId = $_SESSION['user']['id'] ?? 1;
                $tourneeId = $this->model->createTournee($_POST, $userId);
                $_SESSION['flash_success'] = "Bon de chargement et départ de tournée enregistrés avec succès !";
                $this->redirect('tournees/details/' . $tourneeId);
                return;
            } catch (\Throwable $e) {
                $_SESSION['tournee_form_error'] = "Erreur : " . $e->getMessage();
                $_SESSION['tournee_form_old'] = $_POST;
                $this->redirect('tournees/nouveau');
                return;
            }
        }
        $this->redirect('tournees');
    }

    public function details($id) {
        $id = intval($id);
        $tournee = $this->model->getById($id);
        if (!$tournee) {
            $_SESSION['flash_error'] = "Tournée introuvable.";
            $this->redirect('tournees');
            return;
        }

        $items = $this->model->getItems($id);
        $sales = $this->model->getSales($id);
        $expenses = $this->model->getExpenses($id);
        $cashAccounts = $this->model->getCashAccounts();
        $expenseCategories = $this->model->getExpenseCategories();
        $packagingTypes = $this->model->getPackagingTypes();

        $this->view('pages/tournees/details', [
            'title' => 'Dépouillement & Décharge : ' . $tournee['reference'],
            'active_menu' => 'tournees',
            'tournee' => $tournee,
            'items' => $items,
            'sales' => $sales,
            'expenses' => $expenses,
            'cashAccounts' => $cashAccounts,
            'expenseCategories' => $expenseCategories,
            'packagingTypes' => $packagingTypes,
            'flash_success' => $_SESSION['flash_success'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function saveDecharge() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tourneeId = intval($_POST['tournee_id'] ?? 0);
            try {
                $userId = $_SESSION['user']['id'] ?? 1;
                $this->model->saveDecharge($tourneeId, $_POST, $userId);
                $_SESSION['flash_success'] = "Décharge et clôture de la tournée enregistrées avec succès ! Tous les grands livres ont été équilibrés.";
                $this->redirect('tournees/details/' . $tourneeId);
                return;
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = "Erreur lors de la décharge : " . $e->getMessage();
                $this->redirect('tournees/details/' . $tourneeId);
                return;
            }
        }
        $this->redirect('tournees');
    }

    public function imprimerChargement($id) {
        $id = intval($id);
        $tournee = $this->model->getById($id);
        if (!$tournee) {
            $_SESSION['flash_error'] = "Tournée introuvable.";
            $this->redirect('tournees');
            return;
        }

        $items = $this->model->getItems($id);

        $this->view('pages/tournees/print_chargement', [
            'title' => 'Bon de Chargement - ' . $tournee['reference'],
            'tournee' => $tournee,
            'items' => $items,
            'hideSidebar' => true
        ]);
    }

    public function annuler($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($id);
            $reason = trim($_POST['cancel_reason'] ?? 'Annulation par l\'utilisateur');
            try {
                $userId = $_SESSION['user']['id'] ?? 1;
                $this->model->cancelTournee($id, $userId, $reason);
                $_SESSION['flash_success'] = "Tournée annulée avec succès et stock réintégré intégralement en magasin.";
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = "Erreur : " . $e->getMessage();
            }
            $this->redirect('tournees/details/' . $id);
            return;
        }
        $this->redirect('tournees');
    }
}
