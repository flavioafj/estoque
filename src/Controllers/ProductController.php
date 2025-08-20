<?php

$productPath = __DIR__ . '/../Models/Product.php';
$categoryPath = __DIR__ . '/../Models/Category.php';
$validatorPath = __DIR__ . '/../Helpers/Validator.php';

if (!file_exists($productPath)) {
    error_log("ProductController.php: Não encontrou Product.php em $productPath", 3, __DIR__ . '/../../logs/error.log');
    die("Erro: Não foi possível carregar Product.php.");
}
if (!file_exists($categoryPath)) {
    error_log("ProductController.php: Não encontrou Category.php em $categoryPath", 3, __DIR__ . '/../../logs/error.log');
    die("Erro: Não foi possível carregar Category.php.");
}
if (!file_exists($validatorPath)) {
    error_log("ProductController.php: Não encontrou Validator.php em $validatorPath", 3, __DIR__ . '/../../logs/error.log');
    die("Erro: Não foi possível carregar Validator.php.");
}

require_once $productPath;
require_once $categoryPath;
require_once $validatorPath;

/* use Models\Product;
use Models\Category;
use Helpers\Validator; */

class ProductController {
    public $productModel;
    public $categoryModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }

    public function store($data) {
        $rules = [
            'nome' => 'required|unique:produtos,nome',
            'codigo' => 'unique:produtos,codigo',
            'categoria_id' => 'required|exists:categorias,id',
            'estoque_atual' => 'required|numeric|min:0',
            'estoque_minimo' => 'required|numeric|min:0',
            'preco_venda' => 'required|numeric|min:0',
            'ativo' => 'boolean',
            'unidade_medida_id' => 'exists:unidades_medida,id',
            'estoque_maximo' => 'numeric|min:0',
            'preco_custo' => 'numeric|min:0',
            'margem_lucro' => 'numeric|min:0',
            'fornecedor_principal_id' => 'exists:fornecedores,id'
        ];

        $errors = Validator::validate($data, $rules);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /estoque-sorveteria/admin/products.php');
            exit;
        }

        $id = $this->productModel->create($data);
        if ($id) {
            $_SESSION['success'] = 'Produto criado com sucesso!';
            header('Location: /estoque-sorveteria/admin/products.php');
            exit;
        }

        $_SESSION['errors'] = ['general' => ['Erro ao criar produto.']];
        header('Location: /estoque-sorveteria/admin/products.php');
        exit;
    }

    public function update($id, $data) {
        $rules = [
            'nome' => "required|unique:produtos,nome,$id",
            'codigo' => "unique:produtos,codigo,$id",
            'categoria_id' => 'required|exists:categorias,id',
            'estoque_atual' => 'required|numeric|min:0',
            'estoque_minimo' => 'required|numeric|min:0',
            'preco_venda' => 'required|numeric|min:0',
            'ativo' => 'boolean',
            'unidade_medida_id' => 'exists:unidades_medida,id',
            'estoque_maximo' => 'numeric|min:0',
            'preco_custo' => 'numeric|min:0',
            'margem_lucro' => 'numeric|min:0',
            'fornecedor_principal_id' => 'exists:fornecedores,id'
        ];

        $errors = Validator::validate($data, $rules);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /estoque-sorveteria/admin/products.php');
            exit;
        }

        $result = $this->productModel->update($id, $data);
        if ($result) {
            $_SESSION['success'] = 'Produto atualizado com sucesso!';
            header('Location: /estoque-sorveteria/admin/products.php');
            exit;
        }

        $_SESSION['errors'] = ['general' => ['Erro ao atualizar produto.']];
        header('Location: /estoque-sorveteria/admin/products.php');
        exit;
    }

    public function destroy($id) {
        $result = $this->productModel->delete($id);
        if ($result) {
            $_SESSION['success'] = 'Produto desativado com sucesso!';
            header('Location: /estoque-sorveteria/admin/products.php');
            exit;
        }

        $_SESSION['errors'] = ['general' => ['Erro ao desativar produto.']];
        header('Location: /estoque-sorveteria/admin/products.php');
        exit;
    }
}