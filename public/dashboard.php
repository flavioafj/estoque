<?php
// public/dashboard.php

require_once '../config/config.php';

use Middleware\Auth;
use Helpers\Session;


// Middleware: Apenas usuários logados podem acessar esta página
Auth::check();

// --- Lógica do Dashboard ---
$pageTitle = "Dashboard";
$userName = Session::getUserName();

// Carregar templates
require_once '../templates/header.php';
require_once '../templates/navigation.php';


use Models\Alert;

$alertModel = new Alert();
$pendingCount = count($alertModel->getPendingAlerts());


?>

<?php if ($pendingCount > 0): ?>
    <div class="alert-warning">
        <span class="close-button">&times;</span>
        Você tem <?php echo $pendingCount; ?> alertas de estoque baixo pendentes. <a href="public/alerts">Ver Alertas</a>
    </div>
<?php endif; ?>

<main class="container mt-4">
    <h1>Bem-vindo, <?php echo htmlspecialchars($userName); ?>!</h1>
    <p>Este é o painel principal do Sistema de Controle de Estoque.</p>
    
    </main>

<?php
require_once '../templates/footer.php';