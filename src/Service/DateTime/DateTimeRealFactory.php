<?php

namespace App\Service\DateTime;

class DateTimeRealFactory implements DateTimeFactory
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
