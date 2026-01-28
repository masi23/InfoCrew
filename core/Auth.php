<?php

class Auth
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login($user)
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
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function user()
    {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    public static function isLoggedIn(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user && ($user['role'] ?? '') === 'admin';
    }

    public static function checkAdmin()
    {
        if (!self::isAdmin()) {
            throw new Exception("Brak autoryzacji - wymagane uprawnienia administratora");
        }
    }

    public static function checkLogin()
    {
        if (!self::isLoggedIn()) {
            throw new Exception("Brak autoryzacji - wymagane zalogowanie");
        }
    }
}
