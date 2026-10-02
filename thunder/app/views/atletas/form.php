<?php
/**
 * Formulário Atleta Completo - Layout Refinado e Contraste Ajustado
 */
$atleta = $atleta ?? [];
$isEdit = isset($atleta['id_atleta']);
// PADRONIZAÇÃO: Usando BASE_URL na Action
$action = $isEdit ? BASE_URL . '/atletas/update' : BASE_URL . '/atletas/store';
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

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

<form action="<?= $action ?>" method="POST" enctype="multipart/form-data">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id_atleta" value="<?= $atleta['id_atleta'] ?>">
        <input type="hidden" name="foto_atual" value="<?= $atleta['foto'] ?? '' ?>">
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header bg-dark text-white fw-bold text-uppercase small">Dados Pessoais</div>
        <div class="card-body row g-3">
            <div class="col-md-3 text-center border-end">
                <label class="form-label small text-uppercase d-block">Foto do Perfil</label>
                <?php
                // Define a foto padrão usando a URL
                $fotoPath = BASE_URL . '/uploads/atletas/sem-foto.png';

                if (!empty($atleta['foto'])) {
                    // PADRONIZAÇÃO: Usando BASE_PATH para verificação física
                    $arquivoNoDisco = BASE_PATH . '/uploads/atletas/' . $atleta['foto'];

                    if (file_exists($arquivoNoDisco)) {
                        $fotoPath = BASE_URL . '/uploads/atletas/' . $atleta['foto'];
                    }
                }
                ?>
                <img src="<?= $fotoPath ?>" id="preview-foto" class="img-thumbnail mb-2 img-preview">
                <input type="file" name="foto" class="form-control form-control-sm" accept="image/*"
                    onchange="previewImage(this)">
            </div>

            <div class="col-md-9 row g-3">
                <div class="col-md-5">
                    <label class="form-label small text-uppercase">Nome</label>
                    <input type="text" name="nome" id="nome" class="form-control"
                        value="<?= htmlspecialchars($atleta['nome'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-uppercase">Sobrenome</label>
                    <input type="text" name="sobrenome" id="sobrenome" class="form-control"
                        value="<?= htmlspecialchars($atleta['sobrenome'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-uppercase">Apelido</label>
                    <input type="text" name="apelido" class="form-control"
                        value="<?= htmlspecialchars($atleta['apelido'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-uppercase">CPF</label>
                    <input type="text" name="cpf" id="cpf" class="form-control" placeholder="000.000.000-00"
                        value="<?= htmlspecialchars($atleta['cpf'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-uppercase">RG</label>
                    <input type="text" name="rg" class="form-control"
                        value="<?= htmlspecialchars($atleta['rg'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-uppercase">Nacionalidade</label>
                    <input type="text" name="nacionalidade" class="form-control"
                        value="<?= htmlspecialchars($atleta['nacionalidade'] ?? 'Brasileiro(a)') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-uppercase">Nascimento</label>
                    <input type="date" name="data_nascimento" id="data_nascimento" class="form-control"
                        value="<?= $atleta['data_nascimento'] ?? '' ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-uppercase">Sexo</label>
                    <select name="sexo" id="sexo" class="form-select filter-peso" required>
                        <option value="" disabled <?= !isset($atleta['sexo']) ? 'selected' : '' ?>>Selecione...</option>
                        <option value="M" <?= ($atleta['sexo'] ?? '') == 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= ($atleta['sexo'] ?? '') == 'F' ? 'selected' : '' ?>>Feminino</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-uppercase">Status</label>
                    <select name="status" class="form-select">
                        <option value="ativo" <?= (($atleta['status'] ?? '') == 'ativo') ? 'selected' : '' ?>>Ativo
                        </option>
                        <option value="inativo" <?= (($atleta['status'] ?? '') == 'inativo') ? 'selected' : '' ?>>Inativo
                        </option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 border-start border-primary border-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
            <span class="fw-bold text-uppercase small">Graduações e Faixas</span>
            <button type="button" class="btn btn-light btn-sm fw-bold" onclick="addGraduacao()">
                <i class="fas fa-plus"></i> ADICIONAR
            </button>
        </div>
        <div class="card-body">
            <div id="container-graduacoes">
                <?php
                $graduacoes_list = $graduacoesAtleta ?? [['id_modalidade' => '', 'id_graduacao' => '']];
                foreach ($graduacoes_list as $index => $ag): ?>
                    <div class="row g-2 mb-2 item-graduacao">
                        <div class="col-md-5">
                            <select name="graduacoes[<?= $index ?>][id_modalidade]" class="form-select"
                                onchange="filterGraduacoes(this)" required>
                                <option value="">Modalidade...</option>
                                <?php foreach ($modalidades as $m): ?>
                                    <option value="<?= $m['id_modalidade'] ?>" <?= ($ag['id_modalidade'] == $m['id_modalidade']) ? 'selected' : '' ?>><?= $m['nome'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <select name="graduacoes[<?= $index ?>][id_graduacao]" class="form-select select-graduacao"
                                data-selected="<?= $ag['id_graduacao'] ?>" required>
                                <option value="">Faixa...</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger w-100" onclick="removeGraduacao(this)">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="card mb-4 border-start border-danger border-4">
        <div class="card-header bg-danger text-white fw-bold text-uppercase small">Perfil de Luta e Cartel</div>
        <div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label small text-uppercase">Slug (URL)</label>
                <input type="text" name="slug" id="slug" class="form-control"
                    value="<?= htmlspecialchars($atleta['slug'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small text-uppercase">Modalidade Base</label>
                <select name="id_modalidade" class="form-select filter-peso" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($modalidades as $m): ?>
                        <option value="<?= $m['id_modalidade'] ?>" <?= ($atleta['id_modalidade'] ?? '') == $m['id_modalidade'] ? 'selected' : '' ?>><?= $m['nome'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-uppercase">Categoria de Peso</label>
                <select name="id_categoria_peso" id="id_categoria_peso" class="form-select"
                    data-selected="<?= $atleta['id_categoria_peso'] ?? '' ?>" required>
                    <option value="">Aguardando filtros...</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small text-uppercase">Peso (kg)</label>
                <input type="number" step="0.01" name="peso" class="form-control" value="<?= $atleta['peso'] ?? '' ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-uppercase">Altura (cm)</label>
                <input type="number" name="altura" class="form-control" value="<?= $atleta['altura'] ?? '' ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-uppercase">Envergadura (cm)</label>
                <input type="number" name="envergadura" class="form-control"
                    value="<?= $atleta['envergadura'] ?? '' ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label small text-uppercase">Equipe / Academia</label>
                <select name="id_equipe" id="id_equipe" class="form-select">
                    <option value="">Nenhuma / Independente</option>
                    <?php foreach ($equipes as $e): ?>
                        <option value="<?= $e['id_equipe'] ?>" <?= ($atleta['id_equipe'] ?? '') == $e['id_equipe'] ? 'selected' : '' ?>><?= $e['nome'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label small text-uppercase">Sherdog URL</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-link"></i></span>
                    <input type="url" name="sherdog" class="form-control" value="<?= $atleta['sherdog'] ?? '' ?>">
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label small text-uppercase">Tapology URL</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-link"></i></span>
                    <input type="url" name="tapology" class="form-control" value="<?= $atleta['tapology'] ?? '' ?>">
                </div>
            </div>

            <div class="col-md-12">
                <label class="form-label small text-uppercase">Cartel Profissional (Vitorias - Derrotas -
                    Empates)</label>
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-success text-white">V</span>
                    <input type="number" name="vitorias" class="form-control text-center fw-bold"
                        value="<?= $atleta['vitorias'] ?? 0 ?>">
                    <span class="input-group-text bg-danger text-white">D</span>
                    <input type="number" name="derrotas" class="form-control text-center fw-bold"
                        value="<?= $atleta['derrotas'] ?? 0 ?>">
                    <span class="input-group-text bg-secondary text-white">E</span>
                    <input type="number" name="empates" class="form-control text-center fw-bold"
                        value="<?= $atleta['empates'] ?? 0 ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-5">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-secondary text-white fw-bold text-uppercase small">Contato</div>
                <div class="card-body row g-3">
                    <div class="col-12">
                        <label class="form-label small">E-mail</label>
                        <input type="email" name="email" class="form-control" value="<?= $atleta['email'] ?? '' ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">WhatsApp</label>
                        <input type="text" name="whatsapp" class="form-control"
                            value="<?= $atleta['whatsapp'] ?? '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Instagram</label>
                        <input type="text" name="instagram" class="form-control" placeholder="@"
                            value="<?= $atleta['instagram'] ?? '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Facebook</label>
                        <input type="text" name="facebook" class="form-control"
                            value="<?= $atleta['facebook'] ?? '' ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-secondary text-white fw-bold text-uppercase small">Localização</div>
                <div class="card-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label small">CEP</label>
                        <div class="input-group">
                            <input type="text" id="cep" name="cep" class="form-control"
                                value="<?= $atleta['cep'] ?? '' ?>">
                            <button class="btn btn-dark" type="button" onclick="buscarCEP()"><i
                                    class="fas fa-search"></i></button>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small">Logradouro (Rua)</label>
                        <input type="text" id="logradouro" name="logradouro" class="form-control"
                            value="<?= $atleta['logradouro'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Número</label>
                        <input type="text" name="numero" class="form-control" value="<?= $atleta['numero'] ?? '' ?>">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small">Complemento</label>
                        <input type="text" name="complemento" class="form-control"
                            value="<?= $atleta['complemento'] ?? '' ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Bairro</label>
                        <input type="text" id="bairro" name="bairro" class="form-control"
                            value="<?= $atleta['bairro'] ?? '' ?>">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small">Cidade</label>
                        <input type="text" id="cidade" name="cidade" class="form-control"
                            value="<?= $atleta['cidade'] ?? '' ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">UF</label>
                        <input type="text" id="uf" name="estado" class="form-control text-center" maxlength="2"
                            value="<?= $atleta['estado'] ?? '' ?>">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small">País</label>
                        <input type="text" name="pais" class="form-control" value="<?= $atleta['pais'] ?? 'Brasil' ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center pb-5">
        <hr class="my-4">
        <a href="<?= BASE_URL ?>/atletas" class="btn btn-outline-secondary px-5 me-2 fw-bold">CANCELAR</a>
        <button type="submit" class="btn <?= $isEdit ? 'btn-success' : 'btn-danger' ?> btn-lg fw-bold px-5 shadow">
            <i class="fas fa-save me-2"></i> <?= $isEdit ? 'SALVAR ALTERAÇÕES' : 'FINALIZAR CADASTRO' ?>
        </button>
    </div>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    const listaCategorias = <?= json_encode($categorias ?? []) ?>;
    const listaGraduacoes = <?= json_encode($graduacoes ?? []) ?>;
    let gradIndex = <?= count($graduacoes_list) ?>;

    $(document).ready(function () {
        // Inicializa Select2 para equipes
        $('#id_equipe').select2({
            placeholder: "Selecione a equipe...",
            width: '100%'
        });

        /**
         * FILTRO DE CATEGORIAS DE PESO
         * Dispara sempre que o sexo ou a modalidade base mudar
         */
        $('.filter-peso, #sexo').on('change', function () {
            const sexo = $('#sexo').val();
            const mod = $('select[name="id_modalidade"]').val();
            const $cat = $('#id_categoria_peso');

            // Recupera o ID selecionado anteriormente (útil na edição)
            const selected = $cat.data('selected') || $cat.val();

            // Só executa se ambos os campos estiverem preenchidos
            if (sexo && mod) {
                // Filtra a listaCategorias (que deve vir via PHP json_encode no topo do script)
                const filtradas = listaCategorias
                    .filter(c => c.sexo == sexo && c.id_modalidade == mod)
                    .sort((a, b) => parseFloat(a.peso_max) - parseFloat(b.peso_max));

                $cat.html('<option value="">Selecione a categoria...</option>');

                if (filtradas.length > 0) {
                    filtradas.forEach(c => {
                        const pesoTexto = c.peso_max ? ` (Até ${c.peso_max}kg)` : '';
                        const selectedAttr = (c.id_categoria_peso == selected) ? 'selected' : '';
                        $cat.append(`<option value="${c.id_categoria_peso}" ${selectedAttr}>${c.nome}${pesoTexto}</option>`);
                    });
                } else {
                    $cat.html('<option value="">Nenhuma categoria encontrada...</option>');
                }
            } else {
                $cat.html('<option value="">Aguardando sexo e modalidade...</option>');
            }
        });

        // 1. Dispara o filtro pela primeira vez ao carregar a página (importante para o modo Edição)
        if ($('#sexo').val() || $('select[name="id_modalidade"]').val()) {
            $('.filter-peso').first().trigger('change');
        }

        // 2. Inicializa as graduações existentes
        document.querySelectorAll('.item-graduacao select[onchange]').forEach(el => {
            filterGraduacoes(el);
        });
    });

    function buscarCEP() {
        let cep = $('#cep').val().replace(/\D/g, '');
        if (cep.length === 8) {
            $.getJSON(`https://viacep.com.br/ws/${cep}/json/`, function (d) {
                if (!("erro" in d)) {
                    $("#logradouro").val(d.logradouro);
                    $("#bairro").val(d.bairro);
                    $("#cidade").val(d.localidade);
                    $("#uf").val(d.uf);
                }
            });
        }
    }

    function filterGraduacoes(element) {
        const $row = $(element).closest('.item-graduacao');
        const modId = $(element).val();
        const $selectFaixa = $row.find('.select-graduacao');
        const selected = $selectFaixa.data('selected');
        $selectFaixa.html('<option value="">...</option>');
        if (modId) {
            const faixas = listaGraduacoes.filter(g => g.id_modalidade == modId);
            faixas.forEach(f => {
                $selectFaixa.append(`<option value="${f.id_graduacao}" ${f.id_graduacao == selected ? 'selected' : ''}>${f.nome}</option>`);
            });
        }
    }

    function addGraduacao() {
        const html = `
            <div class="row g-2 mb-2 item-graduacao">
                <div class="col-md-5">
                    <select name="graduacoes[${gradIndex}][id_modalidade]" class="form-select" onchange="filterGraduacoes(this)" required>
                        <option value="">Modalidade...</option>
                        <?php foreach ($modalidades as $m): ?>
                            <option value="<?= $m['id_modalidade'] ?>"><?= $m['nome'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <select name="graduacoes[${gradIndex}][id_graduacao]" class="form-select select-graduacao" required><option value="">Faixa...</option></select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger w-100" onclick="removeGraduacao(this)"><i class="fas fa-trash-alt"></i></button>
                </div>
            </div>`;
        $('#container-graduacoes').append(html);
        gradIndex++;
    }

    function removeGraduacao(btn) { if ($('.item-graduacao').length > 1) $(btn).closest('.item-graduacao').remove(); }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => $('#preview-foto').attr('src', e.target.result);
            reader.readAsDataURL(input.files[0]);
        }
    }

    $('#nome, #sobrenome').on('input', function () {
        const full = ($('#nome').val() + ' ' + $('#sobrenome').val()).trim();
        const slug = full.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9 -]/g, '').replace(/\s+/g, '-');
        $('#slug').val(slug);
    });
</script>