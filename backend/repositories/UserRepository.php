<?php

require_once 'models/User.php';
require_once 'core/ORM.php';

class UserRepository
{
    private array $users;

    public function __construct()
    {
        $this->users = require 'data/users.php';
    }

    public function findByUsername(string $username): ?User
    {
        foreach ($this->users as $row) {
            if ($row['username'] === $username) {
                return ORM::mapUser($row);
            }
        }
        return null;
    }
}
