<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\ReglementsModel;

class ReglementsController extends Controller {
    private $model;
    public function __construct() { $this->model = new ReglementsModel(); }

    public function index() {
        $search = trim($_GET['search'] ?? '');
        $clientId = !empty($_GET['client_id']) ? intval($_GET['client_id']) : null;
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
            'client_id' => $clientId,
            'cash_account_id' => $cashAccountId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        $reglements = $this->model->getFiltered($filters);
        $totalReglements = 0;
        foreach ($reglements as $r) { $totalReglements += floatval($r['amount']); }

        $clients = $this->model->getClients();
        $cashAccounts = $this->model->getCashAccountsWithBalances();

        $this->view('pages/reglements/index', [
            'title' => 'Règlements & Encaissements Clients',
            'active_menu' => 'reglements',
            'reglements' => $reglements,
            'clients' => $clients,
            'cashAccounts' => $cashAccounts,
            'totalReglements' => $totalReglements,
            'filterSearch' => $search,
            'filterClientId' => $clientId,
            'filterCashAccountId' => $cashAccountId,
            'filterPeriod' => $period,
            'filterStartDate' => $startDate,
            'filterEndDate' => $endDate,
            'flash_success' => $_SESSION['flash_success'] ?? null
        ]);
        unset($_SESSION['flash_success']);
    }

    public function form($clientSlug = null) {
        $clients = $this->model->getClientsWithDebt();
        $selectedClientId = null;
        if ($clientSlug) {
            foreach ($clients as $c) {
                if ($c['slug'] === $clientSlug || strval($c['id']) === strval($clientSlug)) {
                    $selectedClientId = $c['id'];
                    break;
                }
            }
        }

        $cashAccounts = $this->model->getCashAccountsWithBalances();

        $this->view('pages/reglements/form', [
            'title' => 'Encaisser Règlement Client',
            'active_menu' => 'reglements',
            'clients' => $clients,
            'selectedClientId' => $selectedClientId,
            'cash_accounts' => $cashAccounts,
            'flash_error' => $_SESSION['reg_error'] ?? null,
            'flash_old' => $_SESSION['reg_old'] ?? []
        ]);
        unset($_SESSION['reg_error'], $_SESSION['reg_old']);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $isAdvance = !empty($_POST['is_advance']) || (!empty($_POST['payment_type']) && $_POST['payment_type'] === 'avoir');
                $data = [
                    'payment_date' => !empty($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d'),
                    'client_id' => intval($_POST['client_id'] ?? 0),
                    'amount' => floatval($_POST['amount'] ?? 0),
                    'cash_account_id' => intval($_POST['cash_account_id'] ?? 0),
                    'reference' => trim($_POST['reference'] ?? ''),
                    'notes' => trim($_POST['notes'] ?? ''),
                    'is_advance' => $isAdvance
                ];
                $id = $this->model->add($data);

                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'id' => $id, 'message' => "Paiement / Avoir {$id} enregistré avec succès !"]);
                    exit;
                }

                $_SESSION['flash_success'] = "Règlement client {$id} enregistré avec succès !";
                $this->redirect('reglements/receipt/' . $id);
            } catch (\Exception $e) {
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                    exit;
                }
                $_SESSION['reg_error'] = $e->getMessage();
                $_SESSION['reg_old'] = $_POST;
                $clientParam = !empty($_POST['client_id']) ? '/' . $_POST['client_id'] : '';
                $this->redirect('reglements/form' . $clientParam);
            }
        }
    }

    public function quickAvoir() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            exit;
        }

        try {
            $clientId = intval($_POST['client_id'] ?? 0);
            $amount = floatval($_POST['amount'] ?? 0);
            $cashAccountId = intval($_POST['cash_account_id'] ?? 0);
            $notes = trim($_POST['notes'] ?? 'Reliquat monnaie / Avoir client');
            $paymentDate = !empty($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d');

            if ($clientId <= 0) {
                throw new \Exception("Veuillez sélectionner un client.");
            }
            if ($amount <= 0) {
                throw new \Exception("Le montant de l'avoir doit être supérieur à 0 FCFA.");
            }
            if ($cashAccountId <= 0) {
                throw new \Exception("Veuillez sélectionner un compte d'encaissement.");
            }

            $data = [
                'payment_date' => $paymentDate,
                'client_id' => $clientId,
                'amount' => $amount,
                'cash_account_id' => $cashAccountId,
                'reference' => 'AVOIR-' . date('YmdHis'),
                'notes' => $notes,
                'is_advance' => true
            ];

            $paymentId = $this->model->add($data);
            $clientInfo = $this->model->getClientDebt($clientId);
            $newDebt = floatval($clientInfo['solde_du'] ?? 0);

            echo json_encode([
                'success' => true,
                'message' => "Avoir de " . number_format($amount, 0, ',', ' ') . " FCFA enregistré avec succès !",
                'payment_id' => $paymentId,
                'client_id' => $clientId,
                'amount' => $amount,
                'new_debt' => $newDebt,
                'new_avoir' => max(0, -$newDebt)
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function receipt($id = null) {
        if (!$id) {
            $this->redirect('reglements');
            return;
        }

        $payment = $this->model->getPaymentDetails($id);
        if (!$payment) {
            $this->redirect('reglements');
            return;
        }

        $this->view('pages/reglements/receipt', [
            'title' => 'Reçu de Règlement Client #' . $payment['id'],
            'active_menu' => 'reglements',
            'payment' => $payment
        ]);
    }

    public function delete($id = null) {
        if ($id) { 
            // Admin only constraint
            if (!\App\Core\Helper::isAdmin()) {
                $_SESSION['flash_error'] = "Accès refusé : Seul l'administrateur est autorisé à annuler un règlement client.";
                $this->redirect('reglements');
                return;
            }

            try {
                $this->model->delete($id); 
                $_SESSION['flash_success'] = "Règlement client annulé : la dette du client a été restaurée et le montant a été déduit de la caisse.";
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Erreur lors de l'annulation du règlement : " . $e->getMessage();
            }
        }
        $this->redirect('reglements');
    }
}
