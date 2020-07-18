<?php

declare(strict_types=1);

namespace Model\Cashbook\ReadModel\Queries;

use Cake\Chronos\Date;
use Model\Event\SkautisEventId;

/**
 * @see EventPragueParticipantsQueryHandler
 */
final class EventPragueParticipantsQuery
{
    private SkautisEventId $id;

    private string $registrationNumber;

    private Date $startDate;

    public function __construct(SkautisEventId $id, string $registrationNumber, Date $startDate)
    {
        $this->id                 = $id;
        $this->registrationNumber = $registrationNumber;
        $this->startDate          = $startDate;
    }

    public function getId() : SkautisEventId
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
