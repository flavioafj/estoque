<?php
use Helpers\Session;


require_once __DIR__ . '/../../config/config.php';

$controllerPath = __DIR__ . '/../../src/Controllers/CategoryController.php';
if (!file_exists($controllerPath)) {
    error_log("categories.php: Não encontrou CategoryController.php em $controllerPath", 3, __DIR__ . '/../../logs/error.log');
    die("Erro: Não foi possível carregar o controlador de categorias.");
}
require_once $controllerPath;


$sessionPath = __DIR__ . '/../../src/Helpers/Session.php';
if (!file_exists($sessionPath)) {
    error_log("categories.php: Não encontrou Session.php em $sessionPath", 3, __DIR__ . '/../../logs/error.log');
    die("Erro: Não foi possível carregar o helper de sessão.");
}
require_once $sessionPath;

// Inicializa a sessão
if (session_status() === PHP_SESSION_NONE) {
    Session::start();
}

$controller = new CategoryController();
$categories = $controller->getAllCategories();

// Verifica se navigation.php existe
$navigationPath = __DIR__ . '/../../templates/navigation.php';
if (!file_exists($navigationPath)) {
    error_log("categories.php: Não encontrou navigation.php em $navigationPath", 3, __DIR__ . '/../../logs/error.log');
}

include __DIR__ . '/../../templates/header.php'; // Conforme Parte 1
include $navigationPath; // Caminho ajustado
include __DIR__ . '/../../templates/alerts.php'; // Conforme Parte 1
?>

<h1>Gestão de Categorias</h1>
<form action="/estoque-sorveteria/category/store" method="POST">
    <input type="text" name="nome" placeholder="Nome" required>
    <textarea name="descricao" placeholder="Descrição"></textarea>
    <label><input type="checkbox" name="ativo" checked> Ativo</label>
    <button type="submit">Criar</button>
</form>

<table>
    <thead><tr><th>ID</th><th>Nome</th><th>Descrição</th><th>Ativo</th><th>Ações</th></tr></thead>
    <tbody>
        <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?= htmlspecialchars($cat['id']) ?></td>
                <td><?= htmlspecialchars($cat['nome']) ?></td>
                <td><?= htmlspecialchars($cat['descricao'] ?? '') ?></td>
                <td><?= $cat['ativo'] ? 'Sim' : 'Não' ?></td>
                <td>
                    <form action="/estoque-sorveteria/category/update/<?= $cat['id'] ?>" method="POST" style="display:inline;">
                        <input type="text" name="nome" value="<?= htmlspecialchars($cat['nome']) ?>" required>
                        <textarea name="descricao"><?= htmlspecialchars($cat['descricao'] ?? '') ?></textarea>
                        <label><input type="checkbox" name="ativo" <?= $cat['ativo'] ? 'checked' : '' ?>> Ativo</label>
                        <button type="submit">Editar</button>
                    </form>
                    <form action="/estoque-sorveteria/category/destroy/<?= $cat['id'] ?>" method="POST" style="display:inline;">
                        <button type="submit">Desativar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../../templates/footer.php'; // Conforme Parte 1 ?>