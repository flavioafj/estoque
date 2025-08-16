<?php
// public/index.php

// Carrega as configurações e o autoloader
require_once '../config/config.php';

use Helpers\Session;

Session::start();

// Se o usuário já estiver logado, redireciona para o dashboard.
// Caso contrário, redireciona para a página de login.
if (Session::isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
} else {
    header('Location: login.php');
    exit();
}