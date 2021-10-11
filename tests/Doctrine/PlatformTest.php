<?php

namespace App\Tests\Doctrine;

use App\Doctrine\Platform;
use PHPUnit\Framework\TestCase;

class PlatformTest extends TestCase
{
    private Platform $platform;

    public function setUp(): void
    {
        parent::setUp();

        $this->platform = new Platform;
    }

    public function testSupportsForeignKeyConstraints(): void
    {
        self::assertTrue($this->platform->supportsForeignKeyConstraints());
    }
}
