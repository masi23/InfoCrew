<?php

require_once __DIR__ . '/../repositories/PlatformRepository.php';
require_once __DIR__ . '/../core/Auth.php';

class PlatformController
{
    private PlatformRepository $repo;

    public function __construct()
    {
        $this->repo = new PlatformRepository();
    }

    /**
     * Lista wszystkich platform
     * GET ?controller=platform&action=index
     */
    public function index()
    {
        $platforms = $this->repo->findAll();
        echo json_encode($platforms);
    }

    /**
     * Dodawanie platformy (wymaga admina)
     * POST ?controller=platform&action=create
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
            echo json_encode(['error' => 'Nazwa platformy jest wymagana']);
            return;
        }

        if ($this->repo->create($data)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd dodawania platformy']);
        }
    }

    /**
     * Aktualizacja platformy (wymaga admina)
     * POST ?controller=platform&action=update&id={}
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
            echo json_encode(['error' => 'Brak ID platformy']);
            return;
        }

        if ($this->repo->update($id, $data)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd aktualizacji platformy']);
        }
    }

    /**
     * Usuwanie platformy (wymaga admina)
     * GET ?controller=platform&action=delete&id={}
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
            echo json_encode(['error' => 'Brak ID platformy']);
            return;
        }

        if ($this->repo->delete($id)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd usuwania platformy']);
        }
    }
}
