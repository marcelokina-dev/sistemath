<form action="<?= isset($graduacao) ? BASE_URL . '/graduacao/update' : BASE_URL . '/graduacao/store' ?>" method="POST">
    
    <?php if(isset($graduacao)): ?>
        <input type="hidden" name="id_graduacao" value="<?= $graduacao['id_graduacao'] ?>">
    <?php endif; ?>

    <div class="card mb-4 shadow-sm border-0 rounded-3">
        <div class="card-header bg-dark text-white text-uppercase small fw-bold py-3">
            <i class="fas fa-medal me-2 text-danger"></i> Configuração da Graduação
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                
                <div class="col-md-7">
                    <label class="small fw-bold text-muted text-uppercase mb-1">Nome da Graduação / Faixa</label>
                    <input type="text" name="nome" class="form-control border-gray-300 shadow-none py-2" 
                           value="<?= $graduacao['nome'] ?? '' ?>" required placeholder="Ex: Faixa Preta, Azul, Iniciante...">
                </div>

                <div class="col-md-5">
                    <label class="small fw-bold text-muted text-uppercase mb-1">Modalidade Correspondente</label>
                    <select name="id_modalidade" class="form-select border-gray-300 shadow-none py-2" required>
                        <option value="">Selecione a modalidade...</option>
                        <?php foreach ($modalidades as $mod): ?>
                            <option value="<?= $mod['id_modalidade'] ?>" 
                                <?= (isset($graduacao) && $graduacao['id_modalidade'] == $mod['id_modalidade']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($mod['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-danger btn-lg px-5 fw-bold text-uppercase shadow-sm">
            <i class="fas fa-save me-2"></i> Salvar Graduação
        </button>
        <a href="<?= BASE_URL ?>/graduacao" class="btn btn-outline-secondary btn-lg px-4 text-uppercase fw-bold border-2">
            Cancelar
        </a>
    </div>
</form>