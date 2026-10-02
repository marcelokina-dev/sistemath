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


<div id="content" class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-dark text-white p-3 rounded-3">
                <i class="fas fa-gavel fa-lg"></i>
            </div>
            <div>
                <h1 class="h3 fw-bold text-uppercase mb-0 tracking-tighter">
                    <?= isset($arbitro['id_arbitro']) ? 'Editar Árbitro' : 'Novo Árbitro' ?>
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item small"><a href="<?= BASE_URL ?>/arbitros"
                                class="text-decoration-none text-muted">Árbitros</a></li>
                        <li class="breadcrumb-item active small text-danger fw-bold" aria-current="page">Registro</li>
                    </ol>
                </nav>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/arbitros" class="btn btn-outline-dark fw-bold px-4">
            <i class="fas fa-arrow-left me-2"></i> VOLTAR
        </a>
    </div>

    <form action="<?= BASE_URL ?>/arbitros/<?= isset($arbitro['id_arbitro']) ? 'update' : 'store' ?>" method="POST">
        <?php if (isset($arbitro['id_arbitro'])): ?>
            <input type="hidden" name="id_arbitro" value="<?= $arbitro['id_arbitro'] ?>">
            <input type="hidden" name="id_endereco" value="<?= $arbitro['id_endereco'] ?? '' ?>">
            <input type="hidden" name="id_contato" value="<?= $arbitro['id_contato'] ?? '' ?>">
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-8">

                <div class="card shadow-sm border-0 rounded-3 mb-4 bg-info-gerais">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold text-uppercase text-muted small">Informações Gerais</h6>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label-custom">Nome Completo</label>
                                <input type="text" name="nome" class="form-control"
                                    value="<?= htmlspecialchars($arbitro['nome'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label-custom">Apelido</label>
                                <input type="text" name="apelido" class="form-control"
                                    value="<?= htmlspecialchars($arbitro['apelido'] ?? '') ?>"
                                    placeholder="Ex: Big John">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-custom">Sexo</label>
                                <select name="sexo" class="form-select" required>
                                    <option value="">Selecione...</option>
                                    <option value="M" <?= ($arbitro['sexo'] ?? '') == 'M' ? 'selected' : '' ?>>Masculino
                                    </option>
                                    <option value="F" <?= ($arbitro['sexo'] ?? '') == 'F' ? 'selected' : '' ?>>Feminino
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 mt-4">
                                <label class="form-label-custom">Modalidades que Arbitra</label>
                                <div class="modalidade-selector">
                                    <?php
                                    $selectedMods = $arbitro['modalidades_ids'] ?? [];
                                    if (!is_array($selectedMods))
                                        $selectedMods = [];

                                    foreach ($modalidades as $mod): ?>
                                        <label class="mod-check-item">
                                            <input type="checkbox" name="modalidades[]" value="<?= $mod['id_modalidade'] ?>"
                                                <?= in_array($mod['id_modalidade'], $selectedMods) ? 'checked' : '' ?>>
                                            <span class="mod-name">
                                                <?= htmlspecialchars($mod['nome']) ?>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>




                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 rounded-3 bg-info-gerais">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold text-uppercase text-muted small">Localização</h6>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label-custom">CEP</label>
                                <div class="input-group">
                                    <input type="text" name="cep" id="cep" class="form-control"
                                        value="<?= htmlspecialchars($arbitro['cep'] ?? '') ?>" maxlength="9"
                                        placeholder="00000-000">
                                    <button class="btn btn-dark" type="button" id="btnBuscaCEP"
                                        onclick="buscarCEP(document.getElementById('cep').value)">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label-custom">Logradouro / Rua</label>
                                <input type="text" name="logradouro" id="logradouro" class="form-control"
                                    value="<?= htmlspecialchars($arbitro['logradouro'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label-custom">Nº</label>
                                <input type="text" name="numero" class="form-control"
                                    value="<?= htmlspecialchars($arbitro['numero'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-custom">Bairro</label>
                                <input type="text" name="bairro" id="bairro" class="form-control"
                                    value="<?= htmlspecialchars($arbitro['bairro'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label-custom">Cidade</label>
                                <input type="text" name="cidade" id="cidade" class="form-control"
                                    value="<?= htmlspecialchars($arbitro['cidade'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label-custom">UF</label>
                                <input type="text" name="estado" id="uf" class="form-control text-center" maxlength="2"
                                    value="<?= htmlspecialchars($arbitro['estado'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 rounded-3 mb-4 bg-info-gerais">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold text-uppercase text-muted small">Contato & Status</h6>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="mb-3">
                            <label class="form-label-custom">E-mail Profissional</label>
                            <input type="email" name="email" class="form-control"
                                value="<?= htmlspecialchars($arbitro['email'] ?? '') ?>"
                                placeholder="exemplo@email.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">WhatsApp / Celular</label>
                            <input type="text" name="celular" id="celular" class="form-control"
                                value="<?= htmlspecialchars($arbitro['celular'] ?? '') ?>"
                                placeholder="(00) 00000-0000">
                        </div>
                        <div class="mb-0">
                            <label class="form-label-custom">Status no Sistema</label>
                            <select name="status" class="form-select">
                                <?php $statusBanco = isset($arbitro['status']) ? strtolower(trim($arbitro['status'])) : 'ativo'; ?>
                                <option value="Ativo" <?= ($statusBanco === 'ativo') ? 'selected' : '' ?>>🟢 Ativo</option>
                                <option value="Inativo" <?= ($statusBanco === 'inativo') ? 'selected' : '' ?>>🔴 Inativo
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 rounded-3 bg-dark">
                    <div class="card-body p-4">
                        <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold text-uppercase mb-3 shadow">
                            <i class="fas fa-save me-2"></i> SALVAR DADOS
                        </button>
                        <a href="<?= BASE_URL ?>/arbitros"
                            class="btn btn-link w-100 text-white text-decoration-none small fw-bold opacity-75">
                            Cancelar Alterações
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // Máscara CEP
    document.getElementById('cep').addEventListener('input', function (e) {
        let x = e.target.value.replace(/\D/g, '').match(/(\d{0,5})(\d{0,3})/);
        e.target.value = x[1] + (x[2] ? '-' + x[2] : '');
        if (e.target.value.length === 9) buscarCEP(e.target.value);
    });

    // Busca ViaCEP
    function buscarCEP(valor) {
        const cep = valor.replace(/\D/g, '');
        if (cep.length === 8) {
            const btn = document.getElementById('btnBuscaCEP');
            const icon = btn.querySelector('i');
            icon.className = "fas fa-spinner fa-spin";

            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(res => res.json())
                .then(dados => {
                    if (!dados.erro) {
                        document.getElementById('logradouro').value = dados.logradouro;
                        document.getElementById('bairro').value = dados.bairro;
                        document.getElementById('cidade').value = dados.localidade;
                        document.getElementById('uf').value = dados.uf;
                    }
                })
                .finally(() => { icon.className = "fas fa-search"; });
        }
    }

    // Máscara Celular
    document.getElementById('celular').addEventListener('input', function (e) {
        let r = e.target.value.replace(/\D/g, "");
        if (r.length > 11) r = r.substring(0, 11);
        if (r.length > 10) r = r.replace(/^(\d{2})(\d{5})(\d{4}).*/, "($1) $2-$3");
        else if (r.length > 5) r = r.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, "($1) $2-$3");
        else if (r.length > 2) r = r.replace(/^(\d{2})(\d{0,5})/, "($1) $2");
        else if (r.length > 0) r = r.replace(/^(\d*)/, "($1");
        e.target.value = r;
    });
</script>