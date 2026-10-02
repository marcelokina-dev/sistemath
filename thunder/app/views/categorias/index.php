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
</style>

<div id="content" class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <i class="fas fa-weight-hanging fa-2x text-dark"></i>
            <h1 class="h2 fw-bold text-uppercase mb-0 tracking-tighter">Categorias</h1>
        </div>
        <a href="<?= BASE_URL ?>/categorias/create" class="btn btn-danger btn-lg shadow-sm fw-bold px-4">
            <i class="fas fa-plus me-2"></i> NOVA CATEGORIA
        </a>
    </div>

    <div class="card mb-4 shadow-sm border-0 rounded-3">
        <div class="card-body p-3">
            <form method="GET" action="<?= BASE_URL ?>/categorias" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.65rem;">Buscar por
                        nome</label>
                    <input type="text" name="nome" class="form-control form-control-sm border-gray-300 shadow-none"
                        placeholder="Ex: Peso Pena..." value="<?= $filtros['nome'] ?? '' ?>">
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold text-muted text-uppercase mb-1"
                        style="font-size: 0.65rem;">Modalidade</label>
                    <select name="modalidade" class="form-select form-select-sm border-gray-300 shadow-none">
                        <option value="">Todas</option>
                        <?php foreach ($modalidades as $m): ?>
                            <option value="<?= $m['id_modalidade'] ?>" <?= (isset($filtros['modalidade']) && $filtros['modalidade'] == $m['id_modalidade']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.65rem;">Sexo</label>
                    <select name="sexo" class="form-select form-select-sm border-gray-300 shadow-none">
                        <option value="">Todos</option>
                        <option value="M" <?= (isset($filtros['sexo']) && $filtros['sexo'] == 'M') ? 'selected' : '' ?>>
                            Masculino</option>
                        <option value="F" <?= (isset($filtros['sexo']) && $filtros['sexo'] == 'F') ? 'selected' : '' ?>>
                            Feminino</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark btn-sm w-100 fw-bold text-uppercase">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                    <a href="<?= BASE_URL ?>/categorias"
                        class="btn btn-outline-secondary btn-sm w-100 fw-bold text-uppercase">
                        Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-zebra table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 text-uppercase text-muted small fw-bold" style="font-size: 0.65rem;">ID</th>
                        <th class="text-uppercase text-muted small fw-bold" style="font-size: 0.65rem;">Categoria</th>
                        <th class="text-uppercase text-muted small fw-bold" style="font-size: 0.65rem;">Modalidade /
                            Sexo</th>
                        <th class="text-uppercase text-muted small fw-bold" style="font-size: 0.65rem;">Limites (KG)
                        </th>
                        <th class="text-end pe-4 text-uppercase text-muted small fw-bold" style="font-size: 0.65rem;">
                            Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($categorias)): ?>
                        <?php foreach ($categorias as $cat): ?>
                            <tr>
                                <td class="ps-4 small text-muted">
                                    #<?= str_pad($cat['id_categoria_peso'], 3, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <span class="fw-bold text-dark text-uppercase"
                                        style="font-size: 0.9rem;"><?= htmlspecialchars($cat['nome']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border fw-medium text-uppercase"
                                        style="font-size: 0.7rem;">
                                        <?= htmlspecialchars($cat['modalidade_nome'] ?? 'N/A') ?>
                                    </span>
                                    <span
                                        class="badge <?= $cat['sexo'] == 'M' ? 'bg-primary' : 'bg-danger' ?> bg-opacity-10 <?= $cat['sexo'] == 'M' ? 'text-primary' : 'text-danger' ?> border fw-medium text-uppercase ms-1"
                                        style="font-size: 0.7rem;">
                                        <?= $cat['sexo'] == 'M' ? 'Masc' : 'Fem' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="small">
                                        <span class="text-muted">Min:</span>
                                        <strong
                                            class="text-dark"><?= number_format($cat['peso_min'] ?? 0, 3, ',', '.') ?></strong>
                                        <span class="text-muted ms-2">Max:</span>
                                        <strong
                                            class="text-danger"><?= number_format($cat['peso_max'] ?? 0, 3, ',', '.') ?></strong>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-sm border rounded">
                                        <a href="<?= BASE_URL ?>/categorias/edit?id=<?= $cat['id_categoria_peso'] ?>"
                                            class="btn btn-white btn-sm py-1 px-2" title="Editar">
                                            <i class="fas fa-edit text-primary"></i>
                                        </a>
                                



                                        <a href="<?= BASE_URL ?>/categorias/delete?id=<?= $cat['id_categoria_peso'] ?>"
                                            class="btn btn-sm btn-light text-danger border-0 shadow-none"
                                            onclick="return confirm('Deseja realmente excluir esta categoria?')" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </a>


                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted small">Nenhuma categoria encontrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPaginas > 1): ?>
            <div class="card-footer bg-white border-top p-3">
                <div class="d-flex flex-column align-items-center">
                    <nav aria-label="Navegação de páginas">
                        <ul class="pagination pagination-sm mb-2">
                            <li class="page-item <?= ($paginaAtual <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link shadow-none"
                                    href="?pagina=<?= $paginaAtual - 1 ?>&nome=<?= urlencode($filtros['nome']) ?>&modalidade=<?= $filtros['modalidade'] ?>&sexo=<?= $filtros['sexo'] ?>">Anterior</a>
                            </li>

                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <li class="page-item <?= ($i == $paginaAtual) ? 'active' : '' ?>">
                                    <a class="page-link shadow-none"
                                        href="?pagina=<?= $i ?>&nome=<?= urlencode($filtros['nome']) ?>&modalidade=<?= $filtros['modalidade'] ?>&sexo=<?= $filtros['sexo'] ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= ($paginaAtual >= $totalPaginas) ? 'disabled' : '' ?>">
                                <a class="page-link shadow-none"
                                    href="?pagina=<?= $paginaAtual + 1 ?>&nome=<?= urlencode($filtros['nome']) ?>&modalidade=<?= $filtros['modalidade'] ?>&sexo=<?= $filtros['sexo'] ?>">Próxima</a>
                            </li>
                        </ul>
                    </nav>

                    
                </div>
            </div>
        <?php endif; ?>


    </div>
</div>

