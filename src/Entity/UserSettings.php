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
    private $timezone = 'UTC';

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }
}
