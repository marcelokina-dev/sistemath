<?php
$isLocal = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);

if ($isLocal) {
    // URL para o navegador (Links, CSS, JS, Imagens)
    define('BASE_URL', '/thunder/public'); 
    
    // Caminho físico para o PHP (file_exists, include, uploads)
    define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/thunder/public');
    
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'thunder_fight');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    // Na produção (Ex: se for www.seusite.com.br direto na raiz)
    define('BASE_URL', ''); 
    
    // Na produção, o BASE_PATH geralmente é apenas o DOCUMENT_ROOT
    define('BASE_PATH', $_SERVER['DOCUMENT_ROOT']); 
    
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'u123456789_thunder');
    define('DB_USER', 'u123456789_admin');
    define('DB_PASS', 'SuaSenhaSegura123!');
}