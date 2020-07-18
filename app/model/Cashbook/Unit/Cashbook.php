<?php

declare(strict_types=1);

namespace Model\Cashbook\Unit;

use Assert\Assert;
use Doctrine\ORM\Mapping as ORM;
use Model\Cashbook\Cashbook\CashbookId;
use Model\Cashbook\Unit;

/**
 * @ORM\Entity()
 * @ORM\Table(name="ac_unit_cashbooks")
 */
class Cashbook
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /** @ORM\Column(type="smallint") */
    private int $year;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity=Unit::class, inversedBy="cashbooks")
     */
    private Unit $unit;

    /** @ORM\Column(type="cashbook_id") */
    private CashbookId $cashbookId;

    public function __construct(int $id, Unit $unit, int $year, CashbookId $cashbookId)
    {
        Assert::that($year)->greaterThan(0);
        $this->id         = $id;
        $this->year       = $year;
        $this->unit       = $unit;
        $this->cashbookId = $cashbookId;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getYear() : int
    {
        return $this->year;
    }

    public function getCashbookId() : CashbookId
    {
        return $this->cashbookId;
    }
}
