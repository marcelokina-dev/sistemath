<?php

require_once __DIR__ . '/BaseController.php';

class RankingController extends BaseController
{
    private $rankingModel;
    private $modalidadeModel;
    private $categoriaModel;
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
        $this->rankingModel = new RankingModel($this->db);
        $this->modalidadeModel = new ModalidadeModel($this->db);
        $this->categoriaModel = new CategoriaPesoModel($this->db);
    }

    /**
     * TELA PRINCIPAL DO RANKING
     * Com filtros por modalidade, sexo e categoria
     */
    public function index()
    {
        // 1. Captura de Parâmetros da URL
        $id_modalidade = isset($_GET['modalidade']) ? (int) $_GET['modalidade'] : null;
        $sexo          = isset($_GET['sexo']) ? $_GET['sexo'] : 'M';
        $id_categoria  = isset($_GET['categoria']) ? (int) $_GET['categoria'] : null;
        $pagina        = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        // 2. Inicialização de variáveis para a View
        $modalidades  = $this->modalidadeModel->listarTodas();
        $categorias   = [];
        $ranking      = [];
        $totalPaginas = 1;

        // 3. Lógica de busca baseada nos filtros
        if ($id_modalidade) {
            // Carrega as categorias para preencher o select dependente via View
            $categorias = $this->categoriaModel->buscarPorModalidadeESexo($id_modalidade, $sexo);

            if ($id_categoria) {
                // Busca os dados do ranking (normalmente limitado a 15 registros por página no Model)
                $ranking = $this->rankingModel->obterRanking($id_modalidade, $id_categoria, $sexo, $pagina);

                // Calcula o total de páginas para o componente de paginação
                $totalRegistros = $this->rankingModel->contarTotalRanking($id_modalidade, $id_categoria, $sexo);
                $totalPaginas   = ceil($totalRegistros / 15);
            }
        }

        // 4. Renderização da View
        $this->render('ranking/index', [
            'titulo'       => 'Ranking Oficial',
            'modalidades'  => $modalidades,
            'categorias'   => $categorias,
            'ranking'      => $ranking,
            'totalPaginas' => $totalPaginas,
            'paginaAtual'  => $pagina,
            'filtros'      => [
                'modalidade' => $id_modalidade,
                'sexo'       => $sexo,
                'categoria'  => $id_categoria
            ]
        ]);
    }

    /**
     * RECALCULAR RANKING GLOBAL
     * Processa pontos de todas as lutas e sincroniza status de campeões
     */
    public function atualizar()
    {
        // Limpa buffers de saída para evitar problemas de headers já enviados
        if (ob_get_level()) ob_end_clean();

        $sucesso = $this->rankingModel->processarRankingGlobal();

        if ($sucesso) {
            return $this->redirect("/ranking?status=sucesso&t=" . time());
        } else {
            return $this->redirect("/ranking?status=erro_no_model");
        }
    }

    /**
     * AJAX: BUSCAR CATEGORIAS
     * Retorna JSON para popular o select de categorias conforme a modalidade/sexo
     */
    public function buscarCategoriasPorModalidade()
    {
        if (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');

        $id_modalidade = $_GET['modalidade'] ?? null;
        $sexo          = $_GET['sexo'] ?? 'M';

        try {
            $categorias = $this->categoriaModel->buscarPorModalidadeESexo($id_modalidade, $sexo);
            echo json_encode($categorias);
        } catch (Exception $e) {
            echo json_encode([]);
        }
        exit;
    }

    /**
     * REMOVER ATLETA DO RANKING
     * Oculta o atleta de uma categoria específica e recalcula
     */
    public function remover_atleta()
    {
        $id_atleta = $_GET['id'] ?? null;
        $id_cat    = $_GET['cat'] ?? null;
        $id_mod    = $_GET['mod'] ?? null;
        $sexo      = $_GET['sexo'] ?? 'M';

        if ($id_atleta && $id_cat && $id_mod) {
            // 1. Marca como invisível ou remove do ranking (conforme lógica do Model)
            $this->rankingModel->removerAtleta($id_atleta, $id_cat, $id_mod);

            // 2. Sincroniza o ranking para aplicar a mudança imediatamente
            $this->rankingModel->processarRankingGlobal();

            return $this->redirect("/ranking?modalidade=$id_mod&categoria=$id_cat&sexo=$sexo&status=removido");
        } 
        
        return $this->redirect("/ranking?status=erro_parametros");
    }
}