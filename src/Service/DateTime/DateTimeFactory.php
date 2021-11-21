<?php

declare(strict_types=1);

namespace App\Service\DateTime;

use DateTimeImmutable;

interface DateTimeFactory
{
    public function now(): DateTimeImmutable;
}
