<?php

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
            'columns' => ['ID', 'Active', 'Age', 'Tag', 'Due', 'Title'],
            'data' => [
                ['1daf6745', '', '2m', '', '7mon', 'Current task 8'],
                ['8670b12e', '1m', '1m', '', '8mon', 'Current task 9'],
                ['2c2bbc1d', '9m', '9m', 'bar foo', '', 'Current task 1'],
                ['b3bb6502', '7m', '7m', 'baz', '', 'Current task 3'],
                ['8570760c', '5m', '5m', '', '', 'Current task 5'],
                ['a0e84ad1', '3m', '3m', '', '', 'Current task 7'],
                ['5aa61370', '', '8m', 'foo', '', 'Current task 2'],
                ['288d7410', '', '6m', '', '', 'Current task 4'],
                ['738c8de9', '', '4m', '', '', 'Current task 6'],
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
                'columns' => ['ID', 'Active', 'Age', 'Tag', 'Due', 'Title'],
                'data' => [
                    ['2c2bbc1d', '9m', '9m', 'bar foo', '', 'Current task 1'],
                    ['5aa61370', '', '8m', 'foo', '', 'Current task 2'],
                ],
            ],
        );

        $this->client->request('GET', '/', ['q' => '+foo +bar']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_current',
            [
                'columns' => ['ID', 'Active', 'Age', 'Tag', 'Due', 'Title'],
                'data' => [
                    ['2c2bbc1d', '9m', '9m', 'bar foo', '', 'Current task 1'],
                ],
            ],
        );

        $this->client->request('GET', '/', ['q' => '+foo 1']);
        self::assertGridContent(
            '.grid_current',
            [
                'columns' => ['ID', 'Active', 'Age', 'Tag', 'Due', 'Title'],
                'data' => [
                    ['2c2bbc1d', '9m', '9m', 'bar foo', '', 'Current task 1'],
                ],
            ],
        );

        $this->client->request('GET', '/', ['q' => '+foo -bar']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_current',
            [
                'columns' => ['ID', 'Active', 'Age', 'Tag', 'Due', 'Title'],
                'data' => [
                    ['5aa61370', '', '8m', 'foo', '', 'Current task 2'],
                ],
            ],
        );

        $this->client->request('GET', '/', ['q' => '-foo']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_current',
            [
                'columns' => ['ID', 'Active', 'Age', 'Tag', 'Due', 'Title'],
                'data' => [
                    ['1daf6745', '', '2m', '', '7mon', 'Current task 8'],
                    ['8670b12e', '1m', '1m', '', '8mon', 'Current task 9'],
                    ['b3bb6502', '7m', '7m', 'baz', '', 'Current task 3'],
                    ['8570760c', '5m', '5m', '', '', 'Current task 5'],
                    ['a0e84ad1', '3m', '3m', '', '', 'Current task 7'],
                    ['288d7410', '', '6m', '', '', 'Current task 4'],
                    ['738c8de9', '', '4m', '', '', 'Current task 6'],
                ],
            ],
        );

        $this->client->request('GET', '/', ['q' => '-foo -baz']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_current',
            [
                'columns' => ['ID', 'Active', 'Age', 'Tag', 'Due', 'Title'],
                'data' => [
                    ['1daf6745', '', '2m', '', '7mon', 'Current task 8'],
                    ['8670b12e', '1m', '1m', '', '8mon', 'Current task 9'],
                    ['8570760c', '5m', '5m', '', '', 'Current task 5'],
                    ['a0e84ad1', '3m', '3m', '', '', 'Current task 7'],
                    ['288d7410', '', '6m', '', '', 'Current task 4'],
                    ['738c8de9', '', '4m', '', '', 'Current task 6'],
                ],
            ],
        );
    }
}
