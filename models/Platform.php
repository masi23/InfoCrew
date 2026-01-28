<?php

class Platform
{
    public int $id;
    public string $name;
    public string $logo;
    public string $color;

    public function __construct($id = 0, $name = '', $logo = '', $color = '#3b82f6')
    {
        $this->id = $id;
        $this->name = $name;
        $this->logo = $logo;
        $this->color = $color;
    }
}
