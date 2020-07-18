<?php

declare(strict_types=1);

namespace Model\Cashbook\ReadModel\QueryHandlers;

use Model\Cashbook\CashbookNotFound;
use Model\Cashbook\ReadModel\Queries\CashbookQuery;
use Model\Cashbook\Repositories\ICashbookRepository;
use Model\DTO\Cashbook\Cashbook;

class CashbookQueryHandler
{
    private ICashbookRepository $cashbooks;

    public function __construct(ICashbookRepository $cashbooks)
    {
        $this->cashbooks = $cashbooks;
    }

    /**
     * @throws CashbookNotFound
     */
    public function __invoke(CashbookQuery $query) : Cashbook
    {
        $cashbook = $this->cashbooks->find($query->getCashbookId());

        return new Cashbook(
            $cashbook->getId(),
            $cashbook->getType(),
            $cashbook->getCashChitNumberPrefix(),
            $cashbook->getBankChitNumberPrefix(),
            $cashbook->getNote(),
            $cashbook->hasOnlyNumericChitNumbers()
        );
    }
}
