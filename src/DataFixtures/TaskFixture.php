<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Service\DateTime\DateTimeFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TaskFixture extends Fixture implements DependentFixtureInterface
{
    public function __construct(private DateTimeFactory $dateTimeFactory)
    {
    }

    public function getDependencies(): array
    {
        return [UserFixture::class];
    }

    public function load(ObjectManager $manager): void
    {
        $now = $this->dateTimeFactory->now();
        $user = $this->getReference(UserFixture::JOHN_DOE);
        for ($i = 1; $i < 10; ++$i) {
            $task = new Task();
            $task->setUser($user);
            $task->setTitle("Current task $i");
            $minutesAgo = 10 - $i;
            $task->setCreated($now->modify("-${minutesAgo}minutes"));
            $task->setUpdated($task->getCreated());
            if (0 == $i) {
                $task->setWait($task->getCreated());
            }
            if (1 === $i % 2) {
                $task->setStarted($task->getCreated());
            }
            if (8 <= $i) {
                $task->setDue($task->getCreated()->modify("+${i}months"));
            }
            $manager->persist($task);
            $this->addReference(self::referenceName('current', $i), $task);
        }

        for ($i = 1; $i < 9; ++$i) {
            $task = new Task();
            $task->setUser($user);
            $task->setTitle("Delayed task $i");
            $minutesAgo = 9 - $i;
            $task->setCreated($now->modify("-${minutesAgo}minutes"));
            $task->setUpdated($task->getCreated());
            $task->setWait($task->getCreated()->modify("+$i day"));
            $manager->persist($task);
            $this->addReference(self::referenceName('waiting', $i), $task);
        }

        for ($i = 1; $i < 11; ++$i) {
            $task = new Task();
            $task->setUser($user);
            $task->setTitle("Done task $i");
            $daysAgo = 11 - $i;
            $task->setCreated($now->modify("-${daysAgo}days"));
            $task->setUpdated($task->getCreated());
            $hoursOffset = 1 == $i % 2 ? 36 : 12;
            $task->setEnded($task->getCreated()->modify("+${hoursOffset}hours"));
            $manager->persist($task);
            $this->addReference(self::referenceName('completed', $i), $task);
        }

        $manager->flush();
    }

    public static function referenceName(string $list, int $i): string
    {
        return sprintf('task_%s_%d', $list, $i);
    }
}
