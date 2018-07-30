<?php

declare(strict_types=1);

namespace Model\Cashbook\Cashbook;

use Consistence\Doctrine\Enum\EnumAnnotation;
use Doctrine\ORM\Mapping as ORM;
use Model\Cashbook\Operation;

/**
 * @ORM\Embeddable()
 */
class Category
{
    /**
     * @var int
     * @ORM\Column(type="integer", name="category")
     */
    private $id;

    /**
     * @var Operation
     * @ORM\Column(type="string_enum", name="category_operation_type")
     * @EnumAnnotation(class=Operation::class)
     */
    private $operationType;

    public function __construct(int $id, Operation $operationType)
    {
        $this->id            = $id;
        $this->operationType = $operationType;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getOperationType() : Operation
    {
        return $this->operationType;
    }
}
