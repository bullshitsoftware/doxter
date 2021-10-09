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
        $titles = $crawler->filter('.grid__col-title')->each(fn (Crawler $c) => $c->text());
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

        $crawler = $this->client->request('GET', '/current');
        self::assertResponseIsSuccessful();
        $titles = $crawler->filter('.grid__col-title')->each(fn (Crawler $node) => $node->text());
        self::assertSame($expectTitles, $titles);
    }

    public function testCurrentFilter(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $crawler = $this->client->request('GET', '/', ['q' => '+foo']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__col-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['bar foo', 'foo'], $tags);
        $titles = $crawler->filter('.grid__col-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Current task 1', 'Current task 2'], $titles);

        $crawler = $this->client->request('GET', '/', ['q' => '+foo +bar']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__col-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['bar foo'], $tags);
        $titles = $crawler->filter('.grid__col-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Current task 1'], $titles);

        $crawler = $this->client->request('GET', '/', ['q' => '+foo -bar']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__col-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['foo'], $tags);
        $titles = $crawler->filter('.grid__col-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Current task 2'], $titles);

        $crawler = $this->client->request('GET', '/', ['q' => '-foo']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__col-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['baz', '', '', '', '', '', ''], $tags);
        $titles = $crawler->filter('.grid__col-title')->each(fn (Crawler $c) => $c->text());
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
        $tags = $crawler->filter('.grid__col-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['', '', '', '', '', ''], $tags);
        $titles = $crawler->filter('.grid__col-title')->each(fn (Crawler $c) => $c->text());
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
        $titles = $crawler->filter('.grid__col-title')->each(fn (Crawler $c) => $c->text());
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
            $titles,
        );
    }

    public function testWaitingFilter(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $crawler = $this->client->request('GET', '/waiting', ['q' => '+foo']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__col-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['bar foo', 'foo'], $tags);
        $titles = $crawler->filter('.grid__col-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Delayed task 1', 'Delayed task 2'], $titles);

        $crawler = $this->client->request('GET', '/waiting', ['q' => '+foo +bar']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__col-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['bar foo'], $tags);
        $titles = $crawler->filter('.grid__col-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Delayed task 1'], $titles);

        $crawler = $this->client->request('GET', '/waiting', ['q' => '+foo -bar']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__col-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['foo'], $tags);
        $titles = $crawler->filter('.grid__col-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Delayed task 2'], $titles);

        $crawler = $this->client->request('GET', '/waiting', ['q' => '-foo']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__col-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['baz', '', '', '', '', ''], $tags);
        $titles = $crawler->filter('.grid__col-title')->each(fn (Crawler $c) => $c->text());
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
        $tags = $crawler->filter('.grid__col-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['', '', '', '', ''], $tags);
        $titles = $crawler->filter('.grid__col-title')->each(fn (Crawler $c) => $c->text());
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
        $titles = $crawler->filter('.grid__col-title')->each(fn (Crawler $c) => $c->text());
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
            $titles,
        );
    }

    public function testCompletedFilter(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $crawler = $this->client->request('GET', '/completed', ['q' => '+foo']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__col-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['foo', 'bar foo'], $tags);
        $titles = $crawler->filter('.grid__col-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Done task 2', 'Done task 1'], $titles);

        $crawler = $this->client->request('GET', '/completed', ['q' => '+foo +bar']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__col-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['bar foo'], $tags);
        $titles = $crawler->filter('.grid__col-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Done task 1'], $titles);

        $crawler = $this->client->request('GET', '/completed', ['q' => '+foo -bar']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__col-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['foo'], $tags);
        $titles = $crawler->filter('.grid__col-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Done task 2'], $titles);

        $crawler = $this->client->request('GET', '/completed', ['q' => '-foo']);
        self::assertResponseIsSuccessful();
        self::assertCount(6 * 8, $crawler->filter('.sm-only'));
        $tags = $crawler->filter('.grid__col-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['', '', '', '', '', '', '', 'baz'], $tags);
        $titles = $crawler->filter('.grid__col-title')->each(fn (Crawler $c) => $c->text());
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
        $tags = $crawler->filter('.grid__col-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['', '', '', '', '', '', ''], $tags);
        $titles = $crawler->filter('.grid__col-title')->each(fn (Crawler $c) => $c->text());
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
    }
}
