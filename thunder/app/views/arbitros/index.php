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
            <i class="fas fa-gavel fa-2x text-dark"></i>
            <div>
                <h1 class="h2 fw-bold text-uppercase mb-0 tracking-tighter">Árbitros</h1>
                <p class="text-muted small mb-0">Gestão de oficiais da Thunder Fight</p>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/arbitros/create" class="btn btn-danger btn-lg shadow-sm fw-bold px-4">
            <i class="fas fa-plus me-2"></i> NOVO ÁRBITRO
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4 rounded-3">
        <div class="card-body p-4">
            <form method="GET" action="<?= BASE_URL ?>/arbitros" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-muted fw-bold small text-uppercase mb-1"
                        style="font-size: 0.65rem;">Busca Rápida</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($_GET['nome'] ?? '') ?>"
                        class="form-control form-control-sm border-gray-300 shadow-none" placeholder="Nome ou Apelido">
                </div>

                <div class="col-md-2">
                    <label class="form-label text-muted fw-bold small text-uppercase mb-1"
                        style="font-size: 0.65rem;">Modalidade</label>
                    <select name="modalidade" class="form-select form-select-sm border-gray-300 shadow-none">
                        <option value="">Todas</option>
                        <?php foreach ($modalidades as $mod): ?>
                            <option value="<?= $mod['id_modalidade'] ?>" <?= ($_GET['modalidade'] ?? '') == $mod['id_modalidade'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($mod['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label text-muted fw-bold small text-uppercase mb-1"
                        style="font-size: 0.65rem;">Sexo</label>
                    <select name="sexo" class="form-select form-select-sm border-gray-300 shadow-none">
                        <option value="">Ambos</option>
                        <option value="M" <?= ($_GET['sexo'] ?? '') == 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= ($_GET['sexo'] ?? '') == 'F' ? 'selected' : '' ?>>Feminino</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label text-muted fw-bold small text-uppercase mb-1"
                        style="font-size: 0.65rem;">Status</label>
                    <select name="status" class="form-select form-select-sm border-gray-300 shadow-none">
                        <option value="">Todos</option>
                        <option value="Ativo" <?= ($_GET['status'] ?? '') == 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                        <option value="Inativo" <?= ($_GET['status'] ?? '') == 'Inativo' ? 'selected' : '' ?>>Inativo
                        </option>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark fw-bold flex-grow-1 text-uppercase py-2 shadow-sm"
                        style="font-size: 0.75rem;">Filtrar</button>
                    <a href="<?= BASE_URL ?>/arbitros" class="btn btn-outline-secondary flex-grow-1 text-uppercase py-2"
                        style="font-size: 0.75rem;">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-zebra table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-muted small text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">
                        <th class="px-4 py-3 border-0">Árbitro / Apelido</th>
                        <th class="px-4 py-3 border-0">Sexo</th>
                        <th class="px-4 py-3 border-0">Localização</th>
                        <th class="px-4 py-3 border-0">Modalidades</th>
                        <th class="px-4 py-3 border-0">Status</th>
                        <th class="px-4 py-3 border-0 text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if (!empty($arbitros)): ?>
                        <?php foreach ($arbitros as $arb):
                            $statusLabel = strtoupper($arb['status'] ?? 'ATIVO');
                            $isAtivo = ($statusLabel === 'ATIVO');
                            $statusClass = $isAtivo ? 'bg-success-subtle text-success border-success-subtle' : 'bg-secondary-subtle text-secondary border-secondary-subtle';
                            ?>
                            <tr>
                                <td class="px-4">
                                    <div class="fw-bold text-dark mb-0"><?= htmlspecialchars($arb['nome']) ?></div>
                                    <?php if (!empty($arb['apelido'])): ?>
                                        <div class="text-muted small" style="font-size: 0.75rem;">
                                            "<?= htmlspecialchars($arb['apelido']) ?>"</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 text-muted small"><?= ($arb['sexo'] ?? 'M') == 'M' ? 'Masculino' : 'Feminino' ?>
                                </td>
                                <td class="px-4">
                                    <div class="small text-muted">
                                        <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                        <?= htmlspecialchars($arb['cidade'] ?? 'N/A') ?>/<?= htmlspecialchars($arb['estado'] ?? '--') ?>
                                    </div>
                                </td>
                                <td class="px-4">
                                    <?php
                                    if (!empty($arb['modalidades'])):
                                        $lista = is_array($arb['modalidades']) ? $arb['modalidades'] : explode(',', $arb['modalidades']);
                                        foreach ($lista as $m): ?>
                                            <span class="badge bg-light text-secondary border fw-normal text-uppercase me-1"
                                                style="font-size: 0.65rem;">
                                                <?= htmlspecialchars(trim($m)) ?>
                                            </span>
                                        <?php endforeach;
                                    endif; ?>
                                </td>
                                <td class="px-4">
                                    <span
                                        class="badge rounded-pill <?= $statusClass ?> border small px-3"><?= $statusLabel ?></span>
                                </td>
                                <td class="px-4 text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="<?= BASE_URL ?>/arbitros/edit?id=<?= $arb['id_arbitro'] ?>"
                                            class="btn btn-sm btn-light text-secondary border-0" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                         


                                        <a href="<?= BASE_URL ?>/arbitros/delete?id=<?= $arb['id_arbitro'] ?>"
                                            class="btn btn-sm btn-light text-danger border-0 shadow-none"
                                            onclick="return confirm('Deseja realmente excluir este arbitro?')" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </a>




                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-search me-2"></i> Nenhum árbitro encontrado com os filtros aplicados.
                            </td>
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

