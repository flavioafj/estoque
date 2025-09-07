<?php  
require_once '../config/config.php';  
use Middleware\Auth;  
use Helpers\Session;  
//use Models\Product;  

require_once SRC_PATH . '/Models/Product.php';
  
Auth::check(); 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {  
    $action = $_GET['action'];  
    $produtoId = intval($_POST['produto_id']);  
    $quantidade = floatval($_POST['quantidade']);  
    $usuarioId = Session::getUserId();  
  
    if ($quantidade <= 0) {  
        echo json_encode(['success' => false, 'message' => 'Quantidade inválida']);  
        exit;  
    }  
  
    $productModel = new Product();  
    $produto = $productModel->getById($produtoId);  
    if ($produto['estoque_atual'] < $quantidade) {  
        echo json_encode(['success' => false, 'message' => 'Estoque insuficiente']);  
        exit;  
    }  
  
    if ($action === 'saida_direta') {  
        $movimentacao = new \Models\Movimentacao();  
        if ($movimentacao->registrarSaidaDireta($produtoId, $quantidade, $usuarioId)) {  
            echo json_encode(['success' => true]);  
        } else {  
            echo json_encode(['success' => false, 'message' => 'Erro ao registrar saída']);  
        }  
        exit;  
    } elseif ($action === 'add_cart') {  
        Session::addToCart($produtoId, $quantidade);  
        echo json_encode(['success' => true]);  
        exit;  
    }  
}  
  
if (Session::isAdmin()) {  
    header('Location: /estoque-sorveteria/public/dashboard.php');  
    exit;  
}  
  
$productModel = new Product();  
$produtos = $productModel->getProdutosPorSaidasDesc();  
  
require_once '../templates/header.php';  
require_once '../templates/navigation.php';  
?>  
  
<main class="container mt-4">  
    <h1>Produtos</h1>  
    <!-- Div superior com ícones de últimas saídas -->  
    <div class="last-exits">  
        <a href="/estoque-sorveteria/public/my_exits.php" class="icon">📤 Últimas Saídas</a>  
    </div>  
  
    <div class="product-grid">  
        <?php foreach ($produtos as $produto): ?>  
            <?php include '../templates/product-card.php'; ?>  
        <?php endforeach; ?>  
    </div>  
</main>  
  
<script src="/estoque-sorveteria/public/assets/js/main.js"></script>  
<?php require_once '../templates/footer.php'; ?>  