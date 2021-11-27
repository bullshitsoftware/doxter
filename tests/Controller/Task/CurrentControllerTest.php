<?php

declare(strict_types=1);

namespace App\Tests\Controller\Task;

use App\Tests\Controller\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class CurrentControllerTest extends WebTestCase
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
        $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();
        $grid = [
            'columns' => ['ID', 'Active', 'Age', 'Tag', 'Due', 'Title', 'Urg'],
            'data' => [
                ['2c2bbc1d', '9m', '9m', 'bar foo', '', 'Current task 1', '6.00'],
                ['b3bb6502', '7m', '7m', 'baz', '', 'Current task 3', '4.00'],
                ['8670b12e', '1m', '1m', '', '8mon', 'Current task 9', '3.26'],
                ['8570760c', '5m', '5m', '', '', 'Current task 5', '3.00'],
                ['a0e84ad1', '3m', '3m', '', '', 'Current task 7', '3.00'],
                ['5aa61370', '', '8m', 'foo', '', 'Current task 2', '2.00'],
                ['1daf6745', '', '2m', '', '7mon', 'Current task 8', '1.29'],
                ['288d7410', '', '6m', '', '', 'Current task 4', '1.00'],
                ['738c8de9', '', '4m', '', '', 'Current task 6', '1.00'],
            ],
        ];
        self::assertGridContent('.grid_current', $grid);

        $this->client->request('GET', '/current');
        self::assertResponseIsSuccessful();
        self::assertGridContent('.grid_current', $grid);
    }

    public function testNoData(): void
    {
        self::loginUserByEmail('jane.doe@example.com');
        $this->client->request('GET', '/');
        self::assertSelectorExists('.search');
        self::assertSelectorTextContains('.message', 'Yay! No tasks found');
    }

    public function testFilter(): void
    {
        self::loginUserByEmail();
        $this->client->request('GET', '/', ['q' => '+foo']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_current',
            [
                'columns' => ['ID', 'Active', 'Age', 'Tag', 'Due', 'Title', 'Urg'],
                'data' => [
                    ['2c2bbc1d', '9m', '9m', 'bar foo', '', 'Current task 1', '6.00'],
                    ['5aa61370', '', '8m', 'foo', '', 'Current task 2', '2.00'],
                ],
            ],
        );

        $this->client->request('GET', '/', ['q' => '+foo +bar']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_current',
            [
                'columns' => ['ID', 'Active', 'Age', 'Tag', 'Due', 'Title', 'Urg'],
                'data' => [
                    ['2c2bbc1d', '9m', '9m', 'bar foo', '', 'Current task 1', '6.00'],
                ],
            ],
        );

        $this->client->request('GET', '/', ['q' => '+foo 1']);
        self::assertGridContent(
            '.grid_current',
            [
                'columns' => ['ID', 'Active', 'Age', 'Tag', 'Due', 'Title', 'Urg'],
                'data' => [
                    ['2c2bbc1d', '9m', '9m', 'bar foo', '', 'Current task 1', '6.00'],
                ],
            ],
        );

        $this->client->request('GET', '/', ['q' => '+foo -bar']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_current',
            [
                'columns' => ['ID', 'Active', 'Age', 'Tag', 'Due', 'Title', 'Urg'],
                'data' => [
                    ['5aa61370', '', '8m', 'foo', '', 'Current task 2', '2.00'],
                ],
            ],
        );

        $this->client->request('GET', '/', ['q' => '-foo']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_current',
            [
                'columns' => ['ID', 'Active', 'Age', 'Tag', 'Due', 'Title', 'Urg'],
                'data' => [
                    ['b3bb6502', '7m', '7m', 'baz', '', 'Current task 3', '4.00'],
                    ['8670b12e', '1m', '1m', '', '8mon', 'Current task 9', '3.26'],
                    ['8570760c', '5m', '5m', '', '', 'Current task 5', '3.00'],
                    ['a0e84ad1', '3m', '3m', '', '', 'Current task 7', '3.00'],
                    ['1daf6745', '', '2m', '', '7mon', 'Current task 8', '1.29'],
                    ['288d7410', '', '6m', '', '', 'Current task 4', '1.00'],
                    ['738c8de9', '', '4m', '', '', 'Current task 6', '1.00'],
                ],
            ],
        );

        $this->client->request('GET', '/', ['q' => '-foo -baz']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_current',
            [
                'columns' => ['ID', 'Active', 'Age', 'Tag', 'Due', 'Title', 'Urg'],
                'data' => [
                    ['8670b12e', '1m', '1m', '', '8mon', 'Current task 9', '3.26'],
                    ['8570760c', '5m', '5m', '', '', 'Current task 5', '3.00'],
                    ['a0e84ad1', '3m', '3m', '', '', 'Current task 7', '3.00'],
                    ['1daf6745', '', '2m', '', '7mon', 'Current task 8', '1.29'],
                    ['288d7410', '', '6m', '', '', 'Current task 4', '1.00'],
                    ['738c8de9', '', '4m', '', '', 'Current task 6', '1.00'],
                ],
            ],
        );
    }

    public function testFilterDates(): void
    {
        self::loginUserByEmail();
        $this->client->request('GET', '/', ['q' => 'started>=2007-01-01']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_current',
            [
                'columns' => ['ID', 'Active', 'Age', 'Tag', 'Due', 'Title', 'Urg'],
                'data' => [
                    ['2c2bbc1d', '9m', '9m', 'bar foo', '', 'Current task 1', '6.00'],
                    ['b3bb6502', '7m', '7m', 'baz', '', 'Current task 3', '4.00'],
                    ['8670b12e', '1m', '1m', '', '8mon', 'Current task 9', '3.26'],
                    ['8570760c', '5m', '5m', '', '', 'Current task 5', '3.00'],
                    ['a0e84ad1', '3m', '3m', '', '', 'Current task 7', '3.00'],
                ],
            ],
        );
    }
}
