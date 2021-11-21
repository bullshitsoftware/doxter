<?php

declare(strict_types=1);

namespace App\Tests\Doctrine;

use App\Doctrine\Platform;
use PHPUnit\Framework\TestCase;

class PlatformTest extends TestCase
{
    private Platform $platform;

    protected function setUp(): void
    {
        parent::setUp();

        $this->platform = new Platform();
    }

    public function testSupportsForeignKeyConstraints(): void
    {
        self::assertTrue($this->platform->supportsForeignKeyConstraints());
    }
}
