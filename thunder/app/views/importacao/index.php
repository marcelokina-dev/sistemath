<?php
$temErro = !empty($relatorio) && strpos(implode('', $relatorio), '❌') !== false;
?>

<div class="container-fluid p-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-bolt text-danger"></i> Importação em Massa
            </h2>
            <p class="text-muted mb-0">
                Cadastre atletas e importe o card completo de um evento em uma única operação.
            </p>
        </div>

        <a href="<?= $this->url('/tapology') ?>" class="btn btn-outline-danger">
            <i class="fas fa-globe"></i> Importar pelo Tapology
        </a>
    </div>

    <?php if (!empty($relatorio)): ?>
        <div class="card mb-4 border-<?= $temErro ? 'danger' : 'success' ?>">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>
                    <i class="fas fa-clipboard-check"></i>
                    Relatório da última importação
                </strong>
                <span class="badge bg-<?= $temErro ? 'danger' : 'success' ?>">
                    <?= $temErro ? 'ATENÇÃO' : 'CONCLUÍDA' ?>
                </span>
            </div>

            <div class="card-body p-0">
                <div style="max-height:360px; overflow:auto; background:#111; color:#e8e8e8; font-family:Consolas,Monaco,monospace; font-size:13px;">
                    <?php foreach ($relatorio as $linha): ?>
                        <?php
                            $classe = '';
                            if (strpos($linha, '❌') !== false) {
                                $classe = 'text-danger';
                            } elseif (strpos($linha, '✅') !== false) {
                                $classe = 'text-success';
                            } elseif (strpos($linha, '⚠️') !== false) {
                                $classe = 'text-warning';
                            } elseif (strpos($linha, '⏭️') !== false) {
                                $classe = 'text-info';
                            } elseif (strpos($linha, '🆕') !== false) {
                                $classe = 'text-primary';
                            }
                        ?>
                        <div class="px-3 py-1 <?= $classe ?>">
                            <?= htmlspecialchars($linha) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= $this->url('/importacao/processar') ?>">

        <!-- EVENTO -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-danger text-white">
                <strong>
                    <i class="fas fa-calendar-alt"></i>
                    1. Evento
                </strong>
                <span class="float-end small">
                    obrigatório para importar o card
                </span>
            </div>

            <div class="card-body">
                <select name="id_evento" class="form-select form-select-lg">
                    <option value="">-- Selecione o evento --</option>

                    <?php foreach ($eventos as $e): ?>
                        <option value="<?= (int)$e['id_evento'] ?>">
                            <?= htmlspecialchars($e['nome']) ?>
                            (<?= htmlspecialchars($e['data_evento']) ?>)
                            — <?= htmlspecialchars($e['slugs']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- ATLETAS -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <strong>
                    <i class="fas fa-user-friends"></i>
                    2. Atletas
                </strong>
                <span class="float-end small">
                    opcional
                </span>
            </div>

            <div class="card-body">

                <div class="alert alert-light border mb-3">
                    <strong>Use esta área para cadastrar ou atualizar atletas.</strong>
                    <br>
                    Se o atleta já existir, o sistema atualiza somente os campos preenchidos.
                    Se não existir, cria um novo cadastro.
                </div>

                <p class="mb-2">
                    <strong>Formato — 1 atleta por linha:</strong>
                </p>

                <div class="bg-light border rounded p-2 mb-3 small">
                    <code>
                        nome | sobrenome | apelido | sexo(M/F) | nacionalidade |
                        modalidade(P/A) | categoria | peso(kg) | altura(m) |
                        equipe | vitorias | derrotas | empates | tapology_url | sherdog_url
                    </code>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <div class="small text-muted">
                            <strong>Modalidade:</strong>
                            P = MMA Profissional · A = MMA Amador
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">
                            <strong>Categoria:</strong>
                            nome da categoria ou peso em kg.
                        </div>
                    </div>
                </div>

                <textarea
                    name="roster"
                    rows="8"
                    class="form-control font-monospace"
                    placeholder="Isabela|Oliveira||F|Brasileira|P|Peso Leve|70|1.69|Equipe|1|0|0|https://www.tapology.com/fightcenter/fighters/492226-isabela-oliveira|
Arthur|Portes|Zig|M|Brasileiro|A|Peso Meio-Médio|70|1.70|Equipe|3|1|0|https://www.tapology.com/fightcenter/fighters/274412-arthur-portes|"
                ></textarea>

                <div class="form-text mt-2">
                    A URL do Tapology é opcional nesta importação manual.
                </div>
            </div>
        </div>

        <!-- CARD -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <strong>
                    <i class="fas fa-fist-raised"></i>
                    3. Card de Lutas
                </strong>
                <span class="float-end small">
                    importa as lutas já com resultado
                </span>
            </div>

            <div class="card-body">

                <p class="mb-2">
                    <strong>Formato — 1 luta por linha:</strong>
                </p>

                <div class="bg-light border rounded p-2 mb-3 small">
                    <code>
                        ordem | vermelho | azul | vencedor(V/A/E) | método |
                        round | tempo(mm:ss) | rounds | modalidade |
                        categoria | árbitro | cinturão(0/1)
                    </code>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <strong><i class="fas fa-users"></i> Atletas</strong>
                            <p class="small text-muted mb-0 mt-1">
                                Use o ID do atleta, por exemplo <code>1809</code>,
                                ou nome completo.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <strong><i class="fas fa-trophy"></i> Resultado</strong>
                            <p class="small text-muted mb-0 mt-1">
                                V = vermelho · A = azul · E = empate/NC.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <strong><i class="fas fa-layer-group"></i> Modalidade</strong>
                            <p class="small text-muted mb-0 mt-1">
                                P = profissional · A = amador ·
                                ou informe o ID diretamente.
                            </p>
                        </div>
                    </div>
                </div>

                <ul class="small text-muted">
                    <li>
                        <strong>Método:</strong>
                        <?= htmlspecialchars(implode(', ', array_map(fn($m) => $m['sigla'], $metodos))) ?>
                    </li>
                    <li>
                        <strong>Categoria:</strong>
                        nome, ID ou peso em kg (ex.: <code>61kg</code>).
                    </li>
                    <li>
                        <strong>Árbitro:</strong>
                        se não existir, será cadastrado automaticamente.
                    </li>
                    <li>
                        <strong>MMA Amador:</strong>
                        decisão UD/SD/MD sem tempo informado recebe automaticamente
                        <code>3:00</code>.
                    </li>
                    <li>
                        Linhas iniciadas por <code>#</code> são ignoradas.
                    </li>
                </ul>

                <textarea
                    name="card"
                    rows="14"
                    class="form-control font-monospace"
                    placeholder="1|Fulano da Silva,Fulaninho,M|Ciclano Souza,,M|V|SUB|1|2:18|3||Peso Pena|Anderson Ullysess|0
2|1631|1809|V|KO|2|1:30|3|5|71kg|Anderson Ullysess|0
3|1811|1440|A|UD|3||3|1|61kg|Anderson Ullysess|0"
                ></textarea>
            </div>
        </div>

        <!-- AÇÕES -->
        <div class="card shadow-sm mb-4">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">

                <div>
                    <strong>
                        <i class="fas fa-database"></i>
                        Pronto para importar
                    </strong>
                    <div class="small text-muted">
                        A operação é feita em uma única transação.
                        Se ocorrer erro, os dados da operação são revertidos.
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a
                        href="<?= $this->url('/tapology') ?>"
                        class="btn btn-outline-secondary"
                    >
                        <i class="fas fa-globe"></i>
                        Usar Tapology
                    </a>

                    <button type="submit" class="btn btn-danger btn-lg">
                        <i class="fas fa-upload"></i>
                        Processar Importação
                    </button>
                </div>

            </div>
        </div>

    </form>

</div>
