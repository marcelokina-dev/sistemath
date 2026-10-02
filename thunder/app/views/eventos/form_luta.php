<?php
// Determina se é edição ou novo cadastro
$isEdit = isset($luta['id_luta']) && !empty($luta['id_luta']);
$action = $isEdit ? BASE_URL . '/eventos/update-luta' : BASE_URL . '/eventos/salvar-luta';

// Define a ordem: se for edição usa a da luta, se for novo usa a próxima sugerida
$ordemExibicao = $isEdit ? ($luta['ordem_card'] ?? 0) : ($proximaOrdem ?? 1);
?>

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
</style>

<form action="<?= $action ?>" method="POST" id="formNovaLuta">
    <input type="hidden" name="id_evento" value="<?= $evento['id_evento'] ?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id_luta" value="<?= $luta['id_luta'] ?>">
    <?php endif; ?>

    <div class="row mb-4">
<div class="col-md-3">
    <label class="small fw-bold text-uppercase">ORDEM NO CARD</label>
    <input type="number" name="ordem_card" class="form-control" value="<?= $ordemExibicao ?>" min="1">
</div>
        <div class="col-md-3">
            <label class="small fw-bold text-uppercase">Nº de Rounds</label>
            <select name="num_rounds" class="form-control">
                <?php $nRounds = $luta['num_rounds'] ?? ''; ?>
                <option value="1" <?= ($isEdit && $nRounds == 1) ? 'selected' : '' ?>>1 Round</option>
                <option value="2" <?= ($isEdit && $nRounds == 2) ? 'selected' : '' ?>>2 Rounds</option>
                <option value="3" <?= ($isEdit && $nRounds == 3) ? 'selected' : '' ?>>3 Rounds</option>
                <option value="5" <?= ($isEdit && $nRounds == 5) ? 'selected' : '' ?>>5 Rounds</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="small fw-bold text-uppercase">Duração (Minutos)</label>
            <input type="text" name="duracao_round" class="form-control" placeholder="00:05:00"
                value="<?= $isEdit ? ($luta['duracao_round'] ?? '00:05:00') : '00:05:00' ?>">
        </div>
        <div class="col-md-3 text-center">
            <label class="d-block small fw-bold text-uppercase">🏆 Cinturão</label>
            <input type="checkbox" name="vale_cinturao" value="1" style="transform: scale(1.5);" 
                <?= ($isEdit && isset($luta['vale_cinturao']) && $luta['vale_cinturao'] == 1) ? 'checked' : '' ?>>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="p-3 border rounded bg-light border-left-primary shadow-sm">
                <label class="text-primary font-weight-bold small text-uppercase">🔵 Corner Azul</label>
                <select name="id_atleta_azul" id="atleta_azul" class="form-control select2-atleta" required>
                    <option value="">Buscar por nome, apelido, equipe ou categoria...</option>
                    <?php foreach ($atletas as $a): ?>
                        <?= montarOpcaoAtleta($a, $isEdit && $luta['id_atleta_azul'] == $a['id_atleta']) ?>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="p-3 border rounded bg-light border-right-danger shadow-sm">
                <label class="text-danger font-weight-bold small text-uppercase">🔴 Corner Vermelho</label>
                <select name="id_atleta_vermelho" id="atleta_vermelho" class="form-control select2-atleta" required <?= $isEdit ? '' : 'disabled' ?>>
                    <option value="">Selecione o azul primeiro...</option>
                    <?php foreach ($atletas as $a): ?>
                        <?= montarOpcaoAtleta($a, $isEdit && $luta['id_atleta_vermelho'] == $a['id_atleta']) ?>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <?php
    /**
     * Monta uma <option> rica pro seletor de atleta: nome, apelido, categoria de
     * peso, equipe, cartel e o ID (#123) — tudo visível, pra nunca confundir dois
     * atletas de nome parecido. data-foto/categoria/equipe/cartel alimentam o
     * template visual do Select2 (com miniatura) feito no script abaixo.
     */
    function montarOpcaoAtleta($a, $selecionado = false)
    {
        $sexo = $a['sexo'] ?? 'M';
        $nomeCompleto = trim(($a['nome'] ?? '') . (!empty($a['apelido']) ? " '" . $a['apelido'] . "'" : '') . ' ' . ($a['sobrenome'] ?? ''));
        $categoria = $a['nome_categoria'] ?? 'Sem categoria';
        $equipe = $a['nome_equipe'] ?? 'Sem equipe';
        $cartel = ($a['vitorias'] ?? 0) . 'V-' . ($a['derrotas'] ?? 0) . 'D-' . ($a['empates'] ?? 0) . 'E';
        $foto = !empty($a['foto']) ? BASE_URL . '/uploads/atletas/' . $a['foto'] : BASE_URL . '/img/sem-foto.png';

        // Texto puro: o que aparece já fechado e no que o Select2 busca por texto
        $label = "{$nomeCompleto} ({$sexo}) — {$categoria} — {$equipe} — {$cartel} — #{$a['id_atleta']}";

        $sel = $selecionado ? 'selected' : '';
        return '<option value="' . $a['id_atleta'] . '"'
            . ' data-sexo="' . htmlspecialchars($sexo) . '"'
            . ' data-foto="' . htmlspecialchars($foto) . '"'
            . ' data-categoria="' . htmlspecialchars($categoria) . '"'
            . ' data-equipe="' . htmlspecialchars($equipe) . '"'
            . ' data-cartel="' . htmlspecialchars($cartel) . '"'
            . ' data-id="' . $a['id_atleta'] . '"'
            . ($sel ? ' selected' : '') . '>'
            . htmlspecialchars($label) . '</option>';
    }
    ?>

    <div class="row">
        <div class="col-md-4">
            <label class="small fw-bold text-uppercase">Modalidade</label>
            <select name="id_modalidade" id="id_modalidade" class="form-control" required>
                <option value="">Selecione...</option>
                <?php foreach ($modalidades as $m): ?>
                    <option value="<?= $m['id_modalidade'] ?>" <?= ($isEdit && ($luta['id_modalidade'] ?? '') == $m['id_modalidade']) ? 'selected' : '' ?>>
                        <?= $m['nome'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="small fw-bold text-uppercase">Categoria de Peso</label>
            <select name="id_categoria_peso" id="id_categoria_peso" class="form-control" required <?= $isEdit ? '' : 'disabled' ?>>
                <?php if($isEdit): ?>
                    <option value="<?= $luta['id_categoria_peso'] ?>" selected>Carregando...</option>
                <?php else: ?>
                    <option value="">Aguardando seleção...</option>
                <?php endif; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="small fw-bold text-uppercase">Árbitro Central</label>
            <select name="id_arbitro" class="form-control select2">
                <option value="">A definir...</option>
                <?php foreach ($arbitros as $arb): ?>
                    <option value="<?= $arb['id_arbitro'] ?>" <?= ($isEdit && ($luta['id_arbitro'] ?? '') == $arb['id_arbitro']) ? 'selected' : '' ?>>
                        <?= $arb['nome'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="mt-5 text-center">
        <hr>
        <a href="<?= BASE_URL ?>/eventos/lutas?id=<?= $evento['id_evento'] ?>"
            class="btn btn-secondary btn-lg px-4 mr-2 shadow-sm text-uppercase fw-bold">Cancelar</a>
        <button type="submit" class="btn btn-danger btn-lg px-5 shadow text-uppercase fw-bold"
            style="background-color: #d93737;">
            <?= $isEdit ? 'Atualizar Luta' : 'Salvar e Casar Luta' ?>
        </button>
    </div>
</form>

<script>
$(document).ready(function () {
    // Variável para controlar se estamos no carregamento inicial da edição
    let estaCarregandoEdicao = <?= $isEdit ? 'true' : 'false' ?>;

    // Monta o "cartão" de cada atleta na lista do Select2 (foto + categoria + equipe + cartel)
    function formatarAtleta(opcao) {
        if (!opcao.id) return opcao.text;
        var $el = $(opcao.element);
        var foto = $el.data('foto');
        var categoria = $el.data('categoria');
        var equipe = $el.data('equipe');
        var cartel = $el.data('cartel');
        var id = $el.data('id');
        // Nome + apelido (sem o resto do texto bruto, que fica como fallback de busca)
        var nomeExibicao = opcao.text.split(' — ')[0];

        var $linha = $(
            '<div class="d-flex align-items-center py-1">' +
                '<img src="' + foto + '" style="width:32px;height:32px;object-fit:cover;border-radius:50%;margin-right:8px;flex-shrink:0;" onerror="this.src=\'<?= BASE_URL ?>/img/sem-foto.png\'">' +
                '<div style="line-height:1.15;">' +
                    '<div class="fw-bold">' + nomeExibicao + ' <span class="text-muted">#' + id + '</span></div>' +
                    '<div class="small text-muted">' + categoria + ' · ' + equipe + ' · ' + cartel + '</div>' +
                '</div>' +
            '</div>'
        );
        return $linha;
    }

    function initSelect2() {
        if ($.fn.select2) {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
            $('.select2-atleta').select2({
                theme: 'bootstrap-5',
                width: '100%',
                templateResult: formatarAtleta,
                templateSelection: function (opcao) {
                    return opcao.id ? opcao.text.split(' — ')[0] : opcao.text;
                },
                matcher: function (params, data) {
                    // Busca por qualquer parte do texto completo: nome, apelido, equipe, categoria ou #ID
                    if (!params.term) return data;
                    var termo = params.term.toLowerCase().replace('#', '');
                    var texto = (data.text || '').toLowerCase();
                    if (texto.indexOf(termo) > -1) return data;
                    return null;
                }
            });
        }
    }
    initSelect2();

    function carregarCategorias(id_mod, id_selecionado = null) {
        var $cat = $('#id_categoria_peso');
        var azulElement = $('#atleta_azul').find(':selected');
        var sexoAtleta = azulElement.data('sexo');

        if (!id_mod || !sexoAtleta) {
            if(!estaCarregandoEdicao) {
                $cat.html('<option value="">Selecione Atleta e Modalidade...</option>').prop('disabled', true);
            }
            return;
        }

        $.ajax({
            url: '<?= BASE_URL ?>/eventos/buscarCategoriasPorModalidade',
            type: 'GET',
            data: { id_modalidade: id_mod, sexo: sexoAtleta },
            dataType: 'json',
            success: function (data) {
                var options = '<option value="">Selecione a Categoria...</option>';
                if (data && data.length > 0) {
                    $.each(data, function (i, item) {
                        var selected = (id_selecionado == item.id_categoria_peso) ? 'selected' : '';
                        var pesoExibicao = item.peso_max ? ' (até ' + item.peso_max + 'kg)' : '';
                        options += `<option value="${item.id_categoria_peso}" ${selected}>${item.nome}${pesoExibicao}</option>`;
                    });
                    $cat.html(options).prop('disabled', false);
                } else {
                    $cat.html('<option value="">Nenhuma categoria encontrada</option>').prop('disabled', true);
                }
            }
        });
    }

    $('#atleta_azul').on('change', function () {
        var azulId = $(this).val();
        var sexo = $(this).find(':selected').data('sexo');
        var $vermelho = $('#atleta_vermelho');

        // Se não for o boot da edição, recarrega categorias
        if (!estaCarregandoEdicao) {
            carregarCategorias($('#id_modalidade').val());
        }

        if (!azulId) {
            $vermelho.val('').prop('disabled', true).trigger('change');
            return;
        }

        $vermelho.prop('disabled', false);
        $vermelho.find('option').each(function () {
            var sOpcao = $(this).data('sexo');
            var vermelhoId = $(this).val();

            if (vermelhoId !== "" && (sOpcao !== sexo || vermelhoId === azulId)) {
                $(this).prop('disabled', true).hide();
            } else {
                $(this).prop('disabled', false).show();
            }
        });

        // Só limpa o valor se o usuário estiver mudando manualmente
        if (!estaCarregandoEdicao) {
            $vermelho.val('').trigger('change');
        }

        if ($.fn.select2) {
            $vermelho.select2('destroy');
            initSelect2();
        }
    });

    $('#id_modalidade').on('change', function () {
        if (!estaCarregandoEdicao) {
            carregarCategorias($(this).val());
        }
    });

    // --- Lógica de Inicialização de Edição ---
    if (estaCarregandoEdicao) {
        var modId = $('#id_modalidade').val();
        var catId = "<?= $luta['id_categoria_peso'] ?? '' ?>";
        var vermelhoGravado = "<?= $luta['id_atleta_vermelho'] ?? '' ?>";

        // 1. Filtra o corner vermelho baseado no azul atual sem limpar o valor
        $('#atleta_azul').trigger('change');
        
        // 2. Garante que o valor gravado do vermelho permaneça
        $('#atleta_vermelho').val(vermelhoGravado).trigger('change');

        // 3. Carrega as categorias selecionando a correta
        if (modId) {
            carregarCategorias(modId, catId);
        }

        // Libera os eventos normais após um breve delay
        setTimeout(function() {
            estaCarregandoEdicao = false;
        }, 800);
    }

    $('#formNovaLuta').on('submit', function () {
        $(this).find(':disabled').removeAttr('disabled');
    });
});
</script>