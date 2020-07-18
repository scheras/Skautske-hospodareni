<?php

declare(strict_types=1);

namespace Model\Payment\ReadModel\Queries;

use Model\Common\UnitId;
use Model\Payment\ReadModel\QueryHandlers\RegistrationWithoutGroupQueryHandler;

/**
 * @see RegistrationWithoutGroupQueryHandler
 */
final class RegistrationWithoutGroupQuery
{
    private UnitId $unitId;

    public function __construct(UnitId $unitId)
    {
        $this->unitId = $unitId;
    }

    public function getUnitId() : UnitId
    {
        return $this->unitId;
    }
}
