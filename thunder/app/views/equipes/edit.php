<div id="content" class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-dark text-white p-3 rounded-3">
                <i class="fas fa-users fa-lg"></i>
            </div>
            <div>
                <h1 class="h3 fw-bold text-uppercase mb-0 tracking-tighter">Editar Equipe</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item small">
                            <a href="<?= BASE_URL ?>/equipes" class="text-decoration-none text-muted">Equipes</a>
                        </li>
                        <li class="breadcrumb-item active small text-danger fw-bold" aria-current="page">Cadastro</li>
                    </ol>
                </nav>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/equipes" class="btn btn-outline-dark fw-bold px-4">
            <i class="fas fa-arrow-left me-2"></i> VOLTAR
        </a>
    </div>

    <?php
    /**
     * O form.php agora é auto-suficiente. 
     * Ele contém a tag <form> e o botão de salvar adequado.
     */
    include 'form.php';
    ?>
    
    </div>