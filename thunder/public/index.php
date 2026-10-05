<?php
/**
 * THUNDER FIGHT - Sistema de Gestão Esportiva
 * Arquivo de Entrada (Bootstrap)
 */

// 1. CONFIGURAÇÕES GLOBAIS (Primeira coisa a carregar)
require_once __DIR__ . '/../core/Config.php';

// Sessão global: relatórios e prévias dos fluxos de importação
// precisam permanecer disponíveis após o redirect.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inicia o buffer de saída para evitar erros de "Headers already sent"
if (!ob_get_length()) {
    ob_start();
}

// --- CORE ---
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Router.php';

// --- CONTROLLERS ---
require_once __DIR__ . '/../app/controllers/BaseController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/AtletasController.php';
require_once __DIR__ . '/../app/controllers/ArbitrosController.php';
require_once __DIR__ . '/../app/controllers/EquipesController.php';
require_once __DIR__ . '/../app/controllers/ModalidadesController.php';
require_once __DIR__ . '/../app/controllers/GraduacaoController.php';
require_once __DIR__ . '/../app/controllers/CategoriasController.php';
require_once __DIR__ . '/../app/controllers/RegrasController.php';
require_once __DIR__ . '/../app/controllers/EventosController.php';
require_once __DIR__ . '/../app/controllers/RankingController.php';
require_once __DIR__ . '/../app/controllers/HistoricoController.php';
require_once __DIR__ . '/../app/controllers/ImportacaoController.php';
require_once __DIR__ . '/../app/controllers/TapologyController.php';

// --- MODELS ---
require_once __DIR__ . '/../app/models/AtletaModel.php';
require_once __DIR__ . '/../app/models/ModalidadeModel.php';
require_once __DIR__ . '/../app/models/GraduacaoModel.php';
require_once __DIR__ . '/../app/models/CategoriaPesoModel.php';
require_once __DIR__ . '/../app/models/EquipeModel.php';
require_once __DIR__ . '/../app/models/ArbitroModel.php';
require_once __DIR__ . '/../app/models/RegraPontuacaoModel.php';
require_once __DIR__ . '/../app/models/EventoModel.php';
require_once __DIR__ . '/../app/models/LutaModel.php';
require_once __DIR__ . '/../app/models/RankingModel.php';
require_once __DIR__ . '/../app/models/HistoricoModel.php';

$router = new Router();

// ==========================================
// DEFINIÇÃO DE ROTAS
// ==========================================

// --- Dashboard ---
$router->add('/', 'DashboardController', 'index');
$router->add('/dashboard', 'DashboardController', 'index');

// --- Atletas ---
$router->add('/atletas', 'AtletasController', 'index');
$router->add('/atletas/show', 'AtletasController', 'show');
$router->add('/atletas/create', 'AtletasController', 'create');
$router->add('/atletas/store', 'AtletasController', 'store');
$router->add('/atletas/edit', 'AtletasController', 'edit');
$router->add('/atletas/update', 'AtletasController', 'update');
$router->add('/atletas/delete', 'AtletasController', 'delete');

// --- Árbitros ---
$router->add('/arbitros', 'ArbitrosController', 'index');
$router->add('/arbitros/show', 'ArbitrosController', 'show');
$router->add('/arbitros/create', 'ArbitrosController', 'create');
$router->add('/arbitros/store', 'ArbitrosController', 'store');
$router->add('/arbitros/edit', 'ArbitrosController', 'edit');
$router->add('/arbitros/update', 'ArbitrosController', 'update');
$router->add('/arbitros/delete', 'ArbitrosController', 'delete');

// --- Equipes ---
$router->add('/equipes', 'EquipesController', 'index');
$router->add('/equipes/create', 'EquipesController', 'create');
$router->add('/equipes/store', 'EquipesController', 'store');
$router->add('/equipes/edit', 'EquipesController', 'edit');
$router->add('/equipes/update', 'EquipesController', 'update');
$router->add('/equipes/delete', 'EquipesController', 'delete');

// --- Modalidades ---
$router->add('/modalidades', 'ModalidadesController', 'index');
$router->add('/modalidades/create', 'ModalidadesController', 'create');
$router->add('/modalidades/store', 'ModalidadesController', 'store');
$router->add('/modalidades/edit', 'ModalidadesController', 'edit');
$router->add('/modalidades/update', 'ModalidadesController', 'update');
$router->add('/modalidades/delete', 'ModalidadesController', 'delete');

// --- Graduações ---
$router->add('/graduacao', 'GraduacaoController', 'index');
$router->add('/graduacao/create', 'GraduacaoController', 'create');
$router->add('/graduacao/store', 'GraduacaoController', 'store');
$router->add('/graduacao/edit', 'GraduacaoController', 'edit');
$router->add('/graduacao/update', 'GraduacaoController', 'update');
$router->add('/graduacao/delete', 'GraduacaoController', 'delete');

// --- Categorias de Peso ---
$router->add('/categorias', 'CategoriasController', 'index');
$router->add('/categorias/create', 'CategoriasController', 'create');
$router->add('/categorias/store', 'CategoriasController', 'store');
$router->add('/categorias/edit', 'CategoriasController', 'edit');
$router->add('/categorias/update', 'CategoriasController', 'update');
$router->add('/categorias/delete', 'CategoriasController', 'delete');
$router->add('/categorias/buscarPorModalidade', 'CategoriasController', 'buscarPorModalidade');

// --- Ranking ---
$router->add('/ranking', 'RankingController', 'index');
$router->add('/ranking/buscarCategoriasPorModalidade', 'RankingController', 'buscarCategoriasPorModalidade');
$router->add('/ranking/atualizar', 'RankingController', 'atualizar'); 
$router->add('/ranking/remover_atleta', 'RankingController', 'remover_atleta');

// --- Galeria de Campeões (ADM) ---
$router->add('/campeoes', 'HistoricoController', 'index');
$router->add('/campeoes/vacante', 'HistoricoController', 'vacante');
$router->add('/campeoes/registrar', 'HistoricoController', 'registrar');
$router->add('/campeoes/excluir_historico', 'HistoricoController', 'excluir_historico');
$router->add('/campeoes/registrar_pelo_ranking', 'HistoricoController', 'registrar_pelo_ranking');

// --- Regras de Pontuação ---
$router->add('/regras', 'RegrasController', 'index');
$router->add('/regras/create', 'RegrasController', 'create');
$router->add('/regras/store', 'RegrasController', 'store');
$router->add('/regras/edit', 'RegrasController', 'edit');
$router->add('/regras/update', 'RegrasController', 'update');
$router->add('/regras/delete', 'RegrasController', 'delete');

// --- Gestão de Eventos ---
$router->add('/eventos', 'EventosController', 'index');
$router->add('/eventos/create', 'EventosController', 'create');
$router->add('/eventos/store', 'EventosController', 'store');
$router->add('/eventos/edit', 'EventosController', 'edit');
$router->add('/eventos/update', 'EventosController', 'update');
$router->add('/eventos/delete', 'EventosController', 'delete');
$router->add('/eventos/card', 'EventosController', 'lutas');
$router->add('/eventos/reordenar-luta', 'EventosController', 'reordenarLuta');

// --- Gestão de Lutas (Sub-rotas de Eventos) ---
$router->add('/eventos/lutas', 'EventosController', 'lutas');
$router->add('/eventos/nova-luta', 'EventosController', 'novaLuta');
$router->add('/eventos/salvar-luta', 'EventosController', 'salvarLuta');
$router->add('/eventos/edit-luta', 'EventosController', 'editLuta');
$router->add('/eventos/update-luta', 'EventosController', 'updateLuta');
$router->add('/eventos/excluir-luta', 'EventosController', 'excluirLuta');
$router->add('/eventos/buscarCategoriasPorModalidade', 'EventosController', 'buscarCategoriasPorModalidade');

// --- Lançamento de Resultados ---
$router->add('/eventos/lancar-resultado', 'EventosController', 'lancarLuta'); 
$router->add('/eventos/salvar-resultado', 'EventosController', 'salvarResultado'); 

// --- Importacao em Massa ---
$router->add('/importacao', 'ImportacaoController', 'index');
$router->add('/importacao/processar', 'ImportacaoController', 'processar');
// --- Importação do Tapology ---
$router->add('/tapology', 'TapologyController', 'index');
$router->add('/tapology/analisar', 'TapologyController', 'analisar');
$router->add('/tapology/importar', 'TapologyController', 'importar');

// Compatibilidade com links/formulários antigos
$router->add('/importacao/tapology', 'TapologyController', 'index');
$router->add('/importacao/tapology/analisar', 'TapologyController', 'analisar');
$router->add('/importacao/tapology/importar', 'TapologyController', 'importar');


// ==========================================
// PROCESSAMENTO DA URI (DINÂMICO)
// ==========================================

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

/**
 * Lógica para remover o prefixo da pasta se existir.
 * Extrai o PATH da BASE_URL definida no Config.php.
 */
$basePath = parse_url(BASE_URL, PHP_URL_PATH);

if (!empty($basePath) && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Limpeza básica
if (empty($uri) || $uri === '') {
    $uri = '/';
}

// Garante que comece com /
if ($uri !== '/' && $uri[0] !== '/') {
    $uri = '/' . $uri;
}

// Conecta ao banco e executa a rota
$db = Database::getConnection();
$router->run($uri, $db);

ob_end_flush();