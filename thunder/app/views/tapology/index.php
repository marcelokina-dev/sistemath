<div class="container-fluid p-4">
<h2><i class="fas fa-bolt"></i> Importar card do Tapology</h2>
<p class="text-muted">
    Cole o conteúdo visível do card. O sistema identifica atletas já cadastrados
    e, quando encontrar a URL individual do Tapology, consulta o perfil e pode
    cadastrar automaticamente o atleta novo.
</p>

<?php if(!empty($ok)): ?>
<div class="alert alert-success"><?= htmlspecialchars($ok) ?></div>
<?php endif; ?>

<?php if(!empty($erro)): ?>
<div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<form method="POST" action="<?= $this->url('/tapology/analisar') ?>" class="card mb-4">
    <div class="card-header bg-dark text-white">1. Evento e card</div>

    <div class="card-body">
        <select name="id_evento" class="form-select mb-3" required>
            <option value="">-- Selecione o evento --</option>

            <?php foreach($eventos as $e): ?>
                <option value="<?= $e['id_evento'] ?>">
                    <?= htmlspecialchars($e['nome']) ?>
                    (<?= htmlspecialchars($e['data_evento']) ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <textarea
            name="tapology_texto"
            rows="18"
            class="form-control"
            required
            placeholder="Cole aqui o card copiado do Tapology..."
        ></textarea>

        <div class="form-text">
            Se o conteúdo tiver URLs de perfis
            <code>tapology.com/fightcenter/fighters/...</code>,
            elas serão usadas para consultar os dados completos do atleta.
        </div>
    </div>

    <div class="card-footer">
        <button class="btn btn-danger btn-lg">
            <i class="fas fa-search"></i> Analisar card
        </button>
    </div>
</form>

<?php if(!empty($preview)): ?>

<div class="card">
    <div class="card-header bg-danger text-white">
        2. Prévia —
        <?= (int)$preview['total'] ?> luta(s) encontrada(s)
    </div>

    <div class="card-body">
        <p class="small text-muted">
            <span class="badge bg-success">VERDE</span> atleta já cadastrado.
            <span class="badge bg-primary">AZUL</span> atleta novo localizado no Tapology.
            <span class="badge bg-warning text-dark">AMARELO</span> ambiguidade.
            <span class="badge bg-danger">VERMELHO</span> não localizado.
        </p>

        <form method="POST" action="<?= $this->url('/tapology/importar') ?>">

        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Vermelho</th>
                        <th>Azul</th>
                        <th>Resultado</th>
                        <th>Método</th>
                        <th>Categoria</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach($preview['lutas'] as $i=>$l): ?>
                    <tr>
                        <td><?= (int)$l['ordem'] ?></td>

                        <?php foreach(['vermelho','azul'] as $lado): ?>
                            <?php $m=$l[$lado.'_match']; ?>

                            <td>
                                <strong><?= htmlspecialchars($l[$lado]) ?></strong><br>

                                <?php if($m['status']==='encontrado'): ?>

                                    <span class="badge bg-success">
                                        #<?= (int)$m['id'] ?> encontrado
                                    </span>

                                    <input
                                        type="hidden"
                                        name="atleta[<?= $i ?>.<?= $lado ?>]"
                                        value="<?= (int)$m['id'] ?>"
                                    >

                                <?php elseif($m['status']==='novo_tapology'): ?>

                                    <span class="badge bg-primary">
                                        NOVO — Tapology localizado
                                    </span>

                                    <?php if(!empty($m['tapology_profile'])): ?>
                                        <div class="small mt-1">
                                            <strong>
                                                <?= htmlspecialchars($m['tapology_profile']['nome_completo'] ?? $l[$lado]) ?>
                                            </strong>

                                            <?php if(!empty($m['tapology_profile']['apelido'])): ?>
                                                — <?= htmlspecialchars($m['tapology_profile']['apelido']) ?>
                                            <?php endif; ?>

                                            <br>

                                            Record:
                                            <?= (int)($m['tapology_profile']['vitorias'] ?? 0) ?>-
                                            <?= (int)($m['tapology_profile']['derrotas'] ?? 0) ?>-
                                            <?= (int)($m['tapology_profile']['empates'] ?? 0) ?>

                                            <?php if(!empty($m['tapology_profile']['equipe'])): ?>
                                                <br>Equipe:
                                                <?= htmlspecialchars($m['tapology_profile']['equipe']) ?>
                                            <?php endif; ?>

                                            <?php if(!empty($m['tapology_profile']['peso'])): ?>
                                                <br>Peso:
                                                <?= number_format((float)$m['tapology_profile']['peso'], 2, ',', '.') ?> kg
                                            <?php endif; ?>

                                            <?php if(!empty($m['tapology_profile']['altura'])): ?>
                                                <br>Altura:
                                                <?= number_format((float)$m['tapology_profile']['altura'], 2, ',', '.') ?> m
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="small text-primary mt-1">
                                        Será cadastrado automaticamente ao confirmar.
                                    </div>

                                <?php elseif($m['status']==='novo_erro_tapology'): ?>

                                    <span class="badge bg-danger">
                                        TAPOLOGY COM ERRO
                                    </span>

                                    <div class="small text-danger mt-1">
                                        <?= htmlspecialchars($m['tapology_profile']['erro'] ?? 'Não foi possível consultar o perfil.') ?>
                                    </div>

                                <?php elseif($m['status']==='novo'): ?>

                                    <span class="badge bg-danger">
                                        NOVO — sem URL Tapology
                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-warning text-dark">
                                        AMBÍGUO
                                    </span>

                                    <?php foreach($m['opcoes'] as $op): ?>
                                        <div>
                                            <label>
                                                <input
                                                    type="radio"
                                                    name="atleta[<?= $i ?>.<?= $lado ?>]"
                                                    value="<?= (int)$op['id_atleta'] ?>"
                                                >
                                                #<?= (int)$op['id_atleta'] ?>
                                                <?= htmlspecialchars($this->label($op)) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>

                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>

                        <td><?= htmlspecialchars($l['vencedor'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($l['metodo'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($l['categoria'] ?: '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="alert alert-info">
            <strong>Atleta novo:</strong>
            quando o perfil individual do Tapology foi localizado,
            o sistema vai criar o cadastro automaticamente usando nome,
            apelido, record, peso, altura, equipe (quando já existir no banco),
            categoria, modalidade e URL do Tapology.
        </div>

        <?php if(!$this->podeImportar($preview)): ?>
            <div class="alert alert-warning">
                <strong>Atenção:</strong>
                existe pelo menos um atleta sem identificação.
                Para importar essa luta, precisamos da URL individual do atleta
                ou de um cadastro manual no banco.
            </div>
        <?php endif; ?>

        <button
            class="btn btn-success btn-lg"
            <?= $this->podeImportar($preview) ? '' : 'disabled' ?>
        >
            <i class="fas fa-check"></i>
            Confirmar importação
        </button>

        </form>
    </div>
</div>

<?php endif; ?>
</div>
