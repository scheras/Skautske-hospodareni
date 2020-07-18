<?php

declare(strict_types=1);

namespace Model\Infrastructure\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Model\Cashbook\Cashbook\Recipient;

class RecipientType extends StringType
{
    public function getName() : string
    {
        return 'recipient';
    }

    /**
     * @param mixed $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform) : ?string
    {
        /** @var Recipient $value */
        return $value === null ? null : $value->getName();
    }

    /**
     * @param mixed $value
     */
    public function convertToPHPValue($value, AbstractPlatform $platform) : ?Recipient
    {
        return $value === null ? null : new Recipient($value);
    }
}
