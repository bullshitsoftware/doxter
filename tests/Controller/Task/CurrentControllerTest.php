<?php

namespace App\Tests\Controller\Task;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class CurrentControllerTest extends WebTestCase
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
        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        $expectTitles = [
            'Current task 8',
            'Current task 9',
            'Current task 1',
            'Current task 3',
            'Current task 5',
            'Current task 7',
            'Current task 2',
            'Current task 4',
            'Current task 6',
        ];
        self::assertSame($expectTitles, $titles);
        $active = $crawler->filter('.grid__cell-active')->each(fn (Crawler $c) => $c->text());
        $expectedActive = ['', '1m',  '9m', '7m', '5m', '3m', '', '', ''];
        self::assertSame($expectedActive, $active);
        $age = $crawler->filter('.grid__cell-age')->each(fn (Crawler $c) => $c->text());
        $expectedAge = ['2m', '1m', '9m', '7m', '5m', '3m', '8m', '6m', '4m'];
        self::assertSame($expectedAge, $age);
        $due = $crawler->filter('.grid__cell-due')->each(fn (Crawler $c) => $c->text());
        $expectedDue = ['7mon', '8mon', '', '', '', '', '', '', ''];
        self::assertSame($expectedDue, $due);

        $crawler = $this->client->request('GET', '/current');
        self::assertResponseIsSuccessful();
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $node) => $node->text());
        self::assertSame($expectTitles, $titles);
        $active = $crawler->filter('.grid__cell-active')->each(fn (Crawler $c) => $c->text());
        self::assertSame($expectedActive, $active);
        $age = $crawler->filter('.grid__cell-age')->each(fn (Crawler $c) => $c->text());
        self::assertSame($expectedAge, $age);
        $due = $crawler->filter('.grid__cell-due')->each(fn (Crawler $c) => $c->text());
        self::assertSame($expectedDue, $due);
    }

    public function testNoData(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('jane.doe@example.com'));
        $this->client->request('GET', '/');
        self::assertSelectorTextContains('div.alert', 'Yay! No tasks found');
    }

    public function testFilter(): void
    {
        $this->client->loginUser($this->userRepository->findOneByEmail('john.doe@example.com'));
        $crawler = $this->client->request('GET', '/', ['q' => '+foo']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['bar foo', 'foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Current task 1', 'Current task 2'], $titles);

        $crawler = $this->client->request('GET', '/', ['q' => '+foo +bar']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['bar foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Current task 1'], $titles);

        $crawler = $this->client->request('GET', '/', ['q' => '+foo 1']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['bar foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Current task 1'], $titles);

        $crawler = $this->client->request('GET', '/', ['q' => '+foo -bar']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['foo'], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['Current task 2'], $titles);

        $crawler = $this->client->request('GET', '/', ['q' => '-foo']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['', '', 'baz', '', '', '', ''], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(
            [
                'Current task 8',
                'Current task 9',
                'Current task 3',
                'Current task 5',
                'Current task 7',
                'Current task 4',
                'Current task 6',
            ],
            $titles,
        );

        $crawler = $this->client->request('GET', '/', ['q' => '-foo -baz']);
        self::assertResponseIsSuccessful();
        $tags = $crawler->filter('.grid__cell-tag')->each(fn (Crawler $c) => $c->text());
        self::assertSame(['', '', '', '', '', ''], $tags);
        $titles = $crawler->filter('.grid__cell-title')->each(fn (Crawler $c) => $c->text());
        self::assertSame(
            [
                'Current task 8',
                'Current task 9',
                'Current task 5',
                'Current task 7',
                'Current task 4',
                'Current task 6',
            ],
            $titles,
        );
    }
}
