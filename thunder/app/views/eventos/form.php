<?php
$isEdit = isset($evento['id_evento']);
// Adicionada a barra correta no store
$action = $isEdit ? BASE_URL . '/eventos/update' : BASE_URL . '/eventos/store';
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

<form action="<?= $action ?>" method="POST" enctype="multipart/form-data" id="formEvento">
    <div class="row g-4">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id_evento" value="<?= $evento['id_evento'] ?>">
            <input type="hidden" name="id_endereco" value="<?= $evento['id_endereco'] ?>">
            <input type="hidden" name="foto_atual" value="<?= $evento['foto'] ?? '' ?>">
        <?php endif; ?>

        <div class="col-12">
            <h5 class="text-danger fw-bold text-uppercase mb-3"><i class="fas fa-trophy me-2"></i>Informações do Evento
            </h5>
        </div>

        <div class="col-md-5">
            <label class="form-label small fw-bold text-uppercase">Nome do Evento</label>
            <input type="text" name="nome" class="form-control form-control-lg" placeholder="Ex: Thunder Fight 60"
                value="<?= htmlspecialchars($evento['nome'] ?? '') ?>" required>
        </div>

        <div class="col-md-3">
            <label class="form-label small fw-bold text-uppercase">Data</label>
            <input type="date" name="data_evento" class="form-control form-control-lg"
                value="<?= $evento['data_evento'] ?? date('Y-m-d') ?>" required>
        </div>

        <div class="col-md-4">
            <label class="form-label small fw-bold text-uppercase">Status Atual</label>
            <select name="status" class="form-select form-control-lg" required>
                <?php
                $status_opcoes = ['Planejamento', 'Confirmado', 'Realizado'];
                $status_atual = $evento['status'] ?? 'Planejamento';
                foreach ($status_opcoes as $opcao):
                    ?>
                    <option value="<?= $opcao ?>" <?= ($status_atual == $opcao) ? 'selected' : '' ?>>
                        <?= $opcao ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label small fw-bold text-uppercase">Local (Ex: Ginásio Ibirapuera)</label>
            <input type="text" name="local_nome" class="form-control form-control-lg"
                value="<?= htmlspecialchars($evento['local_nome'] ?? '') ?>" required>
        </div>




        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">MODALIDADE DO EVENTO</label>
            <select name="id_modalidade" class="form-select" required>
                <option value="">Selecione...</option>
                <?php foreach ($modalidades as $m): ?>
                    <option value="<?= $m['id_modalidade'] ?>" <?= (isset($evento['id_modalidade']) && $evento['id_modalidade'] == $m['id_modalidade']) ? 'selected' : '' ?>>
                        <?= $m['nome'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label small fw-bold text-uppercase">Cartaz / Foto</label>
            <input type="file" name="foto" class="form-control form-control-lg" accept="image/*">
            <?php if ($isEdit && !empty($evento['foto'])): ?>
                <div class="mt-2 small text-muted">Arquivo atual: <?= $evento['foto'] ?></div>
            <?php endif; ?>
        </div>

        <hr class="my-4 opacity-25">

        <div class="col-12 d-flex justify-content-between align-items-center">
            <h5 class="text-danger fw-bold text-uppercase mb-0"><i class="fas fa-map-marker-alt me-2"></i>Endereço</h5>
            <span id="cep-status" class="badge bg-light text-dark fw-normal small"></span>
        </div>

        <div class="col-md-4">
            <label class="form-label small fw-bold text-uppercase">CEP</label>
            <input type="text" id="cep" name="cep" class="form-control form-control-lg" placeholder="00000-000"
                maxlength="9" onblur="buscaCEP(this.value)">
        </div>

        <div class="col-md-8">
            <label class="form-label small fw-bold text-uppercase">Logradouro (Rua/Av)</label>
            <input type="text" id="logradouro" name="logradouro" class="form-control form-control-lg"
                value="<?= htmlspecialchars($evento['logradouro'] ?? '') ?>">
        </div>

        <div class="col-md-3">
            <label class="form-label small fw-bold text-uppercase">Número</label>
            <input type="text" name="numero" class="form-control form-control-lg"
                value="<?= htmlspecialchars($evento['numero'] ?? '') ?>">
        </div>

        <div class="col-md-5">
            <label class="form-label small fw-bold text-uppercase">Bairro</label>
            <input type="text" id="bairro" name="bairro" class="form-control form-control-lg"
                value="<?= htmlspecialchars($evento['bairro'] ?? '') ?>">
        </div>

        <div class="col-md-4">
            <label class="form-label small fw-bold text-uppercase">Cidade</label>
            <input type="text" id="cidade" name="cidade" class="form-control form-control-lg"
                value="<?= htmlspecialchars($evento['cidade'] ?? '') ?>">
        </div>

        <div class="col-md-3">
            <label class="form-label small fw-bold text-uppercase">Estado (UF)</label>
            <input type="text" id="estado" name="estado" class="form-control form-control-lg" maxlength="2"
                value="<?= htmlspecialchars($evento['estado'] ?? '') ?>">
        </div>

        <div class="col-md-9">
            <label class="form-label small fw-bold text-uppercase">País</label>
            <input type="text" name="pais" class="form-control form-control-lg"
                value="<?= htmlspecialchars($evento['pais'] ?? 'Brasil') ?>">
        </div>

        <div class="col-12 mt-5">
            <div class="d-flex justify-content-end gap-3">
                <button type="reset" class="btn btn-light fw-bold text-uppercase px-4 border">Limpar</button>
                <button type="submit" class="btn btn-danger fw-bold text-uppercase px-5 shadow-sm">
                    <i class="fas fa-save me-2"></i> <?= $isEdit ? 'Atualizar Evento' : 'Salvar Evento' ?>
                </button>
            </div>
        </div>
    </div>
</form>

<script>
    // Mantido sua função original de buscaCEP
    function buscaCEP(valor) {
        const cep = valor.replace(/\D/g, '');
        const status = document.getElementById('cep-status');

        if (cep !== "" && /^[0-9]{8}$/.test(cep)) {
            status.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando...';

            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (!data.erro) {
                        document.getElementById('logradouro').value = data.logradouro;
                        document.getElementById('bairro').value = data.bairro;
                        document.getElementById('cidade').value = data.localidade;
                        document.getElementById('estado').value = data.uf;
                        status.innerHTML = '<span class="text-success"><i class="fas fa-check"></i> CEP Localizado</span>';
                    } else {
                        status.innerHTML = '<span class="text-danger">CEP não encontrado</span>';
                    }
                })
                .catch(() => {
                    status.innerHTML = '<span class="text-danger">Erro ao consultar CEP</span>';
                });
        }
    }
</script>