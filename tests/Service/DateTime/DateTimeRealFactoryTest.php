<?php

namespace App\Tests\Service\DateTime;

use App\Service\DateTime\DateTimeRealFactory;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class DateTimeRealFactoryTest extends TestCase
{
    private DateTimeRealFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new DateTimeRealFactory();
    }

    public function testNow(): void
    {
        $now1 = new DateTimeImmutable();
        $now2 = $this->factory->now();
        self::assertTrue(($now2->getTimestamp() - $now1->getTimestamp()) <= 1);
    }
}
