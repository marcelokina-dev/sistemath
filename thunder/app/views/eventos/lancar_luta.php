<div class="content-wrapper">
    <div class="content-header p-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 fw-bold text-uppercase mb-0">Lançar Resultado Oficial</h1>
                <a href="<?= BASE_URL ?>/eventos/lutas?id=<?= $luta['id_evento'] ?>" class="btn btn-outline-secondary fw-bold text-uppercase px-4">
                    <i class="fas fa-arrow-left me-2"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <section class="content px-4">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-9">

                    <form action="<?= BASE_URL ?>/eventos/salvar-resultado" method="POST" id="formResultado">
                        
                        <input type="hidden" name="id_luta" value="<?= $luta['id_luta'] ?>">
                        <input type="hidden" name="id_evento" value="<?= $luta['id_evento'] ?>">

                        <div class="card shadow-sm border-0 mb-4 overflow-hidden">
                            <div class="card-header bg-dark text-center py-3">
                                <small class="text-light text-uppercase fw-bold">
                                    <?= htmlspecialchars($luta['categoria_nome'] ?? 'Categoria') ?> #<?= $luta['id_luta'] ?>
                                </small>
                                <div class="d-flex justify-content-center align-items-center mt-2 gap-3">
                                    <h2 class="h4 fw-bold text-primary mb-0"><?= htmlspecialchars($luta['atleta_azul_nome']) ?></h2>
                                    <span class="badge bg-secondary rounded-circle px-2 py-1">VS</span>
                                    <h2 class="h4 fw-bold text-danger mb-0"><?= htmlspecialchars($luta['atleta_vermelho_nome']) ?></h2>
                                </div>
                            </div>
                            
                            <div class="card-body p-4 bg-white">
                                <div class="border border-warning border-dashed rounded p-3 text-center mb-4 bg-light">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input" type="checkbox" name="vale_cinturao" id="valeCinturao" value="1" <?= ($luta['vale_cinturao'] == 1) ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold text-uppercase ms-2" for="valeCinturao">🏆 Esta luta vale cinturão?</label>
                                    </div>
                                </div>

                                <h6 class="text-muted text-uppercase fw-bold mb-3 small">Vencedor e Autoridade</h6>
                                <div class="row mb-4">
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold small text-uppercase">Declarar Vencedor</label>
                                        <select name="id_vencedor" id="declarar_vencedor" class="form-select form-select-lg border-2" required>
                                            <option value="">Escolha o vencedor...</option>
                                            <option value="<?= $luta['id_atleta_azul'] ?>">CANTO AZUL: <?= $luta['atleta_azul_nome'] ?></option>
                                            <option value="<?= $luta['id_atleta_vermelho'] ?>">CANTO VERMELHO: <?= $luta['atleta_vermelho_nome'] ?></option>
                                            <option value="empate">EMPATE / NO CONTEST</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold small text-uppercase">Árbitro Central</label>
                                        <select name="id_arbitro" class="form-select form-select-lg" required>
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
                                        <input type="number" name="ordem_card" class="form-control form-control-lg text-center" value="<?= $luta['ordem_card'] ?>">
                                    </div>
                                </div>

                                <h6 class="text-muted text-uppercase fw-bold mb-3 small">Informações Técnicas</h6>
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase">Método</label>
                                        <select name="metodo" id="metodo_combate" class="form-select form-select-lg" required>
                                            <option value="">Como terminou?</option>
                                            <option value="KO">Nocaute (KO)</option>
                                            <option value="TKO">Nocaute Técnico (TKO)</option>
                                            <option value="Finalização">Finalização</option>
                                            <option value="Decisão Unânime">Decisão Unânime</option>
                                            <option value="Decisão Dividida">Decisão Dividida</option>
                                            <option value="Empate">Empate</option>
                                            <option value="No Contest">No Contest</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-uppercase">Round</label>
                                        <input type="number" name="round_final" class="form-control form-control-lg text-center" placeholder="1" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-uppercase">Tempo</label>
                                        <input type="text" name="tempo_final" id="tempo_final" class="form-control form-control-lg text-center" placeholder="0:00" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold text-uppercase py-3">
                                    Publicar Resultado Oficial
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
document.addEventListener('DOMContentLoaded', function() {
    const campoVencedor = document.getElementById('declarar_vencedor');
    const campoMetodo = document.getElementById('metodo_combate');

    campoVencedor.addEventListener('change', function() {
        const valorVencedor = this.value;
        const opcoesMetodo = campoMetodo.options;

        if (valorVencedor === 'empate') {
            // Se escolheu Empate, só permite métodos de empate
            for (let i = 0; i < opcoesMetodo.length; i++) {
                const opt = opcoesMetodo[i];
                if (opt.value === 'Empate' || opt.value === 'No Contest' || opt.value === "") {
                    opt.style.display = 'block';
                    opt.disabled = false;
                } else {
                    opt.style.display = 'none';
                    opt.disabled = true;
                }
            }
            campoMetodo.value = 'Empate';
        } else {
            // Se escolheu vencedor, remove opção de empate do método
            for (let i = 0; i < opcoesMetodo.length; i++) {
                const opt = opcoesMetodo[i];
                if (opt.value === 'Empate' || opt.value === 'No Contest') {
                    opt.style.display = 'none';
                    opt.disabled = true;
                } else {
                    opt.style.display = 'block';
                    opt.disabled = false;
                }
            }
            if (campoMetodo.value === 'Empate') campoMetodo.value = "";
        }
    });
});
</script>