<?php

namespace App\Service\DateTime;

class DateTimeFixedFactory implements DateTimeFactory
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('2007-01-02 03:04:05');
    }
}
