<?php

namespace Models;

use Models\BaseModel;

class Movimentacao extends BaseModel
{
    protected $table = 'movimentacoes';

    /**
     * Cria uma nova movimentação e retorna seu ID.
     *
     * @param int $tipoMovimentacaoId
     * @param int $usuarioId
     * @param string $observacao
     * @param int|null $fornecedorId
     * @return int|false
     */
    public function criar(int $tipoMovimentacaoId, int $usuarioId, string $observacao = '', ?int $fornecedorId = null)
    {
        $sql = "INSERT INTO {$this->table} (tipo_movimentacao_id, data_movimentacao, usuario_id, fornecedor_id, observacoes, status) VALUES (?, NOW(), ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $status = 'PROCESSADO';
        
        if ($stmt->execute([$tipoMovimentacaoId, $usuarioId, $fornecedorId, $observacao, $status])) {
            return $this->db->lastInsertId();
        }
        return false;
    }
}