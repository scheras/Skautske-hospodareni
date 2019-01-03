<?php

declare(strict_types=1);

namespace Model\Travel\Command;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Model\Travel\Travel\Type;
use Nette\SmartObject;

/**
 * @ORM\Embeddable()
 * @property-read DateTimeImmutable $date
 * @property-read Type $transportType
 * @property-read string $startPlace
 * @property-read string $endPlace
 */
class TravelDetails
{
    use SmartObject;

    /**
     * @var DateTimeImmutable
     * @ORM\Column(type="datetime_immutable", name="start_date")
     */
    private $date;

    /**
     * @var Type
     * @ORM\ManyToOne(targetEntity=Type::class)
     * @ORM\JoinColumn(name="type", referencedColumnName="type")
     */
    private $transportType;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $startPlace;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $endPlace;

    public function __construct(DateTimeImmutable $date, Type $transportType, string $startPlace, string $endPlace)
    {
        $this->date          = $date;
        $this->transportType = $transportType;
        $this->startPlace    = $startPlace;
        $this->endPlace      = $endPlace;
    }

    public function getDate() : DateTimeImmutable
    {
        return $this->date;
    }

    public function getTransportType() : Type
    {
        return $this->transportType;
    }

    public function getStartPlace() : string
    {
        return $this->startPlace;
    }

    public function getEndPlace() : string
    {
        return $this->endPlace;
    }
}
