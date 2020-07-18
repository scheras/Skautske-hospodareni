<?php

declare(strict_types=1);

namespace Model\Cashbook\ReadModel\Queries;

use Model\Cashbook\Cashbook\CashbookId;
use Model\Cashbook\Operation;
use Model\Cashbook\ReadModel\QueryHandlers\CategoryPairsQueryHandler;

/**
 * @see CategoryPairsQueryHandler
 */
final class CategoryPairsQuery
{
    private CashbookId $cashbookId;

    private ?Operation $operationType = null;

    public function __construct(CashbookId $cashbookId, ?Operation $operationType = null)
    {
        $this->cashbookId    = $cashbookId;
        $this->operationType = $operationType;
    }

    public function getCashbookId() : CashbookId
    {
        return $this->cashbookId;
    }

    public function getOperationType() : ?Operation
    {
        return $this->operationType;
    }
}
