<?php

require_once 'repositories/MovieRepository.php';

class MovieController
{
    public function index()
    {
        $repo = new MovieRepository();
        $movies = $repo->findAll();

        require 'views/json.php';
    }

    public function show()
    {
        $id = $_GET['id'] ?? null;

        $repo = new MovieRepository();
        $movie = $repo->findById($id);

        require 'views/json.php';
    }
}
