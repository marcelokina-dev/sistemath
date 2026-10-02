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
            <i class="fas fa-fist-raised fa-2x text-dark"></i>
            <div>
                <h1 class="h2 fw-bold text-uppercase mb-0 tracking-tighter">Modalidades</h1>
                <p class="text-muted small mb-0 text-uppercase fw-bold" style="font-size: 0.65rem;">Gerenciamento de tipos de luta</p>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/modalidades/create" class="btn btn-danger btn-lg shadow-sm fw-bold px-4">
            <i class="fas fa-plus me-2"></i> NOVA MODALIDADE
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4 rounded-3">
        <div class="card-body p-4">
            <form method="GET" action="<?= BASE_URL ?>/modalidades" class="row g-3 align-items-end">
                <div class="col-md-9">
                    <label class="form-label text-muted fw-bold small text-uppercase mb-1" style="font-size: 0.65rem;">Busca por Nome</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($filtroNome ?? '') ?>"
                        class="form-control border-gray-300 shadow-none" placeholder="Ex: MMA, Muay Thai...">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark fw-bold flex-grow-1 text-uppercase py-2 shadow-sm" style="font-size: 0.75rem;">Filtrar</button>
                    <a href="<?= BASE_URL ?>/modalidades" class="btn btn-outline-secondary flex-grow-1 text-uppercase py-2" style="font-size: 0.75rem;">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-zebra table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-muted small text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">
                        <th class="px-4 py-3 border-0" style="width: 80px;">ID</th>
                        <th class="px-4 py-3 border-0">Nome da Modalidade</th>
                        <th class="px-4 py-3 border-0" style="width: 150px;">Status</th>
                        <th class="px-4 py-3 border-0 text-center" style="width: 120px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($modalidades)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted small text-uppercase">Nenhuma modalidade encontrada.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($modalidades as $m): ?>
                        <tr>
                            <td class="px-4 text-muted small">#<?= $m['id_modalidade'] ?></td>
                            <td class="px-4">
                                <span class="fw-bold text-dark text-uppercase"><?= htmlspecialchars($m['nome']) ?></span>
                            </td>
                            <td class="px-4">
                                <?php 
                                    $status = $m['status'] ?? 'inativo';
                                    if ($status === 'ativo'): 
                                ?>
                                    <span class="badge bg-success-soft text-uppercase fw-bold p-2" style="font-size: 0.65rem; color: #3a3a3a;">Ativo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-soft text-uppercase fw-bold p-2" style="font-size: 0.65rem; color: #e01b35;">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="<?= BASE_URL ?>/modalidades/edit?id=<?= $m['id_modalidade'] ?>" 
                                       class="btn btn-sm btn-light text-secondary border shadow-none" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="confirmarExclusao(<?= $m['id_modalidade'] ?>)" 
                                            class="btn btn-sm btn-light text-danger border shadow-none" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($totalPaginas) && $totalPaginas > 1): ?>
            <div class="card-footer bg-white border-top p-4">
                <div class="d-flex flex-column align-items-center justify-content-center">
                    <nav aria-label="Navegação">
                        <ul class="pagination pagination-sm mb-2 shadow-none">
                            <li class="page-item <?= ($paginaAtual <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link px-3" href="?pagina=<?= $paginaAtual - 1 ?>&nome=<?= urlencode($filtroNome ?? '') ?>">Anterior</a>
                            </li>

                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <li class="page-item <?= ($i == $paginaAtual) ? 'active' : '' ?>">
                                    <a class="page-link px-3" href="?pagina=<?= $i ?>&nome=<?= urlencode($filtroNome ?? '') ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= ($paginaAtual >= $totalPaginas) ? 'disabled' : '' ?>">
                                <a class="page-link px-3" href="?pagina=<?= $paginaAtual + 1 ?>&nome=<?= urlencode($filtroNome ?? '') ?>">Próxima</a>
                            </li>
                        </ul>
                    </nav>
                    <div class="text-muted small fw-medium">
                        Página <span class="text-dark fw-bold"><?= $paginaAtual ?></span> de <span class="text-dark fw-bold"><?= $totalPaginas ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmarExclusao(id) {
    if (confirm('Deseja realmente excluir esta modalidade? \n\nAtenção: A exclusão falhará se houver categorias de peso vinculadas a ela.')) {
        window.location.href = '<?= BASE_URL ?>/modalidades/delete?id=' + id;
    }
}
</script>