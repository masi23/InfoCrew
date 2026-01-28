<?php

class Router
{
    public function handleRequest()
    {
        $controllerName = $_GET['controller'] ?? 'movie';
        $action = $_GET['action'] ?? 'index';

        $controllerClass = ucfirst($controllerName) . 'Controller';
        $controllerFile = __DIR__ . "/../controllers/$controllerClass.php";

        if (!file_exists($controllerFile)) {
            http_response_code(404);
            echo json_encode(['error' => "Controller '$controllerClass' not found"]);
            return;
        }

        require_once $controllerFile;

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            http_response_code(404);
            echo json_encode(['error' => "Action '$action' not found in '$controllerClass'"]);
            return;
        }

        try {
            $controller->$action();
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
