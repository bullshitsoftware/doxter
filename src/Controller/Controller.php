<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\DateTime\DateTimeFactory;
use DateTimeImmutable;
use function get_class;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class Controller extends AbstractController
{
    public const FLASH_SUCCESS = 'message_green';
    public const FLASH_ERROR = 'message_pink';

    public function __construct(private DateTimeFactory $dateTimeFactory)
    {
    }

    protected function now(): DateTimeImmutable
    {
        return $this->dateTimeFactory->now();
    }

    protected function getUser(): ?User
    {
        $user = parent::getUser();
        if (null !== $user && !$user instanceof User) {
            throw new LogicException(sprintf('Invalid user instance: %s', get_class($user)));
        }

        return $user;
    }

    protected function getUserOrException(): User
    {
        $user = $this->getUser();
        if (null === $user) {
            throw new LogicException('No user in storage');
        }

        return $user;
    }
}
