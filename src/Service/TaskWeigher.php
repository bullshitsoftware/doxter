<?php

namespace App\Service;

use App\Entity\Task;
use DateTimeImmutable;

class TaskWeigher
{
    /**
     * @param array{tag:array<string,float>,date:array{age?:float,due?:float,started?:float}} $config
     */
    public function __construct(private array $config, private DateTimeImmutable $now)
    {
    }

    public function weigh(Task $task): float
    {
        return $this->weighTags($task) + $this->weighDates($task);
    }

    private function weighTags(Task $task): float
    {
        $config = $this->config['tag'];
        $weight = 0;
        foreach ($task->getTags() as $tag) {
            $weight += $config[$tag] ?? 1;
        }

        return $weight;
    }

    private function weighDates(Task $task): float
    {
        $weekDiff = fn (DateTimeImmutable $a, DateTimeImmutable $b) => floor(
            ($a->getTimestamp() - $b->getTimestamp()) / (7 * 24 * 60 * 60)
        );

        $config = $this->config['date'];
        $weight = 0;

        $ageMod = $config['age'] ?? 1;
        $weight += $ageMod * (1 + $weekDiff($this->now, $task->getCreated()));
        if (null !== $task->getStarted()) {
            $startedMod = $config['started'] ?? 2;
            $weight += $startedMod * (1 + $weekDiff($this->now, $task->getStarted()));
        }
        if (null !== $task->getDue()) {
            $dueMode = $config['due'] ?? 10;
            $weight += $dueMode / max(1, $weekDiff($task->getDue(), $this->now));
        }

        return $weight;
    }
}
