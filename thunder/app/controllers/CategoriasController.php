<?php

require_once __DIR__ . '/BaseController.php';

class CategoriasController extends BaseController
{
    private $categoriaModel;
    private $modalidadeModel;
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
        $this->categoriaModel = new CategoriaPesoModel($this->db);
        $this->modalidadeModel = new ModalidadeModel($this->db);
    }

    /**
     * LISTAGEM DE CATEGORIAS
     */
    public function index()
    {
        $itensPorPagina = 15;
        $paginaAtual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
        if ($paginaAtual < 1) $paginaAtual = 1;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        $filtros = [
            'nome' => $_GET['nome'] ?? '',
            'modalidade' => $_GET['modalidade'] ?? '',
            'sexo' => $_GET['sexo'] ?? ''
        ];

        $totalRegistros = $this->categoriaModel->contarTotal($filtros);
        $categorias = $this->categoriaModel->listarPaginado($itensPorPagina, $offset, $filtros);
        $modalidades = $this->modalidadeModel->listarTodas();

        $totalPaginas = ceil($totalRegistros / $itensPorPagina);

        $this->render('categorias/index', [
            'categorias' => $categorias,
            'modalidades' => $modalidades,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'filtros' => $filtros
        ]);
    }

    /**
     * TELA DE CADASTRO
     */
    public function create()
    {
        $modalidades = $this->modalidadeModel->listarTodas();
        $this->render('categorias/create', [
            'titulo' => 'Nova Categoria',
            'modalidades' => $modalidades
        ]);
    }

    /**
     * SALVAR NO BANCO
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'nome' => trim($_POST['nome']),
                'id_modalidade' => (int) $_POST['id_modalidade'],
                'sexo' => $_POST['sexo'] ?? 'M',
                'peso_min' => str_replace(',', '.', $_POST['peso_min']),
                'peso_max' => str_replace(',', '.', $_POST['peso_max'])
            ];

            if ($this->categoriaModel->salvar($dados)) {
                return $this->redirect('/categorias?msg=sucesso');
            } else {
                return $this->redirect('/categorias/create?msg=erro');
            }
        }
    }

    /**
     * TELA DE EDIÇÃO
     */
    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            return $this->redirect('/categorias');
        }

        $categoria = $this->categoriaModel->buscarPorId($id);
        $modalidades = $this->modalidadeModel->listarTodas();

        $this->render('categorias/edit', [
            'titulo' => 'Editar Categoria',
            'categoria' => $categoria,
            'modalidades' => $modalidades
        ]);
    }

    /**
     * ATUALIZAR NO BANCO
     */
    public function update()
    {
        $id = $_POST['id_categoria_peso'] ?? null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
            $sexo = $_POST['sexo'] ?? null;

            if (!$sexo) {
                return $this->redirect('/categorias/edit?id=' . $id . '&msg=erro_sexo');
            }

            $dados = [
                'nome' => trim($_POST['nome']),
                'id_modalidade' => (int) $_POST['id_modalidade'],
                'sexo' => $sexo,
                'peso_min' => str_replace(',', '.', $_POST['peso_min']),
                'peso_max' => str_replace(',', '.', $_POST['peso_max'])
            ];

            if ($this->categoriaModel->atualizar($id, $dados)) {
                return $this->redirect('/categorias?msg=editado');
            } else {
                return $this->redirect('/categorias/edit?id=' . $id . '&msg=erro');
            }
        }
    }

    /**
     * EXCLUIR
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->categoriaModel->excluir($id);
            return $this->redirect('/categorias?msg=excluido');
        }
        return $this->redirect('/categorias');
    }

    /**
     * AJAX PARA O FORM DE LUTAS
     */
    public function buscarCategoriasPorModalidade()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $id_mod = $_GET['id_modalidade'] ?? null;
        $sexo = $_GET['sexo'] ?? null;

        $categorias = $this->categoriaModel->buscarPorModalidadeESexo($id_mod, $sexo);
        echo json_encode($categorias);
        exit;
    }
}