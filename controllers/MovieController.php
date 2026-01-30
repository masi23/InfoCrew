<?php

require_once __DIR__ . '/../repositories/MovieRepository.php';
require_once __DIR__ . '/../repositories/ReviewRepository.php';
require_once __DIR__ . '/../core/Auth.php';

class MovieController
{
    private MovieRepository $repo;
    private ReviewRepository $reviewRepo;

    public function __construct()
    {
        $this->repo = new MovieRepository();
        $this->reviewRepo = new ReviewRepository();
    }

    // ==================== FILMY ====================

    public function index()
    {
        $movies = $this->repo->findFiltered($_GET);
        header('Content-Type: application/json');
        echo json_encode(array_values($movies));
    }

    public function show()
{
    $id = $_GET['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Brak ID']);
        return;
    }

    $movie = $this->repo->findById($id);

    if (!$movie) {
        http_response_code(404);
        echo json_encode(['error' => 'Film nie istnieje']);
        return;
    }

    echo json_encode($movie);
}

    // ==================== RECENZJE ====================

   public function likeReview()
{
    $movieId = $_GET['movieId'] ?? null;
    $reviewId = $_GET['reviewId'] ?? null;

    if (!$movieId || !$reviewId) {
        http_response_code(400);
        echo json_encode(['error' => 'Brak wymaganych parametrów']);
        return;
    }

    $this->reviewRepo->likeReview($movieId, $reviewId);
    echo json_encode(['success' => true]);
}

   public function allReviews()
{
    try {
        Auth::checkAdmin();
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['error' => $e->getMessage()]);
        return;
    }

    $reviews = $this->reviewRepo->getAll(); // Pobiera surowe recenzje
    $movies = $this->repo->getAll(); // Pobiera wszystkie filmy z movies.php

    // Tworzymy mapę [id_filmu => tytuł_filmu] dla szybkiego wyszukiwania
    $movieTitles = [];
    foreach ($movies as $movie) {
        $movieTitles[$movie['id']] = $movie['title'];
    }

    // Dopasowujemy tytuły do recenzji
    foreach ($reviews as &$review) {
        $review['movieTitle'] = $movieTitles[$review['movieId']] ?? 'Nieznany';
    }

    echo json_encode($reviews);
}
   public function deleteReview()
   {
    try {
        Auth::checkAdmin();
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['error' => $e->getMessage()]);
        return;
    }

    $movieId = $_GET['movieId'] ?? null;
    $reviewId = $_GET['reviewId'] ?? null;

    if ($this->reviewRepo->delete($movieId, $reviewId)) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Błąd usuwania recenzji']);
    }
}


   public function highlightReview()
   {
    try {
        Auth::checkAdmin();
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['error' => $e->getMessage()]);
        return;
    }

    $movieId = $_GET['movieId'] ?? null;
    $reviewId = $_GET['reviewId'] ?? null;
    $data = json_decode(file_get_contents('php://input'), true);
    $highlight = $data['highlight'] ?? false;

    if ($this->reviewRepo->setHighlight($movieId, $reviewId, $highlight)) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Błąd zmiany wyróżnienia']);
    }
}
    public function addReview()
{
    $movieId = $_GET['id'] ?? null;

    if (!$movieId) {
        http_response_code(400);
        echo json_encode(['error' => 'Brak ID filmu']);
        return;
    }

    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input || strlen(trim($input['text'] ?? '')) < 3) {
        http_response_code(400);
        echo json_encode(['error' => 'Recenzja musi mieć min. 3 znaki']);
        return;
    }
    // Generowanie unikalnego ID recenzji (wszystkie recenzje a Nie dla kazdego filmu)
    $allReviews = $this->reviewRepo->getAll();
    $nextId = 1;

    if (!empty($allReviews)) 
    {
        $ids = array_column($allReviews, 'id');
        $nextId = max($ids) + 1;
    }

    $review = [
        //'id' => substr(uniqid(), -6),
        'id' => $nextId,
        'movieId' => $movieId,
        'user' => htmlspecialchars($input['user'] ?? 'Użytkownik'),
        'text' => $input['text'],
        'rating' => (int)($input['rating'] ?? 0),
        'date' => date('Y-m-d'),
        'likes' => 0,
        'highlighted' => false
    ];

    $this->reviewRepo->add($review);

    echo json_encode(['success' => true]);
}

public function create() {
    try {
        Auth::checkAdmin(); // Sprawdzenie uprawnień
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->repo->create($data)) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Błąd zapisu filmu");
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

public function update() {
    try {
        Auth::checkAdmin();
        $id = $_GET['id'] ?? null;
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->repo->update($id, $data)) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Błąd aktualizacji filmu");
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

public function delete() {
    try {
        Auth::checkAdmin();
        $id = $_GET['id'] ?? null;
        if ($this->repo->delete($id)) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Błąd usuwania filmu");
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// controllers/MovieController.php

public function userDeleteReview() {
    try {
        // 1. Pobierz zalogowanego użytkownika z sesji
        $currentUser = Auth::getCurrentUser(); // Zakładam, że masz taką metodę w Auth.php
        if (!$currentUser) {
            throw new Exception("Musisz być zalogowany, aby usunąć komentarz.");
        }

        $movieId = $_GET['movieId'] ?? null;
        $reviewId = $_GET['reviewId'] ?? null;

        // 2. Pobierz recenzję, aby sprawdzić autora
        $review = $this->reviewRepo->getById($reviewId); // Musisz mieć taką metodę w repozytorium

        if (!$review) {
            throw new Exception("Recenzja nie istnieje.");
        }

        // 3. Sprawdź, czy zalogowany użytkownik to autor recenzji
        if ($review['user'] !== $currentUser['username'] && !Auth::isAdmin()) {
            throw new Exception("Nie masz uprawnień do usunięcia tej recenzji.");
        }

        // 4. Usuń
        if ($this->reviewRepo->delete($movieId, $reviewId)) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Błąd podczas usuwania.");
        }

    } catch (Exception $e) {
        http_response_code(403);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
}
