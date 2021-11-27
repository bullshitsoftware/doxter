<?php

declare(strict_types=1);

namespace App\Tests\Controller\Task;

use App\Tests\Controller\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class WaitingControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
    }

    public function testNoFilter(): void
    {
        self::loginUserByEmail();
        $this->client->request('GET', '/waiting');
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_waiting',
            [
                'columns' => ['ID', 'Age', 'Tag', 'Wait', 'Remaining', 'Title'],
                'data' => [
                    ['1d44a8c5', '8m', 'bar foo', '2007-01-03', '23h', 'Delayed task 1'],
                    ['32a50e63', '7m', 'foo', '2007-01-04', '1d', 'Delayed task 2'],
                    ['babaad5b', '6m', 'baz', '2007-01-05', '2d', 'Delayed task 3'],
                    ['6d6cb3f2', '5m', '', '2007-01-06', '3d', 'Delayed task 4'],
                    ['004b3576', '4m', '', '2007-01-07', '4d', 'Delayed task 5'],
                    ['67fa1571', '3m', '', '2007-01-08', '5d', 'Delayed task 6'],
                    ['35482f30', '2m', '', '2007-01-09', '6d', 'Delayed task 7'],
                    ['e8966f17', '1m', '', '2007-01-10', '7d', 'Delayed task 8'],
                ],
            ],
        );
    }

    public function testNoData(): void
    {
        self::loginUserByEmail('jane.doe@example.com');
        $this->client->request('GET', '/waiting');
        self::assertSelectorExists('.search');
        self::assertSelectorTextContains('.message', 'Yay! No tasks found');
    }

    public function testFilter(): void
    {
        self::loginUserByEmail();
        $this->client->request('GET', '/waiting', ['q' => '+foo']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_waiting',
            [
                'columns' => ['ID', 'Age', 'Tag', 'Wait', 'Remaining', 'Title'],
                'data' => [
                    ['1d44a8c5', '8m', 'bar foo', '2007-01-03', '23h', 'Delayed task 1'],
                    ['32a50e63', '7m', 'foo', '2007-01-04', '1d', 'Delayed task 2'],
                ],
            ],
        );

        $this->client->request('GET', '/waiting', ['q' => '+foo +bar']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_waiting',
            [
                'columns' => ['ID', 'Age', 'Tag', 'Wait', 'Remaining', 'Title'],
                'data' => [
                    ['1d44a8c5', '8m', 'bar foo', '2007-01-03', '23h', 'Delayed task 1'],
                ],
            ],
        );

        $this->client->request('GET', '/waiting', ['q' => '+foo 1']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_waiting',
            [
                'columns' => ['ID', 'Age', 'Tag', 'Wait', 'Remaining', 'Title'],
                'data' => [
                    ['1d44a8c5', '8m', 'bar foo', '2007-01-03', '23h', 'Delayed task 1'],
                ],
            ],
        );

        $this->client->request('GET', '/waiting', ['q' => '+foo -bar']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_waiting',
            [
                'columns' => ['ID', 'Age', 'Tag', 'Wait', 'Remaining', 'Title'],
                'data' => [
                    ['32a50e63', '7m', 'foo', '2007-01-04', '1d', 'Delayed task 2'],
                ],
            ],
        );

        $this->client->request('GET', '/waiting', ['q' => '-foo']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_waiting',
            [
                'columns' => ['ID', 'Age', 'Tag', 'Wait', 'Remaining', 'Title'],
                'data' => [
                    ['babaad5b', '6m', 'baz', '2007-01-05', '2d', 'Delayed task 3'],
                    ['6d6cb3f2', '5m', '', '2007-01-06', '3d', 'Delayed task 4'],
                    ['004b3576', '4m', '', '2007-01-07', '4d', 'Delayed task 5'],
                    ['67fa1571', '3m', '', '2007-01-08', '5d', 'Delayed task 6'],
                    ['35482f30', '2m', '', '2007-01-09', '6d', 'Delayed task 7'],
                    ['e8966f17', '1m', '', '2007-01-10', '7d', 'Delayed task 8'],
                ],
            ],
        );

        $this->client->request('GET', '/waiting', ['q' => '-foo -baz']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_waiting',
            [
                'columns' => ['ID', 'Age', 'Tag', 'Wait', 'Remaining', 'Title'],
                'data' => [
                    ['6d6cb3f2', '5m', '', '2007-01-06', '3d', 'Delayed task 4'],
                    ['004b3576', '4m', '', '2007-01-07', '4d', 'Delayed task 5'],
                    ['67fa1571', '3m', '', '2007-01-08', '5d', 'Delayed task 6'],
                    ['35482f30', '2m', '', '2007-01-09', '6d', 'Delayed task 7'],
                    ['e8966f17', '1m', '', '2007-01-10', '7d', 'Delayed task 8'],
                ],
            ],
        );
    }

    public function testFilterDates(): void
    {
        self::loginUserByEmail();
        $this->client->request('GET', '/waiting', ['q' => 'wait>=2007-01-03 wait<2007-01-04']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_waiting',
            [
                'columns' => ['ID', 'Age', 'Tag', 'Wait', 'Remaining', 'Title'],
                'data' => [
                    ['1d44a8c5', '8m', 'bar foo', '2007-01-03', '23h', 'Delayed task 1'],
                ],
            ],
        );
    }
}
