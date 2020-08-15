<?php

declare(strict_types=1);

namespace Model\Google;

use Model\AggregateId;

final class OAuthId extends AggregateId
{
    public static function fromStringOrNull(?string $id) : ?self
    {
        if ($id === null) {
            return null;
        }

        return self::fromString($id);
    }
}
