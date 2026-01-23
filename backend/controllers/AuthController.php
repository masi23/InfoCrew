<?php
require_once 'repositories/UserRepository.php';
require_once 'core/Auth.php';

class AuthController {
    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        //var_dump($data); die();
        $repo = new UserRepository();
        $user = $repo->findByUsername($data['username'] ?? '');

        if ($user && password_verify($data['password'] ?? '', $user->password)) {
            Auth::login($user);
            echo json_encode(['success' => true]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Nieprawidłowe dane logowania']);
        }
    }

    public function check() {
        try {
            Auth::checkAdmin();
            echo json_encode(['status' => 'logged_in']);
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['status' => 'unauthorized']);
        }
    }

    public function logout() {
        Auth::logout();
        echo json_encode(['success' => true]);
    }

    public function updateProfile() {
        Auth::checkAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        $repo = new UserRepository();
        if ($repo->update($data)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd zapisu']);
        }
    }
}