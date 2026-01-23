<?php

require_once 'repositories/MovieRepository.php';
require_once 'core/Auth.php';

class MovieController
{
    /**
     * Pobieranie filmów z uwzględnieniem filtrów
     * GET ?controller=movie&action=index
     */
    public function index()
    {
        $repo = new MovieRepository();

        // Zmieniono z getAll() na findFiltered($_GET), aby obsługiwać wyszukiwanie i filtry
        $movies = $repo->findFiltered($_GET);

        header('Content-Type: application/json');
        ob_clean();

        // array_values() gwarantuje, że po filtrowaniu otrzymamy czystą tablicę JSON [0, 1, 2...]
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

        $repo = new MovieRepository();
        $movie = $repo->findById($id);

        header('Content-Type: application/json');
        if ($movie) {
            echo json_encode($movie);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Film nie istnieje']);
        }
    }

    /**
     * Dodawanie nowego filmu
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

        $repo = new MovieRepository();
        if ($repo->create($data)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd zapisu w bazie danych']);
        }
    }

    /**
     * Edycja istniejącego filmu
     * POST ?controller=movie&action=update&id={id}
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

        $repo = new MovieRepository();
        if ($repo->update($id, $data)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd aktualizacji']);
        }
    }

    /**
     * Usuwanie filmu
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

        $repo = new MovieRepository();
        if ($repo->delete($id)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd usuwania']);
        }
    }

    /**
     * Dodawanie nowej recenzji do filmu
     * POST ?controller=movie&action=addReview&id={id}
     */
    public function addReview()
    {
        $id = $_GET['id'] ?? null;
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$id || !$data || empty($data['text'])) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Brak ID filmu lub treści recenzji']);
            return;
        }

        $repo = new MovieRepository();

        if ($repo->addReview($id, $data)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Błąd podczas zapisywania recenzji']);
        }
    }
}