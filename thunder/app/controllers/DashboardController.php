<?php
require_once __DIR__ . '/../models/DashboardModel.php';

class DashboardController extends BaseController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function index() {
        $model = new DashboardModel($this->db);

        // 1. Coleta de KPIs
        $stats = [
            'totalAtletas'      => $model->getTotalAtletas(),
            'totalEquipes'      => $model->getTotalEquipes(),
            'lutasRealizadas'   => $model->getTotalLutasFinalizadas(),
            'lutasAgendadas'    => $model->getLutasAgendadas(),
            // Alterado aqui para buscar apenas os realizados
            'eventosRealizados' => $model->getEventosRealizados() 
        ];

        // 2. Dados de Listas e Gráfico
        $recentes = $model->getAtletasRecentes(5);
        $regras = $model->getRegrasResumo();
        $crescimento = $model->getCrescimentoAtletasPorMes();

        // 3. Preparação do Gráfico
        $labels = [];
        $data = [];
        foreach ($crescimento as $item) {
            $labels[] = date('M/y', strtotime($item['mes_ano'] . "-01"));
            $data[] = $item['total_atletas'];
        }

        $this->render('dashboard/index', [
            'stats'         => $stats,
            'recentes'      => $recentes,
            'regras'        => $regras,
            'graficoLabels' => json_encode($labels),
            'graficoData'   => json_encode($data)
        ]);
    }
}