<?php
/**
 * Controlador do Dashboard
 * src/Controllers/DashboardController.php
 */

namespace Controllers;

use Models\Report;
use Helpers\Session;

require_once __DIR__ . '/../Models/Report.php';
require_once __DIR__ . '/../Helpers/Session.php';

class DashboardController extends BaseController
{
    private $reportModel;

    public function __construct()
    {
        //parent::__construct();
        $this->reportModel = new Report();
        // Verificar permissão de administrador
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Acesso não autorizado']);
            exit;
        }
    }

    /**
     * Retorna resumo do dashboard (valor FIFO total)
     */
    public function getSummary()
    {
        try {
            $sql = "SELECT SUM(estoque_atual) as total_estoque FROM produtos WHERE ativo = TRUE";
            $total_estoque = $this->reportModel->rawQuery($sql)[0]['total_estoque'] ?? 0;
            $valor_fifo = 0;
            $produtos = $this->reportModel->rawQuery("SELECT id FROM produtos WHERE ativo = TRUE");
            foreach ($produtos as $produto) {
                $valor_fifo += $this->reportModel->calculateFIFO($produto['id']);
            }
            echo json_encode([
                'total_estoque' => $total_estoque,
                'valor_fifo' => number_format($valor_fifo, 2, '.', '')
            ]);
        } catch (\Exception $e) {
            error_log("DashboardController::getSummary: " . $e->getMessage(), 3, __DIR__ . '/../../logs/error.log');
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao carregar resumo']);
        }
    }

    /**
     * Retorna produtos com estoque baixo
     */
    public function getLowStock()
    {
        try {
            $sql = "SELECT id, codigo, nome, estoque_atual, estoque_minimo, categoria, fornecedor 
                    FROM vw_produtos_estoque_critico 
                    ORDER BY estoque_atual ASC 
                    LIMIT 10";
            $result = $this->reportModel->rawQuery($sql);
            echo json_encode($result);
        } catch (\Exception $e) {
            error_log("DashboardController::getLowStock: " . $e->getMessage(), 3, __DIR__ . '/../../logs/error.log');
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao carregar estoque baixo']);
        }
    }

    /**
     * Retorna movimentações recentes
     */
    public function getRecentMovements()
    {
        try {
            $sql = "SELECT id, tipo, categoria, documento_numero, data_movimentacao, valor_total, fornecedor, usuario 
                    FROM vw_movimentacoes_recentes 
                    ORDER BY data_movimentacao DESC 
                    LIMIT 10";
            $result = $this->reportModel->rawQuery($sql);
            echo json_encode($result);
        } catch (\Exception $e) {
            error_log("DashboardController::getRecentMovements: " . $e->getMessage(), 3, __DIR__ . '/../../logs/error.log');
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao carregar movimentações']);
        }
    }

    /**
     * Retorna dados para gráfico de giro de estoque
     */
    public function getStockTurnover()
    {
        try {
            $sql = "
                SELECT 
                    DATE_FORMAT(m.data_movimentacao, '%Y-%m') as periodo,
                    tm.tipo,
                    SUM(mi.quantidade) as total_quantidade
                FROM movimentacoes m
                JOIN movimentacao_itens mi ON m.id = mi.movimentacao_id
                JOIN tipos_movimentacao tm ON m.tipo_movimentacao_id = tm.id
                WHERE m.status = 'PROCESSADO'
                    AND m.data_movimentacao >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY periodo, tm.tipo
                ORDER BY periodo ASC";
            $result = $this->reportModel->rawQuery($sql);
            
            // Formatar para Chart.js
            $entradas = [];
            $saidas = [];
            $periodos = [];
            foreach ($result as $row) {
                if (!in_array($row['periodo'], $periodos)) {
                    $periodos[] = $row['periodo'];
                }
                if ($row['tipo'] == 'ENTRADA') {
                    $entradas[$row['periodo']] = (float)$row['total_quantidade'];
                } else {
                    $saidas[$row['periodo']] = (float)$row['total_quantidade'];
                }
            }
            echo json_encode([
                'labels' => $periodos,
                'entradas' => array_values(array_replace(array_fill_keys($periodos, 0), $entradas)),
                'saidas' => array_values(array_replace(array_fill_keys($periodos, 0), $saidas))
            ]);
        } catch (\Exception $e) {
            error_log("DashboardController::getStockTurnover: " . $e->getMessage(), 3, __DIR__ . '/../../logs/error.log');
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao carregar dados do gráfico']);
        }
    }
}