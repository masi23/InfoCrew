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
}
