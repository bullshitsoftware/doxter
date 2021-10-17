<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LogoutControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    public function testLogout(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);

        $this->client->loginUser($userRepository->findOneByEmail('john.doe@example.com'));
        $this->client->request('GET', '/logout');
        self::assertResponseRedirects('http://localhost/login');
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
    }
}
