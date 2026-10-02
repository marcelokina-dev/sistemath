<?php
// Se a variável $equipe não existir (caso de novo cadastro), criamos um array vazio
if (!isset($equipe) || $equipe === null) {
    $equipe = [];
}

$isEdit = !empty($equipe['id_equipe']);
$action = $isEdit ? BASE_URL . '/equipes/update' : BASE_URL . '/equipes/store';
?>

<style>
    /* Estilo para transformar o checkbox em um "botão" clicável e visível */
    .modalidade-selector {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding: 15px;
        background: #fdfdfd;
        border: 1px solid #eee;
        border-radius: 8px;
    }

    .mod-check-item {
        position: relative;
    }

    /* Esconde o checkbox original feio */
    .mod-check-item input[type="checkbox"] {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }

    /* Cria o novo visual do botão de seleção */
    .mod-name {
        display: inline-block;
        padding: 8px 16px;
        background-color: #ffffff;
        border: 2px solid #dee2e6;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 700;
        color: #495057;
        transition: all 0.2s ease;
        cursor: pointer;
        user-select: none;
    }

    /* Quando passa o mouse */
    .mod-check-item:hover .mod-name {
        border-color: #dc3545;
        color: #dc3545;
    }

    /* Quando está MARCADO (Checked) */
    .mod-check-item input[type="checkbox"]:checked+.mod-name {
        background-color: #dc3545;
        border-color: #dc3545;
        color: #ffffff;
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
    }

    /* Forçar visibilidade dos campos de texto (conforme anterior) */
    .form-control {
        border: 2px solid #ced4da !important;
        background-color: #ffffff !important;
        color: #000000 !important;
    }

/* Espaço entre o título do bloco e a primeira linha de campos */
    .card-header {
        padding-bottom: 10 !important;
        margin-bottom: 20px !important;
    }

    /* Estilo exclusivo para o bloco de Informações Gerais */
    .bg-info-gerais {
        background-color: #dadada !important;
        /* Cinza levemente mais escuro */
        border: 1px solid #dae0e5 !important;
        box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.02);
        /* Sombra interna suave */
    }

    /* Garante que as labels e inputs dentro desse bloco fiquem legíveis */
    .bg-info-gerais .form-label-custom {
        color: #333 !important;
        font-weight: 700 !important;
    }

    .bg-info-gerais .form-control {
        border: 2px solid #adb5bd !important;
        /* Bordas dos inputs mais escuras */
        background-color: #ffffff !important;
    }

    /* Estilo para as modalidades dentro desse fundo cinza */
    .modalidade-selector {
        background-color: rgba(0, 0, 0, 0.05) !important;
        padding: 15px;
        border-radius: 8px;
    }

    /* 1. Criar espaço entre a Label e o Input */
    .form-label-custom {
        display: block !important;
        margin-bottom: 8px !important;
        /* Espaço entre o título e o campo */
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        color: #495057 !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>

<form action="<?= $action ?>" method="POST" enctype="multipart/form-data">

    <?php if ($isEdit): ?>
        <input type="hidden" name="id_equipe" value="<?= $equipe['id_equipe'] ?>">
    <?php endif; ?>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-dark text-white fw-bold text-uppercase small">
            <?= $isEdit ? 'Editar Dados da Equipe' : 'Dados da Equipe / Academia' ?>
        </div>
        <div class="card-body row g-3">
            <div class="col-md-3 mb-3 text-center border-end">
                <label class="form-label text-muted small fw-bold text-uppercase d-block">Logo da Equipe</label>


                <?php
                // Define a foto padrão usando a URL
                $fotoPath = BASE_URL . '/uploads/equipes/sem-foto.png';

                if (!empty($equipe['foto'])) {
                    // PADRONIZAÇÃO: Usando BASE_PATH para verificação física
                    $arquivoNoDisco = BASE_PATH . '/uploads/equipes/' . $equipe['foto'];

                    if (file_exists($arquivoNoDisco)) {
                        $fotoPath = BASE_URL . '/uploads/equipes/' . $equipe['foto'];
                    }
                }
                ?>



                <img src="<?= $fotoPath ?>" id="preview" alt="Logo" class="img-thumbnail mb-2 shadow-sm"
                    style="height: 140px; width: 140px; object-fit: cover; border-radius: 10px;">

                <input type="file" name="foto" id="foto" class="form-control form-control-sm" accept="image/*"
                    onchange="previewImage(this)">
                <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">PNG, JPG ou WEBP.</small>
            </div>

            <div class="col-md-9">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="nome" class="form-label text-muted small fw-bold text-uppercase">Nome da Academia /
                            Equipe</label>
                        <input type="text" name="nome" id="nome" class="form-control"
                            value="<?= htmlspecialchars($equipe['nome'] ?? '') ?>" required
                            placeholder="Ex: Matriz Thunder Fight">
                    </div>

                    <div class="col-md-8 mb-3">
                        <label for="responsavel" class="form-label text-muted small fw-bold text-uppercase">Mestre /
                            Responsável</label>
                        <input type="text" name="responsavel" id="responsavel" class="form-control"
                            value="<?= htmlspecialchars($equipe['responsavel'] ?? '') ?>"
                            placeholder="Nome do Professor">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="status" class="form-label text-muted small fw-bold text-uppercase">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="Ativo" <?= (isset($equipe['status']) && $equipe['status'] == 'Ativo') ? 'selected' : '' ?>>Ativo</option>
                            <option value="Inativo" <?= (isset($equipe['status']) && $equipe['status'] == 'Inativo') ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100 border-0 border-start border-secondary border-4">
                <div class="card-header bg-secondary text-white fw-bold text-uppercase small">Contato e Redes</div>
                <div class="card-body row g-3">
                    <div class="col-md-6 mb-3">
                        <label for="whatsapp"
                            class="form-label text-muted small fw-bold text-uppercase">WhatsApp</label>
                        <input type="text" name="whatsapp" id="whatsapp" class="form-control"
                            value="<?= htmlspecialchars($equipe['whatsapp'] ?? '') ?>" placeholder="(11) 99999-9999">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="instagram"
                            class="form-label text-muted small fw-bold text-uppercase">Instagram</label>
                        <div class="input-group">
                            <span class="input-group-text small">@</span>
                            <input type="text" name="instagram" id="instagram" class="form-control"
                                value="<?= htmlspecialchars(str_replace('@', '', $equipe['instagram'] ?? '')) ?>"
                                placeholder="usuario">
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="facebook"
                            class="form-label text-muted small fw-bold text-uppercase">Facebook</label>
                        <div class="input-group">
                            <span class="input-group-text small"><i class="fab fa-facebook-f"></i></span>
                            <input type="text" name="facebook" id="facebook" class="form-control"
                                value="<?= htmlspecialchars($equipe['facebook'] ?? '') ?>"
                                placeholder="facebook.com/academia">
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="email" class="form-label text-muted small fw-bold text-uppercase">E-mail</label>
                        <input type="email" name="email" id="email" class="form-control"
                            value="<?= htmlspecialchars($equipe['email'] ?? '') ?>" placeholder="contato@academia.com">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100 border-0 border-start border-primary border-4">
                <div class="card-header bg-primary text-white fw-bold text-uppercase small">Localização</div>
                <div class="card-body row g-3">
                    <div class="col-md-4 mb-3">
                        <label for="cep" class="form-label text-muted small fw-bold text-uppercase">CEP</label>
                        <div class="input-group">
                            <input type="text" id="cep" name="cep" class="form-control"
                                value="<?= htmlspecialchars($equipe['cep'] ?? '') ?>" maxlength="9"
                                onkeyup="mascaraCEP(this)">
                            <button class="btn btn-dark" type="button" onclick="buscarCEP()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label for="logradouro" class="form-label text-muted small fw-bold text-uppercase">Rua /
                            Logradouro</label>
                        <input type="text" id="logradouro" name="logradouro" class="form-control"
                            value="<?= htmlspecialchars($equipe['logradouro'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="numero" class="form-label text-muted small fw-bold text-uppercase">Nº</label>
                        <input type="text" id="numero" name="numero" class="form-control"
                            value="<?= htmlspecialchars($equipe['numero'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="complemento"
                            class="form-label text-muted small fw-bold text-uppercase">Compl.</label>
                        <input type="text" id="complemento" name="complemento" class="form-control"
                            value="<?= htmlspecialchars($equipe['complemento'] ?? '') ?>">
                    </div>
                    <div class="col-md-5 mb-3">
                        <label for="bairro" class="form-label text-muted small fw-bold text-uppercase">Bairro</label>
                        <input type="text" id="bairro" name="bairro" class="form-control"
                            value="<?= htmlspecialchars($equipe['bairro'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="cidade" class="form-label text-muted small fw-bold text-uppercase">Cidade</label>
                        <input type="text" id="cidade" name="cidade" class="form-control"
                            value="<?= htmlspecialchars($equipe['cidade'] ?? '') ?>">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="estado" class="form-label text-muted small fw-bold text-uppercase">UF</label>
                        <input type="text" id="estado" name="estado" class="form-control" maxlength="2"
                            style="text-transform: uppercase;" value="<?= htmlspecialchars($equipe['estado'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="pais" class="form-label text-muted small fw-bold text-uppercase">País</label>
                        <input type="text" id="pais" name="pais" class="form-control"
                            value="<?= htmlspecialchars($equipe['pais'] ?? 'Brasil') ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>




<div class="card shadow-sm border-0 rounded-3 bg-dark">
                    <div class="card-body p-4">
                        <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold text-uppercase mb-3 shadow">
                            <i class="fas fa-save me-2"></i> SALVAR DADOS
                        </button>
                        <a href="<?= BASE_URL ?>/equipes"
                            class="btn btn-link w-100 text-white text-decoration-none small fw-bold opacity-75">
                            Cancelar Alterações
                        </a>
                    </div>
                </div>



</form>

<script>
    function mascaraCEP(t) {
        t.value = t.value.replace(/\D/g, "");
        t.value = t.value.replace(/^(\d{5})(\d)/, "$1-$2");
    }

    function buscarCEP() {
        let cep = document.getElementById('cep').value.replace(/\D/g, '');
        if (cep !== "") {
            let validacep = /^[0-9]{8}$/;
            if (validacep.test(cep)) {
                document.getElementById('logradouro').value = "...";
                document.getElementById('bairro').value = "...";
                document.getElementById('cidade').value = "...";
                document.getElementById('estado').value = "...";

                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(dados => {
                        if (!("erro" in dados)) {
                            document.getElementById('logradouro').value = dados.logradouro;
                            document.getElementById('bairro').value = dados.bairro;
                            document.getElementById('cidade').value = dados.localidade;
                            document.getElementById('estado').value = dados.uf;
                            document.getElementById('numero').focus();
                        } else {
                            alert("CEP não encontrado.");
                        }
                    })
                    .catch(() => alert("Erro ao buscar o CEP. Tente novamente."));
            }
        }
    }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>