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
            echo json_encode(['error' => 'Brak ID filmu lub recenzji']);
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

        echo json_encode($this->reviewRepo->getAll());
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

}
