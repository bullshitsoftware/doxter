<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Symfony\Component\Validator\Constraints\Timezone;

#[Embeddable()]
class UserSettings
{
    #[
        Column(type: 'string', length: 32, options: ['default' => 'UTC']),
        Timezone(),
    ]
    private string $timezone = 'UTC';

    #[Column(type: 'string', length: 16, options: ['default' => 'Y-m-d'])]
    private string $dateFormat = 'Y-m-d';

    #[Column(type: 'string', length: 16, options: ['default' => 'Y-m-d H:i:s'])]
    private string $dateTimeFormat = 'Y-m-d H:i:s';

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getDateFormat(): string
    {
        return $this->dateFormat;
    }

    public function setDateFormat(string $dateFormat): self
    {
        $this->dateFormat = $dateFormat;

        return $this;
    }

    public function getDateTimeFormat(): string
    {
        return $this->dateTimeFormat;
    }

    public function setDateTimeFormat(string $dateTimeFormat): self
    {
        $this->dateTimeFormat = $dateTimeFormat;

        return $this;
    }
}
