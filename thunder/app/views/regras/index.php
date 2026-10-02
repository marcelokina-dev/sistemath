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
        <h1 class="h3 fw-bold text-uppercase mb-0">Regras de Pontuação</h1>
        <a href="<?= BASE_URL ?>/regras/create" class="btn btn-danger fw-bold shadow-sm">
            <i class="fas fa-plus me-2"></i> NOVA REGRA
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="<?= BASE_URL ?>/regras" method="GET" class="row g-2">
                <div class="col-md-10">
                    <input type="text" name="busca" class="form-control" placeholder="Filtrar por modalidade..." value="<?= htmlspecialchars($busca ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-dark w-100">Buscar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="table-responsive">
            <table class="table table-zebra table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4">Modalidade</th>
                        <th class="px-4">Resultado</th>
                        <th class="px-4">Método</th>
                        <th class="px-4 text-center">Pontos</th>
                        <th class="px-4 text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($regras)): ?>
                        <tr><td colspan="5" class="text-center py-4">Nenhuma regra encontrada.</td></tr>
                    <?php else: ?>
                        <?php foreach ($regras as $r): ?>
                        <tr>
                            <td class="px-4 fw-bold"><?= htmlspecialchars($r['modalidade_nome']) ?></td>
                            <td class="px-4">
                                <span class="badge <?= $r['resultado'] == 'vitoria' ? 'bg-success' : 'bg-secondary' ?> text-uppercase">
                                    <?= htmlspecialchars($r['resultado']) ?>
                                </span>
                            </td>
                            <td class="px-4 small text-muted"><?= htmlspecialchars($r['metodo_nome']) ?> (<?= $r['sigla'] ?>)</td>
                            <td class="px-4 text-center"><span class="badge bg-dark px-3"><?= $r['pontos'] ?> pts</span></td>
                            <td class="px-4 text-center">
                                <a href="<?= BASE_URL ?>/regras/edit?id=<?= $r['id_regra'] ?>" class="btn btn-sm btn-outline-secondary border-0"><i class="fas fa-edit"></i></a>
                                <button onclick="confirmarExclusao(<?= $r['id_regra'] ?>)" class="btn btn-sm btn-outline-danger border-0"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($totalPaginas > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $paginaAtual <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?pagina=<?= $paginaAtual - 1 ?>&busca=<?= $busca ?>">Anterior</a>
            </li>
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <li class="page-item <?= $i == $paginaAtual ? 'active' : '' ?>">
                    <a class="page-link" href="?pagina=<?= $i ?>&busca=<?= $busca ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $paginaAtual >= $totalPaginas ? 'disabled' : '' ?>">
                <a class="page-link" href="?pagina=<?= $paginaAtual + 1 ?>&busca=<?= $busca ?>">Próxima</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<script>
function confirmarExclusao(id) {
    if (confirm('Tem certeza que deseja excluir esta regra?')) {
        window.location.href = '<?= BASE_URL ?>/regras/delete?id=' + id;
    }
}
</script>