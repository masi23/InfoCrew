public function update(array $newData): bool
{
    foreach ($this->users as $key => $user) {
        if ($user['role'] === 'admin') { // Aktualizacja konta admina
            if (!empty($newData['username'])) $this->users[$key]['username'] = $newData['username'];
            if (!empty($newData['password'])) $this->users[$key]['password'] = password_hash($newData['password'], PASSWORD_DEFAULT);

            $content = "<?php\n\nreturn " . var_export($this->users, true) . ";";
            return file_put_contents(__DIR__ . '/../data/users.php', $content) !== false;
        }
    }
    return false;
}