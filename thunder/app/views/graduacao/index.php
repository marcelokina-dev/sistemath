
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
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">




            <form method="GET" action="<?= BASE_URL ?>/graduacao" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="small fw-bold text-muted text-uppercase mb-1">Filtrar por Modalidade</label>
                    <select name="id_modalidade" class="form-select shadow-none">
                        <option value="">Todas as Modalidades</option>
                        <?php foreach ($modalidades as $mod): ?>
                            <option value="<?= $mod['id_modalidade'] ?>" <?= ($filtro_modalidade == $mod['id_modalidade']) ? 'selected' : '' ?>>
                                <?= $mod['nome'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-dark fw-bold px-4">
                            <i class="fas fa-search me-2"></i> BUSCAR
                        </button>
                        <a href="<?= BASE_URL ?>/graduacao" class="btn btn-outline-secondary fw-bold px-4">
                            <i class="fas fa-eraser me-2"></i> LIMPAR
                        </a>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="<?= BASE_URL ?>/graduacao/create" class="btn btn-danger fw-bold shadow-sm">
                        <i class="fas fa-plus me-2"></i> NOVA GRADUAÇÃO
                    </a>
                </div>
            </form>






        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-zebra table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-muted small text-uppercase">
                        <th class="px-4 py-3">Modalidade</th>
                        <th class="px-4 py-3">Graduação</th>
                        <th class="px-4 py-3 text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($graduacoes as $g): ?>
                        <tr>
                            <td class="px-4 fw-bold text-danger"><?= $g['modalidade_nome'] ?></td>
                            <td class="px-4 fw-bold"><?= $g['graduacao_nome'] ?></td>
                            <td class="px-4 text-center">
                                <a href="<?= BASE_URL ?>/graduacao/edit?id=<?= $g['id_graduacao'] ?>"
                                    class="btn btn-sm btn-light border"><i class="fas fa-edit"></i></a>
                                <a href="<?= BASE_URL ?>/graduacao/delete?id=<?= $g['id_graduacao'] ?>"
                                    class="btn btn-sm btn-light text-danger border" onclick="return confirm('Excluir?')"><i
                                        class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPaginas > 1): ?>
            <div class="card-footer bg-white py-3">
                <nav>
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <li class="page-item <?= ($paginaAtiva == $i) ? 'active' : '' ?>">
                                <a class="page-link shadow-none"
                                    href="?pagina=<?= $i ?>&id_modalidade=<?= $filtro_modalidade ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>