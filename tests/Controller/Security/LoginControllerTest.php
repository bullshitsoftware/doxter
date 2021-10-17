<?php

namespace App\Tests\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    public function testNoRememberMe(): void
    {
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            'email' => 'john.doe@example.com',
            'password' => 'qwerty',
        ]);
        self::assertResponseRedirects('/login');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert', 'Invalid credentials.');

        $this->client->submitForm('Sign in', [
            'email' => 'john.doe@example.com',
            'password' => 'john.doe@example.com',
            '_remember_me' => false,
        ]);
        self::assertResponseRedirects('/');
        self::assertNull($this->client->getCookieJar()->get('REMEMBERME'));
        $this->client->request('GET', '/login');
        self::assertResponseRedirects('/');
    }

    public function testRememberMe(): void
    {
        $crawler = $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();
        self::assertSame('checked', $crawler->filter('input[name="_remember_me"]')->first()->attr('checked'));

        $this->client->submitForm('Sign in', [
            'email' => 'invalid@example.com',
            'password' => 'invalid',
        ]);
        self::assertResponseRedirects('/login');
        $crawler = $this->client->followRedirect();
        self::assertSame('checked', $crawler->filter('input[name="_remember_me"]')->first()->attr('checked'));

        $this->client->submitForm('Sign in', [
            'email' => 'invalid@example.com',
            'password' => 'invalid',
            '_remember_me' => false,
        ]);
        self::assertResponseRedirects('/login');
        $crawler = $this->client->followRedirect();
        self::assertNull($crawler->filter('input[name="_remember_me"]')->first()->attr('checked'));

        $this->client->submitForm('Sign in', [
            'email' => 'john.doe@example.com',
            'password' => 'john.doe@example.com',
            '_remember_me' => true,
        ]);
        self::assertResponseRedirects('/');
        self::assertNotNull($this->client->getCookieJar()->get('REMEMBERME'));
    }
}
