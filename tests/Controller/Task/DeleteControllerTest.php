<?php

namespace App\Tests\Controller\Task;

use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DeleteControllerTest extends WebTestCase
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

    public function testInvalidToken(): void
    {
        $task = $this->taskRepository->findOneByTitle('Current task 1');
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $this->client->request('POST', '/delete/'.$task->getId(), ['_token' => 'wrong one']);
        self::assertResponseRedirects('/view/'.$task->getId());
        $this->client->followRedirect();
        self::assertSelectorTextSame('.flash', 'Failed to delete "Current task 1" task. Please try again');
    }
}
