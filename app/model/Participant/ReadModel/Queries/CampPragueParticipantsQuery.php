<?php

declare(strict_types=1);

namespace Model\Cashbook\ReadModel\Queries;

use Cake\Chronos\Date;
use Model\Event\SkautisCampId;

/**
 * @see CampPragueParticipantStatisticsQueryHandler
 */
final class CampPragueParticipantsQuery
{
    private SkautisCampId $id;

    private string $registrationNumber;

    private Date $startDate;

    public function __construct(SkautisCampId $id, string $registrationNumber, Date $startDate)
    {
        $this->id                 = $id;
        $this->registrationNumber = $registrationNumber;
        $this->startDate          = $startDate;
    }

    public function getId() : SkautisCampId
    {
        return $this->id;
    }

    public function getRegistrationNumber() : string
    {
        return $this->registrationNumber;
    }

    public function getStartDate() : Date
    {
        return $this->startDate;
    }
}
