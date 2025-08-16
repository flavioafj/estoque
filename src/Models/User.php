<?php
/**
 * Classe User - Modelo de Usuário
 * src/Models/User.php
 */

namespace Models;

class User extends BaseModel {
    protected $table = 'usuarios';
    protected $fillable = [
        'nome_completo',
        'email',
        'usuario',
        'senha',
        'perfil_id',
        'ativo',
        'ultimo_acesso',
        'token_recuperacao',
        'token_expiracao'
    ];
    
    // Autenticar usuário
    public function authenticate($username, $password) {
        // Buscar usuário
        $sql = "SELECT u.*, p.nome as perfil_nome 
                FROM {$this->table} u 
                JOIN perfis p ON u.perfil_id = p.id 
                WHERE (u.usuario = :username OR u.email = :username) 
                AND u.ativo = 1";
        
        $user = $this->db->queryOne($sql, [':username' => $username]);
        
        if (!$user) {
            return false;
        }
        
        // Verificar senha
        if (!password_verify($password, $user['senha'])) {
            return false;
        }
        
        // Atualizar último acesso
        $this->updateLastAccess($user['id']);
        
        // Remover senha do retorno
        unset($user['senha']);
        
        return $user;
    }
    
    // Atualizar último acesso
    public function updateLastAccess($userId) {
        $sql = "UPDATE {$this->table} SET ultimo_acesso = NOW() WHERE id = :id";
        $this->db->prepare($sql);
        $this->db->bind(':id', $userId);
        return $this->db->execute();
    }
    
    // Criar novo usuário
    public function createUser($data) {
        // Criptografar senha
        if (isset($data['senha'])) {
            $data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        }
        
        return $this->create($data);
    }
    
    // Atualizar usuário
    public function updateUser($id, $data) {
        // Se houver nova senha, criptografar
        if (isset($data['senha']) && !empty($data['senha'])) {
            $data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        } else {
            // Se senha vazia, não atualizar
            unset($data['senha']);
        }
        
        return $this->update($id, $data);
    }
    
    // Buscar usuário com perfil
    public function getUserWithProfile($id) {
        $sql = "SELECT u.*, p.nome as perfil_nome, p.descricao as perfil_descricao 
                FROM {$this->table} u 
                JOIN perfis p ON u.perfil_id = p.id 
                WHERE u.id = :id";
        
        $user = $this->db->queryOne($sql, [':id' => $id]);
        
        if ($user) {
            unset($user['senha']);
        }
        
        return $user;
    }
    
    // Listar todos os usuários com perfis
    public function getAllWithProfiles($onlyActive = false) {
        $sql = "SELECT u.id, u.nome_completo, u.email, u.usuario, 
                       u.ativo, u.ultimo_acesso, u.criado_em,
                       p.nome as perfil_nome 
                FROM {$this->table} u 
                JOIN perfis p ON u.perfil_id = p.id";
        
        if ($onlyActive) {
            $sql .= " WHERE u.ativo = 1";
        }
        
        $sql .= " ORDER BY u.nome_completo";
        
        return $this->db->query($sql);
    }
    
    // Verificar se email existe
    public function emailExists($email, $excludeId = null) {
        return $this->exists('email', $email, $excludeId);
    }
    
    // Verificar se usuário existe
    public function usernameExists($username, $excludeId = null) {
        return $this->exists('usuario', $username, $excludeId);
    }
    
    // Gerar token de recuperação
    public function generateRecoveryToken($email) {
        $user = $this->findBy('email', $email);
        
        if (!$user) {
            return false;
        }
        
        $token = bin2hex(random_bytes(32));
        $expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $this->update($user['id'], [
            'token_recuperacao' => $token,
            'token_expiracao' => $expiration
        ]);
        
        return $token;
    }
    
    // Validar token de recuperação
    public function validateRecoveryToken($token) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE token_recuperacao = :token 
                AND token_expiracao > NOW()";
        
        return $this->db->queryOne($sql, [':token' => $token]);
    }
    
    // Resetar senha com token
    public function resetPasswordWithToken($token, $newPassword) {
        $user = $this->validateRecoveryToken($token);
        
        if (!$user) {
            return false;
        }
        
        return $this->update($user['id'], [
            'senha' => password_hash($newPassword, PASSWORD_DEFAULT),
            'token_recuperacao' => null,
            'token_expiracao' => null
        ]);
    }
    
    // Alterar senha (precisa da senha antiga)
    public function changePassword($userId, $oldPassword, $newPassword) {
        $user = $this->find($userId);
        
        if (!$user || !password_verify($oldPassword, $user['senha'])) {
            return false;
        }
        
        return $this->update($userId, [
            'senha' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);
    }
    
    // Verificar permissão do usuário
    public function hasPermission($userId, $permission) {
        $user = $this->getUserWithProfile($userId);
        
        if (!$user) {
            return false;
        }
        
        // Administrador tem todas as permissões
        if ($user['perfil_id'] == 1) {
            return true;
        }
        
        // Implementar lógica específica de permissões conforme necessário
        // Por enquanto, retorna true/false baseado no perfil
        return false;
    }
    
    // Contar usuários ativos
    public function countActive() {
        return $this->count('ativo = 1');
    }
    
    // Obter usuários online (últimos 5 minutos)
    public function getOnlineUsers() {
        $sql = "SELECT id, nome_completo, ultimo_acesso 
                FROM {$this->table} 
                WHERE ultimo_acesso > DATE_SUB(NOW(), INTERVAL 5 MINUTE) 
                AND ativo = 1 
                ORDER BY ultimo_acesso DESC";
        
        return $this->db->query($sql);
    }
}