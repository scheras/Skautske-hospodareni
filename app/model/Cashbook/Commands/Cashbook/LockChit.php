<?php

declare(strict_types=1);

namespace Model\Cashbook\Commands\Cashbook;

use Model\Cashbook\Cashbook\CashbookId;
use Model\Cashbook\Handlers\Cashbook\LockChitHandler;

/**
 * @see LockChitHandler
 */
final class LockChit
{
    private CashbookId $cashbookId;

    private int $chitId;

    private int $userId;

    public function __construct(CashbookId $cashbookId, int $chitId, int $userId)
    {
        $this->cashbookId = $cashbookId;
        $this->chitId     = $chitId;
        $this->userId     = $userId;
    }

    public function getCashbookId() : CashbookId
    {
        return $this->cashbookId;
    }

    public function getChitId() : int
    {
        return $this->chitId;
    }

    public function getUserId() : int
    {
        return $this->userId;
    }
}
