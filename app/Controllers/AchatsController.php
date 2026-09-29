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
        $status = trim($_GET['status'] ?? 'all');
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
        $countEnAttente = 0;
        $totalEnAttente = 0;
        $globalTotal = 0;

        foreach ($allPurchases as $p) {
            if ($p['status'] === 'Valid') {
                $globalTotal += floatval($p['total_amount']);
                if ($p['payment_method_id'] == 5) {
                    $countCredit++;
                } else {
                    $countCash++;
                }
            } elseif ($p['status'] === 'En_Attente') {
                $countEnAttente++;
                $totalEnAttente += floatval($p['total_amount']);
            }
        }

        // Filtered list
        $filters = [
            'search' => $search,
            'supplier_id' => $supplierId,
            'status' => ($status !== 'all') ? $status : null,
            'payment_mode' => ($paymentMode !== 'all') ? $paymentMode : null,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
        $purchases = $this->model->getFiltered($filters);

        $filteredTotal = 0;
        foreach ($purchases as $p) {
            if ($p['status'] === 'Valid') {
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
            'countEnAttente' => $countEnAttente,
            'totalEnAttente' => $totalEnAttente,
            'globalTotal' => $globalTotal,
            'filteredTotal' => $filteredTotal,
            'filterSearch' => $search,
            'filterSupplierId' => $supplierId,
            'filterStatus' => $status,
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

                $actionType = $_POST['action_type'] ?? ($_POST['submit_action'] ?? 'validate');
                $isHold = ($actionType === 'hold');

                $ref = trim($_POST['reference'] ?? '');
                if (empty($ref)) {
                    if ($isHold) {
                        $ref = 'PREV-' . date('ymd-His');
                    } else {
                        throw new \Exception("Le N° Facture / BL Fournisseur (Référence) est obligatoire (ex: DV-L4L24005/48149/26).");
                    }
                }

                $items = [];
                if (!empty($_POST['items']) && is_array($_POST['items'])) {
                    foreach ($_POST['items'] as $it) {
                        $pId = intval($it['product_id'] ?? 0);
                        $qty = floatval($it['quantity'] ?? 0);
                        $hasDemi = !empty($it['has_demi']) ? 1 : 0;
                        if ($pId > 0 && ($qty > 0 || $hasDemi)) {
                            $unitPrice = floatval($it['unit_price'] ?? 0);
                            $demiUnitPrice = floatval($it['demi_unit_price'] ?? ($unitPrice / 2));
                            $formatType = in_array($it['format_type'] ?? '', ['casier', 'demi']) ? $it['format_type'] : (($qty == 0 && $hasDemi) ? 'demi' : 'casier');
                            $items[] = [
                                'product_id' => $pId,
                                'format_type' => $formatType,
                                'has_demi' => $hasDemi,
                                'quantity' => $qty,
                                'unit_price' => $unitPrice,
                                'demi_unit_price' => $demiUnitPrice,
                                'empties_returned' => isset($it['empties_returned']) ? floatval($it['empties_returned']) : 0,
                                'emballage_cost' => isset($it['emballage_cost']) ? floatval($it['emballage_cost']) : (isset($it['cout_emballage']) ? floatval($it['cout_emballage']) : 0),
                                'packaging_mode' => in_array($it['packaging_mode'] ?? '', ['charge', 'debt']) ? $it['packaging_mode'] : 'charge',
                                'ristourne_unit' => floatval($it['ristourne_unit'] ?? 0)
                            ];
                        }
                    }
                } elseif (!empty($_POST['product_id'])) {
                    $qty = floatval($_POST['quantity'] ?? 1);
                    $hasDemi = !empty($_POST['has_demi']) ? 1 : 0;
                    $unitPrice = floatval($_POST['unit_price'] ?? 0);
                    $demiUnitPrice = floatval($_POST['demi_unit_price'] ?? ($unitPrice / 2));
                    $items[] = [
                        'product_id' => intval($_POST['product_id']),
                        'format_type' => in_array($_POST['format_type'] ?? '', ['casier', 'demi']) ? $_POST['format_type'] : (($qty == 0 && $hasDemi) ? 'demi' : 'casier'),
                        'has_demi' => $hasDemi,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'demi_unit_price' => $demiUnitPrice,
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
                    'is_hold' => $isHold,
                    'action_type' => $actionType,
                    'items' => $items
                ];

                $purchaseId = $this->model->add($data);
                if ($isHold) {
                    \App\Core\Helper::logAudit('CREATE_HOLD', 'Achats', $purchaseId, "Création bon d'approvisionnement prévisionnel (en attente) {$purchaseId}", null, $data);
                    $_SESSION['flash_success'] = "Bon d'approvisionnement " . $purchaseId . " mis en attente avec succès ! Aucun mouvement de stock ou de caisse n'a été appliqué.";
                } else {
                    \App\Core\Helper::logAudit('CREATE', 'Achats', $purchaseId, "Enregistrement du bon d'approvisionnement {$purchaseId} (Réf BL: {$ref})", null, $data);
                    $_SESSION['flash_success'] = "Approvisionnement " . $purchaseId . " enregistré avec succès !";
                }
                $this->redirect('achats/order/' . $purchaseId);
            } catch (\Exception $e) {
                $_SESSION['achats_error'] = $e->getMessage();
                $_SESSION['achats_old'] = $_POST;
                $this->redirect('achats/form');
            }
        }
    }

    public function edit($id = null) {
        if (!$id) {
            $this->redirect('achats');
        }
        $purchase = $this->model->getPurchaseWithItems($id);
        if (!$purchase) {
            $_SESSION['flash_error'] = "Bon d'achat introuvable.";
            $this->redirect('achats');
        }
        if ($purchase['status'] !== 'En_Attente') {
            $_SESSION['flash_error'] = "Ce bon d'achat ne peut plus être modifié car il est déjà " . ($purchase['status'] === 'Valid' ? 'validé' : 'annulé') . ".";
            $this->redirect('achats/order/' . $id);
        }

        $this->view('pages/achats/edit', [
            'title' => 'Modifier Approvisionnement en Attente - #' . $purchase['id'],
            'active_menu' => 'achats',
            'purchase' => $purchase,
            'suppliers' => $this->model->getSuppliers(),
            'products' => $this->model->getProducts(),
            'cash_accounts' => $this->model->getCashAccountsWithBalances(),
            'flash_error' => $_SESSION['achats_edit_error'] ?? null
        ]);
        unset($_SESSION['achats_edit_error']);
    }

    public function update($id = null) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
            try {
                $userId = $_SESSION['user']['id'] ?? 1;
                $actionType = $_POST['action_type'] ?? ($_POST['submit_action'] ?? 'update');

                $ref = trim($_POST['reference'] ?? '');
                if (empty($ref)) {
                    if ($actionType === 'validate') {
                        throw new \Exception("Le N° Facture / BL Fournisseur (Référence) est obligatoire pour valider l'achat.");
                    }
                    $ref = 'PREV-' . date('ymd-His');
                }

                $items = [];
                if (!empty($_POST['items']) && is_array($_POST['items'])) {
                    foreach ($_POST['items'] as $it) {
                        $pId = intval($it['product_id'] ?? 0);
                        $qty = floatval($it['quantity'] ?? 0);
                        $hasDemi = !empty($it['has_demi']) ? 1 : 0;
                        if ($pId > 0 && ($qty > 0 || $hasDemi)) {
                            $unitPrice = floatval($it['unit_price'] ?? 0);
                            $demiUnitPrice = floatval($it['demi_unit_price'] ?? ($unitPrice / 2));
                            $formatType = in_array($it['format_type'] ?? '', ['casier', 'demi']) ? $it['format_type'] : (($qty == 0 && $hasDemi) ? 'demi' : 'casier');
                            $items[] = [
                                'product_id' => $pId,
                                'format_type' => $formatType,
                                'has_demi' => $hasDemi,
                                'quantity' => $qty,
                                'unit_price' => $unitPrice,
                                'demi_unit_price' => $demiUnitPrice,
                                'empties_returned' => isset($it['empties_returned']) ? floatval($it['empties_returned']) : 0,
                                'emballage_cost' => isset($it['emballage_cost']) ? floatval($it['emballage_cost']) : (isset($it['cout_emballage']) ? floatval($it['cout_emballage']) : 0),
                                'packaging_mode' => in_array($it['packaging_mode'] ?? '', ['charge', 'debt']) ? $it['packaging_mode'] : 'charge',
                                'ristourne_unit' => floatval($it['ristourne_unit'] ?? 0)
                            ];
                        }
                    }
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
                    'items' => $items
                ];

                if ($actionType === 'validate') {
                    $this->model->updateHoldPurchase($id, $data, $userId);
                    $this->model->validateHoldPurchase($id, $data, $userId);
                    $_SESSION['flash_success'] = "Bon d'approvisionnement " . $id . " validé et réceptionné avec succès ! Stocks et caisse mis à jour.";
                    $this->redirect('achats/order/' . $id);
                } else {
                    $this->model->updateHoldPurchase($id, $data, $userId);
                    \App\Core\Helper::logAudit('UPDATE_HOLD', 'Achats', $id, "Modification du bon d'achat en attente {$id}", null, $data);
                    $_SESSION['flash_success'] = "Bon d'approvisionnement en attente " . $id . " mis à jour avec succès.";
                    $this->redirect('achats/order/' . $id);
                }
            } catch (\Exception $e) {
                $_SESSION['achats_edit_error'] = $e->getMessage();
                $this->redirect('achats/edit/' . $id);
            }
        } else {
            $this->redirect('achats');
        }
    }

    public function validateHold($id = null) {
        if ($id) {
            try {
                $userId = $_SESSION['user']['id'] ?? 1;
                $data = [
                    'cash_account_id' => !empty($_POST['cash_account_id']) ? intval($_POST['cash_account_id']) : null
                ];
                $this->model->validateHoldPurchase($id, $data, $userId);
                $_SESSION['flash_success'] = "Bon d'approvisionnement " . $id . " validé avec succès ! Mouvements de stock et caisse effectués.";
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Impossible de valider l'approvisionnement : " . $e->getMessage();
            }
            $this->redirect('achats/order/' . $id);
        } else {
            $this->redirect('achats');
        }
    }

    public function cancel($id = null) {
        if ($id) {
            $purchBefore = $this->model->getPurchaseWithItems($id);
            if (!$purchBefore) {
                $_SESSION['flash_error'] = "Bon d'achat introuvable.";
                $this->redirect('achats');
                return;
            }

            // If it is a validated purchase, require Admin role
            if ($purchBefore['status'] === 'Valid' && !\App\Core\Helper::isAdmin()) {
                $_SESSION['flash_error'] = "Accès refusé : Seul l'administrateur est autorisé à annuler un bon d'approvisionnement validé.";
                $this->redirect('achats');
                return;
            }

            try {
                $userId = $_SESSION['user']['id'] ?? 1;
                $reason = trim($_POST['reason'] ?? ($_GET['reason'] ?? ($purchBefore['status'] === 'En_Attente' ? 'Abandon du bon prévisionnel en attente' : 'Annulation demandée par l\'administrateur')));
                $this->model->cancelPurchase($id, $userId, $reason);

                \App\Core\Helper::logAudit(
                    'CANCEL', 
                    'Achats', 
                    $id, 
                    "Annulation bon d'achat {$id} (Statut précédent: {$purchBefore['status']}, Fournisseur: " . ($purchBefore['supplier_name'] ?? '-') . ", Montant: " . number_format($purchBefore['total_amount'] ?? 0, 0, ',', ' ') . " FCFA)", 
                    $purchBefore, 
                    ['status' => 'Cancelled'], 
                    $reason
                );

                if ($purchBefore['status'] === 'En_Attente') {
                    $_SESSION['flash_success'] = "Bon d'approvisionnement en attente " . $id . " annulé.";
                } else {
                    $_SESSION['flash_success'] = "Bon d'achat " . $id . " annulé : le stock a été décrémenté et la caisse/dette a été rééquilibrée.";
                }
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

