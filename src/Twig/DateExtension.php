<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('date_diff', [$this, 'dateDiff'], ['is_safe' => ['html']]),
        ];
    }

    public function dateDiff(\DateTimeInterface $dateA, \DateTimeInterface $dateB): string
    {
        $diff = $dateA->diff($dateB);
        if ($diff->y > 0) {
            return $diff->y . 'y';
        }

        if ($diff->m > 0) {
            return $diff->m . 'mon';
        }

        if ($diff->d > 0) {
            return $diff->d . 'd';
        }

        if ($diff->h > 0) {
            return $diff->h . 'h';
        }

        if ($diff->i > 0) {
            return $diff->i . 'm';
        }

        return $diff->s . 's';
    }
}
