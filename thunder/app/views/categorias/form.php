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
  <form action="<?= BASE_URL ?>/categorias/<?= isset($categoria['id_categoria_peso']) ? 'update' : 'store' ?>" method="POST">
    <?php if (isset($categoria)): ?>
        <input type="hidden" name="id_categoria_peso" value="<?= $categoria['id_categoria_peso'] ?>">
    <?php endif; ?>

    <div class="card mb-4 shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="card-header bg-dark text-white text-uppercase small fw-bold py-3">
            <i class="fas fa-balance-scale me-2"></i> Dados da Categoria de Peso
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="small fw-bold text-muted text-uppercase mb-1">Nome da Categoria</label>
                    <input type="text" name="nome" class="form-control border-gray-300 shadow-none py-2" 
                        value="<?= $categoria['nome'] ?? '' ?>" required placeholder="Ex: Peso Pena...">
                </div>
                <div class="col-md-4">
                    <label class="small fw-bold text-muted text-uppercase mb-1">Modalidade Vinculada</label>
                    <select name="id_modalidade" class="form-select border-gray-300 shadow-none py-2" required>
                        <option value="">Selecione a Modalidade</option>
                        <?php foreach ($modalidades as $m): ?>
                            <option value="<?= $m['id_modalidade'] ?>" 
                                <?= (isset($categoria) && $categoria['id_modalidade'] == $m['id_modalidade']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="small fw-bold text-muted text-uppercase mb-1">Sexo</label>
                    <select name="sexo" class="form-select border-gray-300 shadow-none py-2" required>
                        <option value="M" <?= (isset($categoria) && $categoria['sexo'] == 'M') ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= (isset($categoria) && $categoria['sexo'] == 'F') ? 'selected' : '' ?>>Feminino</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="card-header text-white text-uppercase small fw-bold py-3" style="background-color: #6c757d;">
            <i class="fas fa-weight me-2"></i> Limites de Peso (KG)
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="small fw-bold text-muted text-uppercase mb-1">Peso Mínimo</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-gray-300"><i class="fas fa-stopwatch text-muted"></i></span>
                        <input type="number" step="0.001" name="peso_min" class="form-control border-gray-300 shadow-none py-2" 
                            value="<?= $categoria['peso_min'] ?? '0.000' ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold text-muted text-uppercase mb-1">Peso Máximo (Limite)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-gray-300"><i class="fas fa-weight-hanging text-muted"></i></span>
                        <input type="number" step="0.001" name="peso_max" class="form-control border-gray-300 shadow-none py-2" 
                            value="<?= $categoria['peso_max'] ?? '0.000' ?>" required>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-danger btn-lg px-5 fw-bold text-uppercase shadow-sm" style="background-color: #d9434e; border-color: #d9434e;">
            <i class="fas fa-save me-2"></i> Salvar Categoria
        </button>
        <a href="<?= BASE_URL ?>/categorias" class="btn btn-outline-secondary btn-lg px-4 text-uppercase fw-bold border-2">
            Cancelar
        </a>
    </div>
</form>