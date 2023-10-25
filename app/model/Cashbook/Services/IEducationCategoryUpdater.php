<?php

declare(strict_types=1);

namespace Model\Cashbook\Services;

use InvalidArgumentException;
use Model\Cashbook\Cashbook\CashbookId;

interface IEducationCategoryUpdater
{
    /**
     * Updates statistics in Skautis
     *
     * @param  array<int, float> $cashbookTotals Category totals indexed by category ID
     *
     * @throws InvalidArgumentException
     */
    public function updateCategories(CashbookId $cashbookId, array $cashbookTotals): void;
}
