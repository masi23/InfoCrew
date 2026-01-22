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
    public $language;
    public $genres;
    public $type;
    public $cast;
    public $description;
    public $popularity;

    public function __construct(
        $id, 
        $title, 
        $poster, 
        $backdrop, 
        $platform, 
        $rating, 
        $year, 
        $duration, 
        $language, 
        $genres, 
        $type, 
        $cast, 
        $description, 
        $popularity
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->poster = $poster;
        $this->backdrop = $backdrop;
        $this->platform = $platform; // Poprawiono z platforms na platform
        $this->rating = $rating;
        $this->year = $year;
        $this->duration = $duration;
        $this->language = $language;
        $this->genres = $genres;
        $this->type = $type;
        $this->cast = $cast;
        $this->description = $description;
        $this->popularity = $popularity;
    }
}
