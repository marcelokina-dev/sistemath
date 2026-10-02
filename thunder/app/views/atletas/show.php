<?php
// 1. Inicialização segura
$vit = 0;
$der = 0;
$emp = 0;
$lutas = $lutas ?? [];

foreach ($lutas as $l) {
    $res = isset($l['resultado']) ? mb_strtolower(trim($l['resultado']), 'UTF-8') : '';
    if (in_array($res, ['vitória', 'vitoria'])) {
        $vit++;
    } elseif ($res == 'derrota') {
        $der++;
    } else {
        $emp++;
    }
}

// 2. Lógica de Fallback
$totalVitorias = (!empty($lutas)) ? $vit : ($atleta['vitorias'] ?? 0);
$totalDerrotas = (!empty($lutas)) ? $der : ($atleta['derrotas'] ?? 0);
$totalEmpates = (!empty($lutas)) ? $emp : ($atleta['empates'] ?? 0);
?>
<div id="content" class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <i class="fas fa-id-card fa-2x text-dark"></i>
            <h1 class="h2 fw-bold text-uppercase mb-0 tracking-tighter">Perfil do Atleta</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="/thunder/public/atletas/edit?id=<?= (int)($atleta['id_atleta'] ?? 0) ?>"
                class="btn btn-dark fw-bold px-4 shadow-sm">
                <i class="fas fa-edit me-2"></i> EDITAR
            </a>
            <a href="/thunder/public/atletas" class="btn btn-outline-secondary fw-bold px-4">
                VOLTAR
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow-sm border-0 rounded-3 mb-4 overflow-hidden">
                <div class="bg-danger py-2"></div>
                <div class="card-body text-center p-4">
                    <div class="mb-4">
                        <img src="<?= !empty($atleta['foto']) ? '/thunder/public/uploads/atletas/' . htmlspecialchars($atleta['foto']) : '/thunder/public/img/sem-foto.png' ?>"
                            class="rounded shadow-sm border border-4 border-white"
                            style="width: 220px; height: 220px; object-fit: cover;" alt="Foto do Atleta">
                    </div>

                    <h2 class="fw-bold text-dark mb-1">
                        <?= htmlspecialchars(($atleta['nome'] ?? '') . ' ' . ($atleta['sobrenome'] ?? '')) ?>
                    </h2>
                    <h4 class="text-danger fw-bold text-uppercase mb-3">
                        "<?= htmlspecialchars($atleta['apelido'] ?? 'S/A') ?>"
                    </h4>

                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <span class="badge bg-dark px-3 py-2 text-uppercase" style="letter-spacing: 1px;">
                            <?= htmlspecialchars($atleta['modalidade_nome'] ?? 'Modalidade') ?>
                        </span>
                        <span class="badge <?= (mb_strtolower($atleta['status'] ?? '') == 'ativo') ? 'bg-success' : 'bg-secondary' ?> px-3 py-2 text-uppercase">
                            <?= htmlspecialchars($atleta['status'] ?? 'Inativo') ?>
                        </span>
                    </div>

                    <div class="row g-0 bg-light rounded-3 p-3 mb-4 border">
                        <div class="col-4 border-end">
                            <div class="small text-uppercase text-muted fw-bold" style="font-size: 0.65rem;">Vitórias</div>
                            <div class="h4 fw-bold text-success mb-0"><?= (int)$totalVitorias ?></div>
                        </div>
                        <div class="col-4 border-end">
                            <div class="small text-uppercase text-muted fw-bold" style="font-size: 0.65rem;">Derrotas</div>
                            <div class="h4 fw-bold text-danger mb-0"><?= (int)$totalDerrotas ?></div>
                        </div>
                        <div class="col-4">
                            <div class="small text-uppercase text-muted fw-bold" style="font-size: 0.65rem;">Empates</div>
                            <div class="h4 fw-bold text-secondary mb-0"><?= (int)$totalEmpates ?></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <?php if (!empty($atleta['instagram'])): ?>
                            <a href="https://instagram.com/<?= htmlspecialchars(str_replace('@', '', $atleta['instagram'])) ?>"
                                target="_blank" class="btn btn-outline-dark btn-sm rounded-circle" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($atleta['facebook'])): ?>
                            <a href="<?= str_contains($atleta['facebook'], 'http') ? htmlspecialchars($atleta['facebook']) : 'https://facebook.com/' . htmlspecialchars($atleta['facebook']) ?>"
                                target="_blank" class="btn btn-outline-primary btn-sm rounded-circle" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($atleta['whatsapp'])): ?>
                            <a href="https://wa.me/55<?= preg_replace('/\D/', '', $atleta['whatsapp']) ?>" 
                                target="_blank" class="btn btn-outline-success btn-sm rounded-circle" title="WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($atleta['link_tapology'])): ?>
                            <a href="<?= htmlspecialchars($atleta['link_tapology']) ?>" target="_blank"
                                class="btn btn-outline-info btn-sm rounded-circle" title="Tapology">
                                <i class="fas fa-link"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($atleta['link_sherdog'])): ?>
                            <a href="<?= htmlspecialchars($atleta['link_sherdog']) ?>" target="_blank"
                                class="btn btn-outline-secondary btn-sm rounded-circle" title="Sherdog">
                                <i class="fas fa-trophy"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-dark text-white fw-bold text-uppercase py-3 d-flex justify-content-between">
                    <span><i class="fas fa-bolt me-2 text-warning"></i> Informações de Combate</span>
                    <span class="small text-muted fw-normal">ID: #<?= str_pad($atleta['id_atleta'] ?? 0, 4, '0', STR_PAD_LEFT) ?></span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase fw-bold d-block mb-1">Equipe / Academia</label>
                            <span class="h6 fw-bold text-dark"><?= htmlspecialchars($atleta['equipe_nome'] ?? 'Independente') ?></span>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase fw-bold d-block mb-1">Categoria</label>
                            <span class="h6 fw-bold text-dark"><?= htmlspecialchars($atleta['categoria_nome'] ?? '-') ?></span>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase fw-bold d-block mb-1">Origem</label>
                            <span class="h6 fw-bold text-dark"><?= htmlspecialchars($atleta['cidade'] ?? '-') ?> / <?= htmlspecialchars($atleta['estado'] ?? '-') ?></span>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase fw-bold d-block mb-1">Peso</label>
                            <span class="h6 fw-bold text-dark"><?= !empty($atleta['peso']) ? number_format((float)$atleta['peso'], 2, ',', '.') : '--' ?> kg</span>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase fw-bold d-block mb-1">Altura</label>
                            <span class="h6 fw-bold text-dark"><?= !empty($atleta['altura']) ? number_format((float)$atleta['altura'], 2, ',', '.') . ' m' : '--' ?></span>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase fw-bold d-block mb-1">Envergadura</label>
                            <span class="h6 fw-bold text-dark"><?= !empty($atleta['envergadura']) ? number_format((float)$atleta['envergadura'], 2, ',', '.') . ' m' : '--' ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-danger text-white fw-bold text-uppercase py-3">
                    <i class="fas fa-award me-2"></i> Graduações e Faixas
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="px-4">Modalidade</th>
                                    <th>Graduação / Faixa</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($graduacoes)): ?>
                                    <?php foreach ($graduacoes as $grad): ?>
                                        <tr>
                                            <td class="px-4 fw-bold text-dark">
                                                <?= htmlspecialchars($grad['modalidade_nome'] ?? 'N/A') ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary px-3 py-2">
                                                    <?= htmlspecialchars($grad['graduacao_nome'] ?? 'N/A') ?>
                                                </span>
                                            </td>
                                            <td class="text-center"><i class="fas fa-check-circle text-success"></i></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted small">Nenhuma graduação registrada.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-secondary text-white fw-bold text-uppercase py-3">
                    <i class="fas fa-map-marker-alt me-2"></i> Localização e Contato Interno
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6 border-end">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 text-uppercase small">Documentação</h6>
                            <p class="mb-1 small text-muted text-uppercase fw-bold">CPF: <span class="text-dark fw-normal"><?= htmlspecialchars($atleta['cpf'] ?? '-') ?></span></p>
                            <p class="mb-1 small text-muted text-uppercase fw-bold">RG: <span class="text-dark fw-normal"><?= htmlspecialchars($atleta['rg'] ?? '-') ?></span></p>
                            <p class="mb-1 small text-muted text-uppercase fw-bold">E-mail: <span class="text-dark fw-normal text-lowercase"><?= htmlspecialchars($atleta['email'] ?? '-') ?></span></p>
                            <p class="mb-0 small text-muted text-uppercase fw-bold">Nascimento: <span class="text-dark fw-normal"><?= !empty($atleta['data_nascimento']) ? date('d/m/Y', strtotime($atleta['data_nascimento'])) : '-' ?></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 text-uppercase small">Endereço Residencial</h6>
                            <p class="mb-1 text-dark fw-bold">
                                <?= htmlspecialchars($atleta['logradouro'] ?? 'Rua não informada') ?>, <?= htmlspecialchars($atleta['numero'] ?? 'S/N') ?>
                            </p>
                            <?php if (!empty($atleta['complemento'])): ?>
                                <p class="mb-1 small text-muted">Comp.: <span class="text-dark"><?= htmlspecialchars($atleta['complemento']) ?></span></p>
                            <?php endif; ?>
                            <p class="mb-1 text-dark">
                                <span class="text-muted small text-uppercase fw-bold">Bairro:</span> <?= htmlspecialchars($atleta['bairro'] ?? '-') ?>
                            </p>
                            <p class="mb-1 text-dark">
                                <span class="text-muted small text-uppercase fw-bold">CEP:</span> <?= htmlspecialchars($atleta['cep'] ?? '-') ?>
                            </p>
                            <p class="mb-0 mt-2">
                                <span class="badge bg-danger px-2 py-1">
                                    <i class="fas fa-city me-1"></i>
                                    <?= htmlspecialchars($atleta['cidade'] ?? '-') ?> - <?= htmlspecialchars($atleta['estado'] ?? '-') ?>
                                </span>
                                <span class="ms-1 text-muted small fw-bold"><?= htmlspecialchars($atleta['pais'] ?? 'Brasil') ?></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header bg-dark text-white fw-bold text-uppercase py-3 d-flex justify-content-between align-items-center">
            <span><i class="fas fa-history me-2 text-warning"></i> Histórico de Lutas no Evento</span>
            <span class="badge bg-danger"><?= count($lutas) ?> COMBATES</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="px-4">Evento / Data</th>
                            <th>Modalidade</th> <th>Oponente</th>
                            <th>Resultado</th>
                            <th>Método</th>
                            <th class="text-center">Round</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($lutas)): ?>
                            <?php foreach ($lutas as $luta): ?>
                                <tr>
                                    <td class="px-4">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($luta['evento_nome'] ?? 'N/A') ?></div>
                                        <div class="small text-muted"><?= !empty($luta['data_evento']) ? date('d/m/Y', strtotime($luta['data_evento'])) : '-' ?></div>
                                    </td>
                                    
                                    <td>
                                        <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.75rem;">
                                            <?= htmlspecialchars($luta['modalidade_nome'] ?? ($luta['modalidade'] ?? 'MMA')) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="<?= !empty($luta['oponente_foto']) ? '/thunder/public/uploads/atletas/' . htmlspecialchars($luta['oponente_foto']) : '/thunder/public/img/sem-foto.png' ?>"
                                                 class="rounded-circle border"
                                                 style="width: 32px; height: 32px; object-fit: cover;" alt="Foto Oponente">
                                            <span class="fw-bold"><?= htmlspecialchars($luta['oponente_nome'] ?? 'Oponente') ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $resStr = isset($luta['resultado']) ? mb_strtolower(trim($luta['resultado']), 'UTF-8') : '';
                                        $metodoStr = isset($luta['metodo_nome']) ? mb_strtolower(trim($luta['metodo_nome']), 'UTF-8') : '';
                                        
                                        // Variável auxiliar para customizar o texto exibido se necessário
                                        $textoResultado = $luta['resultado'] ?? 'N/A';

                                        // Força a exibição de 'No Contest' se o método ou o resultado indicarem isso
                                        if ($resStr == 'no contest' || $metodoStr == 'no contest') {
                                            $badgeClass = 'bg-secondary';
                                            $textoResultado = 'No Contest';
                                        } elseif (in_array($resStr, ['vitória', 'vitoria'])) {
                                            $badgeClass = 'bg-success';
                                        } elseif ($resStr == 'derrota') {
                                            $badgeClass = 'bg-danger';
                                        } else {
                                            $badgeClass = 'bg-secondary';
                                        }
                                        ?>
                                        <span class="badge <?= $badgeClass ?> text-uppercase px-2 py-1">
                                            <?= htmlspecialchars($textoResultado) ?>
                                        </span>
                                    </td>
                                    <td class="small fw-bold"><?= htmlspecialchars($luta['metodo_nome'] ?? ($luta['metodo_sigla'] ?? 'N/A')) ?></td>
                                    <td class="text-center fw-bold"><?= htmlspecialchars($luta['round_final'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted small">Nenhuma luta registrada neste evento.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
    </div>
</div>