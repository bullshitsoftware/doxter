<?php

namespace App\Tests\Command;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCreateCommandTest extends KernelTestCase
{
    private CommandTester $tester;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        parent::setUp();

        $application = new Application(self::createKernel());
        $command = $application->find('user:create');

        $this->tester = new CommandTester($command);
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testExecute(): void
    {
        $this->tester->setInputs(['john.doe@example.com']);
        $this->tester->execute([]);
        self::assertTrue(str_contains($this->tester->getDisplay(), 'User already exist'));

        $this->tester->setInputs(['brand.new@example.com', 'qwerty']);
        $this->tester->execute([]);
        self::assertTrue(str_contains($this->tester->getDisplay(), 'User created'));
        $user = $this->userRepository->findOneByEmail('brand.new@example.com');
        self::assertNotNull($user);
        self::assertTrue($this->passwordHasher->isPasswordValid($user, 'qwerty'));
    }
}
