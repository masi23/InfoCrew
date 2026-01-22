<?php

require_once 'repositories/MovieRepository.php';

class MovieController
{
    public function index()
    {
        $filters = $_GET;
        $repo = new MovieRepository();
        $movies = $repo->findFiltered($filters);

        require 'views/json.php';
    }

    public function show()
    {
        $id = $_GET['id'] ?? null;

        $repo = new MovieRepository();
        $movie = $repo->findById($id);

        require 'views/json.php';
    }

    public function create()
{
    Auth::checkAdmin();
    // dodawanie filmu
}

public function update()
{
    Auth::checkAdmin();
    // edycja filmu
}

public function delete()
{
    Auth::checkAdmin();
    // usuwanie filmu
}

}
