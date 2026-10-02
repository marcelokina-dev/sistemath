<?php

require_once __DIR__ . '/BaseController.php';

class ModalidadesController extends BaseController
{
    private $model;
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
        // Inicializa o model passando a conexão com o banco
        $this->model = new ModalidadeModel($this->db);
    }

    /**
     * Listagem de modalidades com paginação e filtro por nome
     */
    public function index()
    {
        $itensPorPagina = 15;
        $paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        if ($paginaAtual < 1) $paginaAtual = 1;
        
        $offset = ($paginaAtual - 1) * $itensPorPagina;
        $filtroNome = $_GET['nome'] ?? '';

        $totalRegistros = $this->model->contarTotal($filtroNome);
        $modalidades = $this->model->listarPaginado($itensPorPagina, $offset, $filtroNome);
        $totalPaginas = ceil($totalRegistros / $itensPorPagina);

        $this->render('modalidades/index', [
            'modalidades'  => $modalidades,
            'paginaAtual'  => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'filtroNome'   => $filtroNome,
            'titulo'       => 'Gerenciar Modalidades'
        ]);
    }

    /**
     * Tela de cadastro
     */
    public function create()
    {
        $this->render('modalidades/create', [
            'titulo' => 'Nova Modalidade'
        ]);
    }

    /**
     * Salva uma nova modalidade
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->model->salvar($_POST)) {
                return $this->redirect('/modalidades?msg=sucesso');
            } else {
                return $this->redirect('/modalidades/create?msg=erro');
            }
        }
    }

    /**
     * Tela de edição
     */
    public function edit()
    {
        $id = $_GET['id'] ?? null;
        $modalidade = $this->model->buscarPorId($id);

        if (!$modalidade) {
            return $this->redirect('/modalidades?msg=nao_encontrado');
        }

        $this->render('modalidades/edit', [
            'titulo'     => 'Editar Modalidade',
            'modalidade' => $modalidade
        ]);
    }

    /**
     * Atualiza os dados da modalidade
     */
    public function update()
    {
        $id = $_POST['id_modalidade'] ?? null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
            if ($this->model->atualizar($id, $_POST)) {
                return $this->redirect('/modalidades?msg=editado');
            } else {
                return $this->redirect('/modalidades/edit?id=' . $id . '&msg=erro');
            }
        }
    }

    /**
     * Exclui uma modalidade
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;
        
        if ($id) {
            if ($this->model->excluir($id)) {
                return $this->redirect('/modalidades?msg=excluido');
            } else {
                // Erro comum: tentativa de excluir modalidade com categorias ou lutas vinculadas
                return $this->redirect('/modalidades?msg=erro_vinculo');
            }
        }
        
        return $this->redirect('/modalidades');
    }
}