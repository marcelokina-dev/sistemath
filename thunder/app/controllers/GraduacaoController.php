<?php

require_once __DIR__ . '/BaseController.php';

class GraduacaoController extends BaseController {
    private $graduacaoModel;
    private $modalidadeModel;
    protected $db;

    public function __construct($db) {
        $this->db = $db;
        $this->graduacaoModel = new GraduacaoModel($this->db);
        $this->modalidadeModel = new ModalidadeModel($this->db);
    }

    /**
     * Listagem principal com busca e paginação
     */
    public function index() {
        $paginaAtiva = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $id_modalidade = !empty($_GET['id_modalidade']) ? (int)$_GET['id_modalidade'] : null;
        $itensPorPagina = 10;

        // Dados para a tabela e paginação
        $graduacoes = $this->graduacaoModel->listarPaginado($paginaAtiva, $itensPorPagina, $id_modalidade);
        $totalItens = $this->graduacaoModel->contarTotal($id_modalidade);
        $totalPaginas = ceil($totalItens / $itensPorPagina);

        // Dados para o select de filtro
        $modalidades = $this->modalidadeModel->listarTodas();

        $this->render('graduacao/index', [
            'graduacoes'        => $graduacoes,
            'modalidades'       => $modalidades,
            'totalPaginas'      => $totalPaginas,
            'paginaAtiva'       => $paginaAtiva,
            'filtro_modalidade' => $id_modalidade,
            'titulo'            => 'Graduações'
        ]);
    }

    /**
     * Tela de criação (Novo)
     */
    public function create() {
        $modalidades = $this->modalidadeModel->listarTodas();

        $this->render('graduacao/create', [
            'titulo'      => 'Nova Graduação',
            'modalidades' => $modalidades
        ]);
    }

    /**
     * Processa a inserção (Store)
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'nome'          => $_POST['nome'] ?? '',
                'id_modalidade' => $_POST['id_modalidade'] ?? null
            ];

            if ($this->graduacaoModel->salvar($dados)) {
                return $this->redirect('/graduacao?msg=sucesso');
            } else {
                return $this->redirect('/graduacao/create?msg=erro');
            }
        }
    }

    /**
     * Tela de edição
     */
    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            return $this->redirect('/graduacao');
        }

        $graduacao = $this->graduacaoModel->buscarPorId($id);
        $modalidades = $this->modalidadeModel->listarTodas();

        if (!$graduacao) {
            return $this->redirect('/graduacao?msg=nao_encontrado');
        }

        $this->render('graduacao/edit', [
            'titulo'      => 'Editar Graduação',
            'graduacao'   => $graduacao,
            'modalidades' => $modalidades
        ]);
    }

    /**
     * Processa a atualização (Update)
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_graduacao'] ?? null;
            
            $dados = [
                'nome'          => $_POST['nome'] ?? '',
                'id_modalidade' => $_POST['id_modalidade'] ?? null
            ];

            if ($this->graduacaoModel->atualizar($id, $dados)) {
                return $this->redirect('/graduacao?msg=editado');
            } else {
                return $this->redirect('/graduacao/edit?id=' . $id . '&msg=erro');
            }
        }
    }

    /**
     * Processa a exclusão (Delete)
     */
    public function delete() {
        $id = $_GET['id'] ?? null;
        
        if ($id) {
            $this->graduacaoModel->excluir($id);
            return $this->redirect('/graduacao?msg=excluido');
        } else {
            return $this->redirect('/graduacao?msg=erro_delete');
        }
    }
}