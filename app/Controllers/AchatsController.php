<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\AchatsModel;

class AchatsController extends Controller {
    private $model;
    public function __construct() { $this->model = new AchatsModel(); }

    public function index() {
        $search = trim($_GET['search'] ?? '');
        $supplierId = !empty($_GET['supplier_id']) ? intval($_GET['supplier_id']) : null;
        $paymentMode = trim($_GET['payment_mode'] ?? 'all');
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

        // Global counts
        $allPurchases = $this->model->getAll();
        $countTotal = count($allPurchases);
        $countCash = 0;
        $countCredit = 0;
        $globalTotal = 0;

        foreach ($allPurchases as $p) {
            if ($p['status'] !== 'Cancelled') {
                $globalTotal += floatval($p['total_amount']);
            }
            if ($p['payment_method_id'] == 5) {
                $countCredit++;
            } else {
                $countCash++;
            }
        }

        // Filtered list
        $filters = [
            'search' => $search,
            'supplier_id' => $supplierId,
            'payment_mode' => ($paymentMode !== 'all') ? $paymentMode : null,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
        $purchases = $this->model->getFiltered($filters);

        $filteredTotal = 0;
        foreach ($purchases as $p) {
            if ($p['status'] !== 'Cancelled') {
                $filteredTotal += floatval($p['total_amount']);
            }
        }

        $suppliers = $this->model->getSuppliers();

        $this->view('pages/achats/index', [
            'title' => 'Gestion des Approvisionnements (Achats)',
            'active_menu' => 'achats',
            'achats' => $purchases,
            'suppliers' => $suppliers,
            'countTotal' => $countTotal,
            'countCash' => $countCash,
            'countCredit' => $countCredit,
            'globalTotal' => $globalTotal,
            'filteredTotal' => $filteredTotal,
            'filterSearch' => $search,
            'filterSupplierId' => $supplierId,
            'filterPaymentMode' => $paymentMode,
            'filterPeriod' => $period,
            'filterStartDate' => $startDate,
            'filterEndDate' => $endDate,
            'flash_success' => $_SESSION['flash_success'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function form() {
        $products = $this->model->getProducts();
        $suppliers = $this->model->getSuppliers();
        $cashAccounts = $this->model->getCashAccountsWithBalances();

        $this->view('pages/achats/form', [
            'title' => 'Réception de Stock & Approvisionnement',
            'active_menu' => 'achats',
            'products' => $products,
            'suppliers' => $suppliers,
            'cash_accounts' => $cashAccounts,
            'flash_error' => $_SESSION['achats_error'] ?? null,
            'flash_old' => $_SESSION['achats_old'] ?? []
        ]);
        unset($_SESSION['achats_error'], $_SESSION['achats_old']);
    }

    public function order($id = null) {
        if (!$id) {
            $this->redirect('achats');
            return;
        }

        $purchase = $this->model->getPurchaseWithItems($id);
        if (!$purchase) {
            $this->redirect('achats');
            return;
        }

        $this->view('pages/achats/order', [
            'title' => 'Bon de Réception Achat #' . $purchase['id'],
            'active_menu' => 'achats',
            'purchase' => $purchase
        ]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $userId = $_SESSION['user']['id'] ?? 1;

                $ref = trim($_POST['reference'] ?? '');
                if (empty($ref)) {
                    throw new \Exception("Le N° Facture / BL Fournisseur (Référence) est obligatoire (ex: DV-L4L24005/48149/26).");
                }

                $items = [];
                if (!empty($_POST['items']) && is_array($_POST['items'])) {
                    foreach ($_POST['items'] as $it) {
                        if (!empty($it['product_id']) && floatval($it['quantity'] ?? 0) > 0) {
                            $items[] = [
                                'product_id' => intval($it['product_id']),
                                'format_type' => in_array($it['format_type'] ?? '', ['casier', 'demi']) ? $it['format_type'] : 'casier',
                                'quantity' => floatval($it['quantity']),
                                'unit_price' => floatval($it['unit_price'] ?? 0),
                                'empties_returned' => isset($it['empties_returned']) ? floatval($it['empties_returned']) : 0,
                                'emballage_cost' => isset($it['emballage_cost']) ? floatval($it['emballage_cost']) : (isset($it['cout_emballage']) ? floatval($it['cout_emballage']) : 0),
                                'packaging_mode' => in_array($it['packaging_mode'] ?? '', ['charge', 'debt']) ? $it['packaging_mode'] : 'charge',
                                'ristourne_unit' => floatval($it['ristourne_unit'] ?? 0)
                            ];
                        }
                    }
                } elseif (!empty($_POST['product_id'])) {
                    $items[] = [
                        'product_id' => intval($_POST['product_id']),
                        'format_type' => in_array($_POST['format_type'] ?? '', ['casier', 'demi']) ? $_POST['format_type'] : 'casier',
                        'quantity' => floatval($_POST['quantity'] ?? 1),
                        'unit_price' => floatval($_POST['unit_price'] ?? 0),
                        'empties_returned' => isset($_POST['empties_returned']) ? floatval($_POST['empties_returned']) : 0,
                        'emballage_cost' => isset($_POST['emballage_cost']) ? floatval($_POST['emballage_cost']) : (isset($_POST['cout_emballage']) ? floatval($_POST['cout_emballage']) : 0),
                        'packaging_mode' => in_array($_POST['packaging_mode'] ?? '', ['charge', 'debt']) ? $_POST['packaging_mode'] : 'charge',
                        'ristourne_unit' => floatval($_POST['ristourne_unit'] ?? 0)
                    ];
                }

                $data = [
                    'purchase_date' => !empty($_POST['purchase_date']) ? $_POST['purchase_date'] : date('Y-m-d'),
                    'supplier_invoice_date' => !empty($_POST['supplier_invoice_date']) ? $_POST['supplier_invoice_date'] : date('Y-m-d'),
                    'supplier_id' => intval($_POST['supplier_id'] ?? 0),
                    'settlement_type' => $_POST['settlement_type'] ?? 'cash',
                    'cash_account_id' => intval($_POST['cash_account_id'] ?? 0),
                    'additional_fees' => floatval($_POST['additional_fees'] ?? 0),
                    'reference' => $ref,
                    'notes' => trim($_POST['notes'] ?? ''),
                    'user_id' => $userId,
                    'items' => $items
                ];

                $purchaseId = $this->model->add($data);
                \App\Core\Helper::logAudit('CREATE', 'Achats', $purchaseId, "Enregistrement du bon d'approvisionnement {$purchaseId} (Réf BL: {$ref})", null, $data);
                $_SESSION['flash_success'] = "Approvisionnement " . $purchaseId . " enregistré avec succès !";
                $this->redirect('achats/order/' . $purchaseId);
            } catch (\Exception $e) {
                $_SESSION['achats_error'] = $e->getMessage();
                $_SESSION['achats_old'] = $_POST;
                $this->redirect('achats/form');
            }
        }
    }

    public function cancel($id = null) {
        if ($id) {
            // Admin only constraint
            if (!\App\Core\Helper::isAdmin()) {
                $_SESSION['flash_error'] = "Accès refusé : Seul l'administrateur est autorisé à annuler un bon d'approvisionnement.";
                $this->redirect('achats');
                return;
            }

            try {
                $purchBefore = $this->model->getPurchaseWithItems($id);
                $userId = $_SESSION['user']['id'] ?? 1;
                $reason = trim($_POST['reason'] ?? ($_GET['reason'] ?? 'Annulation demandée par l\'administrateur'));
                $this->model->cancelPurchase($id, $userId, $reason);

                \App\Core\Helper::logAudit(
                    'CANCEL', 
                    'Achats', 
                    $id, 
                    "Annulation du bon d'achat {$id} (Fournisseur: " . ($purchBefore['supplier_name'] ?? '-') . ", Montant: " . number_format($purchBefore['total_amount'] ?? 0, 0, ',', ' ') . " FCFA)", 
                    $purchBefore, 
                    ['status' => 'Cancelled'], 
                    $reason
                );

                $_SESSION['flash_success'] = "Bon d'achat " . $id . " annulé : le stock a été décrémenté et la caisse/dette a été rééquilibrée.";
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Erreur lors de l'annulation : " . $e->getMessage();
            }
        }
        $this->redirect('achats');
    }

    public function delete($id = null) {
        return $this->cancel($id);
    }
}

