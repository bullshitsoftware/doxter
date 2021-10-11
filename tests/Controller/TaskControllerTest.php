<?php

namespace App\Tests\Controller;

use App\Repository\TaskRepository;
use App\Repository\UserRepository;
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
        $this->client->request('GET', '/');
        self::assertResponseRedirects('/login');

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
        $this->client->request('GET', '/waiting');
        self::assertResponseRedirects('/login');

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
        $this->client->request('GET', '/completed');
        self::assertResponseRedirects('/login');

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
        self::assertCount(6 * 8, $crawler->filter('.sm-only'));
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
        $this->client->request('GET', '/add');
        self::assertResponseRedirects('/login');

        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $this->client->request('GET', '/add');
        self::assertResponseIsSuccessful();
        $this->client->submitForm('Create', [
            'task' => ['title' => 'test'],
        ]);
        self::assertResponseRedirects('/');
        self::assertNotNull($this->taskRepository->findOneByTitle('test'));
        $this->client->followRedirect();
        self::assertSelectorTextSame('.flash', 'Task "test" created');
    }

    public function testView(): void
    {
        $task = $this->taskRepository->findOneByTitle('Current task 1');
        $this->client->request('GET', '/view/' . $task->getId());
        self::assertResponseRedirects('/login');

        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $this->client->request('GET', '/view/' . $task->getId());
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $task->getTitle());
    }

    public function testEdit(): void
    {
        $task = $this->taskRepository->findOneByTitle('Current task 1');
        $this->client->request('GET', '/edit/' . $task->getId());
        self::assertResponseRedirects('/login');

        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $this->client->request('GET', '/edit/' . $task->getId());
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Save', [
            'task' => ['title' => 'test'],
        ]);
        self::assertResponseRedirects('/');
        self::assertNull($this->taskRepository->findOneByTitle('Current task 1'));
        self::assertNotNull($this->taskRepository->findOneByTitle('test'));
        $this->client->followRedirect();
        self::assertSelectorTextSame('.flash', 'Task "test" updated');
    }

    public function testDelete(): void
    {
        $task = $this->taskRepository->findOneByTitle('Current task 1');
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $this->client->request('GET', '/view/' . $task->getId());
        self::assertResponseIsSuccessful();
        $this->client->submitForm('Delete');
        self::assertResponseRedirects('/');
        self::assertNull($this->taskRepository->findOneByTitle('Current task 1'));
        $this->client->followRedirect();
        self::assertSelectorTextSame('.flash', 'Task "Current task 1" deleted');
    }
}
