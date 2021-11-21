<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use App\Entity\User;
use App\Twig\DateExtension;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Security;

class DateExtensionTest extends TestCase
{
    private DateExtension $extension;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Security|MockObject $security */
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($this->user = new User());

        $this->extension = new DateExtension($security);
    }

    public function testDateDiff(): void
    {
        self::assertSame('1y', $this->extension->dateDiff(new DateTime('+366 days'), new DateTime('-1 second')));
        self::assertSame('1d', $this->extension->dateDiff(new DateTime('+1 day'), new DateTime('-1 second')));
        self::assertSame('1h', $this->extension->dateDiff(new DateTime('+1 hour'), new DateTime('-1 second')));
        self::assertSame('1s', $this->extension->dateDiff(new DateTime('+1 second'), new DateTime('-1 second')));
        self::assertSame('1m', $this->extension->dateDiff(new DateTime('+1 minute'), new DateTime('-1 second')));
        self::assertSame('-1d', $this->extension->dateDiff(new DateTime('-1 day'), new DateTime('+1 second')));
    }

    public function testUserDate(): void
    {
        $settings = $this->user->getSettings();
        $settings->setTimezone('Europe/Moscow');
        $settings->setDateFormat('d.m.Y');

        self::assertSame(
            '01.01.2007',
            $this->extension->userDate(new DateTimeImmutable('2006-12-31 23:00')),
        );
    }

    public function testUserDateTime(): void
    {
        $settings = $this->user->getSettings();
        $settings->setTimezone('Europe/Moscow');
        $settings->setDateTimeFormat('d.m.Y H:i:s');

        self::assertSame(
            '01.01.2007 02:00:00',
            $this->extension->userDateTime(new DateTimeImmutable('2006-12-31 23:00')),
        );
    }
}
