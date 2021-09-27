<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLogin(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $client->submitForm('Sign in', [
            'email' => 'john.doe@example.com',
            'password' => 'qwerty',
        ]);
        self::assertResponseRedirects('/login');
        $client->followRedirect();
        self::assertSelectorTextContains('.alert', 'Invalid credentials.');

        $client->submitForm('Sign in', [
            'email' => 'john.doe@example.com',
            'password' => 'john.doe@example.com',
        ]);
        self::assertResponseRedirects('/');
        $client->request('GET', '/login');
        self::assertResponseRedirects('/');
    }

    public function testLogout(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneByEmail('john.doe@example.com');
        $client->loginUser($user);
        $client->request('GET', '/logout');
        self::assertResponseRedirects('http://localhost/login');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }
}
