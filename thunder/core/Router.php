<?php

class Router
{
    protected $routes = [];

    public function add($route, $controller, $method)
    {
        $this->routes[$route] = [
            'controller' => $controller,
            'method' => $method
        ];
    }

    public function run($uri, $db)
    {
        // Limpa a URI de parâmetros query (?id=1)
        $uri = explode('?', $uri)[0];

        // Remove a barra final (ex: /importacao/ vira /importacao), exceto na home
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }

        if (array_key_exists($uri, $this->routes)) {
            $controllerName = $this->routes[$uri]['controller'];
            $methodName = $this->routes[$uri]['method'];

            if (class_exists($controllerName)) {
                // AQUI ESTÁ A CORREÇÃO: Passando o $db para o Controller
                $controller = new $controllerName($db);
                $controller->$methodName();
            } else {
                echo "Erro: Controller $controllerName não encontrado.";
            }
        } else {
            echo "404 - Rota não encontrada: " . htmlspecialchars($uri);
        }
    }
}