<?php

namespace App\Tests\Controller;

use App\Controller\Controller;
use App\Service\DateTime\DateTimeFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ControllerTest extends WebTestCase
{
    public function testGetSubscribedServices(): void
    {
        $services = Controller::getSubscribedServices();
        self::assertSame(DateTimeFactory::class, $services['datetime.factory']);
        self::assertSame(
            AbstractController::getSubscribedServices(),
            array_intersect($services, AbstractController::getSubscribedServices()),
        );
    }
}
