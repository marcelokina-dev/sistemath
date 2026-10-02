<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-2">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/eventos" class="text-secondary">Eventos</a></li>
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/eventos/lutas?id=<?= $evento['id_evento'] ?>" class="text-secondary">
                            Card: <?= $evento['nome'] ?>
                        </a>
                    </li>
                    <li class="breadcrumb-item active text-dark font-weight-bold" aria-current="page">Casar Nova Luta</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h3 mb-0 text-gray-800 font-weight-bold text-uppercase">Casar Novo Confronto</h2>
                <small class="text-muted text-uppercase font-weight-bold"><?= $evento['nome'] ?></small>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header py-4 text-center bg-dark" style="border-top: 4px solid #d93737;">
            <h4 class="m-0 font-weight-bold text-white text-uppercase" style="letter-spacing: 1px;">
                Casar Novo Confronto
            </h4>
            <span class="text-light opacity-75 small text-uppercase"><?= $evento['nome'] ?></span>
        </div>
        
        <div class="card-body p-4">
            <div class="form-container">
                <?php include __DIR__ . '/form_luta.php'; ?>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos para aproximar do design das imagens de referência */
    .breadcrumb-item + .breadcrumb-item::before { content: "/"; }
    .card { border-radius: 8px; overflow: hidden; }
    .bg-dark { background-color: #1a202c !important; } /* Azul marinho escuro do topo */
</small>