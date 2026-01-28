<?php

require_once __DIR__ . '/../models/Platform.php';

class PlatformRepository
{
    private array $platforms;
    private string $filePath;

    public function __construct()
    {
        $this->filePath = __DIR__ . '/../data/platforms.php';
        $this->platforms = file_exists($this->filePath) ? require $this->filePath : $this->getDefaults();
    }

    private function getDefaults(): array
    {
        return [
            ['id' => 1, 'name' => 'Netflix', 'logo' => '', 'color' => '#E50914'],
            ['id' => 2, 'name' => 'Prime Video', 'logo' => '', 'color' => '#00A8E1'],
            ['id' => 3, 'name' => 'HBO Max', 'logo' => '', 'color' => '#B432EA'],
            ['id' => 4, 'name' => 'Disney+', 'logo' => '', 'color' => '#113CCF'],
            ['id' => 5, 'name' => 'Apple TV+', 'logo' => '', 'color' => '#000000'],
        ];
    }

    public function findAll(): array
    {
        return $this->platforms;
    }

    public function findById(int $id): ?array
    {
        foreach ($this->platforms as $platform) {
            if ($platform['id'] === $id) {
                return $platform;
            }
        }
        return null;
    }

    public function create(array $data): bool
    {
        $maxId = 0;
        foreach ($this->platforms as $p) {
            if ($p['id'] > $maxId) $maxId = $p['id'];
        }

        $this->platforms[] = [
            'id' => $maxId + 1,
            'name' => $data['name'],
            'logo' => $data['logo'] ?? '',
            'color' => $data['color'] ?? '#3b82f6'
        ];

        return $this->saveToFile();
    }

    public function update(int $id, array $data): bool
    {
        foreach ($this->platforms as $key => $platform) {
            if ($platform['id'] === $id) {
                if (!empty($data['name'])) {
                    $this->platforms[$key]['name'] = $data['name'];
                }
                if (isset($data['logo'])) {
                    $this->platforms[$key]['logo'] = $data['logo'];
                }
                if (!empty($data['color'])) {
                    $this->platforms[$key]['color'] = $data['color'];
                }
                return $this->saveToFile();
            }
        }
        return false;
    }

    public function delete(int $id): bool
    {
        $initialCount = count($this->platforms);
        $this->platforms = array_values(array_filter(
            $this->platforms,
            fn($p) => $p['id'] !== $id
        ));

        if (count($this->platforms) < $initialCount) {
            return $this->saveToFile();
        }
        return false;
    }

    private function saveToFile(): bool
    {
        $content = "<?php\nreturn " . var_export(array_values($this->platforms), true) . ";\n";
        return file_put_contents($this->filePath, $content) !== false;
    }
}
