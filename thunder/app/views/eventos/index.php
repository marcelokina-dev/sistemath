<style>
    /* Destaca os campos de filtro que estão "apagados" */
    .card .form-control,
    .card .form-select {
        border: 1px solid #ced4da !important;
        /* Borda visível */
        background-color: #ffffff !important;
        color: #212529 !important;
        font-weight: 500;
        height: 38px;
    }

    .card .form-control::placeholder {
        color: #adb5bd;
    }

    /* Melhora o visual do cabeçalho da tabela */
    .table thead th {
        background-color: #e01b35;
        color: #ffffff;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #dee2e6;
    }

    /* Badge de Status (Ativo/Inativo) */
    .badge-status {
        padding: 5px 12px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.7rem;
        text-transform: uppercase;
    }

    /* Zebra: Aplica fundo nas células de linhas pares */
    .table-zebra tbody tr:nth-of-type(even) td {
        background-color: #cdcdcd !important;
    }

    /* Zebra: Aplica fundo nas células de linhas ímpares (garante que fiquem brancas) */
    .table-zebra tbody tr:nth-of-type(odd) td {
        background-color: #ffffff !important;
    }

    /* Ajuste para o cabeçalho não ser afetado se estiver dentro do THEAD */
    .table-zebra thead th {
        background-color: #e01b35 !important;
        /* Seu vermelho Thunder */
        color: #ffffff !important;
    }

    .img-event-thumb {
        width: 60px;
        height: 80px;
        object-fit: cover;
        border: 1px solid #dee2e6;
        transition: transform 0.2s;
    }

    .img-event-thumb:hover {
        transform: scale(2.0);
        z-index: 999;
        position: relative;
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
    }

    .btn-white {
        background: #fff;
        color: #333;
    }

    .btn-white:hover {
        background: #f8f9fa;
        color: #000;
    }

    .badge {
        font-weight: 600;
        font-size: 0.7rem;
        border-radius: 4px;
    }
</style>
<div id="content" class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <i class="fas fa-calendar-alt fa-2x text-dark"></i>
            <div>
                <h1 class="h2 fw-bold text-uppercase mb-0 tracking-tighter">Eventos</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item small"><a href="<?= BASE_URL ?>/dashboard"
                                class="text-decoration-none text-muted">Painel</a></li>
                        <li class="breadcrumb-item active small text-danger fw-bold" aria-current="page">Eventos</li>
                    </ol>
                </nav>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/eventos/create" class="btn btn-danger btn-lg shadow-sm fw-bold px-4">
            <i class="fas fa-plus me-2"></i> NOVA EVENTO
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/eventos" class="row g-3">
                <div class="col-md-4">
                    <label class="small fw-bold text-uppercase">Nome do Evento</label>
                    <input type="text" name="nome" class="form-control" placeholder="Ex: Thunder Fight 60"
                        value="<?= htmlspecialchars($filtros['nome'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold text-uppercase">Ano</label>
                    <select name="ano" class="form-select">
                        <option value="Todos">Todos</option>
                        <?php
                        $anoAtual = (int) date('Y');
                        for ($i = $anoAtual + 1; $i >= 2014; $i--):
                            ?>
                            <option value="<?= $i ?>" <?= ($filtros['ano'] ?? '') == $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold text-uppercase">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="Planejamento" <?= ($filtros['status'] ?? '') == 'Planejamento' ? 'selected' : '' ?>>
                            Planejamento</option>
                        <option value="Confirmado" <?= ($filtros['status'] ?? '') == 'Confirmado' ? 'selected' : '' ?>>
                            Confirmado</option>
                        <option value="Realizado" <?= ($filtros['status'] ?? '') == 'Realizado' ? 'selected' : '' ?>>
                            Realizado</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end justify-content-end gap-2">
                    <button type="submit" class="btn btn-dark fw-bold text-uppercase px-4 shadow-sm"
                        style="letter-spacing: 1px;">
                        FILTRAR
                    </button>
                    <a href="<?= BASE_URL ?>/eventos" class="btn btn-outline-secondary fw-bold text-uppercase px-4"
                        style="letter-spacing: 1px;">
                        LIMPAR
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-zebra table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" style="width: 80px;">CARTAZ</th>
                        <th style="width: 120px;">DATA</th>
                        <th>EVENTO</th>
                        <th>LOCALIZAÇÃO</th>
                        <th class="text-center">STATUS</th>
                        <th class="text-end pe-4">AÇÕES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($eventos)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-calendar-times fa-3x mb-3 text-light"></i>
                                    <p class="fw-bold text-uppercase mb-1">Nenhum evento encontrado</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($eventos as $e):
                            $dataEvento = strtotime($e['data_evento']);
                            $fotoCaminho = !empty($e['foto']) ? BASE_URL . "/uploads/eventos/" . $e['foto'] : BASE_URL . "/assets/img/no-poster.jpg";

                            $statusConfig = [
                                'Planejamento' => 'bg-warning text-dark',
                                'Confirmado' => 'bg-primary',
                                'Realizado' => 'bg-success'
                            ];
                            $badgeClass = $statusConfig[$e['status']] ?? 'bg-secondary';
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <img src="<?= $fotoCaminho ?>" class="img-event-thumb shadow-sm rounded" alt="Poster">
                                </td>
                                <td class="fw-bold text-secondary">
                                    <?= date('d/m/Y', $dataEvento) ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($e['nome']) ?></div>
                                    <small class="text-muted text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                                        <?= htmlspecialchars($e['modalidade_nome'] ?? 'Não definida') ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="small fw-bold text-dark">
                                        <?= htmlspecialchars($e['local_nome'] ?? 'Thunder Fight Center') ?>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                        <?= htmlspecialchars(($e['cidade'] ?? 'São Paulo') . ' / ' . ($e['estado'] ?? 'SP')) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $badgeClass ?> text-uppercase px-3">
                                        <?= $e['status'] ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group border rounded shadow-sm bg-white">
                                        <a href="<?= BASE_URL ?>/eventos/lutas?id=<?= $e['id_evento'] ?>"
                                            class="btn btn-white btn-sm fw-bold px-3 text-danger">
                                            <i class="fas fa-fist-raised me-1"></i> LUTAS
                                        </a>
                                        <a href="<?= BASE_URL ?>/eventos/edit?id=<?= $e['id_evento'] ?>"
                                            class="btn btn-white btn-sm border-start" title="Editar">
                                            <i class="fas fa-edit text-primary"></i>
                                        </a>
                                


                                        <a href="<?= BASE_URL ?>/eventos/delete?id=<?= $e['id_evento'] ?>"
                                            class="btn btn-sm btn-light text-danger border-0 shadow-none"
                                            onclick="return confirm('Deseja realmente excluir este evento?')" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </a>



                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>


            <!-- ADICIONE ISSO LOGO ABAIXO DO FECHAMENTO DA DIV table-responsive -->
            <?php if ($totalPaginas > 1): ?>
                <div class="card-footer bg-white border-top-0 py-3">
                    <nav aria-label="Navegação de páginas">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?= $paginaAtual <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="?pagina=<?= $paginaAtual - 1 ?>&nome=<?= $filtros['nome'] ?>&ano=<?= $filtros['ano'] ?>&status=<?= $filtros['status'] ?>">Anterior</a>
                            </li>

                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <li class="page-item <?= $paginaAtual == $i ? 'active' : '' ?>">
                                    <a class="page-link <?= $paginaAtual == $i ? 'bg-danger border-danger' : '' ?>"
                                        href="?pagina=<?= $i ?>&nome=<?= $filtros['nome'] ?>&ano=<?= $filtros['ano'] ?>&status=<?= $filtros['status'] ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= $paginaAtual >= $totalPaginas ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="?pagina=<?= $paginaAtual + 1 ?>&nome=<?= $filtros['nome'] ?>&ano=<?= $filtros['ano'] ?>&status=<?= $filtros['status'] ?>">Próximo</a>
                            </li>
                        </ul>
                    </nav>
                    <div class="text-center mt-2 small text-muted">
                        Exibindo
                        <?= count($eventos) ?> de
                        <?= $totalRegistros ?> eventos totais.
                    </div>
                </div>
            <?php endif; ?>



        </div>
    </div>
</div>



