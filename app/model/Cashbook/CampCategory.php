<?php

declare(strict_types=1);

namespace Model\Cashbook;

use Money\Money;
use function mb_substr;

final class CampCategory implements ICategory
{
    private int $id;

    private Operation $operationType;

    private string $name;

    private Money $total;

    private ?ParticipantType $participantType = null;

    public function __construct(int $id, Operation $operationType, string $name, Money $total, ?ParticipantType $participantType)
    {
        $this->id              = $id;
        $this->operationType   = $operationType;
        $this->name            = $name;
        $this->total           = $total;
        $this->participantType = $participantType;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getOperationType() : Operation
    {
        return $this->operationType;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getShortcut() : string
    {
        return mb_substr($this->name, 0, 5, 'UTF-8');
    }

    public function getTotal() : Money
    {
        return $this->total;
    }

    public function getParticipantType() : ?ParticipantType
    {
        return $this->participantType;
    }

    public function isVirtual() : bool
    {
        return false;
    }
}
