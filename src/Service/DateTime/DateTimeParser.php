<?php

declare(strict_types=1);

namespace App\Service\DateTime;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

class DateTimeParser
{
    public function parse(string $format, string $timezone, string $date): DateTimeImmutable
    {
        $fmt = '!'.$format;
        $timezone = new DateTimeZone($timezone);
        while ('!' !== $fmt) {
            $result = DateTimeImmutable::createFromFormat($fmt, $date, $timezone);
            if (false !== $result) {
                return $result->setTimezone(new DateTimeZone(date_default_timezone_get()));
            }

            $fmt = mb_substr($fmt, 0, mb_strlen($fmt) - 1);
        }

        throw new InvalidArgumentException(sprintf('Failed to parse "%s" date by "%s" format', $date, $format));
    }
}
