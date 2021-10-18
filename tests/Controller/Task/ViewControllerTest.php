<?php

namespace App\Tests\Controller\Task;

use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ViewControllerTest extends WebTestCase
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

    /**
     * @dataProvider tasksProvider
     */
    public function testTask(array $viewData): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));

        $task = $this->taskRepository->findOneByTitle($viewData['title']);
        $this->client->request('GET', '/view/'.$task->getId());
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $viewData['title']);
        self::assertSelectorTextContains('.grid__cell-id', $task->getId()->toRfc4122());
        self::assertSelectorTextContains('.grid__cell-title', $viewData['title']);
        self::assertSelectorTextContains('.grid__cell-tag', $viewData['tag']);
        self::assertSelectorTextContains('.grid__cell-description', $viewData['description']);
        self::assertSelectorTextContains('.grid__cell-created', $viewData['created']);
        self::assertSelectorTextContains('.grid__cell-updated', $viewData['updated']);
        self::assertSelectorTextContains('.grid__cell-wait', $viewData['wait']);
        self::assertSelectorTextContains('.grid__cell-started', $viewData['started']);
        self::assertSelectorTextContains('.grid__cell-ended', $viewData['ended']);
        self::assertSelectorTextContains('.grid__cell-due', $viewData['due']);
    }

    public function tasksProvider(): array
    {
        return [
            [[
                'title' => 'Current task 1',
                'tag' => 'bar foo',
                'description' => '',
                'created' => '2007-01-02 02:55:05',
                'updated' => '2007-01-02 02:55:05',
                'wait' => '—',
                'started' => '2007-01-02 02:55:05',
                'ended' => '—',
                'due' => '—',
            ]],
            [[
                'title' => 'Current task 9',
                'tag' => '',
                'description' => '',
                'created' => '2007-01-02 03:03:05',
                'updated' => '2007-01-02 03:03:05',
                'wait' => '—',
                'started' => '2007-01-02 03:03:05',
                'ended' => '—',
                'due' => '2007-10-02 03:03:05',
            ]],
            [[
                'title' => 'Delayed task 1',
                'tag' => 'bar foo',
                'description' => '',
                'created' => '2007-01-02 02:56:05',
                'updated' => '2007-01-02 02:56:05',
                'wait' => '2007-01-03 02:56:05',
                'started' => '—',
                'ended' => '—',
                'due' => '—',
            ]],
            [[
                'title' => 'Done task 1',
                'tag' => 'bar foo',
                'description' => '',
                'created' => '2006-12-23 03:04:05',
                'updated' => '2006-12-23 03:04:05',
                'wait' => '—',
                'started' => '—',
                'ended' => '2006-12-24 15:04:05',
                'due' => '—',
            ]],
        ];
    }
}
