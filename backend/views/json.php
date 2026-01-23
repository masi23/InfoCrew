<?php
header('Content-Type: application/json');

echo json_encode(
    $movies ?? $movie,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
);