<?php

namespace App\Tests\Controller\Task;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class WaitingControllerTest extends WebTestCase
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
        $crawler = $this->client->request('GET', '/waiting');
        self::assertResponseIsSuccessful();
        self::assertSame(
            [
                'Delayed task 1',
                'Delayed task 2',
                'Delayed task 3',
                'Delayed task 4',
                'Delayed task 5',
                'Delayed task 6',
                'Delayed task 7',
                'Delayed task 8',
            ],
            $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text()),
        );
        self::assertSame(
            ['8m', '7m', '6m', '5m', '4m', '3m', '2m', '1m'],
            $crawler->filter('.grid__cell-age')->each(fn (Crawler $c) => $c->text()),
        );
        self::assertSame(
            [
                '2007-01-03',
                '2007-01-04',
                '2007-01-05',
                '2007-01-06',
                '2007-01-07',
                '2007-01-08',
                '2007-01-09',
                '2007-01-10',
            ],
            $crawler->filter('.grid__cell-wait')->each(fn (Crawler $c) => $c->text()),
        );
        self::assertSame(
            ['23h', '1d', '2d', '3d', '4d', '5d', '6d', '7d'],
            $crawler->filter('.grid__cell-remaining')->each(fn (Crawler $c) => $c->text()),
        );
    }

    public function testNoData(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('jane.doe@example.com'));
        $this->client->request('GET', '/waiting');
        self::assertSelectorTextContains('div.alert', 'Yay! No tasks found');
    }

    public function testFilter(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $crawler = $this->client->request('GET', '/waiting', ['q' => '+foo']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['bar foo', 'foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Delayed task 1', 'Delayed task 2'], $titles);

        $crawler = $this->client->request('GET', '/waiting', ['q' => '+foo +bar']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['bar foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Delayed task 1'], $titles);

        $crawler = $this->client->request('GET', '/waiting', ['q' => '+foo 1']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['bar foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Delayed task 1'], $titles);

        $crawler = $this->client->request('GET', '/waiting', ['q' => '+foo -bar']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Delayed task 2'], $titles);

        $crawler = $this->client->request('GET', '/waiting', ['q' => '-foo']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['baz', '', '', '', '', ''], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(
            [
                'Delayed task 3',
                'Delayed task 4',
                'Delayed task 5',
                'Delayed task 6',
                'Delayed task 7',
                'Delayed task 8',
            ],
            $titles,
        );

        $crawler = $this->client->request('GET', '/waiting', ['q' => '-foo -baz']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['', '', '', '', ''], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(
            [
                'Delayed task 4',
                'Delayed task 5',
                'Delayed task 6',
                'Delayed task 7',
                'Delayed task 8',
            ],
            $titles,
        );
    }
}
