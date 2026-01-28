<?php

class User
{
    public int $id;
    public string $username;
    public string $password;
    public string $role;

    public function __construct($id = 0, $username = '', $password = '', $role = 'user')
    {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->role = $role;
    }
}
