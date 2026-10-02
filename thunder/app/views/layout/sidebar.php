<?php
// Captura a URI atual para identificar qual menu destacar
$current_uri = $_SERVER['REQUEST_URI'];
?>

<div id="sidebar" class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark sidebar" style="width: 280px; min-height: 100vh;">
    <a href="<?= BASE_URL ?>/dashboard" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4 fw-bold text-uppercase tracking-tighter">Thunder Fight</span>
    </a>
    <hr class="opacity-10">
    
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/dashboard" class="nav-link d-flex align-items-center gap-2 <?= (strpos($current_uri, 'dashboard') !== false) ? 'active bg-danger' : 'text-white' ?>">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>/atletas" class="nav-link d-flex align-items-center gap-2 <?= (strpos($current_uri, 'atletas') !== false) ? 'active bg-danger' : 'text-white' ?>">
                <i class="fas fa-user-ninja"></i> Atletas
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>/equipes" class="nav-link d-flex align-items-center gap-2 <?= (strpos($current_uri, 'equipes') !== false) ? 'active bg-danger' : 'text-white' ?>">
                <i class="fas fa-users"></i> Equipes
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>/arbitros" class="nav-link d-flex align-items-center gap-2 <?= (strpos($current_uri, 'arbitros') !== false) ? 'active bg-danger' : 'text-white' ?>">
                <i class="fas fa-gavel"></i> Árbitros
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>/eventos" class="nav-link d-flex align-items-center gap-2 <?= (strpos($current_uri, 'eventos') !== false) ? 'active bg-danger' : 'text-white' ?>">
                <i class="fas fa-calendar-alt"></i> Eventos
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>/importacao" class="nav-link d-flex align-items-center gap-2 <?= (strpos($current_uri, 'importacao') !== false) ? 'active bg-danger' : 'text-white' ?>">
                <i class="fas fa-bolt"></i> Importacao em Massa
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>/ranking" class="nav-link d-flex align-items-center gap-2 <?= (strpos($current_uri, 'ranking') !== false) ? 'active bg-danger' : 'text-white' ?>">
                <i class="fas fa-trophy"></i> Rankings
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>/campeoes" class="nav-link d-flex align-items-center justify-content-between gap-2 <?= (strpos($current_uri, 'campeoes') !== false) ? 'active bg-danger' : 'text-white' ?>">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-crown text-warning"></i> Galeria de Campeões
                </div>
                <span class="badge bg-danger border-0 small" style="font-size: 0.6rem;">PRO</span>
            </a>
        </li>

                <li>
            <a href="<?= BASE_URL ?>/regras" class="nav-link d-flex align-items-center gap-2 <?= (strpos($current_uri, 'regras') !== false) ? 'active bg-danger' : 'text-white' ?>">
                <i class="fas fa-trophy"></i> Regras de Pontuação
            </a>
        </li>

        <hr class="opacity-10 my-3">
        <small class="text-muted text-uppercase fw-bold mb-2 px-2" style="font-size: 0.65rem; letter-spacing: 1px;">Configurações</small>

        <li>
            <a href="<?= BASE_URL ?>/categorias" class="nav-link d-flex align-items-center gap-2 <?= (strpos($current_uri, 'categorias') !== false) ? 'active bg-danger' : 'text-white' ?>">
                <i class="fas fa-weight-hanging"></i> Categorias
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>/modalidades" class="nav-link d-flex align-items-center gap-2 <?= (strpos($current_uri, 'modalidades') !== false) ? 'active bg-danger' : 'text-white' ?>">
                <i class="fas fa-fist-raised"></i> Modalidades
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>/configuracoes" class="nav-link d-flex align-items-center gap-2 <?= (strpos($current_uri, 'configuracoes') !== false) ? 'active bg-danger' : 'text-white' ?>">
                <i class="fas fa-cog"></i> Configurações
            </a>
        </li>
    </ul>
</div>