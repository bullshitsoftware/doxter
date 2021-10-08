<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TagFixture extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [UserFixture::class, TaskFixture::class];
    }

    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference(UserFixture::JOHN_DOE);

        $tags = ['foo' => null, 'bar' => null, 'baz' => null];
        foreach (array_keys($tags) as $tagName) {
            $tag = new Tag();
            $tag->setUser($user);
            $tag->setName($tagName);
            $tags[$tagName] = $tag;
            $manager->persist($tag);
        }

        $taskTagsMap = [
            TaskFixture::referenceName('current', 1) => [$tags['foo'], $tags['bar']],
            TaskFixture::referenceName('current', 2) => [$tags['foo']],
            TaskFixture::referenceName('current', 3) => [$tags['baz']],

            TaskFixture::referenceName('waiting', 1) => [$tags['foo'], $tags['bar']],
            TaskFixture::referenceName('waiting', 2) => [$tags['foo']],
            TaskFixture::referenceName('waiting', 3) => [$tags['baz']],

            TaskFixture::referenceName('completed', 1) => [$tags['foo'], $tags['bar']],
            TaskFixture::referenceName('completed', 2) => [$tags['foo']],
            TaskFixture::referenceName('completed', 3) => [$tags['baz']],
        ];
        foreach ($taskTagsMap as $task => $taskTags) {
            /** @var Task $task */
            $task = $this->getReference($task);
            foreach ($taskTags as $taskTag) {
                $task->getTags()->add($taskTag);
            }
            $manager->persist($task);
        }
        $manager->flush();
    }
}
