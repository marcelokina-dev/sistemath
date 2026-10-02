<form action="<?= isset($modalidade) ? BASE_URL . '/modalidades/update' : BASE_URL . '/modalidades/store' ?>" method="POST">
    <?php if(isset($modalidade)): ?>
        <input type="hidden" name="id_modalidade" value="<?= $modalidade['id_modalidade'] ?>">
    <?php endif; ?>

    <div class="card mb-4 shadow-sm border-0 rounded-3">
        <div class="card-header bg-dark text-white text-uppercase small fw-bold py-3">
            <i class="fas fa-fist-raised me-2"></i> Configuração da Modalidade
        </div>
        <div class="card-body row g-3 p-4">
            <div class="col-md-8">
                <label class="small fw-bold text-muted text-uppercase mb-1">Nome da Modalidade</label>
                <input type="text" name="nome" class="form-control border-gray-300 shadow-none" 
                    value="<?= $modalidade['nome'] ?? '' ?>" required placeholder="Ex: Muay Thai, MMA, Kickboxing">
            </div>

            <div class="col-md-4">
                <label class="small fw-bold text-muted text-uppercase mb-1">Status</label>
                <select name="status" class="form-select border-gray-300 shadow-none">
                    <option value="ativo" <?= (isset($modalidade) && $modalidade['status'] === 'ativo') ? 'selected' : '' ?>>Ativo</option>
                    <option value="inativo" <?= (isset($modalidade) && $modalidade['status'] === 'inativo') ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-danger btn-lg px-5 fw-bold text-uppercase shadow-sm">
            <i class="fas fa-save me-2"></i> Salvar Modalidade
        </button>
        <a href="<?= BASE_URL ?>/modalidades" class="btn btn-outline-secondary btn-lg px-4 text-uppercase fw-bold border-2">
            Cancelar
        </a>
    </div>
</form>