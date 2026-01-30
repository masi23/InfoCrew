<?php

class ReviewRepository {

    private string $file;

    public function __construct() {
        $this->file = __DIR__ . '/../data/reviews.php';
    }

    private function load() {
        return file_exists($this->file) ? require $this->file : [];
    }

    public function getByMovie($movieId) {
        $reviews = $this->load();
        return array_values(array_filter($reviews, fn($r) => isset($r['movieId']) && $r['movieId'] == $movieId));
    }
    

    public function getAll() {
        return $this->load();
    }

    public function saveAll($reviews) {
        file_put_contents(
            $this->file,
            "<?php\nreturn " . var_export($reviews, true) . ";"
        );
    }

   public function likeReview($movieId, $reviewId) {
    $reviews = $this->load(); // Wczytuje dane z data/reviews.php

    foreach ($reviews as &$review) {
        // Sprawdzamy ID filmu i ID konkretnej recenzji
        if ($review['movieId'] == $movieId && $review['id'] == $reviewId) {
            $review['likes'] = (isset($review['likes']) ? $review['likes'] : 0) + 1;
            break;
        }
    }

    $this->saveAll($reviews); // Zapisuje zaktualizowaną tablicę
    return true;
}
   public function add(array $review)
{
    $reviews = $this->load();

    // Logika blurowania słów przed zapisem
    $badWords = file_exists(__DIR__ . '/../data/badwords.php') ? require __DIR__ . '/../data/badwords.php' : [];
    foreach ($badWords as $word) {
        $word = trim($word);
        if (empty($word)) continue;
        $replacement = str_repeat('*', mb_strlen($word));
        // Używamy preg_replace dla obsługi wielkości liter i polskich znaków
        $review['text'] = preg_replace('/\b' . preg_quote($word, '/') . '\b/iu', $replacement, $review['text']);
    }

    $reviews[] = $review;
    $this->saveAll($reviews);
}
    public function delete($movieId, $reviewId) {
    $reviews = $this->load();
    $initialCount = count($reviews);

    $reviews = array_values(array_filter($reviews, function($r) use ($movieId, $reviewId) {
        return !($r['movieId'] == $movieId && $r['id'] == $reviewId);
    }));

    if (count($reviews) < $initialCount) {
        $this->saveAll($reviews);
        return true;
    }
    return false;
}

  public function setHighlight($movieId, $reviewId, $highlight) {
    $reviews = $this->load();
    foreach ($reviews as &$review) {
        if ($review['movieId'] == $movieId && $review['id'] == $reviewId) {
            $review['highlighted'] = (bool)$highlight;
            break;
        }
    }
    $this->saveAll($reviews);
    return true;
}

}
