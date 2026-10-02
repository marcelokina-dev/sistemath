<?php

require_once __DIR__ . '/BaseController.php';

class RegrasController extends BaseController
{
    private $regraModel;
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
        // Inicializa o model passando a conexão com o banco
        $this->regraModel = new RegraPontuacaoModel($this->db);
    }

    /**
     * Listagem de regras com paginação e busca
     */
    public function index()
    {
        $itensPorPagina = 15;
        $paginaAtual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
        if ($paginaAtual < 1) $paginaAtual = 1;
        
        $offset = ($paginaAtual - 1) * $itensPorPagina;
        $busca = $_GET['busca'] ?? '';

        // Buscando dados com paginação
        $totalRegistros = $this->regraModel->contarTotal($busca);
        $regras = $this->regraModel->listarPaginado($itensPorPagina, $offset, $busca);
        $totalPaginas = ceil($totalRegistros / $itensPorPagina);

        $this->render('regras/index', [
            'regras'      => $regras,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'busca'       => $busca,
            'titulo'      => 'Regras de Pontuação'
        ]);
    }

    /**
     * Tela de criação
     */
    public function create()
    {
        $this->render('regras/create', [
            'titulo'      => 'Nova Regra de Pontuação',
            'modalidades' => $this->regraModel->getModalidades(),
            'metodos'     => $this->regraModel->getMetodos()
        ]);
    }

    /**
     * Salva a nova regra
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->regraModel->salvar($_POST)) {
                return $this->redirect('/regras?msg=sucesso');
            } else {
                return $this->redirect('/regras/create?msg=erro');
            }
        }
    }

    /**
     * Tela de edição
     */
    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            return $this->redirect('/regras');
        }

        $regra = $this->regraModel->buscarPorId($id);
        if (!$regra) {
            return $this->redirect('/regras?msg=nao_encontrado');
        }

        $this->render('regras/edit', [
            'titulo'      => 'Editar Regra de Pontuação',
            'regra'       => $regra,
            'modalidades' => $this->regraModel->getModalidades(),
            'metodos'     => $this->regraModel->getMetodos()
        ]);
    }

    /**
     * Atualiza a regra existente
     */
/**
 * Atualiza a regra existente
 */
public function update()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // AJUSTE AQUI: O nome no seu HTML é 'id_regra' e não 'id'
        $id = $_POST['id_regra'] ?? null; 
        
        if ($id && $this->regraModel->atualizar($id, $_POST)) {
            return $this->redirect('/regras?msg=editado');
        } else {
            // Caso o ID falhe, redireciona com erro
            return $this->redirect('/regras/edit?id=' . $id . '&msg=erro');
        }
    }
}

    /**
     * Exclui uma regra
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->regraModel->excluir($id);
            return $this->redirect('/regras?msg=excluido');
        }
        return $this->redirect('/regras');
    }

    /**
     * Processamento de Pontuação
     * Nota: Este método é geralmente invocado pelo EventosController
     * após o lançamento de um resultado oficial.
     */
    public function processarPontuacaoLuta($id_luta)
    {
        try {
            // Delega a lógica complexa de cálculo para o Model
            return $this->regraModel->processarPontuacaoLuta($id_luta);
        } catch (Exception $e) {
            error_log("Erro ao processar pontuação: " . $e->getMessage());
            return false;
        }
    }
}