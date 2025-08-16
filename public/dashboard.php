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
?>

<main class="container mt-4">
    <h1>Bem-vindo, <?php echo htmlspecialchars($userName); ?>!</h1>
    <p>Este é o painel principal do Sistema de Controle de Estoque.</p>
    
    </main>

<?php
require_once '../templates/footer.php';