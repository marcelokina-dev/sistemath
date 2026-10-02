<div id="content" class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= BASE_URL ?>/categorias" class="btn btn-outline-dark btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="h2 fw-bold text-uppercase mb-0 tracking-tighter">
                    <?= $titulo ?>
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>" class="text-decoration-none text-muted">Painel</a></li>
                        <li class="breadcrumb-item active fw-bold text-danger">Categorias de Peso</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-xl-10">
            <?php include 'form.php'; ?>
        </div>
    </div>
</div>