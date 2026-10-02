
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
            <i class="fas fa-users fa-2x text-dark"></i>
            <div>
                <h1 class="h2 fw-bold text-uppercase mb-0 tracking-tighter">Equipes</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item small"><a href="<?= BASE_URL ?>/dashboard" class="text-decoration-none text-muted">Painel</a></li>
                        <li class="breadcrumb-item active small text-danger fw-bold" aria-current="page">Equipes</li>
                    </ol>
                </nav>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/equipes/create" class="btn btn-danger btn-lg shadow-sm fw-bold px-4">
            <i class="fas fa-plus me-2"></i> NOVA EQUIPE
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4 rounded-3">
        <div class="card-body p-4">
            <form method="GET" action="<?= BASE_URL ?>/equipes" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="filtro_nome" class="form-label text-muted fw-bold small text-uppercase mb-1" style="font-size: 0.65rem;">Busca Rápida</label>
                    <input type="text" id="filtro_nome" name="nome" value="<?= htmlspecialchars($filtros['nome'] ?? '') ?>" class="form-control form-control-sm border-gray-300 shadow-none" placeholder="Nome da Equipe">
                </div>

                <div class="col-md-3">
                    <label for="filtro_cidade" class="form-label text-muted fw-bold small text-uppercase mb-1" style="font-size: 0.65rem;">Cidade</label>
                    <input type="text" id="filtro_cidade" name="cidade" value="<?= htmlspecialchars($filtros['cidade'] ?? '') ?>" class="form-control form-control-sm border-gray-300 shadow-none" placeholder="Ex: São Paulo">
                </div>

                <div class="col-md-2">
                    <label for="filtro_estado" class="form-label text-muted fw-bold small text-uppercase mb-1" style="font-size: 0.65rem;">Estado (UF)</label>
                    <input type="text" id="filtro_estado" name="estado" value="<?= htmlspecialchars($filtros['estado'] ?? '') ?>" class="form-control form-control-sm border-gray-300 shadow-none" placeholder="Ex: SP" maxlength="2">
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark fw-bold flex-grow-1 text-uppercase py-2 shadow-sm" style="font-size: 0.75rem;">Filtrar</button>
                    <a href="<?= BASE_URL ?>/equipes" class="btn btn-outline-secondary flex-grow-1 text-uppercase py-2" style="font-size: 0.75rem;">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-zebra table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-muted small text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">
                        <th class="px-4 py-3 border-0">Equipe / Logo</th>
                        <th class="px-4 py-3 border-0">Localização</th>
                        <th class="px-4 py-3 border-0">Contato</th>
                        <th class="px-4 py-3 border-0 text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if (!empty($equipes)): ?>
                        <?php foreach ($equipes as $eqp): ?>
                        <tr>
                            <td class="px-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle overflow-hidden bg-light border d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                        <?php if (!empty($eqp['foto']) && $eqp['foto'] !== 'default_team.png'): ?>
                                            <img src="<?= BASE_URL ?>/uploads/equipes/<?= $eqp['foto'] ?>" alt="<?= $eqp['nome'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <i class="fas fa-shield-alt text-muted"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark mb-0"><?= $eqp['nome'] ?></div>
                                        <div class="text-danger small fw-bold text-uppercase" style="font-size: 0.65rem;">Afiliada Thunder Fight</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4">
                                <div class="small text-muted">
                                    <i class="fas fa-map-marker-alt me-1 text-danger"></i> 
                                    <?= htmlspecialchars($eqp['cidade'] ?? 'N/D') ?>/<?= htmlspecialchars($eqp['estado'] ?? '--') ?>
                                </div>
                            </td>
                            <td class="px-4">
                                <div class="small fw-bold text-dark"><?= htmlspecialchars($eqp['celular'] ?? $eqp['email'] ?? 'Sem contato') ?></div>
                                <div class="text-muted small" style="font-size: 0.75rem;"><?= !empty($eqp['celular']) ? 'WhatsApp' : 'E-mail' ?></div>
                            </td>
                            <td class="px-4 text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="<?= BASE_URL ?>/equipes/edit?id=<?= $eqp['id_equipe'] ?>" 
                                       class="btn btn-sm btn-light text-secondary border-0 shadow-none" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                           


                                        <a href="<?= BASE_URL ?>/equipes/delete?id=<?= $eqp['id_equipe'] ?>"
                                            class="btn btn-sm btn-light text-danger border-0 shadow-none"
                                            onclick="return confirm('Deseja realmente excluir está equipe?')" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </a>



                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted small italic">Nenhuma equipe encontrada com os filtros aplicados.</td>
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

            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <li class="page-item <?= ($paginaAtual == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?= ($paginaAtual >= $totalPaginas) ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $paginaAtual + 1])) ?>">Próximo</a>
            </li>
        </ul>
    </nav>
</div>
<?php endif; ?>
</div>

