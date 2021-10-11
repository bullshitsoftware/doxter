<?php

namespace App\Service\DateTime;

use DateTimeImmutable;

interface DateTimeFactory
{
    public function now(): DateTimeImmutable;
}
