<?php

namespace App\Tests\Controller\Task;

use App\Entity\Tag;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Service\DateTime\DateTimeFactory;
use function in_array;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AddControllerTest extends WebTestCase
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

    public function testSuccess(): void
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

    public function testInvalidDate(): void
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

    public function testRedirectWaiting(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $this->client->request('GET', '/add');
        self::assertResponseIsSuccessful();
        $this->client->submitForm('Create', [
            'task' => ['title' => 'test', 'wait' => '2007-01-31 00:00:00'],
        ]);
        self::assertResponseRedirects('/waiting');
    }

    public function testRedirectCompleted(): void
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
}
