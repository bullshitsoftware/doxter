<?php

namespace App\Tests\Twig;

use App\Entity\User;
use App\Twig\DateExtension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DateExtensionTest extends TestCase 
{
    private DateExtension $extension;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var TokenStorageInterface|MockObject $tokenStorage */
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->will(self::returnValue(
            $token = $this->createMock(TokenInterface::class)
        ));
        $token->method('getUser')->will(self::returnValue($this->user = new User()));

        $this->extension = new DateExtension($tokenStorage);
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

    public function testUserDate(): void
    {
        $this->user->getSettings()->setTimezone('Europe/Moscow');

        self::assertSame(
            '2007-01-01', 
            $this->extension->userDate(new \DateTimeImmutable('2006-12-31 23:00')),
        );
    }

    public function testUserDateTime(): void
    {
        $this->user->getSettings()->setTimezone('Europe/Moscow');

        self::assertSame(
            '2007-01-01 02:00:00', 
            $this->extension->userDateTime(new \DateTimeImmutable('2006-12-31 23:00')),
        );
    }
}
