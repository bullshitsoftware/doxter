<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    public function testCurrent(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $client->request('GET', '/');
        self::assertResponseRedirects('/login');

        $user = $userRepository->findOneByEmail('john.doe@example.com');
        $client->loginUser($user);
        $crawler = $client->request('GET', '/');
        self::assertResponseIsSuccessful();
        self::assertCount(10, $crawler->filter('tr'));

        $crawler = $client->request('GET', '/current');
        self::assertResponseIsSuccessful();
        self::assertCount(10, $crawler->filter('tr'));
    }

    public function testWait(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $client->request('GET', '/wait');
        self::assertResponseRedirects('/login');

        $user = $userRepository->findOneByEmail('john.doe@example.com');
        $client->loginUser($user);
        $crawler = $client->request('GET', '/wait');
        self::assertResponseIsSuccessful();
        self::assertCount(9, $crawler->filter('tr'));
    }

    public function testDone(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $client->request('GET', '/done');
        self::assertResponseRedirects('/login');

        $user = $userRepository->findOneByEmail('john.doe@example.com');
        $client->loginUser($user);
        $crawler = $client->request('GET', '/done');
        self::assertResponseIsSuccessful();
        self::assertCount(11, $crawler->filter('tr'));
    }
}
