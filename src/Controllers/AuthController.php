<?php
/**
 * Controlador de Autenticação
 * src/Controllers/AuthController.php
 */

namespace Controllers;

use Models\User;
use Helpers\Session;

class AuthController extends BaseController {

    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Exibe a página de login.
     */
    public function showLoginForm() {
        // Se já estiver logado, redireciona para o dashboard
        if (Session::isLoggedIn()) {
            header('Location: /dashboard.php');
            exit();
        }
        $this->render('auth/login', ['title' => 'Login']);
    }

    /**
     * Processa a tentativa de login.
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login.php');
            exit();
        }

        // Validação básica
        $username = $_POST['usuario'] ?? '';
        $password = $_POST['senha'] ?? '';

        if (empty($username) || empty($password)) {
            Session::setFlash('error', 'Usuário e senha são obrigatórios.');
            header('Location: /login.php');
            exit();
        }

        // Autenticar
        $userData = $this->userModel->authenticate($username, $password);

        if ($userData) {
            // Sucesso
            Session::login($userData);
            header('Location: /dashboard.php');
            exit();
        } else {
            // Falha
            Session::setFlash('error', 'Credenciais inválidas. Tente novamente.');
            header('Location: /login.php');
            exit();
        }
    }

    /**
     * Realiza o logout do usuário.
     */
    public function logout() {
        Session::logout();
        Session::setFlash('success', 'Você foi desconectado com sucesso.');
        header('Location: /login.php');
        exit();
    }
}