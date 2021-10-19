<?php

namespace App\Tests\Controller\Security;

use App\Tests\Controller\WebTestCase;

class LoginControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
    }

    public function testNoRememberMe(): void
    {
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();
        self::assertCheckboxChecked('_remember_me');

        $this->client->submitForm('Sign in', [
            'email' => 'john.doe@example.com',
            'password' => 'qwerty',
            '_remember_me' => false,
        ]);
        self::assertResponseRedirects('/login');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.message', 'Invalid credentials.');
        self::assertCheckboxNotChecked('_remember_me');

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
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();
        self::assertCheckboxChecked('_remember_me');

        $this->client->submitForm('Sign in', [
            'email' => 'invalid@example.com',
            'password' => 'invalid',
        ]);
        self::assertResponseRedirects('/login');
        $this->client->followRedirect();
        self::assertCheckboxChecked('_remember_me');

        $this->client->submitForm('Sign in', [
            'email' => 'invalid@example.com',
            'password' => 'invalid',
            '_remember_me' => false,
        ]);
        self::assertResponseRedirects('/login');
        $this->client->followRedirect();
        self::assertCheckboxNotChecked('_remember_me');

        $this->client->submitForm('Sign in', [
            'email' => 'john.doe@example.com',
            'password' => 'john.doe@example.com',
            '_remember_me' => true,
        ]);
        self::assertResponseRedirects('/');
        self::assertNotNull($this->client->getCookieJar()->get('REMEMBERME'));
    }
}
