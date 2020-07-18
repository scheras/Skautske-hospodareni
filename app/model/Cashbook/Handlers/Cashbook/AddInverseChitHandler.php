<?php

declare(strict_types=1);

namespace Model\Cashbook\Handlers\Cashbook;

use Model\Cashbook\Commands\Cashbook\AddInverseChit;
use Model\Cashbook\Repositories\ICashbookRepository;

final class AddInverseChitHandler
{
    private ICashbookRepository $cashbooks;

    public function __construct(ICashbookRepository $cashbooks)
    {
        $this->cashbooks = $cashbooks;
    }

    public function __invoke(AddInverseChit $command) : void
    {
        $cashbook         = $this->cashbooks->find($command->getTargetCashbookId());
        $originalCashbook = $this->cashbooks->find($command->getOriginalCashbookId());

        $cashbook->addInverseChit(
            $originalCashbook,
            $command->getChitId()
        );

        $this->cashbooks->save($cashbook);
    }
}
