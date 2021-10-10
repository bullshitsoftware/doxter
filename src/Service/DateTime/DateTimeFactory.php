<?php

namespace App\Service\DateTime;

interface DateTimeFactory
{
    public function now(): \DateTimeImmutable;
}
