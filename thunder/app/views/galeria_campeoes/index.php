<div id="content" class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 fw-bold text-uppercase mb-0 tracking-tighter">🏆 Galeria de Campeões</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                    <li class="breadcrumb-item"><a href="/thunder/public"
                            class="text-decoration-none text-muted">Painel</a></li>
                    <li class="breadcrumb-item active fw-bold text-danger">Campeões Atuais</li>
                </ol>
            </nav>
        </div>
        <button type="button" class="btn btn-danger btn-sm fw-bold text-uppercase shadow-sm" data-bs-toggle="modal"
            data-bs-target="#modalNovoCampeao">
            <i class="fas fa-crown me-1"></i> Coroar Novo Campeão
        </button>
    </div>

    <div class="card shadow-sm border-0 rounded-3 overflow-hidden mb-5">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title mb-0 fw-bold text-uppercase" style="font-size: 0.85rem;">Detentores Atuais de Cinturão
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 text-uppercase text-muted small fw-bold" style="font-size: 0.65rem;">Foto</th>
                        <th class="text-uppercase text-muted small fw-bold" style="font-size: 0.65rem;">Atleta</th>
                        <th class="text-uppercase text-muted small fw-bold" style="font-size: 0.65rem;">Modalidade /
                            Categoria</th>
                        <th class="text-end pe-4 text-uppercase text-muted small fw-bold" style="font-size: 0.65rem;">
                            Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($campeoes)): ?>
                        <?php foreach ($campeoes as $c): ?>
                            <tr>
                                <td class="ps-4">
                                    <img src="/thunder/public/uploads/atletas/<?= $c['foto'] ?: 'default.png' ?>"
                                        class="rounded-circle border" width="45" height="45" style="object-fit: cover;">
                                </td>
                                <td>
                                    <span class="fw-bold text-dark text-uppercase" style="font-size: 0.9rem;">
                                        <?= htmlspecialchars($c['atleta_nome']) ?>
                                        <?= !empty($c['sobrenome']) ? htmlspecialchars($c['sobrenome']) : '' ?>
                                    </span>
                                    <?php if (!empty($c['apelido'])): ?>
                                        <small class="text-danger fw-bold">"<?= htmlspecialchars($c['apelido']) ?>"</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-uppercase" style="font-size: 0.8rem;">
                                            <?= htmlspecialchars($c['evento_nome'] ?: 'Título Declarado') ?>
                                        </span>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            <?php
                                            $dataExibicao = !empty($c['data_real_evento']) ? $c['data_real_evento'] : $c['data_inicio'];
                                            echo date('d/m/Y', strtotime($dataExibicao));
                                            ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border fw-medium text-uppercase"
                                        style="font-size: 0.7rem;">
                                        <?= htmlspecialchars($c['modalidade_nome']) ?>
                                    </span>
                                    <span class="text-muted small ms-2"><?= htmlspecialchars($c['categoria_nome']) ?> <?= htmlspecialchars($c['peso_max']) ?>Kg</span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="/thunder/public/campeoes/vacante?id=<?= $c['id_historico_campeao'] ?>"
                                        class="btn btn-outline-danger btn-sm fw-bold text-uppercase btn-vacante"
                                        data-nome="<?= htmlspecialchars($c['atleta_nome']) ?>" style="font-size: 0.7rem;">
                                        <i class="fas fa-unlock me-1"></i> Tornar Vago
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="fas fa-ghost fa-3x text-light mb-3"></i>
                                <p class="text-muted mb-0">Nenhum campeão ativo no momento.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title mb-0 fw-bold text-uppercase text-muted" style="font-size: 0.85rem;">Hall da Fama
                (Ex-Campeões)</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 text-uppercase text-muted small fw-bold" style="font-size: 0.65rem;">Atleta</th>
                        <th class="text-uppercase text-muted small fw-bold" style="font-size: 0.65rem;">Categoria</th>
                        <th class="text-center text-uppercase text-muted small fw-bold" style="font-size: 0.65rem;">
                            Status</th>
                        <th class="text-end pe-4 text-uppercase text-muted small fw-bold" style="font-size: 0.65rem;">
                            Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($hall_fama)): ?>
                        <?php foreach ($hall_fama as $h): ?>
                            <tr>
                                <td class="ps-4 small fw-bold text-uppercase">
                                    <?= htmlspecialchars($h['atleta_nome']) ?>
                                    <?= !empty($h['sobrenome']) ? htmlspecialchars($h['sobrenome']) : '' ?>
                                    <?php if (!empty($h['apelido'])): ?>
                                        <small class="text-danger fw-bold">"<?= htmlspecialchars($h['apelido']) ?>"</small>
                                    <?php endif; ?>
                                </td>

                                <td class="small">
                                    <div class="fw-bold text-dark text-uppercase" style="font-size: 0.75rem;">
                                        <?= htmlspecialchars($h['evento_nome'] ?: 'Título Declarado') ?>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.7rem;">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        <?php
                                        // Se houver data do evento, usa ela. Se não, usa a data_inicio do histórico.
                                        $dataExibicao = !empty($h['data_real_evento']) ? $h['data_real_evento'] : $h['data_inicio'];
                                        echo date('d/m/Y', strtotime($dataExibicao));
                                        ?>
                                    </div>
                                </td>

                                <td class="small text-muted"><?= htmlspecialchars($h['modalidade_nome']) ?>
                                    (<?= htmlspecialchars($h['categoria_nome']) ?>) <?= htmlspecialchars($h['peso_max']) ?>Kg</td>
                                <td class="text-center">
                                    <span class="badge bg-light text-muted border text-uppercase" style="font-size: 0.6rem;">
                                        <?= htmlspecialchars($h['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="/thunder/public/campeoes/excluir_historico?id=<?= $h['id_historico_campeao'] ?>"
                                        class="btn btn-link text-danger p-0 btn-excluir-hist" title="Excluir do Histórico">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNovoCampeao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="/thunder/public/campeoes/registrar" method="POST">
                <div class="modal-header bg-dark text-white border-0">
                    <h5 class="modal-title fw-bold text-uppercase" style="font-size: 0.85rem;">Coroar Novo Detentor</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Selecione o Atleta</label>
                        <select name="id_atleta" class="form-select form-select-sm" required>
                            <option value="">Escolher Atleta...</option>
                            <?php foreach ($atletas as $atleta): ?>
                                <option value="<?= $atleta['id_atleta'] ?>"><?= htmlspecialchars($atleta['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-uppercase text-muted">Modalidade</label>
                            <select name="id_modalidade" id="selectModalidade" class="form-select form-select-sm"
                                required>
                                <option value="">Escolher...</option>
                                <?php foreach ($modalidades as $mod): ?>
                                    <option value="<?= $mod['id_modalidade'] ?>"><?= htmlspecialchars($mod['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-uppercase text-muted">Categoria</label>
                            <select name="id_categoria_peso" id="selectCategoria" class="form-select form-select-sm"
                                required disabled>
                                <option value="">Aguardando modalidade...</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id_categoria_peso'] ?>"
                                        data-modalidade="<?= $cat['id_modalidade'] ?>" style="display:none;">
                                        <?= htmlspecialchars($cat['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light btn-sm fw-bold text-uppercase"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger btn-sm fw-bold text-uppercase">Confirmar
                        Conquista</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // 1. FILTRO DINÂMICO DE CATEGORIAS NO MODAL
        const selectMod = document.getElementById('selectModalidade');
        const selectCat = document.getElementById('selectCategoria');

        if (selectMod) {
            selectMod.addEventListener('change', function () {
                const modId = this.value;
                selectCat.disabled = !modId;
                selectCat.value = "";

                Array.from(selectCat.options).forEach(opt => {
                    if (opt.value === "") return;
                    opt.style.display = opt.getAttribute('data-modalidade') === modId ? 'block' : 'none';
                });
            });
        }

        // 2. CONFIRMAÇÃO PARA TORNAR VACANTE
        const botoesVacante = document.querySelectorAll('.btn-vacante');
        botoesVacante.forEach(botao => {
            botao.addEventListener('click', function (e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                const nomeAtleta = this.getAttribute('data-nome');

                Swal.fire({
                    title: 'Tornar Cinturão Vacante?',
                    text: `O atleta ${nomeAtleta} deixará de ser o campeão atual.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Sim, tornar vago!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) window.location.href = url;
                });
            });
        });

        // 3. CONFIRMAÇÃO PARA EXCLUIR HISTÓRICO
        const botoesExcluir = document.querySelectorAll('.btn-excluir-hist');
        botoesExcluir.forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const url = this.getAttribute('href');

                Swal.fire({
                    title: 'Excluir registro?',
                    text: "Esta informação será removida permanentemente do histórico.",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) window.location.href = url;
                });
            });
        });

        // 4. FEEDBACKS DE STATUS
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');

        const alerts = {
            'sucesso_vacante': { title: 'Sucesso!', text: 'Cinturão agora está vacante.', icon: 'success' },
            'sucesso_conquista': { title: 'Novo Campeão!', text: 'Registro atualizado com sucesso.', icon: 'success' },
            'sucesso_exclusao': { title: 'Excluído!', text: 'O registro foi removido do histórico.', icon: 'success' }
        };

        if (alerts[status]) {
            Swal.fire({ ...alerts[status], timer: 3000, showConfirmButton: false });
        }
    });
</script>