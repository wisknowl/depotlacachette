<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\CaisseModel;

class CaisseController extends Controller {
    private $model;
    public function __construct() { $this->model = new CaisseModel(); }

    public function index() {
        $accounts = $this->model->getAccountsWithBalances();

        $totalGlobalSolde = 0;
        $totalEntrees = 0;
        $totalSorties = 0;
        foreach ($accounts as $acc) {
            $totalGlobalSolde += floatval($acc['current_balance']);
            $totalEntrees += floatval($acc['total_in']);
            $totalSorties += floatval($acc['total_out']);
        }

        // Filters processing
        $search = trim($_GET['search'] ?? '');
        $accountId = !empty($_GET['account_id']) ? intval($_GET['account_id']) : null;
        $type = trim($_GET['type'] ?? '');
        $direction = trim($_GET['direction'] ?? '');
        $period = trim($_GET['period'] ?? '');

        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;

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
            'account_id' => $accountId,
            'type' => $type,
            'direction' => $direction,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'limit' => 300
        ];

        $transactions = $this->model->getFilteredTransactions($filters);

        // Filtered Totals
        $filteredIn = 0;
        $filteredOut = 0;
        foreach ($transactions as $t) {
            $filteredIn += floatval($t['amount_in']);
            $filteredOut += floatval($t['amount_out']);
        }

        $rentabiliteModel = new \App\Models\RentabiliteModel();
        $monthMetrics = $rentabiliteModel->getSummaryMetrics(date('Y-m-01'), date('Y-m-t'));

        $this->view('pages/caisse/index', [
            'title' => 'Caisse & Trésorerie',
            'active_menu' => 'caisse',
            'accounts' => $accounts,
            'transactions' => $transactions,
            'totalGlobalSolde' => $totalGlobalSolde,
            'totalEntrees' => $totalEntrees,
            'totalSorties' => $totalSorties,
            'filteredIn' => $filteredIn,
            'filteredOut' => $filteredOut,
            'filteredNet' => ($filteredIn - $filteredOut),
            'filterSearch' => $search,
            'filterAccountId' => $accountId,
            'filterType' => $type,
            'filterDirection' => $direction,
            'filterPeriod' => $period,
            'filterStartDate' => $startDate,
            'filterEndDate' => $endDate,
            'netProfitMonth' => $monthMetrics['benefice_net'],
            'netMarginPctMonth' => $monthMetrics['taux_marge_nette'],
            'flash_success' => $_SESSION['flash_success'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function form() {
        if (!\App\Core\Helper::isAdmin()) {
            $_SESSION['flash_error'] = "Accès refusé : Seul l'administrateur est autorisé à effectuer des opérations d'alimentation ou de transfert de trésorerie.";
            $this->redirect('caisse');
            return;
        }

        $this->view('pages/caisse/form', [
            'title' => 'Alimentation & Opérations de Trésorerie',
            'active_menu' => 'caisse',
            'accounts' => $this->model->getAccountsWithBalances(),
            'flash_error' => $_SESSION['caisse_error'] ?? null,
            'flash_old' => $_SESSION['caisse_old'] ?? []
        ]);
        unset($_SESSION['caisse_error'], $_SESSION['caisse_old']);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!\App\Core\Helper::isAdmin()) {
                    throw new \Exception("Action non autorisée : Seul l'administrateur peut valider une opération de trésorerie.");
                }

                $opType = $_POST['operation_type'] ?? 'deposit';
                
                if ($opType === 'deposit') {
                    $accountId = intval($_POST['deposit_account_id'] ?? ($_POST['cash_account_id'] ?? 0));
                    $data = [
                        'transaction_date' => !empty($_POST['transaction_date']) ? $_POST['transaction_date'] : date('Y-m-d'),
                        'cash_account_id' => $accountId,
                        'deposit_type' => $_POST['deposit_type'] ?? 'Capital Initial / Fond de Roulement',
                        'amount' => floatval($_POST['amount'] ?? 0),
                        'description' => trim($_POST['description'] ?? '')
                    ];
                    $this->model->addDeposit($data);
                    \App\Core\Helper::logAudit('CREATE', 'Trésorerie', $accountId, "Apport de fonds : " . number_format($data['amount'], 0, ',', ' ') . " FCFA ({$data['deposit_type']})", null, $data);
                    $_SESSION['flash_success'] = "Apport de fonds enregistré avec succès ! Le solde de caisse a été crédité.";
                } elseif ($opType === 'transfer') {
                    $data = [
                        'transaction_date' => !empty($_POST['transaction_date']) ? $_POST['transaction_date'] : date('Y-m-d'),
                        'from_account_id' => intval($_POST['from_account_id'] ?? 0),
                        'to_account_id' => intval($_POST['to_account_id'] ?? 0),
                        'amount' => floatval($_POST['amount'] ?? 0),
                        'description' => trim($_POST['description'] ?? '')
                    ];
                    $this->model->addTransfer($data);
                    \App\Core\Helper::logAudit('UPDATE', 'Trésorerie', "CP-{$data['from_account_id']}->CP-{$data['to_account_id']}", "Transfert inter-comptes de " . number_format($data['amount'], 0, ',', ' ') . " FCFA", null, $data);
                    $_SESSION['flash_success'] = "Transfert inter-comptes exécuté avec succès !";
                } elseif ($opType === 'withdrawal') {
                    $accountId = intval($_POST['withdrawal_account_id'] ?? ($_POST['cash_account_id'] ?? 0));
                    $data = [
                        'transaction_date' => !empty($_POST['transaction_date']) ? $_POST['transaction_date'] : date('Y-m-d'),
                        'cash_account_id' => $accountId,
                        'amount' => floatval($_POST['amount'] ?? 0),
                        'description' => trim($_POST['description'] ?? '')
                    ];
                    $this->model->addWithdrawal($data);
                    \App\Core\Helper::logAudit('CREATE', 'Trésorerie', $accountId, "Retrait de trésorerie de " . number_format($data['amount'], 0, ',', ' ') . " FCFA", null, $data);
                    $_SESSION['flash_success'] = "Retrait de trésorerie validé avec succès !";
                }
                
                $this->redirect('caisse');
            } catch (\Exception $e) {
                $_SESSION['caisse_error'] = $e->getMessage();
                $_SESSION['caisse_old'] = $_POST;
                $this->redirect('caisse/form');
            }
        }
    }

    public function cloture() {
        $auditModel = new \App\Models\AuditModel();
        $date = $_GET['date'] ?? date('Y-m-d');
        $accounts = $auditModel->getCashAccounts();
        $defaultAccountId = !empty($accounts) ? $accounts[0]['id'] : 1;
        $accountId = intval($_GET['account_id'] ?? $defaultAccountId);

        $summary = $auditModel->getDailyCaisseSummary($date, $accountId);

        $this->view('pages/caisse/cloture', [
            'title' => 'Clôture Journalière & Billetage de Caisse',
            'active_menu' => 'caisse',
            'summary' => $summary,
            'accounts' => $accounts,
            'selectedDate' => $date,
            'selectedAccountId' => $accountId,
            'flash_success' => $_SESSION['flash_success'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function saveCloture() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $auditModel = new \App\Models\AuditModel();
                $userId = $_SESSION['user']['id'] ?? 1;
                $date = !empty($_POST['closing_date']) ? $_POST['closing_date'] : date('Y-m-d');
                $accountId = intval($_POST['cash_account_id'] ?? 1);
                $theoretical = floatval($_POST['theoretical_balance'] ?? 0);
                $physical = floatval($_POST['physical_cash'] ?? 0);
                $diff = $physical - $theoretical;

                // Billetage array
                $billetage = [
                    'b10000' => intval($_POST['b10000'] ?? 0),
                    'b5000' => intval($_POST['b5000'] ?? 0),
                    'b2000' => intval($_POST['b2000'] ?? 0),
                    'b1000' => intval($_POST['b1000'] ?? 0),
                    'b500' => intval($_POST['b500'] ?? 0),
                    'c500' => intval($_POST['c500'] ?? 0),
                    'c100' => intval($_POST['c100'] ?? 0),
                    'c50' => intval($_POST['c50'] ?? 0),
                    'c25' => intval($_POST['c25'] ?? 0)
                ];

                $data = [
                    'closing_date' => $date,
                    'cash_account_id' => $accountId,
                    'opening_balance' => floatval($_POST['opening_balance'] ?? 0),
                    'total_in' => floatval($_POST['total_in'] ?? 0),
                    'total_out' => floatval($_POST['total_out'] ?? 0),
                    'theoretical_balance' => $theoretical,
                    'physical_cash' => $physical,
                    'difference' => $diff,
                    'billetage' => $billetage,
                    'notes' => trim($_POST['notes'] ?? ''),
                    'closed_by' => $userId
                ];

                $closingId = $auditModel->saveClosing($data);

                // System Audit Log
                $diffDesc = ($diff == 0) ? "Caisse équilibrée" : (($diff > 0) ? "Surplus de " . number_format($diff, 0, ',', ' ') . " FCFA" : "Manquant de " . number_format(abs($diff), 0, ',', ' ') . " FCFA");
                \App\Core\Helper::logAudit(
                    'CASH_CLOSING', 
                    'Caisse', 
                    $closingId, 
                    "Clôture de caisse du " . date('d/m/Y', strtotime($date)) . " - " . $diffDesc . " (Théorique: " . number_format($theoretical, 0, ',', ' ') . " FCFA, Physique: " . number_format($physical, 0, ',', ' ') . " FCFA)", 
                    null, 
                    $data, 
                    $data['notes']
                );

                $_SESSION['flash_success'] = "Clôture de caisse du " . date('d/m/Y', strtotime($date)) . " validée avec succès !";
                $this->redirect('caisse/receipt/' . $closingId);
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Erreur lors de la clôture : " . $e->getMessage();
                $this->redirect('caisse/cloture');
            }
        }
    }

    public function receipt($id = null) {
        if (!$id) {
            $this->redirect('caisse');
            return;
        }

        $auditModel = new \App\Models\AuditModel();
        $closing = $auditModel->getClosingById($id);
        if (!$closing) {
            $this->redirect('caisse');
            return;
        }

        $billetage = !empty($closing['billetage']) ? json_decode($closing['billetage'], true) : [];

        $this->view('pages/caisse/receipt', [
            'title' => 'Procès-Verbal de Clôture de Caisse #' . $closing['id'],
            'active_menu' => 'caisse',
            'closing' => $closing,
            'billetage' => $billetage
        ]);
    }
}

