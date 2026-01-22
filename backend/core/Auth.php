<?php

class Auth
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login(User $user)
    {
        self::start();
        $_SESSION['user'] = [
            'id' => $user->id,
            'username' => $user->username,
            'role' => $user->role
        ];
    }

    public static function logout()
    {
        self::start();
        session_destroy();
    }

    public static function user()
    {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    public static function checkAdmin()
    {
        $user = self::user();
        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Brak dostępu']);
            exit;
        }
    }
}
