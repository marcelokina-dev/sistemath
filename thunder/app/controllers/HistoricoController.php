<?php

require_once __DIR__ . '/BaseController.php';

class HistoricoController extends BaseController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * TELA PRINCIPAL DA GESTÃO DE CAMPEÕES
     */
    public function index()
    {
        $model = new HistoricoModel($this->db);

        // Dados para as tabelas de exibição
        $dados['campeoes'] = $model->obterCampeoesAtuais();
        $dados['hall_fama'] = $model->obterHallDaFama();

        // Dados para os selects do Modal de "Coroar Novo"
        $dados['atletas'] = $this->db->query("SELECT id_atleta, nome FROM atletas ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        $dados['modalidade'] = $this->db->query("SELECT * FROM modalidade ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        $dados['categorias'] = $this->db->query("SELECT * FROM categoria_peso ORDER BY peso_max ASC")->fetchAll(PDO::FETCH_ASSOC);

        $dados['titulo'] = "Gestão de Campeões";
        
        $this->render('galeria_campeoes/index', $dados);
    }

    /**
     * TORNA UM CINTURÃO VACANTE
     */
    public function vacante()
    {
        $id = $_GET['id'] ?? null;

        if ($id) {
            $model = new HistoricoModel($this->db);
            $sucesso = $model->tornarVacante($id);

            $status = $sucesso ? 'sucesso_vacante' : 'erro_vacante';
            return $this->redirect("/campeoes?status=$status");
        }
        
        return $this->redirect("/campeoes");
    }

    /**
     * REGISTRA UM NOVO CAMPEÃO MANUALMENTE
     */
    public function registrar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_atleta = $_POST['id_atleta'] ?? null;
            $id_mod = $_POST['id_modalidade'] ?? null;
            $id_cat = $_POST['id_categoria_peso'] ?? null;

            if ($id_atleta && $id_mod && $id_cat) {
                $model = new HistoricoModel($this->db);
                $sucesso = $model->registrarNovaConquista($id_atleta, $id_mod, $id_cat);
                $status = $sucesso ? 'sucesso_conquista' : 'erro_conquista';
            } else {
                $status = 'erro_campos_invalidos';
            }

            return $this->redirect("/campeoes?status=$status");
        }
        
        return $this->redirect("/campeoes");
    }

    /**
     * EXCLUI UM REGISTRO DO HISTÓRICO
     */
    public function excluir_historico()
    {
        $id = $_GET['id'] ?? null;

        if ($id) {
            $stmt = $this->db->prepare("DELETE FROM historico_campeoes WHERE id_historico_campeao = ?");
            $sucesso = $stmt->execute([$id]);

            $status = $sucesso ? 'sucesso_exclusao' : 'erro_exclusao';
            return $this->redirect("/campeoes?status=$status");
        }
        
        return $this->redirect("/campeoes");
    }

    /**
     * REGISTRA CAMPEÃO VINDO DIRETO DA TELA DE RANKING
     */
    public function registrar_pelo_ranking()
    {
        $id_atleta = $_GET['id_atleta'] ?? null;
        $id_mod = $_GET['id_mod'] ?? null;
        $id_cat = $_GET['id_cat'] ?? null;

        if ($id_atleta && $id_mod && $id_cat) {
            $model = new HistoricoModel($this->db);
            $sucesso = $model->registrarNovaConquista($id_atleta, $id_mod, $id_cat);

            if ($sucesso) {
                // Sincronização automática do Ranking Global após nova conquista
                try {
                    $rankingModel = new RankingModel($this->db);
                    $rankingModel->processarRankingGlobal();
                } catch (Exception $e) {
                    error_log("Erro ao processar ranking global: " . $e->getMessage());
                }

                return $this->redirect("/ranking?modalidade=$id_mod&categoria=$id_cat&status=sucesso_conquista");
            }
            
            return $this->redirect("/ranking?status=erro_conquista");
        }
        
        return $this->redirect("/ranking?status=erro_parametros");
    }
}