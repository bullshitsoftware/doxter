<?php

declare(strict_types=1);

namespace App\Service\DateTime;

use DateTimeImmutable;

class DateTimeRealFactory implements DateTimeFactory
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
