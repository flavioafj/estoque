<?php

namespace App\Controllers;

use Controllers\BaseController;
use Models\Movimentacao;
use Models\MovimentacaoItem;
use Helpers\Session;
use Models\Alert;

class MovimentacaoController extends BaseController
{
    /**
     * Processa o registro de uma entrada de estoque.
     */
    public function registrarEntrada(array $postData, array $fileData)
    {
        $session = new Session();
        $usuarioId = $session->get('user_id');
        $itens = $postData['produtos'] ?? [];
        $tipoMovimentacaoId = $postData['tipo_movimentacao_id'] ?? null; // NOVO
        $observacao = $postData['observacao'] ?? ''; // NOVO

        if (!$tipoMovimentacaoId) { // NOVO
            $session->setFlash('error', 'O tipo de movimentação é obrigatório.');
            header('Location: /estoque-sorveteria/public/admin/entradas.php');
            return;
        }

        
        // Lógica de Upload de Nota Fiscal (placeholder)
        if (isset($fileData['nota_fiscal']) && $fileData['nota_fiscal']['error'] === UPLOAD_ERR_OK) {
            $observacao = "Entrada via NF: " . basename($fileData['nota_fiscal']['name']);
            // Aqui entraria a lógica complexa para ler o XML/PDF da NF e popular o array $itens.
            // Por enquanto, vamos focar na entrada manual.
        } else {
            $observacao = "Entrada manual.";
        }

        if (empty(array_filter($itens))) {
            $session->setFlash('error', 'Nenhum produto foi adicionado à entrada.');
            header('Location: /estoque-sorveteria/public/admin/entradas.php');
            return;
        }

        $movimentacaoModel = new Movimentacao();
        $movimentacaoId = $movimentacaoModel->criar($tipoMovimentacaoId, $usuarioId, $observacao);

        if ($movimentacaoId) {
            $itemModel = new MovimentacaoItem();
            if ($itemModel->adicionarItens($movimentacaoId, $itens)) {

                // Verifica estoque mínimo para cada item
                $alertModel = new Alert();
                foreach ($itens as $item) {
                    $alertModel->checkLowStock($item);
                }

                $session->setFlash('success', 'Entrada de estoque registrada com sucesso!');
            } else {
                $session->setFlash('error', 'Falha ao registrar os itens da entrada.');
            }
        } else {
            $session->setFlash('error', 'Falha ao criar o registro de movimentação.');
        }

        header('Location: /estoque-sorveteria/public/admin/products.php');
    }

    /**
     * Processa o registro de uma saída de estoque.
     */
    public function registrarSaida(array $postData)
    {
        $session = new Session();
        $usuarioId = $session->get('user_id');
        $itens = $postData['produtos'] ?? [];
        $tipoMovimentacaoId = $postData['tipo_movimentacao_id'] ?? null; // NOVO
        $observacao = $postData['observacao'] ?? '';

        if (!$tipoMovimentacaoId) { // NOVO
            $session->setFlash('error', 'O tipo de movimentação é obrigatório.');
            header('Location: /estoque-sorveteria/public/saidas.php');
            return;
        }


        if (empty(array_filter($itens))) {
            $session->setFlash('error', 'Nenhum produto foi selecionado para saída.');
            header('Location: /estoque-sorveteria/public/saidas.php');
            return;
        }

        $movimentacaoModel = new Movimentacao();
        $movimentacaoId = $movimentacaoModel->criar($tipoMovimentacaoId, $usuarioId, $observacao);

        if ($movimentacaoId) {
            $itemModel = new MovimentacaoItem();
            if ($itemModel->adicionarItens($movimentacaoId, $itens)) {

                // Verifica estoque mínimo para cada item
                $alertModel = new Alert();
                foreach ($itens as $produtoId => $item) {
                    $alertModel->checkLowStock($produtoId);   
                }

                $session->setFlash('success', 'Saída de estoque registrada com sucesso!');
            } else {
                $session->setFlash('error', 'Falha ao registrar os itens da saída.');
            }
        } else {
            $session->setFlash('error', 'Falha ao criar o registro de movimentação.');
        }

        header('Location: /estoque-sorveteria/public/dashboard.php');
    }
}