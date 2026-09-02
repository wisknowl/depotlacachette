<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\EmballagesModel;

class EmballagesController extends Controller {
    private $model;
    public function __construct() { $this->model = new EmballagesModel(); }

    public function index() {
        $summary = $this->model->getGlobalSummary();
        $clientDebts = $this->model->getClientDebts();
        $supplierDebts = $this->model->getSupplierDebts();
        $recentMovements = $this->model->getMovements(['limit' => 15]);

        $this->view('pages/emballages/index', [
            'title' => 'Gestion du Parc d\'Emballages (Casiers & Bouteilles)',
            'active_menu' => 'emballages',
            'summary' => $summary,
            'clientDebts' => $clientDebts,
            'supplierDebts' => $supplierDebts,
            'recentMovements' => $recentMovements,
            'packagingTypes' => $this->model->getPackagingTypes(),
            'clients' => $this->model->getClients(),
            'suppliers' => $this->model->getSuppliers(),
            'flash_success' => $_SESSION['flash_success'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function achat() {
        $this->view('pages/emballages/form_achat', [
            'title' => 'Acheter des Emballages Vides (Casiers & Bouteilles)',
            'active_menu' => 'emballages',
            'packagingTypes' => $this->model->getPackagingTypes(),
            'suppliers' => $this->model->getSuppliers(),
            'cashAccounts' => $this->model->getCashAccountsWithBalances(),
            'paymentMethods' => $this->model->getPaymentMethods(),
            'flash_error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_error']);
    }

    public function saveAchat() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $userId = $_SESSION['user']['id'] ?? 1;
                $this->model->recordEmptyPurchase($_POST, $userId);
                $_SESSION['flash_success'] = "Achat d'emballages enregistré avec succès ! Le stock et la caisse ont été mis à jour.";
                $this->redirect('emballages');
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = "Erreur lors de l'achat d'emballages : " . $e->getMessage();
                $this->redirect('emballages/achat');
            }
        }
    }

    public function saveRestitutionClient() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $userId = $_SESSION['user']['id'] ?? 1;
                $clientId = intval($_POST['client_id']);
                $pkgId = intval($_POST['packaging_type_id']);
                $crates = intval($_POST['crates_returned'] ?? 0);
                $bottles = intval($_POST['loose_bottles_returned'] ?? 0);
                $notes = trim($_POST['notes'] ?? '');

                $this->model->recordClientReturn($clientId, $pkgId, $crates, $bottles, $notes, $userId);
                $_SESSION['flash_success'] = "Restitution d'emballages client enregistrée avec succès !";
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = "Erreur lors de la restitution : " . $e->getMessage();
            }
        }
        $this->redirect('emballages');
    }

    public function saveRestitutionFournisseur() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $userId = $_SESSION['user']['id'] ?? 1;
                $supplierId = intval($_POST['supplier_id']);
                $pkgId = intval($_POST['packaging_type_id']);
                $crates = intval($_POST['crates_returned'] ?? 0);
                $bottles = intval($_POST['loose_bottles_returned'] ?? 0);
                $notes = trim($_POST['notes'] ?? '');

                $this->model->recordSupplierReturn($supplierId, $pkgId, $crates, $bottles, $notes, $userId);
                $_SESSION['flash_success'] = "Restitution d'emballages au fournisseur enregistrée avec succès !";
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = "Erreur lors de la restitution : " . $e->getMessage();
            }
        }
        $this->redirect('emballages');
    }

    public function ajustement() {
        if (!\App\Core\Helper::isAdmin()) {
            $_SESSION['flash_error'] = "Accès refusé : Seul l'administrateur est autorisé à effectuer des ajustements d'emballages.";
            $this->redirect('emballages');
            return;
        }

        $this->view('pages/emballages/ajustement', [
            'title' => 'Ajustement & Alimentation du Parc d\'Emballages',
            'active_menu' => 'emballages',
            'packagingTypes' => $this->model->getPackagingTypes(),
            'flash_error' => $_SESSION['emballage_adj_error'] ?? null,
            'flash_old' => $_SESSION['emballage_adj_old'] ?? []
        ]);
        unset($_SESSION['emballage_adj_error'], $_SESSION['emballage_adj_old']);
    }

    public function saveAjustement() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!\App\Core\Helper::isAdmin()) {
                $_SESSION['flash_error'] = "Accès refusé : Seul l'administrateur est autorisé à enregistrer des ajustements d'emballages.";
                $this->redirect('emballages');
                return;
            }

            $notes = trim($_POST['notes'] ?? '');
            if (empty($notes)) {
                $_SESSION['emballage_adj_error'] = "Le motif / justification explicative est obligatoire (ex: Stock initial ouverture, Casse casiers vides, Inventaire physique...).";
                $_SESSION['emballage_adj_old'] = $_POST;
                $this->redirect('emballages/ajustement');
                return;
            }

            try {
                $userId = $_SESSION['user']['id'] ?? 1;
                $this->model->recordAdjustment($_POST, $userId);
                $_SESSION['flash_success'] = "Mouvement d'ajustement du parc d'emballages enregistré avec succès !";
                $this->redirect('emballages');
            } catch (\Throwable $e) {
                $_SESSION['emballage_adj_error'] = "Erreur : " . $e->getMessage();
                $_SESSION['emballage_adj_old'] = $_POST;
                $this->redirect('emballages/ajustement');
            }
        }
    }

    public function mouvements() {
        $filters = [
            'packaging_type_id' => !empty($_GET['packaging_type_id']) ? intval($_GET['packaging_type_id']) : null,
            'movement_type' => !empty($_GET['movement_type']) ? trim($_GET['movement_type']) : null,
            'search' => !empty($_GET['search']) ? trim($_GET['search']) : null,
            'start_date' => !empty($_GET['start_date']) ? trim($_GET['start_date']) : null,
            'end_date' => !empty($_GET['end_date']) ? trim($_GET['end_date']) : null
        ];

        $movements = $this->model->getMovements($filters);

        $this->view('pages/emballages/mouvements', [
            'title' => 'Journal des Mouvements d\'Emballages',
            'active_menu' => 'emballages',
            'movements' => $movements,
            'packagingTypes' => $this->model->getPackagingTypes(),
            'filters' => $filters
        ]);
    }
}
