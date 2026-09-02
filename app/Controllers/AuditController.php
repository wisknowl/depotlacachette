<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Helper;
use App\Models\AuditModel;

class AuditController extends Controller {
    private $model;
    public function __construct() { $this->model = new AuditModel(); }

    public function index() {
        $activeTab = $_GET['tab'] ?? 'ledger';
        if (!in_array($activeTab, ['ledger', 'system', 'closings'])) {
            $activeTab = 'ledger';
        }

        $users = $this->model->getUsers();
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
        if (!in_array($limit, [25, 50, 100, 200, 500, 0])) {
            $limit = 50;
        }
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

        // 1. GRAND LIVRE UNIFIE DATA
        $ledgerFilters = [
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
            'module' => $_GET['module'] ?? '',
            'user_id' => $_GET['user_id'] ?? ''
        ];
        $ledgerTotalCount = ($activeTab === 'ledger') ? $this->model->getGlobalAuditTrailCount($ledgerFilters) : 0;
        $ledgerTotalPages = ($limit > 0 && $ledgerTotalCount > 0) ? max(1, ceil($ledgerTotalCount / $limit)) : 1;
        if ($page > $ledgerTotalPages) $page = $ledgerTotalPages;
        $offset = ($page - 1) * $limit;

        $auditTrail = ($activeTab === 'ledger') ? $this->model->getGlobalAuditTrail($ledgerFilters, $limit, $offset) : [];

        // 2. SYSTEM AUDIT LOGS DATA
        $systemFilters = [
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
            'module' => $_GET['module'] ?? '',
            'action' => $_GET['action'] ?? '',
            'user_id' => $_GET['user_id'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        $systemTotalCount = ($activeTab === 'system') ? $this->model->getSystemAuditLogsCount($systemFilters) : 0;
        $systemTotalPages = ($limit > 0 && $systemTotalCount > 0) ? max(1, ceil($systemTotalCount / $limit)) : 1;
        if ($activeTab === 'system' && $page > $systemTotalPages) $page = $systemTotalPages;
        $systemOffset = ($page - 1) * $limit;

        $systemAuditLogs = ($activeTab === 'system') ? $this->model->getSystemAuditLogs($systemFilters, $limit, $systemOffset) : [];
        $auditActions = $this->model->getDistinctAuditActions();
        $auditModules = $this->model->getDistinctAuditModules();

        // 3. CLOSINGS ARCHIVES DATA
        $closings = ($activeTab === 'closings' || empty($activeTab)) ? $this->model->getClosings(100) : [];

        $this->view('pages/audit/index', [
            'title' => 'Audit & Contrôle de Gestion ERP',
            'active_menu' => 'audit',
            'activeTab' => $activeTab,
            'auditTrail' => $auditTrail,
            'systemAuditLogs' => $systemAuditLogs,
            'closings' => $closings,
            'users' => $users,
            'auditActions' => $auditActions,
            'auditModules' => $auditModules,
            'ledgerFilters' => $ledgerFilters,
            'systemFilters' => $systemFilters,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'totalCount' => ($activeTab === 'system') ? $systemTotalCount : $ledgerTotalCount,
                'totalPages' => ($activeTab === 'system') ? $systemTotalPages : $ledgerTotalPages,
                'offset' => $offset
            ],
            'flash_success' => $_SESSION['flash_success'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    // Backwards compatibility redirects for legacy routes
    public function cloture() {
        $this->redirect('caisse/cloture');
    }

    public function saveCloture() {
        $this->redirect('caisse/cloture');
    }

    public function receipt($id = null) {
        if ($id) {
            $this->redirect('caisse/receipt/' . $id);
        } else {
            $this->redirect('caisse');
        }
    }
}
