<?php

require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../core/Auth.php';

class AuthController
{
    private UserRepository $repo;

    public function __construct()
    {
        $this->repo = new UserRepository();
    }

    /**
     * Logowanie
     * POST ?controller=auth&action=login
     */
    public function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $user = $this->repo->findByUsername($data['username'] ?? '');

        if ($user && password_verify($data['password'] ?? '', $user->password)) {
            Auth::login($user);
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'role' => $user->role
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Nieprawidłowe dane logowania']);
        }
    }

    /**
     * Sprawdzanie statusu logowania
     * GET ?controller=auth&action=check
     */
    public function check()
    {
        if (Auth::isLoggedIn()) {
            echo json_encode([
                'status' => 'logged_in',
                'user' => Auth::user()
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['status' => 'unauthorized']);
        }
    }

    /**
     * Wylogowanie
     * GET ?controller=auth&action=logout
     */
    public function logout()
    {
        Auth::logout();
        echo json_encode(['success' => true]);
    }

    /**
     * Aktualizacja profilu (prosta wersja)
     * POST ?controller=auth&action=updateProfile
     */
    public function updateProfile()
    {
        try {
            Auth::checkAdmin();
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => $e->getMessage()]);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if ($this->repo->update($data)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd zapisu']);
        }
    }

    /**
     * Zmiana hasła z weryfikacją aktualnego hasła
     * POST ?controller=auth&action=changePassword
     */
    public function changePassword()
    {
        try {
            Auth::checkAdmin();
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => $e->getMessage()]);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $user = Auth::user();

        if (empty($data['currentPassword'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Podaj aktualne hasło']);
            return;
        }

        if (empty($data['newPassword'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Podaj nowe hasło']);
            return;
        }

        if (strlen($data['newPassword']) < 6) {
            http_response_code(400);
            echo json_encode(['error' => 'Nowe hasło musi mieć minimum 6 znaków']);
            return;
        }

        $result = $this->repo->updateWithVerification(
            $user['id'],
            $data['currentPassword'],
            [
                'username' => $data['newUsername'] ?? null,
                'newPassword' => $data['newPassword']
            ]
        );

        if ($result === true) {
            // Aktualizuj sesję jeśli zmieniono username
            if (!empty($data['newUsername'])) {
                $_SESSION['user']['username'] = $data['newUsername'];
            }
            echo json_encode(['success' => true, 'message' => 'Hasło zostało zmienione']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => is_string($result) ? $result : 'Błąd zmiany hasła']);
        }
    }
}
