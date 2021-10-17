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

    public function testView(): void
    {
        $task = $this->taskRepository->findOneByTitle('Current task 1');
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $this->client->request('GET', '/view/'.$task->getId());
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Current task 1');
        self::assertSelectorTextContains('.grid__cell-id', $task->getId()->toRfc4122());
        self::assertSelectorTextContains('.grid_cell-title', 'Current task 1');
        self::assertSelectorTextContains('.grid__cell-tag', 'bar foo');
        self::assertSelectorTextContains('.grid__cell-description', '');
        self::assertSelectorTextContains('.grid__cell-created', '2007-01-02 02:55:05');
        self::assertSelectorTextContains('.grid__cell-updated', '2007-01-02 02:55:05');
        self::assertSelectorTextContains('.grid__cell-wait', '—');
        self::assertSelectorTextContains('.grid__cell-started', '2007-01-02 02:55:05');
        self::assertSelectorTextContains('.grid__cell-ended', '—');
    }
}
