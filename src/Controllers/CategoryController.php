<?php
use Middleware\Auth;

require_once __DIR__ . '/../Models/Category.php';
require_once __DIR__ . '/../Helpers/Validator.php';
require_once __DIR__ . '/../Helpers/Session.php'; // Conforme Parte 1
require_once __DIR__ . '/../Middleware/Auth.php'; // Conforme Parte 1

class CategoryController {
    private $categoryModel;

    public function __construct() {
        $this->categoryModel = new Category();
        Auth::checkAdmin(); // Conforme Parte 1
    }

    public function index() {
        $categories = $this->categoryModel->getAll();
        include __DIR__ . '/../../public/admin/categories.php';
    }

    public function getAllCategories() {
        return $this->categoryModel->getAll();
    }

    public function store() {
        $data = $_POST;
        $errors = Validator::validate($data, [
            'nome' => 'required|unique:categorias,nome',
            'descricao' => 'optional',
            'ativo' => 'boolean'
        ]);

        if (!empty($errors)) {
            Session::set('errors', $errors);
            header('Location: /admin/categories.php');
            exit;
        }

        $data['ativo'] = isset($data['ativo']) ? 1 : 0;
        $this->categoryModel->create($data);
        Session::set('success', 'Categoria criada com sucesso.');
        header('Location: /admin/categories.php');
    }

    public function update($id) {
        $data = $_POST;
        $errors = Validator::validate($data, [
            'nome' => "required|unique:categorias,nome,$id",
            'descricao' => 'optional',
            'ativo' => 'boolean'
        ]);

        if (!empty($errors)) {
            Session::set('errors', $errors);
            header('Location: /admin/categories.php');
            exit;
        }

        $data['ativo'] = isset($data['ativo']) ? 1 : 0;
        $this->categoryModel->update($id, $data);
        Session::set('success', 'Categoria atualizada.');
        header('Location: /admin/categories.php');
    }

    public function destroy($id) {
        $this->categoryModel->delete($id);
        Session::set('success', 'Categoria desativada.');
        header('Location: /admin/categories.php');
    }
}