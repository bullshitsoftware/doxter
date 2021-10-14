<?php

namespace App\Tests\Controller;

use App\Entity\Tag;
use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Service\DateTime\DateTimeFactory;
use function in_array;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class TaskControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;
    private TaskRepository $taskRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->taskRepository = static::getContainer()->get(TaskRepository::class);
    }

    public function testCurrent(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        $expectTitles = [
            'Current task 1',
            'Current task 3',
            'Current task 5',
            'Current task 7',
            'Current task 9',
            'Current task 2',
            'Current task 4',
            'Current task 6',
            'Current task 8',
        ];
        self::assertSame($expectTitles, $titles);
        $active = $crawler->filter('.grid__cell-active')->each(fn (Crawler $c) => $c->text());
        $expectedActive = ['9m', '7m', '5m', '3m', '1m', '', '', '', ''];
        self::assertSame($expectedActive, $active);
        $age = $crawler->filter('.grid__cell-age')->each(fn (Crawler $c) => $c->text());
        $expectedAge = ['9m', '7m', '5m', '3m', '1m', '8m', '6m', '4m', '2m'];
        self::assertSame($expectedAge, $age);

        $crawler = $this->client->request('GET', '/current');
        self::assertResponseIsSuccessful();
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $node) => $node->text());
        self::assertSame($expectTitles, $titles);
        $active = $crawler->filter('.grid__cell-active')->each(fn (Crawler $c) => $c->text());
        self::assertSame($expectedActive, $active);
        $age = $crawler->filter('.grid__cell-age')->each(fn (Crawler $c) => $c->text());
        self::assertSame($expectedAge, $age);
    }

    public function testCurrentNoData(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('jane.doe@example.com'));
        $this->client->request('GET', '/');
        self::assertSelectorTextContains('div.alert', 'Yay! No tasks found');
    }

    public function testCurrentFilter(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $crawler = $this->client->request('GET', '/', ['q' => '+foo']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['bar foo', 'foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Current task 1', 'Current task 2'], $titles);

        $crawler = $this->client->request('GET', '/', ['q' => '+foo +bar']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['bar foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Current task 1'], $titles);

        $crawler = $this->client->request('GET', '/', ['q' => '+foo 1']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['bar foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Current task 1'], $titles);

        $crawler = $this->client->request('GET', '/', ['q' => '+foo -bar']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Current task 2'], $titles);

        $crawler = $this->client->request('GET', '/', ['q' => '-foo']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['baz', '', '', '', '', '', ''], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(
            [
                'Current task 3',
                'Current task 5',
                'Current task 7',
                'Current task 9',
                'Current task 4',
                'Current task 6',
                'Current task 8',
            ],
            $titles,
        );

        $crawler = $this->client->request('GET', '/', ['q' => '-foo -baz']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['', '', '', '', '', ''], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(
            [
                'Current task 5',
                'Current task 7',
                'Current task 9',
                'Current task 4',
                'Current task 6',
                'Current task 8',
            ],
            $titles,
        );
    }

    public function testWaiting(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $crawler = $this->client->request('GET', '/waiting');
        self::assertResponseIsSuccessful();
        self::assertSame(
            [
                'Delayed task 1',
                'Delayed task 2',
                'Delayed task 3',
                'Delayed task 4',
                'Delayed task 5',
                'Delayed task 6',
                'Delayed task 7',
                'Delayed task 8',
            ],
            $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text()),
        );
        self::assertSame(
            ['8m', '7m', '6m', '5m', '4m', '3m', '2m', '1m'],
            $crawler->filter('.grid__cell-age')->each(fn (Crawler $c) => $c->text()),
        );
        self::assertSame(
            [
                '2007-01-03',
                '2007-01-04',
                '2007-01-05',
                '2007-01-06',
                '2007-01-07',
                '2007-01-08',
                '2007-01-09',
                '2007-01-10',
            ],
            $crawler->filter('.grid__cell-wait')->each(fn (Crawler $c) => $c->text()),
        );
        self::assertSame(
            ['23h', '1d', '2d', '3d', '4d', '5d', '6d', '7d'],
            $crawler->filter('.grid__cell-remaining')->each(fn (Crawler $c) => $c->text()),
        );
    }

    public function testWaitingNoData(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('jane.doe@example.com'));
        $this->client->request('GET', '/waiting');
        self::assertSelectorTextContains('div.alert', 'Yay! No tasks found');
    }

    public function testWaitingFilter(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $crawler = $this->client->request('GET', '/waiting', ['q' => '+foo']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['bar foo', 'foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Delayed task 1', 'Delayed task 2'], $titles);

        $crawler = $this->client->request('GET', '/waiting', ['q' => '+foo +bar']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['bar foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Delayed task 1'], $titles);

        $crawler = $this->client->request('GET', '/waiting', ['q' => '+foo 1']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['bar foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Delayed task 1'], $titles);

        $crawler = $this->client->request('GET', '/waiting', ['q' => '+foo -bar']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Delayed task 2'], $titles);

        $crawler = $this->client->request('GET', '/waiting', ['q' => '-foo']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['baz', '', '', '', '', ''], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(
            [
                'Delayed task 3',
                'Delayed task 4',
                'Delayed task 5',
                'Delayed task 6',
                'Delayed task 7',
                'Delayed task 8',
            ],
            $titles,
        );

        $crawler = $this->client->request('GET', '/waiting', ['q' => '-foo -baz']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['', '', '', '', ''], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(
            [
                'Delayed task 4',
                'Delayed task 5',
                'Delayed task 6',
                'Delayed task 7',
                'Delayed task 8',
            ],
            $titles,
        );
    }

    public function testCompleted(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $crawler = $this->client->request('GET', '/completed');
        self::assertResponseIsSuccessful();
        self::assertSame(
            [
                'Done task 10',
                'Done task 9',
                'Done task 8',
                'Done task 7',
                'Done task 6',
                'Done task 5',
                'Done task 4',
                'Done task 3',
                'Done task 2',
                'Done task 1',
            ],
            $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text()),
        );
        self::assertSame(
            [
                '2007-01-01',
                '2006-12-31',
                '2006-12-30',
                '2006-12-29',
                '2006-12-28',
                '2006-12-27',
                '2006-12-26',
                '2006-12-25',
                '2006-12-24',
                '2006-12-23',
            ],
            $crawler->filter('.grid__cell-created')->each(fn (Crawler $c) => $c->text()),
        );
        self::assertSame(
            [
                '2007-01-01',
                '2007-01-01',
                '2006-12-30',
                '2006-12-30',
                '2006-12-28',
                '2006-12-28',
                '2006-12-26',
                '2006-12-26',
                '2006-12-24',
                '2006-12-24',
            ],
            $crawler->filter('.grid__cell-completed')->each(fn (Crawler $c) => $c->text()),
        );
        self::assertSame(
            ['1d', '2d', '3d', '4d', '5d', '6d', '7d', '8d', '9d', '10d'],
            $crawler->filter('.grid__cell-age')->each(fn (Crawler $c) => $c->text()),
        );
    }

    public function testCompletedNoData(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('jane.doe@example.com'));
        $this->client->request('GET', '/completed');
        self::assertSelectorTextContains('div.alert', 'No tasks done :-(');
    }

    public function testCompletedFilter(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $crawler = $this->client->request('GET', '/completed', ['q' => '+foo']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['foo', 'bar foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Done task 2', 'Done task 1'], $titles);

        $crawler = $this->client->request('GET', '/completed', ['q' => '+foo +bar']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['bar foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Done task 1'], $titles);

        $crawler = $this->client->request('GET', '/completed', ['q' => '+foo 1']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['bar foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Done task 1'], $titles);

        $crawler = $this->client->request('GET', '/completed', ['q' => '+foo -bar']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Done task 2'], $titles);

        $crawler = $this->client->request('GET', '/completed', ['q' => '-foo']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['', '', '', '', '', '', '', 'baz'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(
            [
                'Done task 10',
                'Done task 9',
                'Done task 8',
                'Done task 7',
                'Done task 6',
                'Done task 5',
                'Done task 4',
                'Done task 3',
            ],
            $titles,
        );

        $crawler = $this->client->request('GET', '/completed', ['q' => '-foo -baz']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['', '', '', '', '', '', ''], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(
            [
                'Done task 10',
                'Done task 9',
                'Done task 8',
                'Done task 7',
                'Done task 6',
                'Done task 5',
                'Done task 4',
            ],
            $titles,
        );
    }

    public function testAdd(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $this->client->request('GET', '/add');
        self::assertResponseIsSuccessful();
        $this->client->submitForm('Create', [
            'task' => [
                'title' => 'test',
                'tags' => 'TAG1 TAG2',
                'description' => 'description',
                'created' => '2006-12-01 01:02:03',
                'wait' => '2006-12-10 03:04:05',
                'started' => '2006-12-11 05:06:07',
                'ended' => '2006-12-12 07:08:09',
            ],
        ]);
        self::assertResponseRedirects('/completed');
        /** @var Task $task */
        $task = $this->taskRepository->findOneByTitle('test');
        self::assertNotNull($task);
        $tags = $task->getTags()->map(fn (Tag $tag) => $tag->getName())->toArray();
        self::assertCount(2, $tags);
        self::assertTrue(in_array('tag1', $tags));
        self::assertTrue(in_array('tag2', $tags));
        self::assertSame('2006-12-01 01:02:03', $task->getCreated()->format('Y-m-d H:i:s'));
        self::assertSame('2007-01-02 03:04:05', $task->getUpdated()->format('Y-m-d H:i:s'));
        self::assertSame('2006-12-10 03:04:05', $task->getWait()->format('Y-m-d H:i:s'));
        self::assertSame('2006-12-11 05:06:07', $task->getStarted()->format('Y-m-d H:i:s'));
        self::assertSame('2006-12-12 07:08:09', $task->getEnded()->format('Y-m-d H:i:s'));
        $this->client->followRedirect();
        self::assertSelectorTextSame('.flash', 'Task "test" created');
    }

    public function testAddInvalidDate(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $this->client->request('GET', '/add');
        self::assertResponseIsSuccessful();
        $this->client->submitForm('Create', [
            'task' => [
                'title' => 'test',
                'created' => 'invalid',
            ],
        ]);
        self::assertSelectorTextSame('.alert', 'This value is not valid.');
    }

    public function testAddRedirectWaiting(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $this->client->request('GET', '/add');
        self::assertResponseIsSuccessful();
        $this->client->submitForm('Create', [
            'task' => ['title' => 'test', 'wait' => '2007-01-31 00:00:00'],
        ]);
        self::assertResponseRedirects('/waiting');
    }

    public function testAddRedirectCompleted(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $this->client->request('GET', '/add');
        self::assertResponseIsSuccessful();
        $this->client->submitForm('Create', [
            'task' => [
                'title' => 'test',
                'ended' => self::getContainer()->get(DateTimeFactory::class)->now()->format('Y-m-d H:i:s'),
            ],
        ]);
        self::assertResponseRedirects('/completed');
    }

    public function testView(): void
    {
        $task = $this->taskRepository->findOneByTitle('Current task 1');
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $this->client->request('GET', '/view/'.$task->getId());
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $task->getTitle());
    }

    public function testEdit(): void
    {
        $task = $this->taskRepository->findOneByTitle('Current task 1');
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $this->client->request('GET', '/edit/'.$task->getId());
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Save', [
            'task' => [
                'title' => 'test',
                'tags' => 'TAG1 TAG2',
                'description' => 'description',
                'created' => '2006-12-01 01:02:03',
                'wait' => '2006-12-10 03:04:05',
                'started' => '2006-12-11 05:06:07',
                'ended' => '2006-12-12 07:08:09',
            ],
        ]);
        self::assertResponseRedirects('/completed');
        self::assertNull($this->taskRepository->findOneByTitle('Current task 1'));
        /** @var Task $task */
        $task = $this->taskRepository->findOneByTitle('test');
        self::assertNotNull($task);
        $tags = $task->getTags()->map(fn (Tag $tag) => $tag->getName())->toArray();
        self::assertCount(2, $tags);
        self::assertTrue(in_array('tag1', $tags));
        self::assertTrue(in_array('tag2', $tags));
        self::assertSame('2006-12-01 01:02:03', $task->getCreated()->format('Y-m-d H:i:s'));
        self::assertSame('2007-01-02 03:04:05', $task->getUpdated()->format('Y-m-d H:i:s'));
        self::assertSame('2006-12-10 03:04:05', $task->getWait()->format('Y-m-d H:i:s'));
        self::assertSame('2006-12-11 05:06:07', $task->getStarted()->format('Y-m-d H:i:s'));
        self::assertSame('2006-12-12 07:08:09', $task->getEnded()->format('Y-m-d H:i:s'));
        $this->client->followRedirect();
        self::assertSelectorTextSame('.flash', 'Task "test" updated');
    }

    public function testEditInvalidDate(): void
    {
        $task = $this->taskRepository->findOneByTitle('Current task 1');
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $this->client->request('GET', '/edit/'.$task->getId());
        $this->client->submitForm('Save', [
            'task' => [
                'title' => 'test',
                'created' => 'invalid',
            ],
        ]);
        self::assertSelectorTextSame('.alert', 'This value is not valid.');
    }

    public function testEditApply(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $task = $this->taskRepository->findOneByTitle('Current task 1');
        $this->client->request('GET', '/edit/'.$task->getId());
        self::assertResponseIsSuccessful();
        $this->client->submitForm('Apply');
        self::assertResponseRedirects('/view/'.$task->getId());
    }

    public function testEditRedirectWaiting(): void
    {
        $task = $this->taskRepository->findOneByTitle('Current task 1');
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $this->client->request('GET', '/edit/'.$task->getId());
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Save', ['task' => ['wait' => '2007-01-31 00:00:00']]);
        self::assertResponseRedirects('/waiting');
    }

    public function testEditRedirectCompleted(): void
    {
        $task = $this->taskRepository->findOneByTitle('Current task 1');
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $this->client->request('GET', '/edit/'.$task->getId());
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Save', [
            'task' => ['ended' => self::getContainer()->get(DateTimeFactory::class)->now()->format('Y-m-d H:i:s')],
        ]);
        self::assertResponseRedirects('/completed');
    }

    public function testDelete(): void
    {
        $task = $this->taskRepository->findOneByTitle('Current task 1');
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $this->client->request('GET', '/view/'.$task->getId());
        self::assertResponseIsSuccessful();
        $this->client->submitForm('Delete');
        self::assertResponseRedirects('/');
        self::assertNull($this->taskRepository->findOneByTitle('Current task 1'));
        $this->client->followRedirect();
        self::assertSelectorTextSame('.flash', 'Task "Current task 1" deleted');
    }

    public function testDeleteInvalidToken(): void
    {
        $task = $this->taskRepository->findOneByTitle('Current task 1');
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $this->client->request('POST', '/delete/'.$task->getId(), ['_token' => 'wrong one']);
        self::assertResponseRedirects('/view/'.$task->getId());
        $this->client->followRedirect();
        self::assertSelectorTextSame('.flash', 'Failed to delete "Current task 1" task. Please try again');
    }
}
