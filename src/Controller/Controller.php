<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\DateTime\DateTimeFactory;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class Controller extends AbstractController
{
    public const FLASH_SUCCESS = 'message_green';
    public const FLASH_ERROR = 'message_pink';

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                'datetime.factory' => DateTimeFactory::class,
            ],
        );
    }

    protected function now(): DateTimeImmutable
    {
        return $this->get('datetime.factory')->now();
    }

    protected function getUser(): ?User
    {
        return parent::getUser();
    }
}
