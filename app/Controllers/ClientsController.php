<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\ClientsModel;

class ClientsController extends Controller {
    private $model;
    public function __construct() { $this->model = new ClientsModel(); }

    public function index() {
        $search = trim($_GET['search'] ?? '');
        $clientTypeId = !empty($_GET['client_type_id']) ? intval($_GET['client_type_id']) : null;
        $debtStatus = trim($_GET['debt_status'] ?? 'all');
        $sort = trim($_GET['sort'] ?? 'debt_desc');

        // Global stats from all clients
        $allClients = $this->model->getAll();
        $countTotal = count($allClients);
        $countDebt = 0;
        $countNoDebt = 0;
        $countLimitExceeded = 0;
        $totalGlobalDebt = 0;

        foreach ($allClients as $c) {
            $solde = floatval($c['solde_du']);
            $maxCred = floatval($c['max_credit']);
            if ($solde > 0) {
                $countDebt++;
                $totalGlobalDebt += $solde;
                if ($maxCred > 0 && $solde >= $maxCred) {
                    $countLimitExceeded++;
                }
            } else {
                $countNoDebt++;
            }
        }

        // Filtered list
        $filters = [
            'search' => $search,
            'client_type_id' => $clientTypeId,
            'debt_status' => $debtStatus,
            'sort' => $sort
        ];
        $clients = $this->model->getFiltered($filters);

        $totalFilteredDebt = 0;
        foreach ($clients as $c) {
            $solde = floatval($c['solde_du']);
            if ($solde > 0) {
                $totalFilteredDebt += $solde;
            }
        }

        $types = $this->model->getTypes();

        $this->view('pages/clients/index', [
            'title' => 'Gestion des Clients & Dettes',
            'active_menu' => 'clients',
            'clients' => $clients,
            'types' => $types,
            'countTotal' => $countTotal,
            'countDebt' => $countDebt,
            'countNoDebt' => $countNoDebt,
            'countLimitExceeded' => $countLimitExceeded,
            'totalGlobalDebt' => $totalGlobalDebt,
            'totalFilteredDebt' => $totalFilteredDebt,
            'filterSearch' => $search,
            'filterClientTypeId' => $clientTypeId,
            'filterDebtStatus' => $debtStatus,
            'filterSort' => $sort,
            'flash_success' => $_SESSION['flash_success'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    private function generateSlug($name, $id = null) {
        $slug = preg_replace('~[^\pL\d]+~u', '-', $name);
        $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
        $slug = preg_replace('~[^-\w]+~', '', $slug);
        $slug = trim($slug, '-');
        $slug = preg_replace('~-+~', '-', $slug);
        $slug = strtolower($slug);
        if (empty($slug)) {
            $slug = 'client-' . time();
        }
        return $this->model->ensureUniqueSlug($slug, $id);
    }

    public function form($slug = null) {
        $client = $slug ? $this->model->getBySlug($slug) : null;
        if ($slug && !$client && is_numeric($slug)) {
            $client = $this->model->getById($slug);
        }
        $this->view('pages/clients/form', [
            'title' => $client ? 'Modifier le Client' : 'Nouveau Client',
            'active_menu' => 'clients',
            'client' => $client,
            'types' => $this->model->getTypes(),
            'flash_error' => $_SESSION['clients_error'] ?? null
        ]);
        unset($_SESSION['clients_error']);
    }

    public function save($id = null) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $targetId = $id ?: (!empty($_POST['id']) ? $_POST['id'] : null);
                $slug = $this->generateSlug($_POST['name'], $targetId);
                $data = [
                    'name' => trim($_POST['name']),
                    'slug' => $slug,
                    'address' => trim($_POST['address'] ?? ''),
                    'phone' => trim($_POST['phone'] ?? ''),
                    'client_type_id' => intval($_POST['client_type_id'] ?? 1),
                    'max_credit' => floatval($_POST['max_credit'] ?? 0)
                ];

                if ($targetId) {
                    $oldClient = $this->model->getById($targetId);
                    $this->model->update($targetId, $data);

                    $creditLimitChanged = ($oldClient && floatval($oldClient['max_credit']) != $data['max_credit']);
                    $action = $creditLimitChanged ? 'CREDIT_LIMIT_CHANGE' : 'UPDATE';
                    $desc = $creditLimitChanged
                        ? "Modification du plafond de crédit pour le client '{$data['name']}' : " . number_format($oldClient['max_credit'], 0, ',', ' ') . " FCFA -> " . number_format($data['max_credit'], 0, ',', ' ') . " FCFA"
                        : "Mise à jour de la fiche client '{$data['name']}'";

                    \App\Core\Helper::logAudit($action, 'Clients', $targetId, $desc, $oldClient, $data);
                    $_SESSION['flash_success'] = "Client mis à jour avec succès !";
                } else {
                    $this->model->add($data);
                    \App\Core\Helper::logAudit('CREATE', 'Clients', $slug, "Création du client '{$data['name']}' (Plafond crédit: " . number_format($data['max_credit'], 0, ',', ' ') . " FCFA)", null, $data);
                    $_SESSION['flash_success'] = "Nouveau client enregistré avec succès !";
                }
                $this->redirect('clients');
            } catch (\Exception $e) {
                $_SESSION['clients_error'] = "Erreur lors de l'enregistrement : " . $e->getMessage();
                $slugParam = !empty($slug) ? '/' . $slug : '';
                $this->redirect('clients/form' . $slugParam);
            }
        }
    }

    public function delete($slug = null) {
        if ($slug) { 
            try {
                $oldClient = $this->model->getBySlug($slug);
                $this->model->delete($slug); 
                \App\Core\Helper::logAudit('DELETE', 'Clients', $slug, "Suppression du client '" . ($oldClient['name'] ?? $slug) . "'", $oldClient, null);
                $_SESSION['flash_success'] = "Client supprimé avec succès !";
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Impossible de supprimer ce client : " . $e->getMessage();
            }
        }
        $this->redirect('clients');
    }
}