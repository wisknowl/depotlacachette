<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Helper;
use App\Models\PayrollModel;
use App\Models\EmployeeLedgerModel;

class PayrollController extends Controller {
    private $model;
    private $ledgerModel;

    public function __construct() { 
        $this->model = new PayrollModel(); 
        $this->ledgerModel = new EmployeeLedgerModel();
    }

    public function index() {
        $payments = $this->model->getAll(100);
        $monthlyStats = $this->model->getMonthlyPayrollStats();
        $employees = $this->model->getEmployees(true);
        $totalEmployeeDebt = $this->model->getTotalOutstandingDebt();

        $this->view('pages/payroll/index', [
            'title' => 'Paie du Personnel & Avances sur Salaire',
            'active_menu' => 'payroll',
            'payments' => $payments,
            'stats' => $monthlyStats,
            'employees' => $employees,
            'total_employee_debt' => $totalEmployeeDebt,
            'flash_success' => $_SESSION['flash_success'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function form() {
        $employees = $this->model->getEmployees(true);
        $cashAccounts = $this->model->getCashAccountsWithBalances();

        $this->view('pages/payroll/form', [
            'title' => 'Effectuer un Paiement / Avance sur Salaire',
            'active_menu' => 'payroll',
            'employees' => $employees,
            'cash_accounts' => $cashAccounts,
            'flash_error' => $_SESSION['payroll_error'] ?? null,
            'flash_old' => $_SESSION['payroll_old'] ?? []
        ]);
        unset($_SESSION['payroll_error'], $_SESSION['payroll_old']);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $userId = $_SESSION['user']['id'] ?? 1;
                $empId = intval($_POST['employee_id'] ?? 0);
                if ($empId <= 0) {
                    throw new \Exception("Veuillez sélectionner un employé.");
                }

                $data = [
                    'payment_date' => !empty($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d'),
                    'employee_id' => $empId,
                    'payment_type' => $_POST['payment_type'] ?? 'Salary',
                    'amount' => floatval($_POST['amount'] ?? 0),
                    'cash_account_id' => intval($_POST['cash_account_id'] ?? 0),
                    'period' => trim($_POST['period'] ?? date('F Y')),
                    'reference' => trim($_POST['reference'] ?? ''),
                    'notes' => trim($_POST['notes'] ?? ''),
                    'user_id' => $userId
                ];

                $paymentId = $this->model->addPayment($data);
                $_SESSION['flash_success'] = "Paiement {$paymentId} enregistré et décaissé de la caisse avec succès !";
                $this->redirect('payroll/receipt/' . $paymentId);
            } catch (\Exception $e) {
                $_SESSION['payroll_error'] = $e->getMessage();
                $_SESSION['payroll_old'] = $_POST;
                $this->redirect('payroll/form');
            }
        }
    }

    public function receipt($id = null) {
        if (!$id) {
            $this->redirect('payroll');
            return;
        }

        $payment = $this->model->getPaymentDetails($id);
        if (!$payment) {
            $this->redirect('payroll');
            return;
        }

        $this->view('pages/payroll/receipt', [
            'title' => 'Bulletin de Paie #' . $payment['id'],
            'active_menu' => 'payroll',
            'payment' => $payment
        ]);
    }

    public function employees() {
        $employees = $this->model->getEmployees(false);

        $this->view('pages/payroll/employees', [
            'title' => 'Registre des Employés & Collaborateurs',
            'active_menu' => 'payroll',
            'employees' => $employees,
            'flash_success' => $_SESSION['flash_success'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function saveEmployee() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id = intval($_POST['id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                $role = trim($_POST['role'] ?? '');

                if (empty($name) || empty($role)) {
                    throw new \Exception("Le nom et la fonction de l'employé sont obligatoires.");
                }

                $data = [
                    'name' => $name,
                    'phone' => trim($_POST['phone'] ?? ''),
                    'role' => $role,
                    'base_salary' => floatval($_POST['base_salary'] ?? 0),
                    'status' => $_POST['status'] ?? 'Active'
                ];

                if ($id > 0) {
                    $this->model->updateEmployee($id, $data);
                    $_SESSION['flash_success'] = "Employé mis à jour avec succès !";
                } else {
                    $this->model->addEmployee($data);
                    $_SESSION['flash_success'] = "Nouvel employé enregistré avec succès !";
                }
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Erreur : " . $e->getMessage();
            }
            $this->redirect('payroll/employees');
        }
    }

    public function deleteEmployee($id = null) {
        if ($id) {
            if (!Helper::isAdmin()) {
                $_SESSION['flash_error'] = "Accès refusé : Seul l'administrateur peut modifier l'état d'un employé.";
                $this->redirect('payroll/employees');
                return;
            }

            $this->model->deleteEmployee($id);
            $_SESSION['flash_success'] = "Employé marqué comme inactif.";
        }
        $this->redirect('payroll/employees');
    }

    public function cancel($id = null) {
        if ($id) {
            if (!Helper::isAdmin()) {
                $_SESSION['flash_error'] = "Accès refusé : Seul l'administrateur est autorisé à annuler un paiement de salaire.";
                $this->redirect('payroll');
                return;
            }

            try {
                $userId = $_SESSION['user']['id'] ?? 1;
                $reason = trim($_POST['reason'] ?? 'Annulation demandée par l\'administrateur');
                $this->model->cancelPayment($id, $userId, $reason);
                $_SESSION['flash_success'] = "Paiement {$id} annulé : le montant a été réintégré dans la caisse.";
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Erreur lors de l'annulation : " . $e->getMessage();
            }
        }
        $this->redirect('payroll');
    }

    public function ledger($employeeId = null) {
        $empId = intval($employeeId ?: ($_GET['employee_id'] ?? 0));
        $type = trim($_GET['type'] ?? '');
        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');
        $search = trim($_GET['search'] ?? '');

        $filters = [
            'employee_id' => $empId,
            'transaction_type' => $type,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'search' => $search
        ];

        $entries = $this->ledgerModel->getFilteredEntries($filters, 250);
        $stats = $this->ledgerModel->getLedgerStats($empId);
        $employees = $this->model->getEmployees(false);
        $cashAccounts = $this->model->getCashAccountsWithBalances();

        $selectedEmployee = null;
        if ($empId > 0) {
            foreach ($employees as $e) {
                if ($e['id'] == $empId) {
                    $selectedEmployee = $e;
                    break;
                }
            }
        }

        $pageTitle = $selectedEmployee 
            ? 'Grand-Livre : ' . htmlspecialchars($selectedEmployee['name']) . ' (' . htmlspecialchars($selectedEmployee['role']) . ')'
            : 'Grand-Livre & Mouvements des Employés';

        $this->view('pages/payroll/ledger', [
            'title' => $pageTitle,
            'active_menu' => 'payroll',
            'entries' => $entries,
            'stats' => $stats,
            'employees' => $employees,
            'cash_accounts' => $cashAccounts,
            'selected_employee_id' => $empId,
            'selected_employee' => $selectedEmployee,
            'selected_type' => $type,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'search' => $search,
            'flash_success' => $_SESSION['flash_success'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function saveReimbursement() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $userId = $_SESSION['user']['id'] ?? 1;
                $empId = intval($_POST['employee_id'] ?? 0);
                $amount = floatval($_POST['amount'] ?? 0);
                $cashAccountId = intval($_POST['cash_account_id'] ?? 0);
                $paymentDate = !empty($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d');
                $notes = trim($_POST['notes'] ?? '');

                if ($empId <= 0) {
                    throw new \Exception("Veuillez sélectionner un employé.");
                }
                if ($amount <= 0) {
                    throw new \Exception("Le montant du remboursement doit être supérieur à 0 FCFA.");
                }
                if ($cashAccountId <= 0) {
                    throw new \Exception("Veuillez choisir la caisse de destination.");
                }

                $ref = $this->ledgerModel->recordReimbursementPayment([
                    'employee_id' => $empId,
                    'amount' => $amount,
                    'cash_account_id' => $cashAccountId,
                    'payment_date' => $paymentDate,
                    'notes' => $notes,
                    'user_id' => $userId
                ]);

                $_SESSION['flash_success'] = "Remboursement {$ref} de " . number_format($amount, 0, ',', ' ') . " FCFA enregistré avec succès ! La caisse a été créditée et le compte de l'employé a été régularisé.";
                $this->redirect('payroll/ledger?employee_id=' . $empId);
                return;
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Erreur lors du remboursement : " . $e->getMessage();
                $this->redirect('payroll/ledger' . ($empId > 0 ? '?employee_id=' . $empId : ''));
                return;
            }
        }
        $this->redirect('payroll/ledger');
    }
}
