<div class="container-fluid p-4">
    <h2 class="mb-3"><i class="fas fa-bolt"></i> Importação em Massa</h2>
    <p class="text-muted">Cole os dados de um evento inteiro (atletas + card de lutas com resultado) e cadastre tudo de uma vez, em vez de formulário por formulário.</p>

    <?php
    $temErro = !empty($relatorio) && strpos(implode('', $relatorio), '❌') !== false;
    $modoAnaliseNoRelatorio = !empty($relatorio) && strpos(implode('', $relatorio), 'MODO ANÁLISE') !== false;
    $corBorda = $temErro ? 'danger' : ($modoAnaliseNoRelatorio ? 'warning' : 'success');
    ?>
    <?php if (!empty($relatorio)): ?>
        <div class="card mb-4 border-<?= $corBorda ?>">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><?= $modoAnaliseNoRelatorio ? '🔍 Prévia da importação (nada foi gravado ainda)' : 'Relatório da última importação' ?></span>
            </div>
            <div class="card-body" style="max-height:400px; overflow:auto; background:#111; color:#0f0; font-family: monospace; font-size: 13px;">
                <?php foreach ($relatorio as $linha): ?>
                    <div><?= htmlspecialchars($linha) ?></div>
                <?php endforeach; ?>
            </div>
            <?php if ($modoAnaliseNoRelatorio && !$temErro): ?>
                <div class="card-footer bg-warning-subtle">
                    Confira as linhas <strong>🟡 (novo)</strong> e <strong>🟢 (já existente)</strong> acima. Se estiver tudo certo,
                    role até o fim do formulário e clique em <strong>"✅ Confirmar e Importar"</strong> — os campos abaixo já
                    continuam preenchidos com o que você colou.
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= $this->url('/importacao/processar') ?>" id="form-importacao">

        <div class="card mb-4">
            <div class="card-header bg-danger text-white">1. Evento (obrigatório só se for importar o card de lutas)</div>
            <div class="card-body">
                <select name="id_evento" class="form-select">
                    <option value="">-- Selecione o evento --</option>
                    <?php foreach ($eventos as $e): ?>
                        <option value="<?= $e['id_evento'] ?>" <?= (!empty($formAnterior['id_evento']) && $formAnterior['id_evento'] == $e['id_evento']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($e['nome']) ?> (<?= $e['data_evento'] ?>) — <?= htmlspecialchars($e['slugs']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-dark text-white">2. Atletas (opcional — enriquece/atualiza perfis: foto, link tapology, equipe, etc.)</div>
            <div class="card-body">
                <p class="mb-2"><strong>Formato (1 por linha), campos separados por <code>|</code>:</strong></p>
                <code class="d-block mb-2">nome | sobrenome | apelido | sexo(M/F) | nacionalidade | modalidade(P/A) | categoria | peso(kg) | altura(m) | equipe | vitorias | derrotas | empates | tapology_url | sherdog_url</code>
                <ul class="small text-muted">
                    <li>Só "nome" é obrigatório. Se o atleta já existir (<strong>agora casado primeiro pela URL do Tapology, e só depois pelo nome</strong>), os dados são <u>atualizados</u> em vez de duplicados.</li>
                    <li><strong>modalidade</strong>: P = MMA Profissional (id 1), A = MMA Amador (id 2) — copie do "Pro MMA Record" / "Amateur MMA Record" do Tapology.</li>
                    <li><strong>categoria</strong>: nome da categoria de peso (ex: "Peso Leve") — usa a modalidade + sexo pra achar a certa.</li>
                    <li><strong>vitorias/derrotas/empates</strong>: cartel (profissional OU amador, o que estiver no perfil do Tapology).</li>
                    <li>Equipe é criada automaticamente se não existir.</li>
                </ul>
                <textarea name="roster" rows="8" class="form-control" placeholder="Isabela|Oliveira||F|Brasileira|P|Peso Leve|70|1.69|GAEA Project|1|0|0|https://www.tapology.com/fightcenter/fighters/492226-isabela-oliveira|
Arthur|Portes|Zig|M|Brasileiro|A|Peso Meio-Médio|70|1.70|IronBrothers|3|1|0|https://www.tapology.com/fightcenter/fighters/274412-arthur-portes|"><?= htmlspecialchars($formAnterior['roster'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-dark text-white">3. Card de Lutas (o principal — cria as lutas já com resultado)</div>
            <div class="card-body">
                <p class="mb-2"><strong>Formato (1 por linha), campos separados por <code>|</code>:</strong></p>
                <code class="d-block mb-2">ordem | vermelho(URL_tapology_ou_ID_ou_nome,apelido,sexo) | azul(idem) | vencedor(V/A/E) | metodo(sigla) | round | tempo(mm:ss) | num_rounds | modalidade(P/A/ID) | categoria | arbitro | vale_cinturao(0/1)</code>
                <ul class="small text-muted">
                    <li>
                        <strong>vermelho/azul</strong> aceita, nessa ordem de prioridade:
                        <br>1) <strong>link do Tapology do atleta</strong> (ex: <code>https://www.tapology.com/fightcenter/fighters/492226-isabela-oliveira</code>) — identificação à prova de homônimo; se o atleta não existir ainda, o sistema <strong>busca o perfil sozinho</strong> e já cria com categoria, equipe e cartel preenchidos;
                        <br>2) <strong>ID do atleta já cadastrado</strong> (ex: <code>1809</code>);
                        <br>3) <code>nome,apelido,sexo</code> pra um atleta novo/existente por nome.
                    </li>
                    <li><strong>vencedor</strong>: V = vermelho venceu, A = azul venceu, E = empate/no contest</li>
                    <li><strong>metodo (sigla)</strong>: <?= implode(', ', array_map(fn($m) => $m['sigla'], $metodos)) ?></li>
                    <li><strong>modalidade</strong>: P=MMA Profissional(1), A=MMA Amador(2), ou o ID numérico direto (ex: 5=Kickboxing). Em branco = usa a modalidade do evento selecionado acima.</li>
                    <li><strong>categoria</strong>: nome (ex: "Peso Pena"), ID numérico, ou <strong>peso em kg</strong> (ex: "61kg") — nesse caso o sistema acha sozinho a categoria certa pela faixa de peso/sexo/modalidade.</li>
                    <li><strong>arbitro</strong>: nome do árbitro central. Se não existir no banco, é criado automaticamente. Deixe em branco se não informado.</li>
                    <li>Se o <strong>método for decisão (UD/SD/MD)</strong>, a luta for do <strong>evento MMA Amador</strong> e o <strong>tempo</strong> ficar em branco, o sistema preenche automaticamente com <strong>3:00</strong>.</li>
                    <li>Linhas iniciadas com <code>#</code> são ignoradas (comentários).</li>
                </ul>
                <textarea name="card" rows="14" class="form-control" placeholder="1|https://www.tapology.com/fightcenter/fighters/309287-luiz-gustavo-rossi|Jonathan Willy,,M|V|SUB|1|1:09|3|2|Peso Leve|Eduardo Mello|0
2|1631|1809|V|KO|2|1:30|3|5|71kg|Anderson Ullysess|0
3|1811|1440|A|UD|3||3|1|61kg|Anderson Ullysess|0"><?= htmlspecialchars($formAnterior['card'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="d-flex gap-2 align-items-center flex-wrap">
            <button type="submit" name="modo_analise" value="1" class="btn btn-outline-warning btn-lg">
                <i class="fas fa-magnifying-glass"></i> 🔍 Analisar (não grava nada)
            </button>
            <button type="submit" class="btn btn-danger btn-lg">
                <i class="fas fa-upload"></i> ✅ Confirmar e Importar
            </button>
            <span class="text-muted small">
                Recomendado: clique em <strong>Analisar</strong> primeiro pra conferir quem vai ser criado/atualizado,
                só depois em <strong>Confirmar e Importar</strong>.
            </span>
        </div>
    </form>
</div>
