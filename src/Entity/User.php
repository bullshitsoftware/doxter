<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[Id(), Column(type: 'uuid')]
    private Uuid $id;

    #[Column(type: 'string', length: 180, unique: true)]
    private string $email;

    #[Column(type: 'string')]
    private string $password;

    #[Embedded(class: UserSettings::class)]
    private UserSettings $settings;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->settings = new UserSettings();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getUsername(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }

    public function getSettings(): UserSettings
    {
        return $this->settings;
    }
}
