<?php

require_once __DIR__ . '/../models/Category.php';

class CategoryRepository
{
    private array $categories;
    private string $filePath;

    public function __construct()
    {
        $this->filePath = __DIR__ . '/../data/categories.php';
        $this->categories = file_exists($this->filePath) ? require $this->filePath : $this->getDefaults();
    }

    private function getDefaults(): array
    {
        return [
            ['id' => 1, 'name' => 'Akcja', 'slug' => 'akcja'],
            ['id' => 2, 'name' => 'Dramat', 'slug' => 'dramat'],
            ['id' => 3, 'name' => 'Sci-Fi', 'slug' => 'sci-fi'],
            ['id' => 4, 'name' => 'Fantasy', 'slug' => 'fantasy'],
            ['id' => 5, 'name' => 'Komedia', 'slug' => 'komedia'],
            ['id' => 6, 'name' => 'Thriller', 'slug' => 'thriller'],
            ['id' => 7, 'name' => 'Horror', 'slug' => 'horror'],
            ['id' => 8, 'name' => 'Romans', 'slug' => 'romans'],
            ['id' => 9, 'name' => 'Animacja', 'slug' => 'animacja'],
            ['id' => 10, 'name' => 'Dokumentalny', 'slug' => 'dokumentalny'],
        ];
    }

    public function findAll(): array
    {
        return $this->categories;
    }

    public function findById(int $id): ?array
    {
        foreach ($this->categories as $category) {
            if ($category['id'] === $id) {
                return $category;
            }
        }
        return null;
    }

    public function create(array $data): bool
    {
        $maxId = 0;
        foreach ($this->categories as $cat) {
            if ($cat['id'] > $maxId) $maxId = $cat['id'];
        }

        $slug = $this->generateSlug($data['name']);
        
        $this->categories[] = [
            'id' => $maxId + 1,
            'name' => $data['name'],
            'slug' => $slug
        ];

        return $this->saveToFile();
    }

    public function update(int $id, array $data): bool
    {
        foreach ($this->categories as $key => $category) {
            if ($category['id'] === $id) {
                if (!empty($data['name'])) {
                    $this->categories[$key]['name'] = $data['name'];
                    $this->categories[$key]['slug'] = $this->generateSlug($data['name']);
                }
                return $this->saveToFile();
            }
        }
        return false;
    }

    public function delete(int $id): bool
    {
        $initialCount = count($this->categories);
        $this->categories = array_values(array_filter(
            $this->categories,
            fn($c) => $c['id'] !== $id
        ));

        if (count($this->categories) < $initialCount) {
            return $this->saveToFile();
        }
        return false;
    }

    private function generateSlug(string $name): string
    {
        $slug = mb_strtolower($name);
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    private function saveToFile(): bool
    {
        $content = "<?php\nreturn " . var_export(array_values($this->categories), true) . ";\n";
        return file_put_contents($this->filePath, $content) !== false;
    }
}
