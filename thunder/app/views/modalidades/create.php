<div id="content" class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= BASE_URL ?>/modalidades" class="btn btn-outline-dark btn-sm rounded-circle">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="h2 fw-bold text-uppercase mb-0 tracking-tighter">
                    <?= isset($modalidade) ? 'Editar Modalidade' : 'Nova Modalidade' ?>
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>" class="text-decoration-none text-muted">Painel</a></li>
                        <li class="breadcrumb-item active fw-bold text-danger">Modalidades</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <?php 
        include 'form.php'; 
    ?>
</div>