<?php

namespace App\Tests\Controller\Settings;

use App\Entity\Task;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordChangeControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->taskRepository = static::getContainer()->get('doctrine')->getManager()->getRepository(Task::class);
    }

    public function testPasswordChange(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $this->client->request('GET', '/settings/password');
        $crawler = $this->client->submitForm('Update password', [
            'password_change' => [
                'oldPassword' => 'qwerty',
                'password' => 'qwerty',
                'passwordConfirm' => '',
            ],
        ]);
        self::assertResponseIsSuccessful();
        $errors = $crawler->filter('form .alert');
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
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($hasher->isPasswordValid($user, 'qwerty'));
        $this->client->followRedirect();
        self::assertSelectorTextSame('.flash', 'User password updated');
    }
}
