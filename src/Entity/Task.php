<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TaskRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Uid\Uuid;

#[Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[Id(), Column(type: 'uuid')]
    private Uuid $id;

    #[
        ManyToOne(targetEntity: User::class),
        JoinColumn(nullable: false),
    ]
    private User $user;

    /**
     * @var array<string>
     */
    #[Column(type: 'json')]
    private array $tags = [];

    #[Column(type: 'string', length: 144)]
    private string $title = '';

    #[Column(type: 'text')]
    private string $description = '';

    #[Column(type: 'datetime_immutable')]
    private DateTimeImmutable $created;

    #[Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updated;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $wait = null;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $started = null;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $ended = null;

    #[Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $due = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): self
    {
        $this->id = $id;

        return $this;
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

    /**
     * @return array<string>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array<string> $tags
     */
    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreated(): DateTimeImmutable
    {
        return $this->created;
    }

    public function setCreated(DateTimeImmutable $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): DateTimeImmutable
    {
        return $this->updated;
    }

    public function setUpdated(DateTimeImmutable $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getWait(): ?DateTimeImmutable
    {
        return $this->wait;
    }

    public function setWait(?DateTimeImmutable $wait): self
    {
        $this->wait = $wait;

        return $this;
    }

    public function getStarted(): ?DateTimeImmutable
    {
        return $this->started;
    }

    public function setStarted(?DateTimeImmutable $started): self
    {
        $this->started = $started;

        return $this;
    }

    public function getEnded(): ?DateTimeImmutable
    {
        return $this->ended;
    }

    public function setEnded(?DateTimeImmutable $ended): self
    {
        $this->ended = $ended;

        return $this;
    }

    public function getDue(): ?DateTimeImmutable
    {
        return $this->due;
    }

    public function setDue(?DateTimeImmutable $due): self
    {
        $this->due = $due;

        return $this;
    }
}
