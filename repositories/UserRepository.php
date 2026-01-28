<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../core/ORM.php';

class UserRepository
{
    private array $users;
    private string $filePath;

    public function __construct()
    {
        $this->filePath = __DIR__ . '/../data/users.php';
        $this->users = file_exists($this->filePath) ? require $this->filePath : [];
    }

    public function findByUsername(string $username): ?User
    {
        foreach ($this->users as $userData) {
            if ($userData['username'] === $username) {
                return new User(
                    $userData['id'],
                    $userData['username'],
                    $userData['password'],
                    $userData['role']
                );
            }
        }
        return null;
    }

    public function findById(int $id): ?User
    {
        foreach ($this->users as $userData) {
            if ($userData['id'] === $id) {
                return new User(
                    $userData['id'],
                    $userData['username'],
                    $userData['password'],
                    $userData['role']
                );
            }
        }
        return null;
    }

    /**
     * Aktualizacja profilu admina (zmiana hasła)
     */
    public function update(array $newData): bool
    {
        foreach ($this->users as $key => $user) {
            if ($user['role'] === 'admin') {
                if (!empty($newData['username'])) {
                    $this->users[$key]['username'] = $newData['username'];
                }
                if (!empty($newData['password'])) {
                    $this->users[$key]['password'] = password_hash($newData['password'], PASSWORD_DEFAULT);
                }
                return $this->saveToFile();
            }
        }
        return false;
    }

    /**
     * Aktualizacja z weryfikacją starego hasła
     */
    public function updateWithVerification(int $userId, string $currentPassword, array $newData): bool|string
    {
        foreach ($this->users as $key => $user) {
            if ($user['id'] === $userId) {
                // Weryfikacja aktualnego hasła
                if (!password_verify($currentPassword, $user['password'])) {
                    return 'Nieprawidłowe aktualne hasło';
                }

                // Aktualizacja danych
                if (!empty($newData['username'])) {
                    // Sprawdź czy nowy username nie jest zajęty
                    foreach ($this->users as $otherUser) {
                        if ($otherUser['id'] !== $userId && $otherUser['username'] === $newData['username']) {
                            return 'Ta nazwa użytkownika jest już zajęta';
                        }
                    }
                    $this->users[$key]['username'] = $newData['username'];
                }

                if (!empty($newData['newPassword'])) {
                    $this->users[$key]['password'] = password_hash($newData['newPassword'], PASSWORD_DEFAULT);
                }

                return $this->saveToFile();
            }
        }
        return 'Użytkownik nie znaleziony';
    }

    /**
     * Zapis do pliku
     */
    private function saveToFile(): bool
    {
        $content = "<?php\nreturn " . var_export($this->users, true) . ";\n";
        return file_put_contents($this->filePath, $content) !== false;
    }
}
