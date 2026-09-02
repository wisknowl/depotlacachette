<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\PaiementsFournisseursModel;

class PaiementsFournisseursController extends Controller {
    private $model;
    public function __construct() { $this->model = new PaiementsFournisseursModel(); }

    public function index() {
        $search = trim($_GET['search'] ?? '');
        $supplierId = !empty($_GET['supplier_id']) ? intval($_GET['supplier_id']) : null;
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
            'supplier_id' => $supplierId,
            'cash_account_id' => $cashAccountId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        $paiements = $this->model->getFiltered($filters);
        $totalPaiements = 0;
        foreach ($paiements as $p) { $totalPaiements += floatval($p['amount']); }

        $suppliers = $this->model->getSuppliers();
        $cashAccounts = $this->model->getCashAccountsWithBalances();

        $this->view('pages/paiements_fournisseurs/index', [
            'title' => 'Règlements Fournisseurs',
            'active_menu' => 'paiements_fournisseurs',
            'paiements' => $paiements,
            'suppliers' => $suppliers,
            'cashAccounts' => $cashAccounts,
            'totalPaiements' => $totalPaiements,
            'filterSearch' => $search,
            'filterSupplierId' => $supplierId,
            'filterCashAccountId' => $cashAccountId,
            'filterPeriod' => $period,
            'filterStartDate' => $startDate,
            'filterEndDate' => $endDate,
            'flash_success' => $_SESSION['flash_success'] ?? null
        ]);
        unset($_SESSION['flash_success']);
    }

    public function form($supplierSlug = null) {
        $suppliers = $this->model->getSuppliersWithDebts();
        $selectedSupplierId = null;
        if ($supplierSlug) {
            foreach ($suppliers as $s) {
                if ($s['slug'] === $supplierSlug || strval($s['id']) === strval($supplierSlug)) {
                    $selectedSupplierId = $s['id'];
                    break;
                }
            }
        }

        $cashAccounts = $this->model->getCashAccountsWithBalances();

        $this->view('pages/paiements_fournisseurs/form', [
            'title' => 'Régler un Fournisseur',
            'active_menu' => 'paiements_fournisseurs',
            'suppliers' => $suppliers,
            'selectedSupplierId' => $selectedSupplierId,
            'cash_accounts' => $cashAccounts,
            'flash_error' => $_SESSION['pf_error'] ?? null,
            'flash_old' => $_SESSION['pf_old'] ?? []
        ]);
        unset($_SESSION['pf_error'], $_SESSION['pf_old']);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'payment_date' => !empty($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d'),
                    'supplier_id' => intval($_POST['supplier_id'] ?? 0),
                    'amount' => floatval($_POST['amount'] ?? 0),
                    'cash_account_id' => intval($_POST['cash_account_id'] ?? 0),
                    'reference' => trim($_POST['reference'] ?? ''),
                    'notes' => trim($_POST['notes'] ?? '')
                ];
                $id = $this->model->add($data);
                $_SESSION['flash_success'] = "Paiement fournisseur {$id} validé et décaissé de la caisse avec succès !";
                $this->redirect('paiementsFournisseurs/receipt/' . $id);
            } catch (\Exception $e) {
                $_SESSION['pf_error'] = $e->getMessage();
                $_SESSION['pf_old'] = $_POST;
                $supplierParam = !empty($_POST['supplier_id']) ? '/' . $_POST['supplier_id'] : '';
                $this->redirect('paiementsFournisseurs/form' . $supplierParam);
            }
        }
    }

    public function receipt($id = null) {
        if (!$id) {
            $this->redirect('paiementsFournisseurs');
            return;
        }

        $paiement = $this->model->getPaiementDetails($id);
        if (!$paiement) {
            $this->redirect('paiementsFournisseurs');
            return;
        }

        $this->view('pages/paiements_fournisseurs/receipt', [
            'title' => 'Bon de Décaissement Fournisseur #' . $paiement['id'],
            'active_menu' => 'paiements_fournisseurs',
            'paiement' => $paiement
        ]);
    }

    public function delete($id = null) {
        if ($id) { 
            // Admin only constraint
            if (!\App\Core\Helper::isAdmin()) {
                $_SESSION['flash_error'] = "Accès refusé : Seul l'administrateur est autorisé à annuler un paiement fournisseur.";
                $this->redirect('paiementsFournisseurs');
                return;
            }

            try {
                $this->model->delete($id); 
                $_SESSION['flash_success'] = "Paiement fournisseur annulé : la dette a été restaurée et la caisse a été recréditée.";
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Erreur lors de l'annulation du paiement : " . $e->getMessage();
            }
        }
        $this->redirect('paiementsFournisseurs');
    }
}
