<?php

namespace App\Twig;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateExtension extends AbstractExtension
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    
    public function getFilters(): array
    {
        return [
            new TwigFilter('date_diff', [$this, 'dateDiff'], ['is_safe' => ['html']]),
            new TwigFilter('user_date', [$this, 'userDate'], ['is_safe' => ['html']]),
            new TwigFilter('user_datetime', [$this, 'userDateTime'], ['is_safe' => ['html']]),
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

    public function userDate(\DateTimeInterface $date): string
    {
        $date = new \DateTimeImmutable($date->format('Y-m-d H:i:s'), $date->getTimezone());
        $date = $date->setTimezone($this->getUserTimezone());

        return $date->format('Y-m-d');
    }

    public function userDateTime(\DateTimeInterface $date): string
    {
        $date = new \DateTimeImmutable($date->format('Y-m-d H:i:s'), $date->getTimezone());
        $date = $date->setTimezone($this->getUserTimezone());

        return $date->format('Y-m-d H:i:s');
    }

    private function getUserTimezone(): \DateTimeZone
    {
        /** @var User|null */
        $user = $this->security->getUser();
        if ($user === null) {
            throw new \LogicException('The filter should only be used for authorized users');
        }
        
        return new \DateTimeZone($user->getSettings()->getTimezone());
    }
}
