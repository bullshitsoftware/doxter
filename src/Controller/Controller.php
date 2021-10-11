<?php

namespace App\Controller;

use App\Service\DateTime\DateTimeFactory;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class Controller extends AbstractController
{
    public const FLASH_SUCCESS = 'flash_success';
    public const FLASH_ERROR = 'flash_error';

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
}
