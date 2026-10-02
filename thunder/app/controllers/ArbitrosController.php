<?php

require_once 'BaseController.php';
require_once __DIR__ . '/../models/ArbitroModel.php';

class ArbitrosController extends BaseController
{
    private $db;
    private $model;

    // O construtor recebe o $db e o repassa ao Model
    public function __construct($db)
    {
        $this->db = $db;
        $this->model = new ArbitroModel($this->db);
    }

    public function index()
    {
        $itensPorPagina = 15;
        $pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
        $offset = ($pagina - 1) * $itensPorPagina;

        $filtros = [
            'nome' => $_GET['nome'] ?? '',
            'sexo' => $_GET['sexo'] ?? '',
            'modalidade' => $_GET['modalidade'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];

        $arbitros = $this->model->getArbitros($filtros, $itensPorPagina, $offset);
        $totalItens = $this->model->countArbitros($filtros);
        $totalPaginas = ceil($totalItens / $itensPorPagina);
        $modalidades = $this->db->query("SELECT * FROM modalidade ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

        $this->render('arbitros/index', [
            'arbitros' => $arbitros,
            'totalPaginas' => $totalPaginas,
            'paginaAtual' => $pagina, // Altere de 'pagina' para 'paginaAtual'
            'modalidades' => $modalidades,
            'filtros' => $filtros
        ]);
    }

    public function create()
    {
        $modalidades = $this->db->query("SELECT * FROM modalidade ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        $this->render('arbitros/form', [
            'modalidades' => $modalidades,
            'titulo' => 'Novo Árbitro'
        ]);
    }

    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            // AJUSTE: Usando o redirect dinâmico
            return $this->redirect('/arbitros');
        }

        $arbitro = $this->model->find($id);
        $modalidades = $this->db->query("SELECT * FROM modalidade ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

        $this->render('arbitros/form', [
            'arbitro' => $arbitro,
            'modalidades' => $modalidades,
            'titulo' => 'Editar Árbitro'
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();

                $sqlEnd = "INSERT INTO endereco (cep, logradouro, numero, bairro, cidade, estado, pais) 
                           VALUES (:cep, :log, :num, :bai, :cid, :uf, 'Brasil')";
                $stmtEnd = $this->db->prepare($sqlEnd);
                $stmtEnd->execute([
                    ':cep' => $_POST['cep'],
                    ':log' => $_POST['logradouro'],
                    ':num' => $_POST['numero'],
                    ':bai' => $_POST['bairro'],
                    ':cid' => $_POST['cidade'],
                    ':uf' => $_POST['estado']
                ]);
                $idEndereco = $this->db->lastInsertId();

                $sqlCont = "INSERT INTO contato (email, celular) VALUES (:email, :cel)";
                $stmtCont = $this->db->prepare($sqlCont);
                $stmtCont->execute([
                    ':email' => $_POST['email'],
                    ':cel' => $_POST['celular']
                ]);
                $idContato = $this->db->lastInsertId();

                $sqlArb = "INSERT INTO arbitros (nome, apelido, sexo, status, id_endereco, id_contato) 
                           VALUES (:nome, :apelido, :sexo, :status, :id_end, :id_cont)";
                $stmtArb = $this->db->prepare($sqlArb);
                $stmtArb->execute([
                    ':nome' => $_POST['nome'],
                    ':apelido' => $_POST['apelido'],
                    ':sexo' => $_POST['sexo'],
                    ':status' => $_POST['status'] ?? 'Ativo',
                    ':id_end' => $idEndereco,
                    ':id_cont' => $idContato
                ]);
                $idArbitro = $this->db->lastInsertId();

                if (!empty($_POST['modalidades'])) {
                    $this->model->syncModalidades($idArbitro, $_POST['modalidades']);
                }

                $this->db->commit();

                // AJUSTE: Usando o redirect dinâmico
                return $this->redirect('/arbitros?msg=sucesso');

            } catch (Exception $e) {
                if ($this->db->inTransaction())
                    $this->db->rollBack();
                die("Erro ao salvar árbitro: " . $e->getMessage());
            }
        }
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idArb = $_POST['id_arbitro'];
            $idEnd = $_POST['id_endereco'];
            $idCont = $_POST['id_contato'];

            try {
                $this->db->beginTransaction();

                $this->db->prepare("UPDATE endereco SET cep = :cep, logradouro = :log, numero = :num, bairro = :bai, cidade = :cid, estado = :uf WHERE id_endereco = :id")
                    ->execute([
                        ':cep' => $_POST['cep'],
                        ':log' => $_POST['logradouro'],
                        ':num' => $_POST['numero'],
                        ':bai' => $_POST['bairro'],
                        ':cid' => $_POST['cidade'],
                        ':uf' => $_POST['estado'],
                        ':id' => $idEnd
                    ]);

                $this->db->prepare("UPDATE contato SET email = :email, celular = :cel WHERE id_contato = :id")
                    ->execute([':email' => $_POST['email'], ':cel' => $_POST['celular'], ':id' => $idCont]);

                $this->db->prepare("UPDATE arbitros SET nome = :nome, apelido = :apelido, sexo = :sexo, status = :status WHERE id_arbitro = :id")
                    ->execute([
                        ':nome' => $_POST['nome'],
                        ':apelido' => $_POST['apelido'],
                        ':sexo' => $_POST['sexo'],
                        ':status' => $_POST['status'],
                        ':id' => $idArb
                    ]);

                $this->model->syncModalidades($idArb, $_POST['modalidades'] ?? []);

                $this->db->commit();

                // AJUSTE: Usando o redirect dinâmico
                return $this->redirect('/arbitros?msg=editado');

            } catch (Exception $e) {
                if ($this->db->inTransaction())
                    $this->db->rollBack();
                die("Erro ao atualizar: " . $e->getMessage());
            }
        }
    }

    public function delete()
    {
        $id = $_GET['id'] ?? null;
        if ($id) {
            try {
                $this->db->beginTransaction();
                $this->db->prepare("DELETE FROM arbitro_modalidade WHERE id_arbitro = :id")->execute([':id' => $id]);
                $this->db->prepare("DELETE FROM arbitros WHERE id_arbitro = :id")->execute([':id' => $id]);
                $this->db->commit();

                // AJUSTE: Usando o redirect dinâmico
                return $this->redirect('/arbitros?msg=excluido');

            } catch (Exception $e) {
                if ($this->db->inTransaction())
                    $this->db->rollBack();
                die("Erro ao excluir: " . $e->getMessage());
            }
        }
    }
}