<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\VentesModel;

class VentesController extends Controller {
    private $model;
    public function __construct() { $this->model = new VentesModel(); }

    public function index() {
        $search = trim($_GET['search'] ?? '');
        $clientId = !empty($_GET['client_id']) ? intval($_GET['client_id']) : null;
        $paymentMode = trim($_GET['payment_mode'] ?? 'all');
        $saleType = trim($_GET['sale_type'] ?? 'all');
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

        // Global counts
        $allSales = $this->model->getAll();
        $countTotal = count($allSales);
        $countCash = 0;
        $countCredit = 0;
        $globalTotal = 0;

        foreach ($allSales as $s) {
            if ($s['status'] !== 'Cancelled') {
                $globalTotal += floatval($s['total_amount']);
            }
            if ($s['payment_method_id'] == 5) {
                $countCredit++;
            } else {
                $countCash++;
            }
        }

        // Filtered list
        $filters = [
            'search' => $search,
            'client_id' => $clientId,
            'payment_mode' => ($paymentMode !== 'all') ? $paymentMode : null,
            'sale_type' => ($saleType !== 'all') ? $saleType : null,
            'cash_account_id' => $cashAccountId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
        $sales = $this->model->getFiltered($filters);

        $filteredTotal = 0;
        foreach ($sales as $s) {
            if ($s['status'] !== 'Cancelled') {
                $filteredTotal += floatval($s['total_amount']);
            }
        }

        $clients = $this->model->getClients();
        $cashAccounts = $this->model->getCashAccountsWithBalances();

        $this->view('pages/ventes/index', [
            'title' => 'Ventes & Facturation',
            'active_menu' => 'ventes',
            'ventes' => $sales,
            'clients' => $clients,
            'cashAccounts' => $cashAccounts,
            'countTotal' => $countTotal,
            'countCash' => $countCash,
            'countCredit' => $countCredit,
            'globalTotal' => $globalTotal,
            'filteredTotal' => $filteredTotal,
            'filterSearch' => $search,
            'filterClientId' => $clientId,
            'filterPaymentMode' => $paymentMode,
            'filterSaleType' => $saleType,
            'filterCashAccountId' => $cashAccountId,
            'filterPeriod' => $period,
            'filterStartDate' => $startDate,
            'filterEndDate' => $endDate,
            'flash_success' => $_SESSION['flash_success'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function form() {
        $tourneeId = !empty($_GET['tournee_id']) ? intval($_GET['tournee_id']) : null;
        $tournee = null;
        $products = [];
        if ($tourneeId) {
            $tModel = new \App\Models\TourneesModel();
            $tournee = $tModel->getById($tourneeId);
            if ($tournee) {
                $products = $tModel->getTourneeProductsForSale($tourneeId);
            }
        }

        if (empty($tournee)) {
            $products = $this->model->getProducts();
        }

        $this->view('pages/ventes/form', [
            'title' => $tournee ? ('Saisie Vente Carnet - ' . $tournee['reference']) : 'Nouvelle Vente Multi-Produits',
            'active_menu' => $tournee ? 'tournees' : 'ventes',
            'clients' => $this->model->getClients(),
            'products' => $products,
            'cash_accounts' => $this->model->getCashAccountsWithBalances(),
            'tournee' => $tournee,
            'tournee_id' => $tourneeId,
            'is_tournee' => !empty($tournee),
            'flash_error' => $_SESSION['ventes_error'] ?? null,
            'flash_old' => $_SESSION['ventes_old'] ?? []
        ]);
        unset($_SESSION['ventes_error'], $_SESSION['ventes_old']);
    }

    public function enDetail() {
        $this->view('pages/ventes/en_detail', [
            'title' => 'Vente au Détail (À la Bouteille)',
            'active_menu' => 'ventes',
            'clients' => $this->model->getClients(),
            'products' => $this->model->getProducts(),
            'cash_accounts' => $this->model->getCashAccountsWithBalances(),
            'flash_error' => $_SESSION['ventes_detail_error'] ?? null,
            'flash_old' => $_SESSION['ventes_detail_old'] ?? []
        ]);
        unset($_SESSION['ventes_detail_error'], $_SESSION['ventes_detail_old']);
    }

    public function invoice($id = null) {
        if (!$id) {
            $this->redirect('ventes');
        }
        $sale = $this->model->getSaleWithItems($id);
        if (!$sale) {
            $_SESSION['flash_error'] = "Facture introuvable.";
            $this->redirect('ventes');
        }
        $this->view('pages/ventes/invoice', [
            'title' => 'Facture N° ' . $sale['id'],
            'active_menu' => 'ventes',
            'sale' => $sale
        ]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $userId = $_SESSION['user']['id'] ?? 1;
                $tourneeId = !empty($_POST['tournee_id']) ? intval($_POST['tournee_id']) : null;
                
                // Parse Multi-item Lines
                $items = [];
                if (!empty($_POST['items']) && is_array($_POST['items'])) {
                    foreach ($_POST['items'] as $it) {
                        if (!empty($it['product_id']) && floatval($it['quantity'] ?? 0) > 0) {
                            $items[] = [
                                'product_id' => intval($it['product_id']),
                                'format_type' => in_array($it['format_type'] ?? '', ['casier', 'demi']) ? $it['format_type'] : 'casier',
                                'quantity' => floatval($it['quantity']),
                                'unit_price' => floatval($it['unit_price'] ?? 0),
                                'crates_returned' => isset($it['crates_returned']) ? intval($it['crates_returned']) : 0,
                                'bottles_returned' => isset($it['bottles_returned']) ? intval($it['bottles_returned']) : 0,
                                'update_catalog_price' => !empty($it['update_catalog_price'])
                            ];
                        }
                    }
                } elseif (!empty($_POST['product_id'])) {
                    // Fallback for single line
                    $items[] = [
                        'product_id' => intval($_POST['product_id']),
                        'format_type' => in_array($_POST['format_type'] ?? '', ['casier', 'demi']) ? $_POST['format_type'] : 'casier',
                        'quantity' => floatval($_POST['quantity'] ?? 1),
                        'unit_price' => floatval($_POST['unit_price'] ?? 0),
                        'crates_returned' => isset($_POST['crates_returned']) ? intval($_POST['crates_returned']) : 0,
                        'bottles_returned' => isset($_POST['bottles_returned']) ? intval($_POST['bottles_returned']) : 0,
                        'update_catalog_price' => !empty($_POST['update_catalog_price'])
                    ];
                }

                $data = [
                    'sale_date' => !empty($_POST['sale_date']) ? $_POST['sale_date'] : date('Y-m-d'),
                    'client_id' => intval($_POST['client_id'] ?? 0),
                    'discount_amount' => floatval($_POST['discount_amount'] ?? 0),
                    'avoir_used' => floatval($_POST['avoir_amount'] ?? ($_POST['avoir_used'] ?? 0)),
                    'settlement_type' => $_POST['settlement_type'] ?? 'cash',
                    'cash_account_id' => intval($_POST['cash_account_id'] ?? 0),
                    'amount_paid' => isset($_POST['amount_paid']) ? floatval($_POST['amount_paid']) : null,
                    'update_catalog_prices' => !empty($_POST['update_catalog_prices']),
                    'reference' => trim($_POST['reference'] ?? ''),
                    'notes' => trim($_POST['notes'] ?? ''),
                    'user_id' => $userId,
                    'tournee_id' => $tourneeId,
                    'sale_type' => $tourneeId ? 'route' : 'comptoir',
                    'items' => $items
                ];

                $saleId = $this->model->add($data);
                \App\Core\Helper::logAudit('CREATE', 'Ventes', $saleId, "Création de la facture de vente {$saleId} (" . ($data['settlement_type'] === 'credit' ? 'Crédit' : 'Comptant') . ")", null, $data);
                $_SESSION['flash_success'] = "Vente " . $saleId . " enregistrée avec succès !";
                
                if ($tourneeId) {
                    $this->redirect('tournees/details/' . $tourneeId);
                } else {
                    $this->redirect('ventes/invoice/' . $saleId);
                }
            } catch (\Exception $e) {
                $_SESSION['ventes_error'] = $e->getMessage();
                $_SESSION['ventes_old'] = $_POST;
                $tourneeParam = !empty($_POST['tournee_id']) ? ('?tournee_id=' . intval($_POST['tournee_id'])) : '';
                $this->redirect('ventes/form' . $tourneeParam);
            }
        }
    }

    public function saveEnDetail() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $userId = $_SESSION['user']['id'] ?? 1;

                $items = [];
                if (!empty($_POST['items']) && is_array($_POST['items'])) {
                    foreach ($_POST['items'] as $it) {
                        if (!empty($it['product_id']) && floatval($it['quantity'] ?? 0) > 0) {
                            $items[] = [
                                'product_id' => intval($it['product_id']),
                                'quantity' => floatval($it['quantity']),
                                'unit_price' => floatval($it['unit_price'] ?? 0),
                                'emballage_mode' => in_array($it['emballage_mode'] ?? '', ['sur_place', 'echange', 'dette']) ? $it['emballage_mode'] : 'sur_place',
                                'bottles_returned' => isset($it['bottles_returned']) ? intval($it['bottles_returned']) : 0,
                                'update_catalog_price' => !empty($it['update_catalog_price'])
                            ];
                        }
                    }
                }

                $data = [
                    'sale_date' => !empty($_POST['sale_date']) ? $_POST['sale_date'] : date('Y-m-d'),
                    'client_id' => intval($_POST['client_id'] ?? 0),
                    'discount_amount' => floatval($_POST['discount_amount'] ?? 0),
                    'avoir_used' => floatval($_POST['avoir_amount'] ?? ($_POST['avoir_used'] ?? 0)),
                    'settlement_type' => $_POST['settlement_type'] ?? 'cash',
                    'cash_account_id' => intval($_POST['cash_account_id'] ?? 0),
                    'amount_paid' => isset($_POST['amount_paid']) ? floatval($_POST['amount_paid']) : null,
                    'reference' => trim($_POST['reference'] ?? ''),
                    'notes' => trim($_POST['notes'] ?? 'Vente au détail / Bouteilles'),
                    'user_id' => $userId,
                    'items' => $items
                ];

                $saleId = $this->model->addRetailSale($data);
                \App\Core\Helper::logAudit('CREATE', 'Ventes', $saleId, "Création vente au détail {$saleId} (" . ($data['settlement_type'] === 'credit' ? 'Crédit' : 'Comptant') . ")", null, $data);
                $_SESSION['flash_success'] = "Vente au détail " . $saleId . " enregistrée avec succès !";
                $this->redirect('ventes/invoice/' . $saleId);
            } catch (\Exception $e) {
                $_SESSION['ventes_detail_error'] = $e->getMessage();
                $_SESSION['ventes_detail_old'] = $_POST;
                $this->redirect('ventes/en-detail');
            }
        }
    }

    public function cancel($id = null) {
        if ($id) {
            // Admin only constraint
            if (!\App\Core\Helper::isAdmin()) {
                $_SESSION['flash_error'] = "Accès refusé : Seul l'administrateur est autorisé à annuler une vente.";
                $this->redirect('ventes');
                return;
            }

            try {
                $saleBefore = $this->model->getSaleWithItems($id);
                $userId = $_SESSION['user']['id'] ?? 1;
                $reason = trim($_POST['reason'] ?? ($_GET['reason'] ?? 'Annulation demandée par l\'administrateur'));
                $this->model->cancelSale($id, $userId, $reason);

                \App\Core\Helper::logAudit(
                    'CANCEL', 
                    'Ventes', 
                    $id, 
                    "Annulation de la vente {$id} (Montant: " . number_format($saleBefore['total_amount'] ?? 0, 0, ',', ' ') . " FCFA, Client: " . ($saleBefore['client_name'] ?? '-') . ")", 
                    $saleBefore, 
                    ['status' => 'Cancelled'], 
                    $reason
                );

                $_SESSION['flash_success'] = "Facture " . $id . " annulée : le stock a été réintégré et la caisse/crédit a été rééquilibré.";
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Erreur lors de l'annulation : " . $e->getMessage();
            }
        }
        $this->redirect('ventes');
    }

    public function delete($id = null) {
        return $this->cancel($id);
    }
}
