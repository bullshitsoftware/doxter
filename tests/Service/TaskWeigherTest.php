<?php

namespace App\Tests\Service;

use App\Entity\Task;
use App\Service\TaskWeigher;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class TaskWeigherTest extends TestCase
{
    /**
     * @dataProvider weighProvider
     */
    public function testWeigh(float $expected, DateTimeImmutable $now, Task $task): void
    {
        $weigher = new TaskWeigher(
            [
                'tag' => ['foo' => 1, 'bar' => 2],
                'date' => [
                    'age' => 1,
                    'started' => 2,
                    'due' => 10,
                ],
            ],
            $now,
        );
        self::assertSame($expected, $weigher->weigh($task));
    }

    /**
     * @return iterable<array{0:float,1:DateTimeImmutable,2:Task}>
     */
    public function weighProvider(): iterable
    {
        $now = new DateTimeImmutable();

        $task = new Task();
        $task->setCreated($now);
        yield 'just created' => [1.0, $now, $task];
        $task = new Task();
        $task->setCreated($now);
        $task->setTags(['foo', 'bar', 'baz']);
        yield 'just created with tags' => [5.0, $now, $task];

        $task = new Task();
        $task->setCreated($now->modify('-1 day'));
        yield 'created day ago' => [1.0, $now, $task];

        $task = new Task();
        $task->setCreated($now->modify('-10 days'));
        yield 'created 10 days ago' => [2.0, $now, $task];

        $task = new Task();
        $task->setCreated($now->modify('-20 days'));
        yield 'created 20 days ago' => [3.0, $now, $task];

        $task = new Task();
        $task->setCreated($now->modify('-10 days'));
        $task->setStarted($now->modify('-1 days'));
        yield 'created 10 days ago, started 1 day ago' => [4.0, $now, $task];

        $task = new Task();
        $task->setCreated($now->modify('-10 days'));
        $task->setStarted($now->modify('-10 days'));
        yield 'created 10 days ago, started 10 days ago' => [6.0, $now, $task];

        $task = new Task();
        $task->setCreated($now->modify('-10 days'));
        $task->setStarted($now->modify('-10 days'));
        $task->setDue($now->modify('+10 days'));
        yield 'created 10 days ago, started 10 days ago, due 10 days' => [16.0, $now, $task];

        $task = new Task();
        $task->setCreated($now->modify('-10 days'));
        $task->setStarted($now->modify('-10 days'));
        $task->setDue($now->modify('+20 days'));
        yield 'created 10 days ago, started 10 days ago, due 20 days' => [11.0, $now, $task];

        $task = new Task();
        $task->setCreated($now->modify('-10 days'));
        $task->setStarted($now->modify('-10 days'));
        $task->setDue($now->modify('+1 days'));
        yield 'created 10 days ago, started 10 days ago, due tommorow' => [16.0, $now, $task];

        $task = new Task();
        $task->setCreated($now->modify('-10 days'));
        $task->setStarted($now->modify('-10 days'));
        $task->setDue($now->modify('-1 days'));
        yield 'created 10 days ago, started 10 days ago, due yesterday' => [16.0, $now, $task];
    }
}
