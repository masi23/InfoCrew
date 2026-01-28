<?php

require_once __DIR__ . '/../models/Movie.php';
require_once __DIR__ . '/../core/ORM.php';
require_once __DIR__ . '/ReviewRepository.php';


class MovieRepository
{
    private array $data;
    private string $filePath;
    private ReviewRepository $reviewRepo;


    public function __construct()
    {
        $this->filePath = __DIR__ . '/../data/movies.php';
        $this->data = file_exists($this->filePath) ? require $this->filePath : [];
        $this->reviewRepo = new ReviewRepository();
    }

    public function getAll(): array
    {
        return $this->data;
    }

    public function findAll(): array
    {
        $movies = [];
        foreach ($this->data as $row) {
            $movies[] = $this->mapToMovie($row);
        }
        return $movies;
    }

    /**
     * Mapuje tablicę na obiekt Movie (obsługuje zarówno 'language' jak i 'country')
     */
    private function mapToMovie(array $row): Movie
    {
        $movie = new Movie();
        $movie->id = $row['id'] ?? null;
        $movie->title = $row['title'] ?? '';
        $movie->poster = $row['poster'] ?? '';
        $movie->backdrop = $row['backdrop'] ?? '';
        $movie->platform = $row['platform'] ?? '';
        $movie->rating = $row['rating'] ?? 0;
        $movie->year = $row['year'] ?? 0;
        $movie->duration = $row['duration'] ?? '';
        // Obsługa zarówno 'country' jak i starszego 'language'
        $movie->country = $row['country'] ?? $row['language'] ?? '';
        $movie->genres = $row['genres'] ?? [];
        $movie->type = $row['type'] ?? '';
        $movie->cast = $row['cast'] ?? [];
        $movie->description = $row['description'] ?? '';
        $movie->popularity = $row['popularity'] ?? 0;
        $movie->reviews = $this->reviewRepo->getByMovie($movie->id);
        return $movie;
    }

    public function findById($id): ?array
    {
        foreach ($this->data as $row) {
            if ($row['id'] == $id) {
                // Zwracamy tablicę z obsługą country/language
                $row['country'] = $row['country'] ?? $row['language'] ?? '';
                $row['reviews'] = $this->reviewRepo->getByMovie($id);
                return $row;
            }
        }
        return null;
    }

    public function create(array $movieData): bool
    {
        $movieData['id'] = time() . rand(100, 999);
        $movieData['reviews'] = [];
        $movieData['popularity'] = $movieData['popularity'] ?? 50;
        $movieData['genres'] = $this->parseArray($movieData['genres'] ?? []);
        $movieData['cast'] = $this->parseArray($movieData['cast'] ?? []);
        
        $this->data[] = $movieData;
        return $this->saveToFile();
    }

    public function update($id, array $newData): bool
    {
        foreach ($this->data as $index => $movie) {
            if ($movie['id'] == $id) {
                // Parsowanie tablic jeśli przyszły jako string
                if (isset($newData['genres'])) {
                    $newData['genres'] = $this->parseArray($newData['genres']);
                }
                if (isset($newData['cast'])) {
                    $newData['cast'] = $this->parseArray($newData['cast']);
                }
                
                // Zachowaj recenzje
                $newData['reviews'] = $movie['reviews'] ?? [];
                
                $this->data[$index] = array_merge($this->data[$index], $newData);
                return $this->saveToFile();
            }
        }
        return false;
    }

    public function delete($id): bool
    {
        $initialCount = count($this->data);
        $this->data = array_values(array_filter($this->data, fn($m) => $m['id'] != $id));

        if (count($this->data) < $initialCount) {
            return $this->saveToFile();
        }
        return false;
    }

    /**
     * Dodawanie recenzji z oceną i filtrowaniem wulgaryzmów
     */
    public function addReview($movieId, $reviewData): bool
    {
        foreach ($this->data as &$movie) {
            if ($movie['id'] == $movieId) {
                if (!isset($movie['reviews']) || !is_array($movie['reviews'])) {
                    $movie['reviews'] = [];
                }

                // Filtrowanie wulgaryzmów
                $filteredText = $this->filterBadWords($reviewData['text'] ?? '');

                $review = [
                    'id' => time() . rand(100, 999),
                    'user' => htmlspecialchars($reviewData['user'] ?? 'Anonim'),
                    'text' => $filteredText,
                    'rating' => max(1, min(10, (int)($reviewData['rating'] ?? 0))),
                    'date' => $reviewData['date'] ?? date('Y-m-d'),
                    'likes' => 0,
                    'highlighted' => false
                ];

                array_unshift($movie['reviews'], $review);
                return $this->saveToFile();
            }
        }
        return false;
    }

    /**
     * Aktualizacja recenzji
     */
    public function updateReview($movieId, $reviewId, $newData): bool
    {
        foreach ($this->data as &$movie) {
            if ($movie['id'] == $movieId && isset($movie['reviews'])) {
                foreach ($movie['reviews'] as &$review) {
                    if (($review['id'] ?? '') == $reviewId) {
                        if (isset($newData['text'])) {
                            $review['text'] = $this->filterBadWords($newData['text']);
                        }
                        if (isset($newData['highlighted'])) {
                            $review['highlighted'] = (bool)$newData['highlighted'];
                        }
                        if (isset($newData['likes'])) {
                            $review['likes'] = (int)$newData['likes'];
                        }
                        return $this->saveToFile();
                    }
                }
            }
        }
        return false;
    }

    /**
     * Usuwanie recenzji
     */
    public function deleteReview($movieId, $reviewId): bool
    {
        foreach ($this->data as &$movie) {
            if ($movie['id'] == $movieId && isset($movie['reviews'])) {
                $initialCount = count($movie['reviews']);
                $movie['reviews'] = array_values(array_filter(
                    $movie['reviews'],
                    fn($r) => ($r['id'] ?? '') != $reviewId
                ));
                
                if (count($movie['reviews']) < $initialCount) {
                    return $this->saveToFile();
                }
            }
        }
        return false;
    }

    /**
     * Podbijanie (like) recenzji
     */
    public function likeReview($movieId, $reviewId): bool
    {
        foreach ($this->data as &$movie) {
            if ($movie['id'] == $movieId && isset($movie['reviews'])) {
                foreach ($movie['reviews'] as &$review) {
                    if (($review['id'] ?? '') == $reviewId) {
                        $review['likes'] = ($review['likes'] ?? 0) + 1;
                        return $this->saveToFile();
                    }
                }
            }
        }
        return false;
    }

    /**
     * Wyróżnianie recenzji przez admina
     */
    public function highlightReview($movieId, $reviewId, $highlight = true): bool
    {
        return $this->updateReview($movieId, $reviewId, ['highlighted' => $highlight]);
    }

    /**
     * Filtrowanie wulgaryzmów
     */
    private function filterBadWords(string $text): string
    {
        $badWords = $this->loadBadWords();
        
        foreach ($badWords as $word) {
            $word = trim($word);
            if (empty($word)) continue;
            
            $pattern = '/\b' . preg_quote($word, '/') . '\b/iu';
            $replacement = str_repeat('*', mb_strlen($word));
            $text = preg_replace($pattern, $replacement, $text);
        }
        
        return $text;
    }

    /**
     * Ładowanie listy zakazanych słów
     */
    private function loadBadWords(): array
    {
        $filePath = __DIR__ . '/../data/badwords.php';
        if (file_exists($filePath)) {
            return require $filePath;
        }
        return [];
    }

    /**
     * NAPRAWIONE filtrowanie - obsługuje wszystkie parametry
     */
    public function findFiltered(array $filters): array
    {
        $movies = $this->findAll();

        // --- WYSZUKIWANIE (Tytuł, Opis, Aktorzy) ---
        if (!empty($filters['search'])) {
            $search = mb_strtolower(trim($filters['search']));
            $movies = array_filter($movies, function ($m) use ($search) {
                $inTitle = mb_strpos(mb_strtolower($m->title ?? ''), $search) !== false;
                $inDesc = mb_strpos(mb_strtolower($m->description ?? ''), $search) !== false;
                $inCast = false;
                if (is_array($m->cast)) {
                    foreach ($m->cast as $actor) {
                        if (mb_strpos(mb_strtolower($actor), $search) !== false) {
                            $inCast = true;
                            break;
                        }
                    }
                }
                return $inTitle || $inDesc || $inCast;
            });
        }

        // --- FILTROWANIE PLATFORM ---
        $platformVal = $filters['platform'] ?? $filters['platforms'] ?? null;
        if (!empty($platformVal)) {
            $platforms = is_array($platformVal) ? $platformVal : explode(',', $platformVal);
            $platforms = array_map('trim', $platforms);
            $movies = array_filter($movies, fn($m) => in_array($m->platform, $platforms));
        }

        // --- FILTROWANIE GATUNKÓW ---
        $categoryVal = $filters['category'] ?? $filters['categories'] ?? null;
        if (!empty($categoryVal)) {
            $categories = is_array($categoryVal) ? $categoryVal : explode(',', $categoryVal);
            $categories = array_map('trim', $categories);
            $movies = array_filter($movies, fn($m) =>
                is_array($m->genres) && !empty(array_intersect($m->genres, $categories))
            );
        }

        // --- KRAJ (zmienione z language) ---
        $countryVal = $filters['country'] ?? $filters['language'] ?? null;
        if (!empty($countryVal)) {
            $movies = array_filter($movies, fn($m) => $m->country === $countryVal);
        }

        // --- ROK ---
        if (!empty($filters['year'])) {
            if ($filters['year'] === 'older') {
                $movies = array_filter($movies, fn($m) => (int)$m->year < 2021);
            } else {
                $movies = array_filter($movies, fn($m) => (int)$m->year == (int)$filters['year']);
            }
        }

        // --- OCENA MINIMALNA ---
        if (!empty($filters['rating'])) {
            $movies = array_filter($movies, fn($m) => (float)$m->rating >= (float)$filters['rating']);
        }

        // --- TYP (Film/Serial) ---
        if (!empty($filters['type'])) {
            $movies = array_filter($movies, fn($m) => $m->type === $filters['type']);
        }

        return $this->sort(array_values($movies), $filters['sort'] ?? null);
    }

    /**
     * Sortowanie wyników
     */
    private function sort(array $movies, ?string $sort): array
    {
        usort($movies, function ($a, $b) use ($sort) {
            return match ($sort) {
                'rating' => ($b->rating ?? 0) <=> ($a->rating ?? 0),
                'newest' => ($b->year ?? 0) <=> ($a->year ?? 0),
                'title' => strcmp($a->title ?? '', $b->title ?? ''),
                default => ($b->popularity ?? 0) <=> ($a->popularity ?? 0),
            };
        });
        return $movies;
    }

    /**
     * Parsowanie tablicy z różnych formatów
     */
    private function parseArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value) && !empty($value)) {
            return array_map('trim', explode(',', $value));
        }
        return [];
    }

    /**
     * Zapis do pliku
     */
    private function saveToFile(): bool
    {
        $content = "<?php\nreturn " . var_export(array_values($this->data), true) . ";\n";
        return file_put_contents($this->filePath, $content) !== false;
    }

    /**
     * Pobieranie wszystkich recenzji (dla panelu admina)
     */
    public function getAllReviews(): array
    {
        $allReviews = [];
        foreach ($this->data as $movie) {
            if (!empty($movie['reviews'])) {
                foreach ($movie['reviews'] as $review) {
                    $review['movieId'] = $movie['id'];
                    $review['movieTitle'] = $movie['title'];
                    $allReviews[] = $review;
                }
            }
        }
        // Sortuj od najnowszych
        usort($allReviews, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));
        return $allReviews;
    }
}
