<?php

class Movie
{
    public $id;
    public $title;
    public $poster;
    public $backdrop;
    public $platform;
    public $rating;
    public $year;
    public $duration;
    public $country;      // Zmienione z language na country
    public $genres;
    public $type;
    public $cast;
    public $description;
    public $popularity;
    public $reviews;

    public function __construct(
        $id = null,
        $title = '',
        $poster = '',
        $backdrop = '',
        $platform = '',
        $rating = 0,
        $year = 0,
        $duration = '',
        $country = '',
        $genres = [],
        $type = '',
        $cast = [],
        $description = '',
        $popularity = 0,
        $reviews = []
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->poster = $poster;
        $this->backdrop = $backdrop;
        $this->platform = $platform;
        $this->rating = $rating;
        $this->year = $year;
        $this->duration = $duration;
        $this->country = $country;
        $this->genres = $genres;
        $this->type = $type;
        $this->cast = $cast;
        $this->description = $description;
        $this->popularity = $popularity;
        $this->reviews = $reviews;
    }
}
