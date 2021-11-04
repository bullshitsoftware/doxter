<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use function count;
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
        $user = self::getContainer()->get(UserRepository::class)->findOneByEmail($email);
        self::$client->loginUser($user);
    }

    /**
     * @param array{columns:array<string>,data:array<array<string>>} $grid
     */
    public static function assertGridContent(string $selector, array $grid, string $message = ''): void
    {
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
