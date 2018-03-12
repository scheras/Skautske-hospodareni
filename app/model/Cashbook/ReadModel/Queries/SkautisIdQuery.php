<?php

declare(strict_types=1);

namespace Model\Cashbook\ReadModel\Queries;

use Model\Cashbook\ReadModel\QueryHandlers\SkautisIdQueryHandler;

/**
 * @see SkautisIdQueryHandler
 */
final class SkautisIdQuery
{

    /** @var int */
    private $cashbookId;

    public function __construct(int $cashbookId)
    {
        $this->cashbookId = $cashbookId;
    }

    public function getCashbookId(): int
    {
        return $this->cashbookId;
    }

}