<?php

require_once 'models/Movie.php';
require_once 'core/ORM.php';

class MovieRepository
{
    private array $data;
    private string $filePath = __DIR__ . '/../data/movies.php';

    public function __construct()
    {
        $this->data = file_exists($this->filePath) ? require $this->filePath : [];
    }

    public function getAll(): array
    {
        return $this->data;
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

    public function create(array $movieData): bool
    {
        $movieData['id'] = time();
        $movieData['reviews'] = [];
        $this->data[] = $movieData;
        return $this->saveToFile();
    }

    public function update($id, array $newData): bool
    {
        foreach ($this->data as $index => $movie) {
            if ($movie['id'] == $id) {
                $this->data[$index] = array_merge($this->data[$index], $newData);
                return $this->saveToFile();
            }
        }
        return false;
    }

    public function delete($id): bool
    {
        $initialCount = count($this->data);
        $this->data = array_filter($this->data, fn($m) => $m['id'] != $id);

        if (count($this->data) < $initialCount) {
            return $this->saveToFile();
        }
        return false;
    }

    public function addReview($movieId, $reviewData)
    {
        foreach ($this->data as &$movie) {
            if ($movie['id'] == $movieId) {
                if (!isset($movie['reviews']) || !is_array($movie['reviews'])) {
                    $movie['reviews'] = [];
                }

                array_unshift($movie['reviews'], [
                    'user' => $reviewData['user'] ?? 'Anonim',
                    'text' => $reviewData['text'],
                    'date' => $reviewData['date'] ?? date('Y-m-d')
                ]);

                return $this->saveToFile();
            }
        }
        return false;
    }

    private function saveToFile(): bool
    {
        $content = "<?php\nreturn " . var_export($this->data, true) . ";";
        return file_put_contents($this->filePath, $content) !== false;
    }

    public function findFiltered(array $filters): array
    {
        $movies = $this->findAll();

        // --- WYSZUKIWANIE (Tytuł, Opis, Aktorzy) ---
        if (!empty($filters['search'])) {
            $search = mb_strtolower($filters['search']);
            $movies = array_filter($movies, function ($m) use ($search) {
                $inTitle = str_contains(mb_strtolower($m->title), $search);
                $inDesc = str_contains(mb_strtolower($m->description), $search);
                $inCast = false;
                if (is_array($m->cast)) {
                    foreach ($m->cast as $actor) {
                        if (str_contains(mb_strtolower($actor), $search)) {
                            $inCast = true;
                            break;
                        }
                    }
                }
                return $inTitle || $inDesc || $inCast;
            });
        }

        // --- FILTROWANIE PLATFORM ---
        $platformVal = $filters['platform'] ?? ($filters['platforms'] ?? null);
        if (!empty($platformVal)) {
            $platforms = explode(',', $platformVal);
            $movies = array_filter($movies, fn($m) => in_array($m->platform, $platforms));
        }

        // --- FILTROWANIE GATUNKÓW ---
        $categoryVal = $filters['category'] ?? ($filters['categories'] ?? null);
        if (!empty($categoryVal)) {
            $categories = explode(',', $categoryVal);
            $movies = array_filter($movies, fn($m) =>
                is_array($m->genres) && array_intersect($m->genres, $categories)
            );
        }

        // --- JĘZYK ---
        if (!empty($filters['language'])) {
            $movies = array_filter($movies, fn($m) => $m->language === $filters['language']);
        }

        // --- ROK ---
        if (!empty($filters['year'])) {
            if ($filters['year'] === 'older') {
                $movies = array_filter($movies, fn($m) => (int)$m->year < 2019);
            } else {
                $movies = array_filter($movies, fn($m) => (int)$m->year == (int)$filters['year']);
            }
        }

        // --- OCENA ---
        if (!empty($filters['rating'])) {
            $movies = array_filter($movies, fn($m) => (float)$m->rating >= (float)$filters['rating']);
        }

        // --- TYP ---
        if (!empty($filters['type'])) {
            $movies = array_filter($movies, fn($m) => $m->type === $filters['type']);
        }

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