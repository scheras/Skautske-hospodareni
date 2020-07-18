<?php

declare(strict_types=1);

namespace Model\Common;

class User
{
    private int $id;

    private string $name;

    private ?string $email = null;

    public function __construct(int $id, string $name, ?string $email)
    {
        $this->id    = $id;
        $this->name  = $name;
        $this->email = $email;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getEmail() : ?string
    {
        return $this->email;
    }
}
