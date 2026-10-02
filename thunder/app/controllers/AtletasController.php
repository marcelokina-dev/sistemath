<?php

class AtletasController extends BaseController
{
    private $db;

    /**
     * O Router injeta a conexão PDO aqui.
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * LISTAGEM DE ATLETAS (Com Filtros e Paginação)
     */
    public function index()
    {
        $atletaModel = new AtletaModel($this->db);
        $modalidadeModel = new ModalidadeModel($this->db);
        $categoriaModel = new CategoriaPesoModel($this->db);

        // Captura os filtros do GET
        $filtros = [
            'busca' => $_GET['busca'] ?? '',
            'sexo' => $_GET['sexo'] ?? '',
            'modalidade' => $_GET['modalidade'] ?? '',
            'equipe' => $_GET['equipe'] ?? '',
            'categoria' => $_GET['categoria'] ?? ''
        ];

        // Paginação
        $paginaAtual = (int) ($_GET['pagina'] ?? 1);
        $limite = 15;
        $offset = ($paginaAtual - 1) * $limite;

        $atletas = $atletaModel->listar($filtros, $limite, $offset);
        $totalRegistros = $atletaModel->contarTotal($filtros);
        $totalPaginas = ceil($totalRegistros / $limite);

        $this->render('atletas/index', [
            'atletas' => $atletas,
            'filtros' => $filtros,
            'equipes' => $atletaModel->listarEquipesCompleto(),
            'modalidades' => $modalidadeModel->listarTodas(),
            'categorias' => $categoriaModel->listarTodas(),
            'totalPaginas' => $totalPaginas,
            'paginaAtual' => $paginaAtual,
            'title' => 'Atletas'
        ]);
    }

    /**
     * MÉTODO PARA AJAX: Busca categorias de peso dinamicamente
     */
    public function buscarCategorias()
    {
        $id_modalidade = $_GET['modalidade'] ?? null;
        $sexo = $_GET['sexo'] ?? '';

        $sexoFiltro = ($sexo === 'Ambo' || empty($sexo)) ? null : $sexo;

        if ($id_modalidade) {
            $categoriaModel = new CategoriaPesoModel($this->db);
            $categorias = $categoriaModel->buscarPorModalidadeESexo($id_modalidade, $sexoFiltro);

            header('Content-Type: application/json');
            echo json_encode($categorias);
            exit;
        }

        echo json_encode([]);
        exit;
    }

    /**
     * FORMULÁRIO DE CADASTRO
     */
    public function create()
    {
        $modalidadeModel = new ModalidadeModel($this->db);
        $graduacaoModel = new GraduacaoModel($this->db);
        $atletaModel = new AtletaModel($this->db);
        $categoriaModel = new CategoriaPesoModel($this->db);

        $this->render('atletas/create', [
            'modalidades' => $modalidadeModel->listarTodas(),
            'graduacoes' => $graduacaoModel->listar(),
            'equipes' => $atletaModel->listarEquipesCompleto(),
            'categorias' => $categoriaModel->listarTodas(),
            'title' => 'Novo Atleta'
        ]);
    }

    /**
     * DETALHES DO ATLETA
     */
    public function show()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            return $this->redirect('/atletas');
        }

        $atletaModel = new AtletaModel($this->db);
        $atleta = $atletaModel->buscarPorId($id);

        if (!$atleta) {
            die("Atleta não encontrado.");
        }

        // Buscamos as lutas do atleta para o histórico
        $lutas = $atletaModel->buscarHistoricoLutas($id);

        $this->render('atletas/show', [
            'atleta' => $atleta,
            'graduacoes' => $atletaModel->buscarGraduacoesPorAtleta($id),
            'lutas' => $lutas, 
            'title' => 'Perfil: ' . $atleta['nome']
        ]);
    }

    /**
     * FORMULÁRIO DE EDIÇÃO
     */
    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            return $this->redirect('/atletas');
        }

        $atletaModel = new AtletaModel($this->db);
        $modalidadeModel = new ModalidadeModel($this->db);
        $graduacaoModel = new GraduacaoModel($this->db);
        $categoriaModel = new CategoriaPesoModel($this->db);

        $atleta = $atletaModel->buscarPorId($id);
        if (!$atleta)
            die("Atleta não encontrado.");

        $this->render('atletas/edit', [
            'atleta' => $atleta,
            'modalidades' => $modalidadeModel->listarTodas(),
            'graduacoes' => $graduacaoModel->listar(),
            'graduacoesAtleta' => $atletaModel->buscarGraduacoesPorAtleta($id),
            'equipes' => $atletaModel->listarEquipesCompleto(),
            'categorias' => $categoriaModel->listarTodas(),
            'title' => 'Editar Atleta'
        ]);
    }

    /**
     * SALVAR NOVO ATLETA (POST)
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $atletaModel = new AtletaModel($this->db);

            $foto = 'sem-foto.png';
            if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === 0) {
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $foto = time() . '_' . uniqid() . '.' . $ext;

                $diretorio = __DIR__ . '/../../public/uploads/atletas/';

                if (!is_dir($diretorio)) {
                    mkdir($diretorio, 0777, true);
                }

                move_uploaded_file($_FILES['foto']['tmp_name'], $diretorio . $foto);
            }

            $graduacoes = $_POST['graduacoes'] ?? [];
            $idAtleta = $atletaModel->salvarCompleto($_POST, $foto, $graduacoes);

            if ($idAtleta) {
                return $this->redirect('/atletas?sucesso=1');
            }
        }
    }

    /**
     * ATUALIZAR ATLETA (POST)
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_atleta'];
            $atletaModel = new AtletaModel($this->db);

            $fotoFinal = $_POST['foto_atual'] ?? 'sem-foto.png';

            if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === 0) {
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $novoNome = time() . '_' . uniqid() . '.' . $ext;
                $diretorio = __DIR__ . '/../../public/uploads/atletas/';

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $diretorio . $novoNome)) {
                    if (!empty($_POST['foto_atual']) && $_POST['foto_atual'] !== 'sem-foto.png') {
                        $caminhoAntigo = $diretorio . $_POST['foto_atual'];
                        if (file_exists($caminhoAntigo)) {
                            @unlink($caminhoAntigo);
                        }
                    }
                    $fotoFinal = $novoNome;
                }
            }

            $graduacoes = $_POST['graduacoes'] ?? [];
            if ($atletaModel->atualizar($id, $_POST, $graduacoes, $fotoFinal)) {
                return $this->redirect('/atletas?editado=1');
            }
        }
    }

    /**
     * EXCLUSÃO
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $atletaModel = new AtletaModel($this->db);
            $atletaModel->excluir($id);
        }
        return $this->redirect('/atletas?excluido=1');
    }
}