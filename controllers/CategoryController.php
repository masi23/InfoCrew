<?php

require_once __DIR__ . '/../repositories/CategoryRepository.php';
require_once __DIR__ . '/../core/Auth.php';

class CategoryController
{
    private CategoryRepository $repo;

    public function __construct()
    {
        $this->repo = new CategoryRepository();
    }

    /**
     * Lista wszystkich kategorii
     * GET ?controller=category&action=index
     */
    public function index()
    {
        $categories = $this->repo->findAll();
        echo json_encode($categories);
    }

    /**
     * Dodawanie kategorii (wymaga admina)
     * POST ?controller=category&action=create
     */
    public function create()
    {
        try {
            Auth::checkAdmin();
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => $e->getMessage()]);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nazwa kategorii jest wymagana']);
            return;
        }

        if ($this->repo->create($data)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd dodawania kategorii']);
        }
    }

    /**
     * Aktualizacja kategorii (wymaga admina)
     * POST ?controller=category&action=update&id={}
     */
    public function update()
    {
        try {
            Auth::checkAdmin();
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => $e->getMessage()]);
            return;
        }

        $id = (int)($_GET['id'] ?? 0);
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Brak ID kategorii']);
            return;
        }

        if ($this->repo->update($id, $data)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd aktualizacji kategorii']);
        }
    }

    /**
     * Usuwanie kategorii (wymaga admina)
     * GET ?controller=category&action=delete&id={}
     */
    public function delete()
    {
        try {
            Auth::checkAdmin();
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => $e->getMessage()]);
            return;
        }

        $id = (int)($_GET['id'] ?? 0);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Brak ID kategorii']);
            return;
        }

        if ($this->repo->delete($id)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd usuwania kategorii']);
        }
    }
}
