<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 fw-bold text-uppercase mb-0">Card do Evento</h1>
            <p class="text-muted fw-bold"><?= htmlspecialchars($evento['nome'] ?? 'Evento') ?></p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/eventos" class="btn btn-outline-secondary fw-bold text-uppercase px-4">
                <i class="fas fa-arrow-left me-2"></i> Voltar
            </a>
            <a href="<?= BASE_URL ?>/eventos/nova-luta?id=<?= $id_evento ?>"
                class="btn btn-danger fw-bold text-uppercase shadow-sm px-4">
                <i class="fas fa-plus me-2"></i> Nova Luta
            </a>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <?php
        $msgType = ($_GET['msg'] == 'erro' || $_GET['msg'] == 'erro_salvar') ? 'danger' : 'success';
        $mensagens = [
            'excluido' => 'Luta removida com sucesso!',
            'atualizado' => 'Luta atualizada com sucesso!',
            'sucesso' => 'Luta cadastrada com sucesso!',
            'resultado_publicado' => 'Resultado oficial publicado!',
            'erro_salvar' => 'Erro ao salvar o resultado no banco.',
            'erro' => 'Ocorreu um erro ao processar a solicitação.'
        ];
        $msgTexto = $mensagens[$_GET['msg']] ?? 'Operação realizada.';
        ?>
        <div class="alert alert-<?= $msgType ?> alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="fas <?= $msgType == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> me-2"></i>
            <?= $msgTexto ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <?php if (empty($lutas)): ?>
                <div class="card border-0 shadow-sm text-center py-5">
                    <div class="card-body">
                        <i class="fas fa-layer-group fa-4x text-light mb-3"></i>
                        <h4 class="text-muted text-uppercase fw-bold">Nenhuma luta casada</h4>
                    </div>
                </div>
            <?php else: ?>
                <?php
                $totalLutas = count($lutas);
                $numeroLuta = $totalLutas;

                foreach ($lutas as $l):
                    $foiRealizada = (($l['status'] ?? '') == 'realizada');
                    $isVencedorAzul = ($foiRealizada && $l['vencedor_id'] == $l['id_atleta_azul']);
                    $isVencedorVermelho = ($foiRealizada && $l['vencedor_id'] == $l['id_atleta_vermelho']);
                    ?>
                    <div class="card shadow-sm border-0 mb-4 overflow-hidden">

                        <div class="bg-dark py-2 text-center d-flex justify-content-center align-items-center gap-3">
                            <small class="text-white fw-bold text-uppercase">
                                <span class="text-warning me-2">LUTA #<?= $numeroLuta ?></span>
                                <i class="fas fa-fist-raised text-danger me-1"></i>
                                <?= htmlspecialchars($l['modalidade_nome'] ?? 'MODALIDADE') ?>
                            </small>

                            <?php if ($foiRealizada): ?>
                                <span class="text-secondary">|</span>
                                <small class="text-white fw-bold text-uppercase">
                                    <span class="text-warning">
                                        <i class="fas fa-trophy"></i> <?= htmlspecialchars($l['metodo_nome'] ?? 'RESULTADO') ?>
                                    </span>
                                    <span class="mx-2">•</span> R<?= $l['round_final'] ?> • <?= $l['tempo_final'] ?>
                                </small>
                            <?php endif; ?>
                        </div>

                        <div class="card-body p-0">
                            <div class="row g-0 align-items-center">
                                <div class="col-md-4 p-4 text-center <?= $isVencedorAzul ? 'bg-primary bg-opacity-10' : '' ?>">
                                    <div class="mb-2 position-relative d-inline-block">
                                        <img src="<?= !empty($l['atleta_azul_foto']) ? BASE_URL . "/uploads/atletas/" . $l['atleta_azul_foto'] : BASE_URL . "/assets/img/no-photo.jpg" ?>"
                                            class="rounded-circle border border-4 <?= $isVencedorAzul ? 'border-primary' : 'border-light' ?>"
                                            style="width: 80px; height: 80px; object-fit: cover;">
                                        <?php if ($isVencedorAzul): ?>
                                            <span
                                                class="badge bg-primary position-absolute top-0 start-100 translate-middle rounded-pill shadow">WIN</span>
                                        <?php endif; ?>
                                    </div>
                                    <h3 class="h5 fw-bold <?= $isVencedorAzul ? 'text-primary' : '' ?> mb-0">
                                        <?= htmlspecialchars($l['atleta_azul_nome'] . ' ' . $l['atleta_azul_sobrenome']) ?>
                                    </h3>
                                    <?php if (!empty($l['atleta_azul_apelido'])): ?>
                                        <div class="text-primary fw-bold small text-uppercase mb-1" style="font-style: italic;">
                                            "<?= htmlspecialchars($l['atleta_azul_apelido']) ?>"
                                        </div>
                                    <?php endif; ?>
                                    <p class="small fw-bold text-secondary mb-0 text-uppercase">Corner Azul</p>
                                </div>

                                <div
                                    class="col-md-4 p-3 text-center bg-light border-start border-end d-flex flex-column align-items-center justify-content-center">
                                    <span class="badge bg-white text-dark border px-3 py-1 mb-1 text-uppercase fw-bold"
                                        style="font-size: 0.7rem;">
                                        Luta
                                    </span>

                                    <?php
                                    $categoriaLimpa = !empty($l['categoria_nome']) ? explode(' (', $l['categoria_nome'])[0] : "Categoria";
                                    ?>
                                    <div class="fw-bold text-dark text-uppercase mb-1"
                                        style="font-size: 0.9rem; line-height: 1.2;">
                                        <?= htmlspecialchars($categoriaLimpa) ?>
                                        <?php if (!empty($l['peso_max'])): ?>
                                            <small class="d-block text-muted fw-normal" style="font-size: 0.75rem;">Até
                                                <?= $l['peso_max'] ?>kg</small>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($foiRealizada): ?>
                                        <div class="border-top pt-2 mt-1 w-75">
                                            <div class="text-muted text-uppercase fw-bold"
                                                style="font-size: 0.6rem; letter-spacing: 0.5px;">Árbitro Central</div>
                                            <div class="fw-bold text-dark text-uppercase small" style="font-size: 0.75rem;">
                                                <?= htmlspecialchars($l['arbitro_nome'] ?? 'Não informado') ?></div>
                                        </div>
                                    <?php else: ?>
                                        <div class="fw-bold text-secondary small mt-1">
                                            <?= $l['num_rounds'] ?> X <?= date('i:s', strtotime($l['duracao_round'])) ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($l['vale_cinturao'])): ?>
                                        <div class="badge bg-warning text-dark mt-2 text-uppercase shadow-sm"
                                            style="font-size: 0.65rem;">
                                            <i class="fas fa-trophy me-1"></i> Cinturão
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div
                                    class="col-md-4 p-4 text-center <?= $isVencedorVermelho ? 'bg-danger bg-opacity-10' : '' ?>">
                                    <div class="mb-2 position-relative d-inline-block">
                                        <img src="<?= !empty($l['atleta_vermelho_foto']) ? BASE_URL . "/uploads/atletas/" . $l['atleta_vermelho_foto'] : BASE_URL . "/assets/img/no-photo.jpg" ?>"
                                            class="rounded-circle border border-4 <?= $isVencedorVermelho ? 'border-danger' : 'border-light' ?>"
                                            style="width: 80px; height: 80px; object-fit: cover;">
                                        <?php if ($isVencedorVermelho): ?>
                                            <span
                                                class="badge bg-danger position-absolute top-0 start-0 translate-middle rounded-pill shadow">WIN</span>
                                        <?php endif; ?>
                                    </div>
                                    <h3 class="h5 fw-bold <?= $isVencedorVermelho ? 'text-danger' : '' ?> mb-0">
                                        <?= htmlspecialchars($l['atleta_vermelho_nome'] . ' ' . $l['atleta_vermelho_sobrenome']) ?>
                                    </h3>
                                    <?php if (!empty($l['atleta_vermelho_apelido'])): ?>
                                        <div class="text-danger fw-bold small text-uppercase mb-1" style="font-style: italic;">
                                            "<?= htmlspecialchars($l['atleta_vermelho_apelido']) ?>"
                                        </div>
                                    <?php endif; ?>
                                    <p class="small fw-bold text-secondary mb-0 text-uppercase">Corner Vermelho</p>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-white d-flex justify-content-center align-items-center gap-3 py-3">
                            <a href="<?= BASE_URL ?>/eventos/edit-luta?id=<?= $l['id_luta'] ?>"
                                class="btn btn-sm btn-link text-primary fw-bold text-decoration-none">
                                <i class="fas fa-edit"></i> EDITAR
                            </a>

                            <?php if (!$foiRealizada): ?>
                                <a href="<?= BASE_URL ?>/eventos/lancar-resultado?id_luta=<?= $l['id_luta'] ?>"
                                    class="btn btn-sm btn-warning fw-bold px-3 shadow-sm">
                                    <i class="fas fa-gavel me-1"></i> LANÇAR RESULTADO
                                </a>
                            <?php else: ?>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-success py-2 px-3 text-uppercase">
                                        <i class="fas fa-check-circle"></i> Finalizada
                                    </span>
                                    <a href="<?= BASE_URL ?>/eventos/lancar-resultado?id_luta=<?= $l['id_luta'] ?>"
                                        class="btn btn-sm btn-outline-secondary fw-bold px-3">
                                        <i class="fas fa-undo"></i> ALTERAR RESULTADO
                                    </a>
                                </div>
                            <?php endif; ?>

                            <button onclick="confirmarExclusao(<?= $l['id_luta'] ?>, <?= $id_evento ?>)"
                                class="btn btn-sm btn-link text-danger fw-bold text-decoration-none">
                                <i class="fas fa-trash-alt"></i> EXCLUIR
                            </button>

               

                     </div>
                    </div>
                    <?php
                    $numeroLuta--;
                endforeach;
                ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function confirmarExclusao(idLuta, idEvento) {
        if (confirm('Tem certeza que deseja excluir esta luta? Esta ação não pode ser desfeita.')) {
            window.location.href = `<?= BASE_URL ?>/eventos/excluir-luta?id=${idLuta}&id_evento=${idEvento}`;
        }
    }
</script>