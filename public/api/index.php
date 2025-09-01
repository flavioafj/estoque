<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/config.php';
//require_once __DIR__ . '/../../src/autoload.php'; uso futuro
// Caminhos para os arquivos necessários
$controllerPath = __DIR__ . '/../../src/Controllers/ProductController.php';
$sessionPath = __DIR__ . '/../../src/Helpers/Session.php';

use Controllers\ReportController;
use Helpers\Session;

// Verifica se os arquivos existem
if (!file_exists($controllerPath)) {
    error_log("api/index.php: Não encontrou ProductController.php em $controllerPath", 3, __DIR__ . '/../../logs/error.log');
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
    exit;
}
if (!file_exists($sessionPath)) {
    error_log("api/index.php: Não encontrou Session.php em $sessionPath", 3, __DIR__ . '/../../logs/error.log');
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
    exit;
}

require_once $controllerPath;
require_once $sessionPath;

// Inicializa a sessão (se autenticação for necessária)
if (session_status() === PHP_SESSION_NONE) {
    Session::start();
}

// Instancia o controlador
$controller = new ProductController();
$requestUri = $_SERVER['REQUEST_URI'];

// Remove query strings, se houver
$requestUri = strtok($requestUri, '?');

// Normaliza a URI para suportar diferentes bases
$basePath = '/estoque-sorveteria/';
if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Define as rotas da API
if ($requestUri === 'api/products') {
    try {
        $products = $controller->productModel->getAll();
        echo json_encode($products);
    } catch (Exception $e) {
        error_log("api/index.php: Erro ao listar produtos: " . $e->getMessage(), 3, __DIR__ . '/../../logs/error.log');
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar produtos']);
    }
} elseif (preg_match('#^api/product/(\d+)$#', $requestUri, $matches)) {
    $id = (int)$matches[1];
    try {
        $product = $controller->productModel->getById($id);
        if ($product) {
            echo json_encode($product);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Produto não encontrado']);
        }
    } catch (Exception $e) {
        error_log("api/index.php: Erro ao buscar produto ID $id: " . $e->getMessage(), 3, __DIR__ . '/../../logs/error.log');
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar produto']);
    }
} elseif (preg_match('#^public/api/reports/custom$#', $requestUri)) {
    $reportController = new ReportController();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        http_response_code(200);
        $reportController->generate();
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
    }
}else {
    http_response_code(404);
    echo json_encode(['error' => 'Rota não encontrada']);
}
