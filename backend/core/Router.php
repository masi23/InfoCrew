<?php

class Router
{
    public function handleRequest()
    {
        $controllerName = $_GET['controller'] ?? 'movie';
        $action = $_GET['action'] ?? 'index';

        $controllerClass = ucfirst($controllerName) . 'Controller';
        $controllerFile = "controllers/$controllerClass.php";

        if (!file_exists($controllerFile)) {
            http_response_code(404);
            echo "Controller not found";
            return;
        }

        require_once $controllerFile;

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            http_response_code(404);
            echo "Action not found";
            return;
        }

        $controller->$action();
    }
}
