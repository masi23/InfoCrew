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
        $reviews = $this->load();

        foreach ($reviews as &$review) {
            if ($review['movieId'] == $movieId && $review['id'] == $reviewId) {
                $review['likes']++;
                break;
            }
        }

        $this->saveAll($reviews);
        return true;
    }
    public function add(array $review)
    {
    $reviews = $this->load();
    $reviews[] = $review;
    $this->saveAll($reviews);
    }

}
