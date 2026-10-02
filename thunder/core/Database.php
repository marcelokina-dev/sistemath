<?php

class Database {

    private static $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection() {
        if (!self::$instance) {
            try {
                // Agora usamos as CONSTANTES que definimos no Config.php
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                
                self::$instance = new PDO(
                    $dsn,
                    DB_USER, 
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );

            } catch (PDOException $e) {
                // Uma mensagem amigável, mas que ainda ajuda a debugar
                die("Falha na conexão com o banco do Thunder Fight: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}