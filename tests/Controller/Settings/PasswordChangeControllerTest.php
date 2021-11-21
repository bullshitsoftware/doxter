<?php

declare(strict_types=1);

namespace App\Tests\Controller\Settings;

use App\Repository\UserRepository;
use App\Tests\Controller\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordChangeControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
        $this->userRepository = self::getContainer()->get(UserRepository::class);
    }

    public function testPasswordChange(): void
    {
        self::loginUserByEmail('john.doe@example.com');
        $this->client->request('GET', '/settings/password');
        $crawler = $this->client->submitForm('Update password', [
            'password_change' => [
                'oldPassword' => 'qwerty',
                'password' => 'qwerty',
                'passwordConfirm' => '',
            ],
        ]);
        self::assertResponseIsSuccessful();
        $errors = $crawler->filter('form .message');
        self::assertSame("This value should be the user's current password.", $errors->first()->text());
        self::assertSame("Passwords don't match", $errors->last()->text());

        $crawler = $this->client->submitForm('Update password', [
            'password_change' => [
                'oldPassword' => 'john.doe@example.com',
                'password' => 'qwerty',
                'passwordConfirm' => 'qwerty',
            ],
        ]);
        self::assertResponseRedirects('/settings/password');
        $user = $this->userRepository->findOneByEmail('john.doe@example.com');
        self::assertNotNull($user);
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($hasher->isPasswordValid($user, 'qwerty'));
        $this->client->followRedirect();
        self::assertSelectorTextSame('.message_flash', 'User password updated');
    }
}
