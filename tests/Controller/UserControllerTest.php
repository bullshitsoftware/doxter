<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }

    public function testSettings(): void
    {
        $this->client->request('GET', '/settings');
        self::assertResponseRedirects('/login');

        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $this->client->request('GET', '/settings');
        $this->client->submitForm('Save', [
            'user_settings' => ['timezone' => 'Europe/Moscow'],
        ]);
        self::assertResponseRedirects('/settings');
        $user = $this->userRepository->findOneByEmail('john.doe@example.com');
        self::assertSame('Europe/Moscow', $user->getSettings()->getTimezone());
    }
}
