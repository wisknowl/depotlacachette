<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\FournisseursModel;

class FournisseursController extends Controller {
    private $model;
    public function __construct() { $this->model = new FournisseursModel(); }

    private function generateSlug($name, $currentId = null) {
        $slug = preg_replace('~[^\pL\d]+~u', '-', $name);
        $slug = iconv('utf-8', 'us-ascii//TRANSLIT//IGNORE', $slug);
        $slug = preg_replace('~[^-\w]+~', '', $slug);
        $slug = trim($slug, '-');
        $slug = preg_replace('~-+~', '-', $slug);
        $slug = strtolower($slug);
        if (empty($slug)) { $slug = 'fournisseur-' . uniqid(); }

        $db = \App\Core\Database::getInstance()->getConnection();
        $baseSlug = $slug;
        $counter = 1;
        while (true) {
            if ($currentId) {
                $stmt = $db->prepare("SELECT COUNT(*) FROM suppliers WHERE slug = ? AND id != ?");
                $stmt->execute([$slug, $currentId]);
            } else {
                $stmt = $db->prepare("SELECT COUNT(*) FROM suppliers WHERE slug = ?");
                $stmt->execute([$slug]);
            }
            if ($stmt->fetchColumn() == 0) { break; }
            $slug = $baseSlug . '-' . (++$counter);
        }
        return $slug;
    }

    public function index() {
        $this->view('pages/fournisseurs/index', ['title' => 'Annuaire Fournisseurs', 'active_menu' => 'fournisseurs', 'fournisseurs' => $this->model->getAll()]);
    }

    public function form($slug = null) {
        $fournisseur = $slug ? $this->model->getBySlug($slug) : null;
        $this->view('pages/fournisseurs/form', [
            'title' => $fournisseur ? 'Modifier Fournisseur' : 'Nouveau Fournisseur', 
            'active_menu' => 'fournisseurs', 
            'fournisseur' => $fournisseur
        ]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = !empty($_POST['id']) ? $_POST['id'] : null;
            $slug = $this->generateSlug($_POST['name'], $id);
            $data = [
                'name' => $_POST['name'],
                'slug' => $slug,
                'address' => $_POST['address'],
                'phone' => $_POST['phone'],
                'contact_name' => $_POST['contact_name']
            ];
            if ($id) {
                $this->model->update($id, $data);
            } else {
                $this->model->add($data);
            }
            $this->redirect('fournisseurs');
        }
    }

    public function delete($slug = null) {
        if ($slug) { $this->model->delete($slug); }
        $this->redirect('fournisseurs');
    }
}