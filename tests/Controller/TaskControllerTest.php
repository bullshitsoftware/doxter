<?php

namespace App\Tests\Controller;

use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
        self::assertCount(10, $crawler->filter('tr'));

        $crawler = $this->client->request('GET', '/current');
        self::assertResponseIsSuccessful();
        self::assertCount(10, $crawler->filter('tr'));
    }

    public function testWait(): void
    {
        $this->client->request('GET', '/wait');
        self::assertResponseRedirects('/login');

        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $crawler = $this->client->request('GET', '/wait');
        self::assertResponseIsSuccessful();
        self::assertCount(9, $crawler->filter('tr'));
    }

    public function testDone(): void
    {
        $this->client->request('GET', '/done');
        self::assertResponseRedirects('/login');

        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $crawler = $this->client->request('GET', '/done');
        self::assertResponseIsSuccessful();
        self::assertCount(11, $crawler->filter('tr'));
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
