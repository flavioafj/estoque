<?php
use Models\BaseModel;

$baseModelPath = __DIR__ . '/BaseModel.php';
if (!file_exists($baseModelPath)) {
    error_log("Product.php: Não encontrou BaseModel.php em $baseModelPath", 3, __DIR__ . '/../../logs/error.log');
    die("Erro: Não foi possível carregar BaseModel.php.");
}
//require_once $baseModelPath;

class Product extends BaseModel {
    protected $table = 'produtos';
    protected $fillable = [
        'nome', 'codigo', 'codigo_barras', 'categoria_id', 'unidade_medida_id',
        'estoque_atual', 'estoque_minimo', 'estoque_maximo', 'preco_custo',
        'preco_venda', 'margem_lucro', 'localizacao', 'fornecedor_principal_id',
        'descricao', 'ativo', 'criado_por', 'foto_url', 'observacoes'
    ];

    public function create($data) {
        $data['criado_por'] = $_SESSION['user_id'] ?? null;
        return parent::create($data);
    }

    public function update($id, $data) {
        return parent::update($id, $data);
    }

    public function delete($id) {
        return parent::softDelete($id);
    }

    public function getAll() {
        return $this->where('ativo', 1);
    }

    public function getById($id) {
        return $this->find($id);
    }
}