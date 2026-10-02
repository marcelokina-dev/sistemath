<?php

require_once 'BaseController.php';
require_once __DIR__ . '/../models/EquipeModel.php';

class EquipesController extends BaseController
{
    private $model;
    private $db;
    private $uploadPath;

    /**
     * O Router passa o objeto PDO ($db) para o construtor.
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->model = new EquipeModel($db);

        // Ajuste no caminho: agora ele descobre a pasta public/uploads a partir de onde o controller está
        $this->uploadPath = __DIR__ . '/../../public/uploads/equipes/';
    }

    /**
     * LISTAGEM DE EQUIPES
     */
    public function index()
    {
        $itensPorPagina = 15;
        $paginaAtual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
        if ($paginaAtual < 1)
            $paginaAtual = 1;
        $offset = ($paginaAtual - 1) * $itensPorPagina;

        $filtros = [
            'nome' => $_GET['nome'] ?? '',
            'cidade' => $_GET['cidade'] ?? '',
            'estado' => $_GET['estado'] ?? ''
        ];

        $equipes = $this->model->getEquipes($filtros, $itensPorPagina, $offset);
        $totalRegistros = $this->model->countEquipes($filtros);
        $totalPaginas = ceil($totalRegistros / $itensPorPagina);

        $this->render('equipes/index', [
            'equipes' => $equipes,
            'filtros' => $filtros,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'totalRegistros' => $totalRegistros,
            'title' => 'Gestão de Equipes'
        ]);
    }

    /**
     * TELA DE CRIAÇÃO
     */
    public function create()
    {
        $this->render('equipes/create', [
            'title' => 'Nova Equipe',
            'equipe' => null
        ]);
    }

    /**
     * PROCESSAR SALVAMENTO (POST)
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fotoNome = 'sem-foto.png';

            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $fotoNome = 'equipe_' . time() . '.' . $ext;

                if (!is_dir($this->uploadPath)) {
                    mkdir($this->uploadPath, 0755, true);
                }

                move_uploaded_file($_FILES['foto']['tmp_name'], $this->uploadPath . $fotoNome);
            }

            if ($this->model->store($_POST, $fotoNome)) {
                return $this->redirect('/equipes?msg=sucesso');
            } else {
                return $this->redirect('/equipes/create?msg=erro');
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
            return $this->redirect('/equipes');
        }

        $dadosDaEquipe = $this->model->find($id);

        if (!$dadosDaEquipe) {
            return $this->redirect('/equipes?msg=nao_encontrado');
        }

        $this->render('equipes/edit', [
            'title' => 'Editar Equipe',
            'equipe' => $dadosDaEquipe
        ]);
    }

    /**
     * PROCESSAR ATUALIZAÇÃO (POST)
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_equipe'] ?? null;
            if (!$id) return $this->redirect('/equipes');

            $equipeAtual = $this->model->find($id);
            $fotoNome = $equipeAtual['foto'] ?? 'sem-foto.png';

            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $novoNome = 'equipe_' . time() . '.' . $ext;

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $this->uploadPath . $novoNome)) {
                    if ($fotoNome !== 'sem-foto.png' && file_exists($this->uploadPath . $fotoNome)) {
                        @unlink($this->uploadPath . $fotoNome);
                    }
                    $fotoNome = $novoNome;
                }
            }

            if ($this->model->update($id, $_POST, $fotoNome)) {
                return $this->redirect('/equipes?msg=editado');
            } else {
                return $this->redirect('/equipes/edit?id=' . $id . '&msg=erro');
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
            $equipe = $this->model->find($id);
            if ($equipe && $equipe['foto'] !== 'sem-foto.png') {
                $caminhoFoto = $this->uploadPath . $equipe['foto'];
                if(file_exists($caminhoFoto)) {
                    @unlink($caminhoFoto);
                }
            }

            if ($this->model->delete($id)) {
                return $this->redirect('/equipes?msg=excluido');
            }
        }
        return $this->redirect('/equipes');
    }
}