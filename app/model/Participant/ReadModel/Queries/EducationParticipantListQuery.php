<?php

declare(strict_types=1);

namespace Model\Cashbook\ReadModel\Queries;

use Model\Cashbook\ReadModel\QueryHandlers\EducationParticipantListQueryHandler;
use Model\Event\SkautisEducationId;

/** @see EducationParticipantListQueryHandler */
final class EducationParticipantListQuery
{
    private SkautisEducationId $educationId;

    public function __construct(SkautisEducationId $id, private bool $onlyAccepted = true)
    {
        $this->educationId = $id;
    }

    public function getEducationId(): SkautisEducationId
    {
        return $this->educationId;
    }

    public function getOnlyAccepted(): bool
    {
        return $this->onlyAccepted;
    }
}
