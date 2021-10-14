<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    public const JOHN_DOE = 'user_john_doe';

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('john.doe@example.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, $user->getEmail()));
        $this->setReference(self::JOHN_DOE, $user);
        $manager->persist($user);

        $user = new User();
        $user->setEmail('jane.doe@example.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, $user->getEmail()));
        $manager->persist($user);

        $manager->flush();
    }
}
