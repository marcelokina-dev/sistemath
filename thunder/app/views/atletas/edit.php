<div id="content" class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-dark text-white p-3 rounded-3">
                <i class="fas fa-user-ninja fa-lg"></i>
            </div>
            <div>
                <h1 class="h3 fw-bold text-uppercase mb-0 tracking-tighter">Editar Atleta</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item small">
                            <a href="<?= BASE_URL ?>/atletas" class="text-decoration-none text-muted">Atletas</a>
                        </li>
                        <li class="breadcrumb-item active small text-danger fw-bold" aria-current="page">Cadastro</li>
                    </ol>
                </nav>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/atletas" class="btn btn-outline-dark fw-bold px-4">
            <i class="fas fa-arrow-left me-2"></i> VOLTAR
        </a>
    </div>



    <?php
    /**
     * Incluímos o form.php que contém todos os campos (Layout de Cards).
     * Para o cadastro novo, a variável $equipe será nula, 
     * fazendo com que o form.php exiba os campos vazios.
     */
    $equipe = null;
    include 'form.php';
    ?>



</div>