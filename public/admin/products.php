<?php
use Helpers\Session;


require_once __DIR__ . '/../../config/config.php';

$controllerPath = __DIR__ . '/../../src/Controllers/ProductController.php';
if (!file_exists($controllerPath)) {
    error_log("products.php: Não encontrou ProductController.php em $controllerPath", 3, __DIR__ . '/../../logs/error.log');
    die("Erro: Não foi possível carregar o controlador de produtos.");
}
require_once $controllerPath;

$sessionPath = __DIR__ . '/../../src/Helpers/Session.php';
if (!file_exists($sessionPath)) {
    error_log("products.php: Não encontrou Session.php em $sessionPath", 3, __DIR__ . '/../../logs/error.log');
    die("Erro: Não foi possível carregar o helper de sessão.");
}
require_once $sessionPath;

// Inicializa a sessão
if (session_status() === PHP_SESSION_NONE) {
    Session::start();
}

$controller = new ProductController();
$products = $controller->productModel->getAll();
$categories = $controller->categoryModel->getAll();

// Verifica se navigation.php existe
$navigationPath = __DIR__ . '/../../templates/navigation.php';
if (!file_exists($navigationPath)) {
    error_log("products.php: Não encontrou navigation.php em $navigationPath", 3, __DIR__ . '/../../logs/error.log');
}

include __DIR__ . '/../../templates/header.php'; // Conforme Parte 1
include $navigationPath; // Caminho ajustado
include __DIR__ . '/../../templates/alerts.php'; // Conforme Parte 1
?>

<h1>Gestão de Produtos</h1>
<form action="/estoque-sorveteria/product/store" method="POST">
    <input type="text" name="nome" placeholder="Nome" required>
    <input type="text" name="codigo" placeholder="Código">
    <select name="categoria_id" required>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['nome']) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="number" name="estoque_atual" placeholder="Estoque Atual" step="0.001" required>
    <input type="number" name="estoque_minimo" placeholder="Estoque Mínimo" step="0.001" required>
    <input type="number" name="preco_venda" placeholder="Preço de Venda" step="0.01" required>
    <textarea name="descricao" placeholder="Descrição"></textarea>
    <label><input type="checkbox" name="ativo" checked> Ativo</label>
    <button type="submit">Criar</button>
</form>

<table>
    <thead><tr><th>ID</th><th>Código</th><th>Nome</th><th>Categoria</th><th>Estoque</th><th>Min. Estoque</th><th>Preço</th><th>Ativo</th><th>Ações</th></tr></thead>
    <tbody>
        <?php foreach ($products as $prod): ?>
            <tr>
                <td><?= htmlspecialchars($prod['id']) ?></td>
                <td><?= htmlspecialchars($prod['codigo'] ?? '') ?></td>
                <td><?= htmlspecialchars($prod['nome']) ?></td>
                <td><?= htmlspecialchars($controller->categoryModel->getById($prod['categoria_id'])['nome'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($prod['estoque_atual']) ?></td>
                <td><?= htmlspecialchars($prod['estoque_minimo']) ?></td>
                <td><?= htmlspecialchars($prod['preco_venda']) ?></td>
                <td><?= $prod['ativo'] ? 'Sim' : 'Não' ?></td>
                <td>
                    <form action="/estoque-sorveteria/product/update/<?= $prod['id'] ?>" method="POST" style="display:inline;">
                        <input type="text" name="nome" value="<?= htmlspecialchars($prod['nome']) ?>" required>
                        <input type="text" name="codigo" value="<?= htmlspecialchars($prod['codigo'] ?? '') ?>">
                        <select name="categoria_id" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['id']) ?>" <?= $cat['id'] == $prod['categoria_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="estoque_atual" value="<?= htmlspecialchars($prod['estoque_atual']) ?>" step="0.001" required>
                        <input type="number" name="estoque_minimo" value="<?= htmlspecialchars($prod['estoque_minimo']) ?>" step="0.001" required>
                        <input type="number" name="preco_venda" value="<?= htmlspecialchars($prod['preco_venda']) ?>" step="0.01" required>
                        <textarea name="descricao"><?= htmlspecialchars($prod['descricao'] ?? '') ?></textarea>
                        <label><input type="checkbox" name="ativo" <?= $prod['ativo'] ? 'checked' : '' ?>> Ativo</label>
                        <button type="submit">Editar</button>
                    </form>
                    <form action="/estoque-sorveteria/product/destroy/<?= $prod['id'] ?>" method="POST" style="display:inline;">
                        <button type="submit">Desativar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../../templates/footer.php'; // Conforme Parte 1 ?>