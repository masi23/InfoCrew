<?php

require_once 'repositories/UserRepository.php';
require_once 'core/Auth.php';

class AuthController
{
    public function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $repo = new UserRepository();
        $user = $repo->findByUsername($data['username']);

        if (!$user || !password_verify($data['password'], $user->password)) {
            http_response_code(401);
            echo json_encode(['error' => 'Nieprawidłowe dane']);
            return;
        }

        Auth::login($user);
        echo json_encode(['success' => true]);
    }

    public function logout()
    {
        Auth::logout();
        echo json_encode(['success' => true]);
    }
}
