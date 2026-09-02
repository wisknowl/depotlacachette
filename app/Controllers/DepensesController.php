<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\DepensesModel;

class DepensesController extends Controller {
    private $model;
    public function __construct() { $this->model = new DepensesModel(); }

    public function index() {
        $search = trim($_GET['search'] ?? '');
        $categoryId = !empty($_GET['category_id']) ? intval($_GET['category_id']) : null;
        $cashAccountId = !empty($_GET['cash_account_id']) ? intval($_GET['cash_account_id']) : null;
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

        $filters = [
            'search' => $search,
            'category_id' => $categoryId,
            'cash_account_id' => $cashAccountId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        $depenses = $this->model->getFiltered($filters);
        $totalAmount = 0;
        foreach ($depenses as $d) { $totalAmount += floatval($d['amount']); }

        $categories = $this->model->getCategories();
        $cashAccounts = $this->model->getCashAccountsWithBalances();

        $this->view('pages/depenses/index', [
            'title' => 'Dépenses & Charges',
            'active_menu' => 'depenses',
            'depenses' => $depenses,
            'categories' => $categories,
            'cashAccounts' => $cashAccounts,
            'totalAmount' => $totalAmount,
            'filterSearch' => $search,
            'filterCategoryId' => $categoryId,
            'filterCashAccountId' => $cashAccountId,
            'filterPeriod' => $period,
            'filterStartDate' => $startDate,
            'filterEndDate' => $endDate,
            'flash_success' => $_SESSION['flash_success'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function form($id = null) {
        $expense = $id ? $this->model->getById($id) : null;
        $this->view('pages/depenses/form', [
            'title' => $expense ? 'Détails Dépense' : 'Enregistrer une Dépense',
            'active_menu' => 'depenses',
            'expense' => $expense,
            'categories' => $this->model->getCategories(),
            'payment_methods' => $this->model->getPaymentMethods(),
            'cash_accounts' => $this->model->getCashAccountsWithBalances(),
            'flash_error' => $_SESSION['depenses_error'] ?? null,
            'flash_old' => $_SESSION['depenses_old'] ?? []
        ]);
        unset($_SESSION['depenses_error'], $_SESSION['depenses_old']);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $userId = $_SESSION['user']['id'] ?? 1;
                $data = [
                    'expense_date' => !empty($_POST['expense_date']) ? $_POST['expense_date'] : date('Y-m-d'),
                    'category_id' => intval($_POST['category_id'] ?? 0),
                    'amount' => floatval($_POST['amount'] ?? 0),
                    'payment_method_id' => intval($_POST['payment_method_id'] ?? 1),
                    'cash_account_id' => intval($_POST['cash_account_id'] ?? 0),
                    'beneficiary' => trim($_POST['beneficiary'] ?? ''),
                    'reference' => trim($_POST['reference'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'user_id' => $userId
                ];

                $expenseId = $this->model->add($data);
                $_SESSION['flash_success'] = "Dépense " . $expenseId . " enregistrée et décaissée avec succès !";
                $this->redirect('depenses/receipt/' . $expenseId);
            } catch (\Exception $e) {
                $_SESSION['depenses_error'] = $e->getMessage();
                $_SESSION['depenses_old'] = $_POST;
                $this->redirect('depenses/form');
            }
        }
    }

    public function receipt($id = null) {
        if (!$id) {
            $this->redirect('depenses');
            return;
        }

        $expense = $this->model->getExpenseDetails($id);
        if (!$expense) {
            $this->redirect('depenses');
            return;
        }

        $this->view('pages/depenses/receipt', [
            'title' => 'Bon de Décaissement Dépense #' . $expense['id'],
            'active_menu' => 'depenses',
            'expense' => $expense
        ]);
    }

    public function delete($id = null) {
        if ($id) { 
            // Admin only constraint
            if (!\App\Core\Helper::isAdmin()) {
                $_SESSION['flash_error'] = "Accès refusé : Seul l'administrateur est autorisé à annuler une dépense.";
                $this->redirect('depenses');
                return;
            }

            try {
                $this->model->delete($id); 
                $_SESSION['flash_success'] = "Dépense " . $id . " annulée : le montant a été réintégré dans la caisse.";
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Erreur lors de l'annulation de la dépense : " . $e->getMessage();
            }
        }
        $this->redirect('depenses');
    }
}