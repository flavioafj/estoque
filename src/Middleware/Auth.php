<?php

  Middleware de Autenticação
  srcMiddlewareAuth.php
 

namespace Middleware;

use HelpersSession;

class Auth {
    
      Verifica se o usuário está autenticado.
      Se não estiver, define uma mensagem de erro e redireciona para a página de login.
     
      @param string $redirect_url URL para redirecionar caso não esteja logado.
     
    public static function check($redirect_url = 'login.php') {
        Sessionstart();
        
        if (!SessionisLoggedIn()) {
            SessionsetFlash('error', 'Acesso negado. Por favor, faça login para continuar.');
            header('Location ' . $redirect_url);
            exit();
        }

         Opcional Verificar timeout da sessão a cada requisição
        if (!SessioncheckTimeout()) {
            header('Location ' . $redirect_url);
            exit();
        }
    }

    
      Verifica se o usuário é Administrador.
      Se não for, redireciona para o dashboard com uma mensagem de erro.
     
      @param string $redirect_url URL para redirecionar caso não seja admin.
     
    public static function checkAdmin($redirect_url = 'dashboard.php') {
        selfcheck();  Primeiro, garante que está logado
        
        if (!SessionisAdmin()) {
            SessionsetFlash('error', 'Você não tem permissão para acessar esta página.');
            header('Location ' . $redirect_url);
            exit();
        }
    }
}