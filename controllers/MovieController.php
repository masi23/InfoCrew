<?php

require_once __DIR__ . '/../repositories/MovieRepository.php';
require_once __DIR__ . '/../core/Auth.php';

class MovieController
{
    private MovieRepository $repo;

    public function __construct()
    {
        $this->repo = new MovieRepository();
    }

    /**
     * Pobieranie filmów z uwzględnieniem filtrów
     * GET ?controller=movie&action=index
     */
    public function index()
    {
        $movies = $this->repo->findFiltered($_GET);
        
        header('Content-Type: application/json');
        echo json_encode(array_values($movies));
    }

    /**
     * Pobieranie danych jednego filmu
     * GET ?controller=movie&action=show&id={id}
     */
    public function show()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Brak ID']);
            return;
        }

        $movie = $this->repo->findById($id);

        header('Content-Type: application/json');
        if ($movie) {
            echo json_encode($movie);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Film nie istnieje']);
        }
    }

    /**
     * Dodawanie nowego filmu (wymaga admina)
     * POST ?controller=movie&action=create
     */
    public function create()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || empty($data['title'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Tytuł jest wymagany']);
            return;
        }

        if ($this->repo->create($data)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd zapisu w bazie danych']);
        }
    }

    /**
     * Edycja istniejącego filmu (wymaga admina)
     * POST ?controller=movie&action=update
     */
    public function update()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $_GET['id'] ?? ($data['id'] ?? null);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Brak ID filmu do edycji']);
            return;
        }

        if ($this->repo->update($id, $data)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd aktualizacji']);
        }
    }

    /**
     * Usuwanie filmu (wymaga admina)
     * GET ?controller=movie&action=delete&id={id}
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Brak ID do usunięcia']);
            return;
        }

        if ($this->repo->delete($id)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd usuwania']);
        }
    }

    // ==================== RECENZJE ====================

    /**
     * Dodawanie nowej recenzji do filmu
     * POST ?controller=movie&action=addReview&id={id}
     */
    public function addReview()
    {
        $id = $_GET['id'] ?? null;
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Brak ID filmu']);
            return;
        }

        if (!$data || empty($data['text'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Brak treści recenzji']);
            return;
        }

        if ($this->repo->addReview($id, $data)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd podczas zapisywania recenzji']);
        }
    }

    /**
     * Aktualizacja recenzji (wymaga admina)
     * POST ?controller=movie&action=updateReview&movieId={}&reviewId={}
     */
    public function updateReview()
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

        if (!$movieId || !$reviewId) {
            http_response_code(400);
            echo json_encode(['error' => 'Brak ID filmu lub recenzji']);
            return;
        }

        if ($this->repo->updateReview($movieId, $reviewId, $data)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd aktualizacji recenzji']);
        }
    }

    /**
     * Usuwanie recenzji (wymaga admina)
     * GET ?controller=movie&action=deleteReview&movieId={}&reviewId={}
     */
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

        if (!$movieId || !$reviewId) {
            http_response_code(400);
            echo json_encode(['error' => 'Brak ID filmu lub recenzji']);
            return;
        }

        if ($this->repo->deleteReview($movieId, $reviewId)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd usuwania recenzji']);
        }
    }

    /**
     * Podbijanie recenzji (like)
     * POST ?controller=movie&action=likeReview&movieId={}&reviewId={}
     */
    public function likeReview()
    {
        $movieId = $_GET['movieId'] ?? null;
        $reviewId = $_GET['reviewId'] ?? null;

        if (!$movieId || !$reviewId) {
            http_response_code(400);
            echo json_encode(['error' => 'Brak ID filmu lub recenzji']);
            return;
        }

        if ($this->repo->likeReview($movieId, $reviewId)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd podbijania recenzji']);
        }
    }

    /**
     * Wyróżnianie recenzji (wymaga admina)
     * POST ?controller=movie&action=highlightReview&movieId={}&reviewId={}
     */
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

        if (!$movieId || !$reviewId) {
            http_response_code(400);
            echo json_encode(['error' => 'Brak ID filmu lub recenzji']);
            return;
        }

        $highlight = $data['highlight'] ?? true;

        if ($this->repo->highlightReview($movieId, $reviewId, $highlight)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd wyróżniania recenzji']);
        }
    }

    /**
     * Pobieranie wszystkich recenzji (dla panelu admina)
     * GET ?controller=movie&action=allReviews
     */
    public function allReviews()
    {
        try {
            Auth::checkAdmin();
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => $e->getMessage()]);
            return;
        }

        $reviews = $this->repo->getAllReviews();
        echo json_encode($reviews);
    }
}
