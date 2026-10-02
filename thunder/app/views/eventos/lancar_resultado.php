<style>
    /* Melhora o contraste dos campos e labels */
    .form-label {
        color: #212529 !important;
        font-weight: 700 !important;
        margin-bottom: 0.3rem;
    }

    .form-control,
    .form-select {
        border: 1px solid #adb5bd !important;
        color: #000 !important;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }

    /* Ajuste Select2 para legibilidade */
    .select2-container--default .select2-selection--single {
        height: 38px !important;
        border: 1px solid #adb5bd !important;
        padding-top: 3px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #000 !important;
    }

    .img-preview {
        height: 160px;
        width: 160px;
        object-fit: cover;
        border-radius: 10px;
        border: 2px solid #dee2e6;
    }

    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .input-group-text {
        border: 1px solid #adb5bd !important;
        font-weight: bold;
    }

    /* Ajuste de tamanho de fonte para os campos de resultado */
    #tempo_final,
    #ordem,
    #arbitro,
    #declarar_vencedor,
    #declarar_vencedor option,
    #metodo_combate,
    #metodo_combate option {
        font-size: 17px !important; /* Fonte reduzida para caber o nome completo */
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<div class="content-wrapper">
    <div class="content-header p-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 fw-bold text-uppercase mb-0">Lançar Resultado Oficial</h1>
                <a href="<?= BASE_URL ?>/eventos/lutas?id=<?= $luta['id_evento'] ?>"
                    class="btn btn-outline-secondary fw-bold text-uppercase px-4">
                    <i class="fas fa-arrow-left me-2"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <section class="content px-4">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-9">

                    <form action="<?= BASE_URL ?>/eventos/salvar-resultado" method="POST">

                        <input type="hidden" name="id_luta" value="<?= $luta['id_luta'] ?>">
                        <input type="hidden" name="id_evento" value="<?= $luta['id_evento'] ?>">

                        <div class="card shadow-sm border-0 mb-4 overflow-hidden">
                            <div class="card-header bg-dark text-center py-3">
                                <small class="text-light text-uppercase fw-bold">
                                    <?= htmlspecialchars($luta['categoria_nome'] ?? 'Categoria') ?>
                                    #<?= $luta['id_luta'] ?>
                                </small>
                                <div class="d-flex justify-content-center align-items-center mt-2 gap-3">
                                    <h2 class="h4 fw-bold text-primary mb-0">
                                        <?= htmlspecialchars($luta['atleta_azul_nome'] ?? 'Atleta Azul') ?>
                                    </h2>
                                    <span class="badge bg-secondary rounded-circle px-2 py-1">VS</span>
                                    <h2 class="h4 fw-bold text-danger mb-0">
                                        <?= htmlspecialchars($luta['atleta_vermelho_nome'] ?? 'Atleta Vermelho') ?>
                                    </h2>
                                </div>
                            </div>

                            <div class="card-body p-4 bg-white">
                                <div class="border border-warning border-dashed rounded p-3 text-center mb-4 bg-light">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input" type="checkbox" name="vale_cinturao"
                                            id="valeCinturao" value="1" <?= (isset($luta['vale_cinturao']) && $luta['vale_cinturao'] == 1) ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold text-uppercase ms-2"
                                            for="valeCinturao">🏆 Esta luta vale cinturão?</label>
                                    </div>
                                </div>

                                <h6 class="text-muted text-uppercase fw-bold mb-3 small">Vencedor e Autoridade</h6>
                                <div class="row mb-4">
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold small text-uppercase">Declarar Vencedor</label>

                                        <select name="id_vencedor" id="declarar_vencedor" class="form-select form-select-lg border-2" required>
                                            <option value="">Escolha o vencedor...</option>
                                            <option value="<?= $luta['id_atleta_azul'] ?>" <?= ($luta['id_vencedor'] == $luta['id_atleta_azul']) ? 'selected' : '' ?>>
                                                Blue: <?= htmlspecialchars($luta['atleta_azul_nome']) ?> <?= htmlspecialchars($luta['atleta_azul_sobrenome']) ?>
                                            </option>
                                            <option value="<?= $luta['id_atleta_vermelho'] ?>" <?= ($luta['id_vencedor'] == $luta['id_atleta_vermelho']) ? 'selected' : '' ?>>
                                                Red: <?= htmlspecialchars($luta['atleta_vermelho_nome']) ?> <?= htmlspecialchars($luta['atleta_vermelho_sobrenome']) ?>
                                            </option>
                                            <option value="empate" <?= ($luta['status'] == 'realizada' && empty($luta['id_vencedor']) && isset($luta['id_metodo']) && $luta['id_metodo'] == 9) ? 'selected' : '' ?>>
                                                Empate
                                            </option>
                                            <option value="empate" <?= ($luta['status'] == 'realizada' && empty($luta['id_vencedor']) && isset($luta['id_metodo']) && $luta['id_metodo'] == 10) ? 'selected' : '' ?>>
                                                No Contest (Sem Resultado)
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-5">
                                        <label class="form-label fw-bold small text-uppercase">Árbitro Central</label>
                                        <select name="id_arbitro" id="arbitro" class="form-select form-select-lg" required>
                                            <option value="">Selecione...</option>
                                            <?php foreach ($arbitros as $arb): ?>
                                                <option value="<?= $arb['id_arbitro'] ?>" <?= ($luta['id_arbitro'] == $arb['id_arbitro']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($arb['nome']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label fw-bold small text-uppercase">Ordem</label>
                                        <input type="number" name="ordem_card" id="ordem" class="form-control form-control-lg text-center" value="<?= $luta['ordem_card'] ?? 1 ?>">
                                    </div>
                                </div>

                                <h6 class="text-muted text-uppercase fw-bold mb-3 small">Informações Técnicas</h6>
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase">Método</label>
                                        <select name="id_metodo" id="metodo_combate" class="form-select form-select-lg" required>
                                            <option value="">Como terminou?</option>
                                            <?php foreach ($metodosVitoria as $m): ?>
                                                <option value="<?= $m['id_metodo'] ?>" data-sigla="<?= $m['sigla'] ?>" <?= ($luta['id_metodo'] == $m['id_metodo']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($m['nome']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-uppercase">Round</label>
                                        <input type="number" id="ordem" name="round_final" class="form-control form-control-lg text-center" value="<?= $luta['round_final'] ?? '' ?>" placeholder="1" min="1" max="5" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-uppercase">Tempo</label>
                                        <input type="text" name="tempo_final" id="tempo_final" class="form-control form-control-lg text-center" value="<?= $luta['tempo_final'] ?? '' ?>" placeholder="0:00" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold text-uppercase py-3 shadow">
                                    <i class="fas fa-check-circle me-2"></i> Publicar Resultado Oficial
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const campoVencedor = document.getElementById('declarar_vencedor');
        const campoMetodo = document.getElementById('metodo_combate');

        if (!campoVencedor || !campoMetodo) return;

        // IDs vindos do banco de dados para os métodos
        const ID_METODO_EMPATE = "9";
        const ID_METODO_NO_CONTEST = "10";

        // =========================================================================
        // LÓGICA 1: QUANDO ALTERA O VENCEDOR -> AJUSTA OS MÉTODOS
        // =========================================================================
        function sincronizarPorVencedor() {
            const textoVencedor = campoVencedor.options[campoVencedor.selectedIndex]?.text.toLowerCase() || "";
            const valorVencedor = campoVencedor.value;
            const opcoesMetodo = campoMetodo.options;

            // Se o vencedor selecionado por texto for "empate"
            if (textoVencedor.includes('empate')) {
                campoMetodo.value = ID_METODO_EMPATE;
                for (let i = 0; i < opcoesMetodo.length; i++) {
                    const opt = opcoesMetodo[i];
                    if (opt.value === ID_METODO_EMPATE || opt.value === "") {
                        opt.hidden = false; opt.disabled = false;
                    } else {
                        opt.hidden = true; opt.disabled = true;
                    }
                }
            } 
            // Se o vencedor selecionado por texto for "no contest"
            else if (textoVencedor.includes('no contest')) {
                campoMetodo.value = ID_METODO_NO_CONTEST;
                for (let i = 0; i < opcoesMetodo.length; i++) {
                    const opt = opcoesMetodo[i];
                    if (opt.value === ID_METODO_NO_CONTEST || opt.value === "") {
                        opt.hidden = false; opt.disabled = false;
                    } else {
                        opt.hidden = true; opt.disabled = true;
                    }
                }
            } 
            // Se escolheu um Atleta Real (tem ID numérico válido)
            else if (valorVencedor !== "" && !isNaN(valorVencedor)) {
                for (let i = 0; i < opcoesMetodo.length; i++) {
                    const opt = opcoesMetodo[i];
                    if (opt.value === ID_METODO_EMPATE || opt.value === ID_METODO_NO_CONTEST) {
                        opt.hidden = true; opt.disabled = true;
                    } else {
                        opt.hidden = false; opt.disabled = false;
                    }
                }
                if (campoMetodo.value === ID_METODO_EMPATE || campoMetodo.value === ID_METODO_NO_CONTEST) {
                    campoMetodo.selectedIndex = 0;
                }
            } 
            else {
                limparFiltros(opcoesMetodo);
            }
        }

        // =========================================================================
        // LÓGICA 2: QUANDO ALTERA O MÉTODO -> AJUSTA O VENCEDOR
        // =========================================================================
        function sincronizarPorMetodo() {
            const valorMetodo = campoMetodo.value;
            const opcoesVencedor = campoVencedor.options;

            if (valorMetodo === ID_METODO_EMPATE) {
                definirSelectPorTexto(campoVencedor, 'empate');
                limparFiltros(opcoesVencedor); 
            } else if (valorMetodo === ID_METODO_NO_CONTEST) {
                definirSelectPorTexto(campoVencedor, 'no contest');
                limparFiltros(opcoesVencedor); 
            } else if (valorMetodo !== "") {
                for (let i = 0; i < opcoesVencedor.length; i++) {
                    const opt = opcoesVencedor[i];
                    const optText = opt.text.toLowerCase();
                    if (optText.includes('empate') || optText.includes('no contest')) {
                        opt.hidden = true; opt.disabled = true;
                    } else {
                        opt.hidden = false; opt.disabled = false;
                    }
                }

                const atualVencText = campoVencedor.options[campoVencedor.selectedIndex]?.text.toLowerCase() || "";
                if (atualVencText.includes('empate') || atualVencText.includes('no contest')) {
                    campoVencedor.selectedIndex = 0;
                }
            } else {
                limparFiltros(opcoesVencedor);
            }
        }

        // =========================================================================
        // FUNÇÕES AUXILIARES
        // =========================================================================
        function definirSelectPorTexto(selectElement, termo) {
            for (let i = 0; i < selectElement.options.length; i++) {
                if (selectElement.options[i].text.toLowerCase().includes(termo)) {
                    selectElement.selectedIndex = i;
                    break;
                }
            }
        }

        function limparFiltros(opcoes) {
            for (let i = 0; i < opcoes.length; i++) {
                opcoes[i].hidden = false;
                opcoes[i].disabled = false;
            }
        }

        campoVencedor.addEventListener('change', sincronizarPorVencedor);
        campoMetodo.addEventListener('change', sincronizarPorMetodo);

        // Inicialização segura
        if (campoMetodo.value === ID_METODO_EMPATE || campoMetodo.value === ID_METODO_NO_CONTEST) {
            limparFiltros(campoVencedor.options);
            sincronizarPorVencedor();
        } else {
            sincronizarPorVencedor();
        }

        // MÁSCARA DO TEMPO (MM:SS)
        const campoTempo = document.getElementById('tempo_final');
        if (campoTempo) {
            campoTempo.addEventListener('input', function (e) {
                let v = e.target.value.replace(/\D/g, '');
                if (v.length > 4) v = v.slice(0, 4);
                if (v.length > 2) {
                    e.target.value = v.slice(0, v.length - 2) + ':' + v.slice(v.length - 2);
                } else {
                    e.target.value = v;
                }
            });
        }
    });
</script>