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
        // Zapisujemy dane jako tablicę (object z repozytorium zostanie zrzucony do tablicy)
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
        // Jeśli nie ma zalogowanego admina, wyrzucamy wyjątek zamiast exit
        if (!$user || ($user['role'] ?? '') !== 'admin') {
            throw new Exception("Brak autoryzacji");
        }
    }
}