<?php
// Tymczasowy plik do wygenerowania hasha
return [
    [
        'id' => 1,
        'username' => 'admin',
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'role' => 'admin'
    ]
];

