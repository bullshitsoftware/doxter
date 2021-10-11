<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Uid\Uuid;

#[
    Entity(repositoryClass: TagRepository::class),
    UniqueConstraint(name: 'tag_unique', columns: ['user_id', 'name']),
]
class Tag
{
    #[Id(), Column(type: 'uuid')]
    private Uuid $id;

    #[
        ManyToOne(targetEntity: User::class),
        JoinColumn(nullable: false),
    ]
    private User $user;

    #[Column(type: 'string', length: 64)]
    private string $name = '';

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
