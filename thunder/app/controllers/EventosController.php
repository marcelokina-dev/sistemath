<?php

require_once __DIR__ . '/BaseController.php';

class EventosController extends BaseController
{
    private $db;
    private $eventoModel;
    private $lutaModel;

    public function __construct($db)
    {
        $this->db = $db;
        $this->eventoModel = new EventoModel($this->db);
        $this->lutaModel = new LutaModel($this->db);
    }

    /**
     * LISTAGEM DE EVENTOS
     */
    public function index()
    {
        $filtros = [
            'nome'   => $_GET['nome'] ?? '',
            'ano'    => $_GET['ano'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];

        $paginaAtual = (int) ($_GET['pagina'] ?? 1);
        $itensPorPagina = 10;

        $resultado = $this->eventoModel->listarComFiltros($filtros, $itensPorPagina, $paginaAtual);

        $this->render('eventos/index', [
            'eventos'        => $resultado['dados'],
            'totalPaginas'   => $resultado['totalPaginas'],
            'totalRegistros' => $resultado['totalRegistros'],
            'paginaAtual'    => $paginaAtual,
            'filtros'        => $filtros,
            'titulo'         => 'Gerenciar Eventos'
        ]);
    }

    /**
     * TELA DE CADASTRO
     */
    public function create()
    {
        $modalidadeModel = new ModalidadeModel($this->db);
        $this->render('eventos/create', [
            'titulo'      => 'Cadastrar Novo Evento',
            'modalidades' => $modalidadeModel->listarTodas()
        ]);
    }

    /**
     * SALVAR EVENTO
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = $_POST;
            $dados['foto'] = $this->uploadFoto($_FILES['foto'] ?? null);

            if ($this->eventoModel->salvar($dados)) {
                return $this->redirect('/eventos?msg=sucesso');
            } else {
                return $this->redirect('/eventos/create?msg=erro');
            }
        }
    }

    /**
     * TELA DE EDIÇÃO
     */
    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) return $this->redirect('/eventos');

        $evento = $this->eventoModel->buscarPorId($id);
        if (!$evento) return $this->redirect('/eventos?msg=nao_encontrado');

        $modalidadeModel = new ModalidadeModel($this->db);
        
        $this->render('eventos/edit', [
            'evento'      => $evento,
            'modalidades' => $modalidadeModel->listarTodas(),
            'titulo'      => 'Editar Evento: ' . $evento['nome']
        ]);
    }

    /**
     * ATUALIZAR EVENTO
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_evento'] ?? null;
            $dados = $_POST;

            if (!empty($_FILES['foto']['name'])) {
                $dados['foto'] = $this->uploadFoto($_FILES['foto']);
            } else {
                $dados['foto'] = $_POST['foto_atual'] ?? null;
            }

            if ($this->eventoModel->atualizar($id, $dados)) {
                return $this->redirect('/eventos?msg=atualizado');
            } else {
                return $this->redirect("/eventos/edit?id=$id&msg=erro");
            }
        }
    }

    /**
     * EXCLUIR EVENTO
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;
        if ($id && $this->eventoModel->excluir($id)) {
            return $this->redirect('/eventos?msg=excluido');
        } else {
            return $this->redirect('/eventos?msg=erro_excluir');
        }
    }

    // --- MÉTODOS DO CARD DE LUTAS ---

    public function lutas()
    {
        $id_evento = $_GET['id'] ?? null;
        if (!$id_evento) return $this->redirect('/eventos');

        $evento = $this->eventoModel->buscarPorId($id_evento);
        $lutas = $this->lutaModel->listarPorEvento($id_evento);

        $this->render('eventos/gerenciar_lutas', [
            'lutas'     => $lutas,
            'evento'    => $evento,
            'id_evento' => $id_evento,
            'titulo'    => 'Card do Evento - ' . ($evento['nome'] ?? '')
        ]);
    }

    public function novaLuta()
    {
        $id_evento = $_GET['id'] ?? null;
        if (!$id_evento) return $this->redirect('/eventos');

        $proximaOrdem = $this->eventoModel->buscarProximaOrdemCard($id_evento);

        $this->render('eventos/nova_luta', [
            'evento'       => $this->eventoModel->buscarPorId($id_evento),
            'proximaOrdem' => $proximaOrdem,
            'modalidades'  => (new ModalidadeModel($this->db))->listarTodas(),
            'atletas'      => (new AtletaModel($this->db))->listarParaCombate(),
            'arbitros'     => (new ArbitroModel($this->db))->listar(),
            'titulo'       => 'Casar Novo Confronto'
        ]);
    }

    public function salvarLuta()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_evento = $_POST['id_evento'] ?? 0;
            if ($this->lutaModel->salvar($_POST)) {
                return $this->redirect("/eventos/lutas?id=$id_evento&msg=sucesso");
            } else {
                return $this->redirect("/eventos/nova-luta?id=$id_evento&msg=erro");
            }
        }
    }

    public function editLuta()
    {
        $id_luta = $_GET['id'] ?? null;
        $luta = $this->lutaModel->buscarPorId($id_luta);

        if (!$luta) return $this->redirect('/eventos?msg=luta_nao_encontrada');

        $this->render('eventos/edit_luta', [
            'luta'        => $luta,
            'evento'      => $this->eventoModel->buscarPorId($luta['id_evento']),
            'modalidades' => (new ModalidadeModel($this->db))->listarTodas(),
            'atletas'     => (new AtletaModel($this->db))->listarParaCombate(),
            'arbitros'    => (new ArbitroModel($this->db))->listar(),
            'titulo'      => 'Editar Luta'
        ]);
    }

    public function updateLuta()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id_luta'] ?? 0);
            $id_evento = (int) ($_POST['id_evento'] ?? 0);

            if ($this->lutaModel->atualizar($id, $_POST)) {
                return $this->redirect("/eventos/lutas?id=$id_evento&msg=atualizado");
            } else {
                return $this->redirect("/eventos/lutas?id=$id_evento&msg=erro_banco");
            }
        }
    }

    public function excluirLuta()
    {
        $id_luta = $_GET['id'] ?? null;
        $id_evento = $_GET['id_evento'] ?? null;

        if ($id_luta && $this->lutaModel->excluir($id_luta)) {
            return $this->redirect("/eventos/lutas?id=$id_evento&msg=excluido");
        } else {
            return $this->redirect("/eventos/lutas?id=$id_evento&msg=erro");
        }
    }

    // --- RESULTADOS E RANKING ---

    public function lancarLuta()
    {
        $id_luta = $_GET['id_luta'] ?? null;
        if (!$id_luta) return $this->redirect('/eventos?msg=id_invalido');

        $luta = $this->lutaModel->buscarPorIdCompleto($id_luta);
        $regraModel = new RegraPontuacaoModel($this->db);
        $metodosVitoria = $regraModel->getMetodosVitoria();

        $this->render('eventos/lancar_resultado', [
            'luta'           => $luta,
            'arbitros'       => (new ArbitroModel($this->db))->listar(),
            'metodosVitoria' => $metodosVitoria,
            'titulo'         => 'Resultado Oficial'
        ]);
    }

    public function salvarResultado()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_luta = $_POST['id_luta'] ?? null;
            $id_evento = $_POST['id_evento'] ?? null;

            if ($this->lutaModel->registrarResultado($_POST)) {
                // Tenta processar o ranking automaticamente
                try {
                    $this->processarRankingLuta($id_luta);
                } catch (Exception $e) {
                    error_log("Erro ao processar ranking: " . $e->getMessage());
                }

                return $this->redirect("/eventos/lutas?id=$id_evento&msg=resultado_publicado");
            } else {
                return $this->redirect("/eventos/lancar-resultado?id_luta=$id_luta&msg=erro_salvar");
            }
        }
    }

    /**
     * Lógica de processamento de Ranking (Pontuação Automática)
     */
    private function processarRankingLuta($id_luta)
    {
        // 1. Busca dados da luta
        $sql = "SELECT vencedor_id, id_atleta_azul, id_atleta_vermelho, id_modalidade, id_categoria_peso, id_metodo 
                FROM lutas WHERE id_luta = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_luta]);
        $luta = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$luta || !$luta['vencedor_id']) return;

        // 2. Busca regras de pontuação
        $sqlRegra = "SELECT pontos_vitoria, pontos_derrota FROM regras_pontuacao 
                     WHERE id_modalidade = ? AND id_metodo = ? LIMIT 1";
        $stmtRegra = $this->db->prepare($sqlRegra);
        $stmtRegra->execute([$luta['id_modalidade'], $luta['id_metodo']]);
        $regra = $stmtRegra->fetch(PDO::FETCH_ASSOC);

        $ptsVence = $regra['pontos_vitoria'] ?? 3;
        $ptsPerde = $regra['pontos_derrota'] ?? 0;

        // 3. Atualiza Vencedor
        $this->atualizarSaldoRanking($luta['vencedor_id'], $luta['id_modalidade'], $luta['id_categoria_peso'], $ptsVence);

        // 4. Atualiza Perdedor
        $id_perdedor = ($luta['vencedor_id'] == $luta['id_atleta_azul']) ? $luta['id_atleta_vermelho'] : $luta['id_atleta_azul'];
        $this->atualizarSaldoRanking($id_perdedor, $luta['id_modalidade'], $luta['id_categoria_peso'], $ptsPerde);
    }

    private function atualizarSaldoRanking($id_atleta, $id_mod, $id_cat, $pontos)
    {
        $sql = "INSERT INTO ranking (id_atleta, id_modalidade, id_categoria_peso, pontos) 
                VALUES (:a, :m, :c, :p) 
                ON DUPLICATE KEY UPDATE pontos = pontos + :p";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':a' => $id_atleta, ':m' => $id_mod, ':c' => $id_cat, ':p' => $pontos]);
    }

    // --- AUXILIARES E AJAX ---

    public function buscarCategoriasPorModalidade()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $id_modalidade = $_GET['id_modalidade'] ?? null;
        $sexo = $_GET['sexo'] ?? null;

        try {
            $categorias = $this->eventoModel->buscarCategoriasPorModalidade($id_modalidade, $sexo);
            echo json_encode($categorias);
        } catch (Exception $e) {
            echo json_encode([]);
        }
        exit;
    }

    private function uploadFoto($file)
    {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) return null;

        $diretorio = __DIR__ . '/../../public/uploads/eventos/';
        if (!is_dir($diretorio)) mkdir($diretorio, 0777, true);

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $novoNome = md5(uniqid()) . "." . $extension;

        return move_uploaded_file($file['tmp_name'], $diretorio . $novoNome) ? $novoNome : null;
    }
}