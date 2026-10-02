<div id="content" class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-dark text-white p-3 rounded-3">
                <i class="fas fa-calendar fa-lg"></i>
            </div>
            <div>
                <h1 class="h3 fw-bold text-uppercase mb-0 tracking-tighter">Novo Evento</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item small">
                            <a href="<?= BASE_URL ?>/eventos" class="text-decoration-none text-muted">Eventos</a>
                        </li>
                        <li class="breadcrumb-item active small text-danger fw-bold" aria-current="page">Cadastro</li>
                    </ol>
                </nav>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/eventos" class="btn btn-outline-dark fw-bold px-4">
            <i class="fas fa-arrow-left me-2"></i> VOLTAR
        </a>
    </div>
                    
                    <?php include 'form.php'; ?>


</div>

<style>
    .form-control:focus, .form-select:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.1);
    }
</style>