<?php

class Review
{
    public int $id;
    public int $movieId;
    public string $user;
    public string $text;
    public int $rating;      // Ocena 1-10
    public string $date;
    public int $likes;       // Liczba polubień (podbijanie)
    public bool $highlighted; // Czy wyróżniona przez admina

    public function __construct(
        $id = 0,
        $movieId = 0,
        $user = 'Anonim',
        $text = '',
        $rating = 0,
        $date = '',
        $likes = 0,
        $highlighted = false
    ) {
        $this->id = $id;
        $this->movieId = $movieId;
        $this->user = $user;
        $this->text = $text;
        $this->rating = $rating;
        $this->date = $date ?: date('Y-m-d');
        $this->likes = $likes;
        $this->highlighted = $highlighted;
    }
}
