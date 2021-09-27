<?php

namespace App\DataFixtures;

use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TaskFixture extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [UserFixture::class];
    }

    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference(UserFixture::JOHN_DOE);
        for ($i = 1; $i < 10; $i++) {
            $task = new Task();
            $task->setUser($user);
            $task->setTitle("Current task $i");
            if ($i == 0) {
                $task->setWait($task->getCreated());
            }
            if ($i % 2 === 1) {
                $task->setStarted($task->getCreated());
            }
            $manager->persist($task);
        }

        for ($i = 1; $i < 9; $i++) {
            $task = new Task();
            $task->setUser($user);
            $task->setTitle("Delayed task $i");
            $task->setWait($task->getCreated()->modify("+$i day"));
            $manager->persist($task);
        }

        for ($i = 1; $i < 11; $i++) {
            $task = new Task();
            $task->setUser($user);
            $task->setTitle("Done task $i");
            $task->setEnded($task->getCreated()->modify("+$i day"));
            $manager->persist($task);
        }

        $manager->flush();
    }
}
