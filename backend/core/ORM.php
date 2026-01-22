<?php

class ORM
{
    public static function map(array $data, string $className)
    {
        return new $className(
            $data['id'],
            $data['title'],
            $data['poster'] ?? null,
            $data['backdrop'] ?? null,
            $data['platform'] ?? $data['platforms'] ?? null, // Obsługa obu wariantów klucza
            $data['rating'],
            $data['year'] ?? null,
            $data['duration'] ?? null,
            $data['language'] ?? null,
            $data['genres'] ?? null,
            $data['type'] ?? null,
            $data['cast'] ?? null,
            $data['description'],
            $data['popularity'] ?? null
        );
    }

    public static function mapUser(array $data): User
{
    return new User(
        $data['id'],
        $data['username'],
        $data['password'],
        $data['role']
    );
}

}