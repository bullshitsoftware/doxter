<?php

declare(strict_types=1);

namespace App\Tests\Controller\Task;

use App\Tests\Controller\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;

class ViewControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
    }

    /**
     * @dataProvider tasksProvider
     *
     * @param array<string,string> $viewData
     */
    public function testTask(array $viewData): void
    {
        self::loginUserByEmail();

        $crawler = $this->client->request('GET', '/view/'.$viewData['id']);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $viewData['title']);
        $grid = $crawler->filter('.grid_task')->first();
        self::assertSame(
            ['ID', 'Title', 'Tag', 'Description', 'Created', 'Updated', 'Wait', 'Started', 'Ended', 'Due'],
            $grid->filter('.grid__label')->each(fn (Crawler $c) => $c->text()),
        );
        self::assertSame(
            array_values($viewData),
            $grid->filter('.grid__cell')->each(fn (Crawler $c) => $c->text()),
        );
    }

    /**
     * @return array<array<array<string,string>>>
     */
    public function tasksProvider(): array
    {
        return [
            [[
                'id' => '2c2bbc1d-e729-4fde-935f-2f5faca6d905',
                'title' => 'Current task 1',
                'tag' => 'bar foo',
                'description' => '',
                'created' => '2007-01-02 02:55:05',
                'updated' => '2007-01-02 02:55:05',
                'wait' => '2007-01-02 03:04:04',
                'started' => '2007-01-02 02:55:05',
                'ended' => '—',
                'due' => '—',
            ]],
            [[
                'id' => '8670b12e-0fa8-4fb9-ab16-e121cc3d9dd9',
                'title' => 'Current task 9',
                'tag' => '',
                'description' => '',
                'created' => '2007-01-02 03:03:05',
                'updated' => '2007-01-02 03:03:05',
                'wait' => '—',
                'started' => '2007-01-02 03:03:05',
                'ended' => '—',
                'due' => '2007-10-02 03:03:05',
            ]],
            [[
                'id' => '1d44a8c5-e126-4f42-ab51-b8d2215049e3',
                'title' => 'Delayed task 1',
                'tag' => 'bar foo',
                'description' => '',
                'created' => '2007-01-02 02:56:05',
                'updated' => '2007-01-02 02:56:05',
                'wait' => '2007-01-03 02:56:05',
                'started' => '—',
                'ended' => '—',
                'due' => '—',
            ]],
            [[
                'id' => 'd74c0d03-a5d7-4fec-accc-4573d8c55878',
                'title' => 'Done task 1',
                'tag' => 'bar foo',
                'description' => '',
                'created' => '2006-12-23 03:04:05',
                'updated' => '2006-12-23 03:04:05',
                'wait' => '—',
                'started' => '—',
                'ended' => '2006-12-24 15:04:05',
                'due' => '—',
            ]],
        ];
    }
}
