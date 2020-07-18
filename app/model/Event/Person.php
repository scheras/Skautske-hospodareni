<?php

declare(strict_types=1);

namespace Model\Event;

use Nette\SmartObject;

/**
 * @property-read int           $id
 * @property-read string        $name
 * @property-read string|NULL   $email
 */
class Person
{
    use SmartObject;

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
