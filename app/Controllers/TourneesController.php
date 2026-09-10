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
        $emballagesReturnedSummary = $this->model->getTourneeReturnedEmballagesSummary($id);

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
            'emballagesReturnedSummary' => $emballagesReturnedSummary,
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

    public function rouvrir($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($id);
            if (empty($_SESSION['user']) || strtolower($_SESSION['user']['role'] ?? '') !== 'admin') {
                $_SESSION['flash_error'] = "Action non autorisée : Seul un Administrateur peut rouvrir une décharge de tournée.";
                $this->redirect('tournees/details/' . $id);
                return;
            }

            $reason = trim($_POST['reopen_reason'] ?? '');
            if (mb_strlen($reason) < 5) {
                $_SESSION['flash_error'] = "Veuillez fournir un motif valable (au moins 5 caractères) pour la réouverture de la décharge.";
                $this->redirect('tournees/details/' . $id);
                return;
            }

            try {
                $userId = $_SESSION['user']['id'] ?? 1;
                $this->model->reopenTournee($id, $userId, $reason);
                $_SESSION['flash_success'] = "Décharge réouverte avec succès ! La tournée est remise au statut 'En Route'. Vous pouvez corriger les chiffres et re-clôturer.";
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = "Erreur lors de la réouverture : " . $e->getMessage();
            }
            $this->redirect('tournees/details/' . $id);
            return;
        }
        $this->redirect('tournees');
    }

    public function annuler($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($id);
            if (empty($_SESSION['user']) || strtolower($_SESSION['user']['role'] ?? '') !== 'admin') {
                $_SESSION['flash_error'] = "Action non autorisée : Seul un Administrateur peut annuler une tournée.";
                $this->redirect('tournees/details/' . $id);
                return;
            }

            $reason = trim($_POST['cancel_reason'] ?? '');
            if (mb_strlen($reason) < 5) {
                $_SESSION['flash_error'] = "Veuillez fournir un motif valable (au moins 5 caractères) pour l'annulation de la tournée.";
                $this->redirect('tournees/details/' . $id);
                return;
            }

            try {
                $userId = $_SESSION['user']['id'] ?? 1;
                $this->model->cancelTourneeComplete($id, $userId, $reason);
                $_SESSION['flash_success'] = "Tournée annulée avec succès : toutes les factures rattachées ont été annulées et le chargement initial du matin a été restitué au magasin.";
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = "Erreur lors de l'annulation : " . $e->getMessage();
            }
            $this->redirect('tournees/details/' . $id);
            return;
        }
        $this->redirect('tournees');
    }
}
