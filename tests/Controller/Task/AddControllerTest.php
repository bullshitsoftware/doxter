<?php

declare(strict_types=1);

namespace App\Tests\Controller\Task;

use App\Repository\TaskRepository;
use App\Service\DateTime\DateTimeFactory;
use App\Tests\Controller\WebTestCase;
use function in_array;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class AddControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private TaskRepository $taskRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
        $this->taskRepository = self::getContainer()->get(TaskRepository::class);
    }

    public function testMinimalFields(): void
    {
        self::loginUserByEmail('john.doe@example.com');
        $this->client->request('GET', '/add');
        self::assertResponseIsSuccessful();
        $this->client->submitForm('Create', [
            'task' => [
                'title' => 'test',
                'created' => '2006-12-01 01:02:03',
            ],
        ]);
        self::assertResponseRedirects('/');
        $task = $this->taskRepository->findOneByTitle('test');
        self::assertNotNull($task);
        self::assertSame('john.doe@example.com', $task->getUser()->getEmail());
        self::assertCount(0, $task->getTags());
        self::assertSame('2007-01-02 03:04:05', $task->getUpdated()->format('Y-m-d H:i:s'));
        $this->client->followRedirect();
        self::assertSelectorTextSame('.message_flash', 'Task "test" created');
    }

    public function testAllFields(): void
    {
        self::loginUserByEmail('john.doe@example.com');
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
                'due' => '2006-12-13 09:10:11',
            ],
        ]);
        self::assertResponseRedirects('/completed');
        $task = $this->taskRepository->findOneByTitle('test');
        self::assertNotNull($task);
        self::assertSame('john.doe@example.com', $task->getUser()->getEmail());
        $tags = $task->getTags();
        self::assertCount(2, $tags);
        self::assertTrue(in_array('tag1', $tags));
        self::assertTrue(in_array('tag2', $tags));
        self::assertSame('2006-12-01 01:02:03', $task->getCreated()->format('Y-m-d H:i:s'));
        self::assertSame('2007-01-02 03:04:05', $task->getUpdated()->format('Y-m-d H:i:s'));
        self::assertSame('2006-12-10 03:04:05', $task->getWait()->format('Y-m-d H:i:s'));
        self::assertSame('2006-12-11 05:06:07', $task->getStarted()->format('Y-m-d H:i:s'));
        self::assertSame('2006-12-12 07:08:09', $task->getEnded()->format('Y-m-d H:i:s'));
        self::assertSame('2006-12-13 09:10:11', $task->getDue()->format('Y-m-d H:i:s'));
        $this->client->followRedirect();
        self::assertSelectorTextSame('.message_flash', 'Task "test" created');
    }

    public function testInvalidDate(): void
    {
        self::loginUserByEmail();
        $this->client->request('GET', '/add');
        self::assertResponseIsSuccessful();
        $this->client->submitForm('Create', [
            'task' => [
                'title' => 'test',
                'created' => 'invalid',
            ],
        ]);
        self::assertSelectorTextSame('.message', 'This value is not valid.');
    }

    public function testRedirectWaiting(): void
    {
        self::loginUserByEmail();
        $this->client->request('GET', '/add');
        self::assertResponseIsSuccessful();
        $this->client->submitForm('Create', [
            'task' => ['title' => 'test', 'wait' => '2007-01-31 00:00:00'],
        ]);
        self::assertResponseRedirects('/waiting');
    }

    public function testRedirectCompleted(): void
    {
        self::loginUserByEmail();
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
