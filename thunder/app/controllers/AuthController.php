<?php

class AuthController {

    public function login(){

        require '../app/views/login.php';

    }

    public function autenticar(){

        $email = $_POST['email'];
        $senha = $_POST['senha'];

        $db = new Database();
        $conn = $db->connect();

        $usuario = new Usuario($conn);

        $login = $usuario->login($email,$senha);

        if($login){

            session_start();

            $_SESSION['usuario'] = $login['nome'];
            $_SESSION['tipo'] = $login['tipo'];

            header("Location: /dashboard");

        }else{

            echo "Login inválido";

        }

    }

    public function logout(){

        session_start();
        session_destroy();

        header("Location: /login");

    }

}