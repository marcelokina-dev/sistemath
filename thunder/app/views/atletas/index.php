<style>
    /* Trava global de segurança contra scroll horizontal no container principal */
    #content.container-fluid {
        overflow-x: hidden !important;
        max-width: 100% !important;
    }

    /* Destaca os campos de filtro que estão "apagados" */
    .card .form-control,
    .card .form-select {
        border: 1px solid #ced4da !important;
        background-color: #ffffff !important;
        color: #212529 !important;
        font-weight: 500;
        height: 38px;
    }

    .card .form-control::placeholder {
        color: #adb5bd;
    }

    /* Estilo padrão Thunder para o cabeçalho da tabela */
    .table thead th {
        background-color: #e01b35 !important;
        color: #ffffff !important;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border: none !important;
    }

    /* Efeito Zebra para facilitar a leitura */
    .table-hover tbody tr:nth-of-type(even) {
        background-color: #f8f9fa;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(224, 27, 53, 0.05) !important;
    }

    /* Zebra: Aplica fundo nas células de linhas pares */
    .table-zebra tbody tr:nth-of-type(even) td {
        background-color: #cdcdcd !important;
    }

    /* Zebra: Aplica fundo nas células de linhas ímpares (garante que fiquem brancas) */
    .table-zebra tbody tr:nth-of-type(odd) td {
        background-color: #ffffff !important;
    }

    /* Evita que textos longos estourem o layout */
    .table td {
        word-wrap: break-word;
        white-space: normal;
    }

    /* Ajuste para garantir que a paginação quebre linha de forma elegante em telas menores */
    .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }
</style>

<div id="content" class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <i class="fas fa-user-ninja fa-2x text-dark"></i>
            <h1 class="h2 fw-bold text-uppercase mb-0 tracking-tighter">Atletas</h1>
        </div>
        <a href="<?= BASE_URL ?>/atletas/create" class="btn btn-danger btn-lg shadow-sm fw-bold px-4">
            <i class="fas fa-plus me-2"></i> NOVO ATLETA
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4 rounded-3">
        <div class="card-body p-4">
            <div class="container-fluid p-0">
                <form method="GET" action="<?= BASE_URL ?>/atletas" class="row g-3 align-items-end">

                    <div class="col-md-2">
                        <label for="filtro_busca" class="form-label text-muted fw-bold small text-uppercase mb-1"
                            style="font-size: 0.65rem;">Busca Rápida</label>
                        <input type="text" id="filtro_busca" name="busca"
                            value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>"
                            class="form-control form-control-sm border-gray-300 shadow-none" placeholder="Nome ou Apelido">
                    </div>

                    <div class="col-md-2">
                        <label for="filtro_modalidade" class="form-label text-muted fw-bold small text-uppercase mb-1"
                            style="font-size: 0.65rem;">Modalidade</label>
                        <select id="filtro_modalidade" name="modalidade"
                            class="form-select form-select-sm border-gray-300 shadow-none">
                            <option value="">Todas</option>
                            <?php foreach ($modalidades as $m): ?>
                                <option value="<?= $m['id_modalidade'] ?>" <?= ($filtros['modalidade'] ?? '') == $m['id_modalidade'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="filtro_equipe" class="form-label text-muted fw-bold small text-uppercase mb-1"
                            style="font-size: 0.65rem;">Equipe</label>
                        <select id="filtro_equipe" name="equipe"
                            class="form-select form-select-sm border-gray-300 shadow-none">
                            <option value="">Todas</option>
                            <?php foreach ($equipes as $e): ?>
                                <option value="<?= $e['id_equipe'] ?>" <?= ($filtros['equipe'] ?? '') == $e['id_equipe'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($e['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="filtro_sexo" class="form-label text-muted fw-bold small text-uppercase mb-1"
                            style="font-size: 0.65rem;">Sexo</label>
                        <select id="filtro_sexo" name="sexo" class="form-select form-select-sm border-gray-300 shadow-none">
                            <option value="">Ambos</option>
                            <option value="M" <?= ($filtros['sexo'] ?? '') == 'M' ? 'selected' : '' ?>>Masc</option>
                            <option value="F" <?= ($filtros['sexo'] ?? '') == 'F' ? 'selected' : '' ?>>Fem</option>
                        </select>
                    </div>

                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-dark fw-bold flex-grow-1 text-uppercase py-2 shadow-sm"
                            style="font-size: 0.75rem;">Filtrar</button>
                        <a href="<?= BASE_URL ?>/atletas" class="btn btn-outline-secondary flex-grow-1 text-uppercase py-2"
                            style="font-size: 0.75rem;">Limpar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-zebra table-hover align-middle mb-0">
                <thead>
                    <tr class="text-white small text-uppercase fw-bold">
                        <th class="px-4 py-3">Foto</th>
                        <th class="px-4 py-3">Atleta</th>
                        <th class="px-4 py-3">Sexo</th>
                        <th class="px-4 py-3">Modalidade</th>
                        <th class="px-4 py-3">Equipe</th>
                        <th class="px-4 py-3">Categoria</th>
                        <th class="px-4 py-3 text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if (!empty($atletas)): ?>
                        <?php foreach ($atletas as $atleta): ?>
                            <tr>
                                <td class="px-4">
                                    <img src="<?= !empty($atleta['foto']) ? BASE_URL . '/uploads/atletas/' . $atleta['foto'] : BASE_URL . '/img/sem-foto.png' ?>"
                                        class="rounded-circle shadow-sm border border-2 border-white" width="45" height="45"
                                        style="object-fit: cover;">
                                </td>
                                <td class="px-4">
                                    <div class="fw-bold text-dark mb-0">
                                        <?= htmlspecialchars(($atleta['nome'] ?? '') . ' ' . ($atleta['sobrenome'] ?? '')) ?>
                                    </div>
                                    <div class="text-danger small fw-bold text-uppercase" style="font-size: 0.65rem;">
                                        <?= htmlspecialchars($atleta['apelido'] ?? '') ?>
                                    </div>
                                </td>
                                <td class="px-4 text-muted small">
                                    <?= ($atleta['sexo'] ?? '') == 'M' ? 'Masculino' : 'Feminino' ?>
                                </td>
                                <td class="px-4">
                                    <span class="badge bg-light text-secondary border fw-normal text-uppercase"
                                        style="font-size: 0.65rem;">
                                        <?= htmlspecialchars($atleta['modalidade_nome'] ?? '-') ?>
                                    </span>
                                </td>
                                <td class="px-4 text-muted small">
                                    <?= htmlspecialchars($atleta['equipe_nome'] ?? 'Independente') ?>
                                </td>
                                <td class="px-4 fw-bold text-dark small">
                                    <?= htmlspecialchars($atleta['categoria_nome'] ?? '-') ?>
                                </td>
                                <td class="px-4">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="<?= BASE_URL ?>/atletas/show?id=<?= $atleta['id_atleta'] ?>"
                                            class="btn btn-sm btn-light text-primary border-0 shadow-none" title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/atletas/edit?id=<?= $atleta['id_atleta'] ?>"
                                            class="btn btn-sm btn-light text-secondary border-0 shadow-none" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/atletas/delete?id=<?= $atleta['id_atleta'] ?>"
                                            class="btn btn-sm btn-light text-danger border-0 shadow-none"
                                            onclick="return confirm('Deseja realmente excluir este atleta?')" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted small italic">Nenhum atleta encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (isset($totalPaginas) && $totalPaginas > 1): ?>
        <div class="d-flex justify-content-center mt-4">
            <nav>
                <ul class="pagination pagination-sm">
                    <li class="page-item <?= ($paginaAtual <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $paginaAtual - 1])) ?>">Anterior</a>
                    </li>

                    <?php
                    // Configuração da janela visível de paginação
                    $maxBotoesVisiveis = 2; // Quantos números mostrar antes e depois da página atual
                    
                    // Primeira página sempre aparece
                    if ($paginaAtual > ($maxBotoesVisiveis + 1)) {
                        echo '<li class="page-item"><a class="page-link shadow-none" href="?' . http_build_query(array_merge($_GET, ['pagina' => 1])) . '">1</a></li>';
                        if ($paginaAtual > ($maxBotoesVisiveis + 2)) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }

                    // Páginas dinâmicas em torno da página atual
                    $inicio = max(1, $paginaAtual - $maxBotoesVisiveis);
                    $fim = min($totalPaginas, $paginaAtual + $maxBotoesVisiveis);

                    for ($i = $inicio; $i <= $fim; $i++): ?>
                        <li class="page-item <?= ($paginaAtual == $i) ? 'active' : '' ?>">
                            <a class="page-link shadow-none" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php
                    // Última página sempre aparece se estiver longe
                    if ($paginaAtual < ($totalPaginas - $maxBotoesVisiveis)) {
                        if ($paginaAtual < ($totalPaginas - $maxBotoesVisiveis - 1)) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        echo '<li class="page-item"><a class="page-link shadow-none" href="?' . http_build_query(array_merge($_GET, ['pagina' => $totalPaginas])) . '">' . $totalPaginas . '</a></li>';
                    }
                    ?>

                    <li class="page-item <?= ($paginaAtual >= $totalPaginas) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $paginaAtual + 1])) ?>">Próximo</a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>