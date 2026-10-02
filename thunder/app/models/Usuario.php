<?php

class Usuario {

    private $conn;
    private $table = "usuarios";

    public function __construct($db){
        $this->conn = $db;
    }

    public function login($email,$senha){

        $query = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email",$email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user && password_verify($senha,$user['senha'])){
            return $user;
        }

        return false;

    }

}