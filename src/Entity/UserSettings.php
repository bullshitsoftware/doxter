<?php

declare(strict_types=1);

namespace App\Entity;

use App\Validator\Weights;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Timezone;

/**
 * @phpstan-type WeightsArray array{tag:array<string,float>,date:array{age:float,due:float,started:float}}
 */
#[Embeddable()]
class UserSettings
{
    #[
        Column(type: 'string', length: 32),
        Timezone(),
    ]
    private string $timezone = 'UTC';

    #[Column(type: 'string', length: 16)]
    private string $dateFormat = 'Y-m-d';

    #[Column(type: 'string', length: 16)]
    private string $dateTimeFormat = 'Y-m-d H:i:s';

    /**
     * @var WeightsArray
     */
    #[
        Column(type: 'json'),
        NotNull(),
        Weights(),
    ]
    private array $weights = [
        'tag' => ['next' => 10.0],
        'date' => ['age' => 1, 'due' => 10, 'started' => 2],
    ];

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

    /**
     * @return WeightsArray
     */
    public function getWeights(): array
    {
        return $this->weights;
    }

    /**
     * @param WeightsArray $weights
     */
    public function setWeights(array $weights): self
    {
        $this->weights = $weights;

        return $this;
    }
}
