<style>
    :root { 
        --thunder-red: #dc3545; 
        --dark-card: #1e1e1e; 
    }
    .kpi-card { border: none; border-radius: 12px; transition: 0.3s; background: #fff; }
    .kpi-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    .icon-box { width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 10px; }
    .text-thunder { color: var(--thunder-red); }
    .bg-gradient-thunder { background: linear-gradient(45deg, #dc3545, #921d27); color: white; }
    
    /* Cores personalizadas para o Bloco de Eventos */
    .text-indigo { color: #6610f2; }
    .bg-indigo-opacity { background-color: rgba(102, 16, 242, 0.1); }
</style>

<div class="container-fluid py-4">
    <div class="mb-4">
        <h2 class="fw-bold text-uppercase">Visão Geral <span class="text-danger">Thunder</span></h2>
        <p class="text-muted">Painel de controle de atletas e competições</p>
    </div>

    <div class="row g-3 mb-4 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5">
        
        <div class="col">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-indigo-opacity text-indigo me-3">
                        <i class="fas fa-calendar-check fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0 small text-uppercase fw-bold" style="font-size: 0.7rem;">Eventos Realizados</h6>
                        <h3 class="mb-0 fw-bold"><?= $stats['eventosRealizados'] ?? 0 ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-danger bg-opacity-10 text-danger me-3">
                        <i class="fas fa-user-ninja fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0 small text-uppercase fw-bold" style="font-size: 0.7rem;">Atletas</h6>
                        <h3 class="mb-0 fw-bold"><?= $stats['totalAtletas'] ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-primary bg-opacity-10 text-primary me-3">
                        <i class="fas fa-users fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0 small text-uppercase fw-bold" style="font-size: 0.7rem;">Equipes</h6>
                        <h3 class="mb-0 fw-bold"><?= $stats['totalEquipes'] ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-success bg-opacity-10 text-success me-3">
                        <i class="fas fa-check-double fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0 small text-uppercase fw-bold" style="font-size: 0.7rem;">Lutas Realizadas</h6>
                        <h3 class="mb-0 fw-bold"><?= $stats['lutasRealizadas'] ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-warning bg-opacity-10 text-warning me-3">
                        <i class="fas fa-clock fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0 small text-uppercase fw-bold" style="font-size: 0.7rem;">Lutas Agendadas</h6>
                        <h3 class="mb-0 fw-bold"><?= $stats['lutasAgendadas'] ?></h3>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-uppercase small">Crescimento da Organização</h6>
                </div>
                <div class="card-body">
                    <canvas id="mainChart" style="height: 300px;"></canvas>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-uppercase small">Últimos Cadastros</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="small text-muted text-uppercase">
                                    <th class="ps-4">Atleta</th>
                                    <th>Modalidade</th>
                                    <th class="text-end pe-4">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recentes as $atleta): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold"><?= $atleta['nome'] ?></div>
                                        <small class="text-danger">"<?= $atleta['apelido'] ?>"</small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= $atleta['modalidade_nome'] ?></span></td>
                                    <td class="text-end pe-4">
                                        <a href="/atletas/edit?id=<?= $atleta['id_atleta'] ?>" class="btn btn-sm btn-light"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card bg-gradient-thunder border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 text-center">
                    <h5 class="fw-bold text-uppercase">Ranking Global</h5>
                    <p class="small opacity-75">Baseado nas vitórias lançadas</p>
                    <a href="/thunder/public/ranking" class="btn btn-light btn-sm fw-bold px-4 rounded-pill">VER RANKING COMPLETO</a>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-uppercase small">Tabela de Pontos</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <ul class="list-group list-group-flush">
                            <?php foreach($regras as $regra): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <span class="small fw-bold text-uppercase"><?= $regra['metodo_nome'] ?></span>
                                <span class="badge bg-dark rounded-pill"><?= $regra['pontos'] ?> PTS</span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('mainChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= $graficoLabels ?>,
            datasets: [{
                label: 'Atletas',
                data: <?= $graficoData ?>,
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
</script>