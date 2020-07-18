<?php

declare(strict_types=1);

namespace Model\Participant\Payment;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable()
 */
final class Event
{
    /** @ORM\Column(type="integer") */
    private int $id;

    /** @ORM\Column(type="string_enum", length=7) */
    private EventType $type;

    public function __construct(int $id, EventType $type)
    {
        $this->id   = $id;
        $this->type = $type;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getType() : EventType
    {
        return $this->type;
    }
}
