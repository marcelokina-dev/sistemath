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
<div id="content" class="container py-4">
    <div class="mb-4">
        <h2 class="fw-bold text-uppercase"><?= $titulo ?></h2>
        <hr>
    </div>

    <form action="<?= isset($regra) ? BASE_URL . '/regras/update' : BASE_URL . '/regras/store' ?>" method="POST">
        
        <?php if (isset($regra)): ?>
            <input type="hidden" name="id_regra" value="<?= $regra['id_regra'] ?>">
        <?php endif; ?>

        <div class="card border-0 shadow-sm p-4 mb-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-muted">MODALIDADE</label>
                    <select name="id_modalidade" class="form-select py-2" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($modalidades as $m): ?>
                            <option value="<?= $m['id_modalidade'] ?>" <?= (isset($regra) && $regra['id_modalidade'] == $m['id_modalidade']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold small text-muted">RESULTADO</label>
                    <select name="resultado" class="form-select py-2" required>
                        <option value="vitoria" <?= (isset($regra) && $regra['resultado'] == 'vitoria') ? 'selected' : '' ?>>Vitória</option>
                        <option value="derrota" <?= (isset($regra) && $regra['resultado'] == 'derrota') ? 'selected' : '' ?>>Derrota</option>
                        <option value="empate" <?= (isset($regra) && $regra['resultado'] == 'empate') ? 'selected' : '' ?>>Empate</option>
                    </select>
                </div>

                <div class="col-md-8">
                    <label class="form-label fw-bold small text-muted">MÉTODO DE VITÓRIA</label>
                    <select name="id_metodo" class="form-select py-2" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($metodos as $met): ?>
                            <option value="<?= $met['id_metodo'] ?>" <?= (isset($regra) && $regra['id_metodo'] == $met['id_metodo']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($met['nome']) ?> (<?= $met['sigla'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">PONTUAÇÃO</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-warning"><i class="fas fa-star"></i></span>
                        <input type="number" name="pontos" class="form-control py-2" value="<?= $regra['pontos'] ?? '0' ?>" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-danger px-5 fw-bold shadow-sm">SALVAR REGRA</button>
            <a href="<?= BASE_URL ?>/regras" class="btn btn-outline-secondary px-4">CANCELAR</a>
        </div>
    </form>
</div>