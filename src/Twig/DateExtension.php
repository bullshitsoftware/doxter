<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\User;
use App\Entity\UserSettings;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use LogicException;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateExtension extends AbstractExtension
{
    public function __construct(private Security $security)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('date_diff', [$this, 'dateDiff'], ['is_safe' => ['html']]),
            new TwigFilter('user_date', [$this, 'userDate'], ['is_safe' => ['html']]),
            new TwigFilter('user_datetime', [$this, 'userDateTime'], ['is_safe' => ['html']]),
        ];
    }

    public function dateDiff(DateTimeInterface $dateA, DateTimeInterface $dateB): string
    {
        $diff = $dateB->diff($dateA);
        $sign = $diff->invert ? '-' : '';

        return match (true) {
            $diff->y > 0 => $sign.$diff->y.'y',
            $diff->m > 0 => $sign.$diff->m.'mon',
            $diff->d > 0 => $sign.$diff->d.'d',
            $diff->h > 0 => $sign.$diff->h.'h',
            $diff->i > 0 => $sign.$diff->i.'m',
            default => $sign.$diff->s.'s',
        };
    }

    public function userDate(DateTimeInterface $date): string
    {
        $userSettings = $this->getUserSettings();
        $date = new DateTimeImmutable($date->format('Y-m-d H:i:s'), $date->getTimezone());
        $date = $date->setTimezone(new DateTimeZone($userSettings->getTimezone()));

        return $date->format($userSettings->getDateFormat());
    }

    public function userDateTime(DateTimeInterface $date): string
    {
        $userSettings = $this->getUserSettings();
        $date = new DateTimeImmutable($date->format('Y-m-d H:i:s'), $date->getTimezone());
        $date = $date->setTimezone(new DateTimeZone($userSettings->getTimezone()));

        return $date->format($userSettings->getDateTimeFormat());
    }

    private function getUserSettings(): UserSettings
    {
        /** @var User|null */
        $user = $this->security->getUser();
        if (null === $user) {
            throw new LogicException('The filter should only be used for authorized users');
        }

        return $user->getSettings();
    }
}
