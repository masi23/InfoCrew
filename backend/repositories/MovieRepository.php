<?php

require_once 'models/Movie.php';
require_once 'core/ORM.php';

class MovieRepository
{
    private array $data;

    public function __construct()
    {
        $this->data = require 'data/movies.php';
    }

    public function findAll(): array
    {
        $movies = [];

        foreach ($this->data as $row) {
            $movies[] = ORM::map($row, Movie::class);
        }

        return $movies;
    }

    public function findById($id): ?Movie
    {
        foreach ($this->data as $row) {
            if ($row['id'] == $id) {
                return ORM::map($row, Movie::class);
            }
        }
        return null;
    }

    public function findFiltered(array $filters): array
{
    $movies = $this->findAll();

    // search
    if (!empty($filters['search'])) {
        $search = mb_strtolower($filters['search']);
        $movies = array_filter($movies, function ($m) use ($search) {
            return str_contains(mb_strtolower($m->title), $search)
                || str_contains(mb_strtolower($m->description), $search)
                || array_filter($m->cast, fn($c) =>
                    str_contains(mb_strtolower($c), $search)
                );
        });
    }

    // platform
    if (!empty($filters['platforms'])) {
        $platforms = explode(',', $filters['platforms']);
        $movies = array_filter($movies, fn($m) =>
            in_array($m->platform, $platforms)
        );
    }

    // categories (genres)
    if (!empty($filters['categories'])) {
        $categories = explode(',', $filters['categories']);
        $movies = array_filter($movies, fn($m) =>
            array_intersect($m->genres, $categories)
        );
    }

    // language
    if (!empty($filters['language'])) {
        $movies = array_filter($movies, fn($m) =>
            $m->language === $filters['language']
        );
    }

    // year
    if (!empty($filters['year'])) {
        if ($filters['year'] === 'older') {
            $movies = array_filter($movies, fn($m) => $m->year < 2019);
        } else {
            $movies = array_filter($movies, fn($m) =>
                $m->year == (int)$filters['year']
            );
        }
    }

    // rating
    if (!empty($filters['rating'])) {
        $movies = array_filter($movies, fn($m) =>
            $m->rating >= (float)$filters['rating']
        );
    }

    // type
    if (!empty($filters['type'])) {
        $movies = array_filter($movies, fn($m) =>
            $m->type === $filters['type']
        );
    }

    // sort
    return $this->sort($movies, $filters['sort'] ?? null);
}

private function sort(array $movies, ?string $sort): array
{
    usort($movies, function ($a, $b) use ($sort) {
        return match ($sort) {
            'rating' => $b->rating <=> $a->rating,
            'newest' => $b->year <=> $a->year,
            'title' => strcmp($a->title, $b->title),
            default => $b->popularity <=> $a->popularity,
        };
    });

    return $movies;
}


}
