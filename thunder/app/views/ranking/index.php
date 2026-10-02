<div id="content" class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 text-gray-800 text-uppercase fw-bold">🏆 Ranking Oficial</h2>
        <a href="<?= BASE_URL ?>/ranking/atualizar" class="btn btn-sm btn-outline-primary fw-bold text-uppercase"
            onclick="return confirm('Isso irá recalcular todos os pontos. Deseja continuar?')">
            <i class="fas fa-sync me-1"></i> Sincronizar Lutas
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/ranking" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="small fw-bold text-muted text-uppercase">Modalidade</label>
                    <select name="modalidade" class="form-select shadow-none" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($modalidades as $m): ?>
                            <option value="<?= $m['id_modalidade'] ?>" <?= (isset($filtros['modalidade']) && $filtros['modalidade'] == $m['id_modalidade']) ? 'selected' : '' ?>>
                                <?= $m['nome'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="small fw-bold text-muted text-uppercase">Sexo</label>
                    <select name="sexo" class="form-select shadow-none">
                        <option value="M" <?= (isset($filtros['sexo']) && $filtros['sexo'] == 'M') ? 'selected' : '' ?>>
                            Masculino</option>
                        <option value="F" <?= (isset($filtros['sexo']) && $filtros['sexo'] == 'F') ? 'selected' : '' ?>>
                            Feminino</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="small fw-bold text-muted text-uppercase">Categoria</label>
                    <select name="categoria" id="select-categoria" class="form-select shadow-none"
                        data-selected="<?= $filtros['categoria'] ?? '' ?>" required>
                        <option value="">Selecione a modalidade primeiro...</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-danger w-100 fw-bold shadow-sm">
                        <i class="fas fa-search me-2"></i> FILTRAR RANKING
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-dark text-white text-uppercase small">
                    <tr>
                        <th class="text-center" style="width: 60px;">Pos</th>
                        <th>Atleta</th>
                        <th class="text-center">Pontos</th>
                        <th class="text-center">V / D / E</th>
                        <th class="text-center">Última Luta</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ranking)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-info-circle fa-2x mb-3 d-block text-light"></i>
                                Selecione os filtros para visualizar a classificação.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php
                        // Lógica de posição baseada na página
                        $itensPorPagina = 15;
                        $posicao = (($paginaAtual - 1) * $itensPorPagina) + 1;

                        foreach ($ranking as $atleta):
                            $isCampeao = (bool) $atleta['campeao'];
                            ?>
                            <tr class="<?= $isCampeao ? 'table-warning' : '' ?>"
                                style="<?= $isCampeao ? 'border-left: 5px solid #ffc107;' : '' ?>">
                                <td class="text-center fw-bold">
                                    <?php if ($isCampeao): ?>
                                        <i class="fas fa-crown text-warning fs-5"></i>
                                    <?php else: ?>
                                        <?= $posicao++ ?>º
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= BASE_URL ?>/uploads/atletas/<?= !empty($atleta['foto']) ? $atleta['foto'] : 'default.jpg' ?>"
                                            width="40" height="40" class="rounded-circle border shadow-sm"
                                            style="object-fit: cover;">
                                        <div>
                                            <span class="fw-bold d-block text-uppercase" style="font-size: 0.85rem;">
                                                <?= htmlspecialchars($atleta['atleta_nome']) ?>
                                                <?php if ($isCampeao): ?>
                                                    <small class="badge bg-warning text-dark ms-1"
                                                        style="font-size: 0.6rem;">CAMPEÃO</small>
                                                <?php endif; ?>
                                            </span>
                                            <small class="text-muted"
                                                style="font-size: 0.7rem;"><?= $atleta['categoria_nome'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $isCampeao ? 'bg-warning text-dark' : 'bg-danger' ?> px-3 shadow-sm">
                                        <?= $atleta['pontos'] ?> pts
                                    </span>
                                </td>
                                <td class="text-center small">
                                    <span class="text-success fw-bold"><?= $atleta['vitorias'] ?>V</span> |
                                    <span class="text-danger fw-bold"><?= $atleta['derrotas'] ?>D</span> |
                                    <span class="text-muted"><?= $atleta['empates'] ?>E</span>
                                </td>
                                <td class="text-center small text-muted">
                                    <?= !empty($atleta['ultima_luta']) ? date('d/m/Y', strtotime($atleta['ultima_luta'])) : '--/--/----' ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <?php if (!$isCampeao): ?>
                                            <a href="<?= BASE_URL ?>/campeoes/registrar_pelo_ranking?id_atleta=<?= $atleta['id_atleta'] ?>&id_mod=<?= $filtros['modalidade'] ?>&id_cat=<?= $filtros['categoria'] ?>"
                                                class="btn btn-sm btn-outline-warning border-0" title="Tornar Campeão">
                                                <i class="fas fa-crown"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL ?>/ranking/remover_atleta?id=<?= $atleta['id_atleta'] ?>&cat=<?= $filtros['categoria'] ?>&mod=<?= $filtros['modalidade'] ?>"
                                            class="btn btn-sm btn-outline-danger border-0 btn-remover-atleta"
                                            title="Ocultar do Ranking">
                                            <i class="fas fa-user-minus"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPaginas > 1): ?>
            <div class="card-footer bg-white py-3 border-0">
                <nav aria-label="Navegação do ranking">
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <li class="page-item <?= $paginaAtual <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link"
                                href="?<?= http_build_query(array_merge($_GET, ['page' => $paginaAtual - 1])) ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <li class="page-item <?= $i == $paginaAtual ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= $paginaAtual >= $totalPaginas ? 'disabled' : '' ?>">
                            <a class="page-link"
                                href="?<?= http_build_query(array_merge($_GET, ['page' => $paginaAtual + 1])) ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                    <div class="text-center mt-2">
                        <small class="text-muted" style="font-size: 0.65rem;">Exibindo página <?= $paginaAtual ?> de
                            <?= $totalPaginas ?></small>
                    </div>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- FEEDBACK DE STATUS (URL PARAMS) ---
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('status') === 'sucesso') {
            Swal.fire({ icon: 'success', title: 'Ranking Sincronizado!', text: 'Os pontos foram recalculados com sucesso.', timer: 3000 });
        } else if (urlParams.get('status') === 'sucesso_remocao') {
            Swal.fire({ icon: 'success', title: 'Atleta Removido!', text: 'O atleta agora está oculto do ranking oficial.', timer: 3000 });
        }

        // --- LÓGICA DE CATEGORIAS ---
        const modalidadeSel = document.querySelector('select[name="modalidade"]');
        const sexoSel = document.querySelector('select[name="sexo"]');
        const categoriaSel = document.getElementById('select-categoria');

        function carregarCategorias() {
            const modId = modalidadeSel.value;
            const sexo = sexoSel.value;
            const idSalvo = categoriaSel.getAttribute('data-selected');

            if (!modId) {
                categoriaSel.innerHTML = '<option value="">Selecione a modalidade primeiro...</option>';
                return;
            }

            categoriaSel.innerHTML = '<option value="">Carregando...</option>';

            fetch(`<?= BASE_URL ?>/ranking/buscarCategoriasPorModalidade?modalidade=${modId}&sexo=${sexo}`)
                .then(res => res.json())
                .then(data => {
                    categoriaSel.innerHTML = '<option value="">Selecione uma categoria...</option>';
                    data.forEach(cat => {
                        const opt = document.createElement('option');
                        opt.value = cat.id_categoria_peso;
                        opt.textContent = `${cat.nome} (até ${cat.peso_max}kg)`;
                        if (cat.id_categoria_peso == idSalvo) opt.selected = true;
                        categoriaSel.appendChild(opt);
                    });
                });
        }

        modalidadeSel.addEventListener('change', carregarCategorias);
        sexoSel.addEventListener('change', carregarCategorias);
        if (modalidadeSel.value) carregarCategorias();

        // --- CONFIRMAÇÃO DE REMOÇÃO ---
        document.addEventListener('click', function (e) {
            if (e.target.closest('.btn-remover-atleta')) {
                e.preventDefault();
                const btn = e.target.closest('.btn-remover-atleta');
                const url = btn.getAttribute('href');

                Swal.fire({
                    title: 'Remover do Ranking?',
                    text: "O atleta deixará de aparecer nesta listagem oficial.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Sim, remover!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) window.location.href = url;
                });
            }
        });
    });
</script>