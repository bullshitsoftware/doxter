<?php

namespace App\Tests\Controller\Task;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class CompletedControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }

    public function testNoFilter(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $crawler = $this->client->request('GET', '/completed');
        self::assertResponseIsSuccessful();
        self::assertSame(
            [
                'Done task 10',
                'Done task 9',
                'Done task 8',
                'Done task 7',
                'Done task 6',
                'Done task 5',
                'Done task 4',
                'Done task 3',
                'Done task 2',
                'Done task 1',
            ],
            $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text()),
        );
        self::assertSame(
            [
                '2007-01-01',
                '2006-12-31',
                '2006-12-30',
                '2006-12-29',
                '2006-12-28',
                '2006-12-27',
                '2006-12-26',
                '2006-12-25',
                '2006-12-24',
                '2006-12-23',
            ],
            $crawler->filter('.grid__cell-created')->each(fn (Crawler $c) => $c->text()),
        );
        self::assertSame(
            [
                '2007-01-01',
                '2007-01-01',
                '2006-12-30',
                '2006-12-30',
                '2006-12-28',
                '2006-12-28',
                '2006-12-26',
                '2006-12-26',
                '2006-12-24',
                '2006-12-24',
            ],
            $crawler->filter('.grid__cell-completed')->each(fn (Crawler $c) => $c->text()),
        );
        self::assertSame(
            ['1d', '2d', '3d', '4d', '5d', '6d', '7d', '8d', '9d', '10d'],
            $crawler->filter('.grid__cell-age')->each(fn (Crawler $c) => $c->text()),
        );
    }

    public function testdNoData(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('jane.doe@example.com'));
        $this->client->request('GET', '/completed');
        self::assertSelectorTextContains('div.alert', 'No tasks done :-(');
    }

    public function testFilter(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $crawler = $this->client->request('GET', '/completed', ['q' => '+foo']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['foo', 'bar foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Done task 2', 'Done task 1'], $titles);

        $crawler = $this->client->request('GET', '/completed', ['q' => '+foo +bar']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['bar foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Done task 1'], $titles);

        $crawler = $this->client->request('GET', '/completed', ['q' => '+foo 1']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['bar foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Done task 1'], $titles);

        $crawler = $this->client->request('GET', '/completed', ['q' => '+foo -bar']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Done task 2'], $titles);

        $crawler = $this->client->request('GET', '/completed', ['q' => '-foo']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['', '', '', '', '', '', '', 'baz'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(
            [
                'Done task 10',
                'Done task 9',
                'Done task 8',
                'Done task 7',
                'Done task 6',
                'Done task 5',
                'Done task 4',
                'Done task 3',
            ],
            $titles,
        );

        $crawler = $this->client->request('GET', '/completed', ['q' => '-foo -baz']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['', '', '', '', '', '', ''], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(
            [
                'Done task 10',
                'Done task 9',
                'Done task 8',
                'Done task 7',
                'Done task 6',
                'Done task 5',
                'Done task 4',
            ],
            $titles,
        );
    }
}
