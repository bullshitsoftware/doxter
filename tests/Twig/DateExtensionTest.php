<?php

namespace App\Tests\Twig;

use App\Twig\DateExtension;
use PHPUnit\Framework\TestCase;

class DateExtensionTest extends TestCase 
{
    private DateExtension $extension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension = new DateExtension();
    }

    public function testDateDiff(): void
    {
        self::assertSame('1y', $this->extension->dateDiff(new \DateTime('now'), new \DateTime('+367 days')));
        self::assertSame('1mon', $this->extension->dateDiff(new \DateTime('+32 days'), new \DateTime('now')));
        self::assertSame('1d', $this->extension->dateDiff(new \DateTime('now'), new \DateTime('+1 day')));
        self::assertSame('1h', $this->extension->dateDiff(new \DateTime('now'), new \DateTime('+1 hour')));
        self::assertSame('1m', $this->extension->dateDiff(new \DateTime('now'), new \DateTime('+1 minute')));
        self::assertSame('1s', $this->extension->dateDiff(new \DateTime('now'), new \DateTime('+1 second')));
    }
}
