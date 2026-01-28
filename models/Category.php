<?php

class Category
{
    public int $id;
    public string $name;
    public string $slug;

    public function __construct($id = 0, $name = '', $slug = '')
    {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug ?: $this->generateSlug($name);
    }

    private function generateSlug(string $name): string
    {
        $slug = mb_strtolower($name);
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}
