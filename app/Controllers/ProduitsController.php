<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\ProduitsModel;

class ProduitsController extends Controller {
    private $model;
    public function __construct() { $this->model = new ProduitsModel(); }

    public function index() {
        $search = trim($_GET['search'] ?? '');
        $categoryId = !empty($_GET['category_id']) ? intval($_GET['category_id']) : null;
        $formatId = !empty($_GET['format_id']) ? intval($_GET['format_id']) : null;
        $factor = !empty($_GET['factor']) ? intval($_GET['factor']) : null;
        $isReturnable = isset($_GET['is_returnable']) && $_GET['is_returnable'] !== '' ? intval($_GET['is_returnable']) : null;
        $packagingTypeId = !empty($_GET['packaging_type_id']) ? intval($_GET['packaging_type_id']) : null;

        $produits = $this->model->getFiltered($search, $categoryId, $formatId, $factor, $isReturnable, $packagingTypeId);

        $this->view('pages/produits/index', [
            'title' => 'Catalogue Produits',
            'active_menu' => 'produits',
            'produits' => $produits,
            'categories' => $this->model->getCategories(),
            'formats' => $this->model->getFormats(),
            'factors' => $this->model->getDistinctFactors(),
            'packagingTypes' => $this->model->getPackagingTypes(),
            'filterSearch' => $search,
            'filterCategoryId' => $categoryId,
            'filterFormatId' => $formatId,
            'filterFactor' => $factor,
            'filterIsReturnable' => $isReturnable,
            'filterPackagingTypeId' => $packagingTypeId
        ]);
    }

    public function form($slug = null) {
        $produit = null;
        if ($slug) { $produit = $this->model->getBySlug($slug); }

        $this->view('pages/produits/form', [
            'title' => $produit ? 'Modifier le Produit' : 'Ajouter un Produit',
            'active_menu' => 'produits',
            'produit' => $produit,
            'categories' => $this->model->getCategories(),
            'formats' => $this->model->getFormats(),
            'packagingTypes' => $this->model->getPackagingTypes()
        ]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            
            $catInput = trim($_POST['category_name'] ?? $_POST['category_id'] ?? '');
            $fmtInput = trim($_POST['format_name'] ?? $_POST['format_id'] ?? '');

            $categoryId = $this->model->getOrCreateCategoryByName($catInput);
            $formatId = $this->model->getOrCreateFormatByName($fmtInput);

            $isReturnable = isset($_POST['is_returnable']) ? intval($_POST['is_returnable']) : 1;
            $coutEmballage = $isReturnable ? floatval($_POST['cout_emballage'] ?? 3600) : 0.00;
            $packagingTypeId = $isReturnable && !empty($_POST['packaging_type_id']) ? intval($_POST['packaging_type_id']) : null;

            $data = [
                'name' => $name,
                'short_code' => trim($_POST['short_code'] ?? ''),
                'slug' => $slug,
                'category_id' => $categoryId,
                'format_id' => $formatId,
                'is_returnable' => $isReturnable,
                'cout_emballage' => $coutEmballage,
                'packaging_type_id' => $packagingTypeId,
                'purchase_price' => floatval($_POST['purchase_price']),
                'price_casier' => floatval($_POST['price_casier']),
                'price_demi' => floatval($_POST['price_demi']),
                'price_unite' => floatval($_POST['price_unite']),
                'alert_stock' => intval($_POST['alert_stock']),
                'factor' => max(1, intval($_POST['factor'] ?: 24))
            ];

            if (!empty($_POST['id'])) {
                $oldProduct = $this->model->getBySlug($_POST['id']);
                $this->model->update($_POST['id'], $data);
                
                $priceChanged = false;
                if ($oldProduct) {
                    $priceChanged = (
                        floatval($oldProduct['purchase_price']) != $data['purchase_price'] ||
                        floatval($oldProduct['price_casier']) != $data['price_casier'] ||
                        floatval($oldProduct['price_demi']) != $data['price_demi'] ||
                        floatval($oldProduct['price_unite']) != $data['price_unite']
                    );
                }

                $action = $priceChanged ? 'PRICE_CHANGE' : 'UPDATE';
                $desc = $priceChanged 
                    ? "Modification de la grille tarifaire pour le produit '{$name}'" 
                    : "Mise à jour de la fiche produit '{$name}'";

                \App\Core\Helper::logAudit($action, 'Produits', $_POST['id'], $desc, $oldProduct, $data);
            } else {
                $this->model->add($data);
                \App\Core\Helper::logAudit('CREATE', 'Produits', $slug, "Création du produit '{$name}' (Prix casier: {$data['price_casier']} FCFA)", null, $data);
            }
            $this->redirect('produits');
        }
    }

    public function delete($slug = null) {
        if ($slug) {
            $oldProduct = $this->model->getBySlug($slug);
            $this->model->delete($slug);
            \App\Core\Helper::logAudit('DELETE', 'Produits', $slug, "Suppression du produit '" . ($oldProduct['name'] ?? $slug) . "'", $oldProduct, null);
        }
        $this->redirect('produits');
    }
}

