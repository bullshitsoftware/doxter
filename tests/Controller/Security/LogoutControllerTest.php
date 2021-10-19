<?php

namespace App\Tests\Controller\Security;

use App\Tests\Controller\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class LogoutControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
    }

    public function testLogout(): void
    {
        self::loginUserByEmail();

        $this->client->request('GET', '/logout');
        self::assertResponseRedirects('http://localhost/login');
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
    }
}
