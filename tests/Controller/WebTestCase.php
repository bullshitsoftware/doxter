<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use function count;
use LogicException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyTestCase;
use Symfony\Component\DomCrawler\Crawler;

abstract class WebTestCase extends SymfonyTestCase
{
    private static ?KernelBrowser $client;

    protected function tearDown(): void
    {
        self::$client = null;

        parent::tearDown();
    }

    /**
     * @param array<string,mixed> $options
     * @param array<string,mixed> $server
     */
    protected static function createClient(array $options = [], array $server = []): KernelBrowser
    {
        self::$client = parent::createClient($options, $server);

        return self::$client;
    }

    public static function loginUserByEmail(string $email = 'john.doe@example.com'): void
    {
        if (null === self::$client) {
            throw new LogicException('static::$client is null, forget to call static::createClient()?');
        }
        $user = self::getContainer()->get(UserRepository::class)->findOneByEmail($email);
        if (null === $user) {
            throw new LogicException(sprintf('No user found by %s email', $email));
        }
        self::$client->loginUser($user);
    }

    /**
     * @param array{columns:array<string>,data:array<array<string>>} $grid
     */
    public static function assertGridContent(string $selector, array $grid, string $message = ''): void
    {
        if (null === self::$client) {
            throw new LogicException('static::$client, forget to call static::createClient()?');
        }
        $crawler = self::$client->getCrawler()->filter($selector)->first();
        self::assertSame(
            $grid['columns'],
            $crawler->filter('.grid__label_md')->each(fn (Crawler $c) => $c->text()),
            $message,
        );
        self::assertSame(
            array_merge(...array_fill(0, count($grid['data']), $grid['columns'])),
            $crawler->filter('.grid__label_sm')->each(fn (Crawler $c) => $c->text()),
            $message,
        );
        self::assertSame(
            array_merge(...$grid['data']),
            $crawler->filter('.grid__cell:not(.grid__cell_space)')->each(fn (Crawler $c) => $c->text()),
            $message,
        );
    }
}
