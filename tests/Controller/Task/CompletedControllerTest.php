<?php

declare(strict_types=1);

namespace App\Tests\Controller\Task;

use App\Tests\Controller\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class CompletedControllerTest extends WebTestCase
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
        $this->client->request('GET', '/completed');
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_completed',
            [
                'columns' => ['ID', 'Created', 'Completed', 'Age', 'Tag', 'Title'],
                'data' => [
                    ['da6dad30', '2007-01-01', '2007-01-01', '1d', '', 'Done task 10'],
                    ['876b7262', '2006-12-31', '2007-01-01', '2d', '', 'Done task 9'],
                    ['7ae9db23', '2006-12-30', '2006-12-30', '3d', '', 'Done task 8'],
                    ['dcc2a5e5', '2006-12-29', '2006-12-30', '4d', '', 'Done task 7'],
                    ['2a15b215', '2006-12-28', '2006-12-28', '5d', '', 'Done task 6'],
                    ['bed3c6d8', '2006-12-27', '2006-12-28', '6d', '', 'Done task 5'],
                    ['85114a9d', '2006-12-26', '2006-12-26', '7d', '', 'Done task 4'],
                    ['14936392', '2006-12-25', '2006-12-26', '8d', 'baz', 'Done task 3'],
                    ['997a2edd', '2006-12-24', '2006-12-24', '9d', 'foo', 'Done task 2'],
                    ['d74c0d03', '2006-12-23', '2006-12-24', '10d', 'bar foo', 'Done task 1'],
                ],
            ],
        );
    }

    public function testNoData(): void
    {
        self::loginUserByEmail('jane.doe@example.com');
        $this->client->request('GET', '/completed');
        self::assertSelectorExists('.search');
        self::assertSelectorTextContains('.message', 'No tasks done :-(');
    }

    public function testFilter(): void
    {
        self::loginUserByEmail();
        $this->client->request('GET', '/completed', ['q' => '+foo']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_completed',
            [
                'columns' => ['ID', 'Created', 'Completed', 'Age', 'Tag', 'Title'],
                'data' => [
                    ['997a2edd', '2006-12-24', '2006-12-24', '9d', 'foo', 'Done task 2'],
                    ['d74c0d03', '2006-12-23', '2006-12-24', '10d', 'bar foo', 'Done task 1'],
                ],
            ],
        );

        $this->client->request('GET', '/completed', ['q' => '+foo +bar']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_completed',
            [
                'columns' => ['ID', 'Created', 'Completed', 'Age', 'Tag', 'Title'],
                'data' => [
                    ['d74c0d03', '2006-12-23', '2006-12-24', '10d', 'bar foo', 'Done task 1'],
                ],
            ],
        );

        $this->client->request('GET', '/completed', ['q' => '+foo 1']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_completed',
            [
                'columns' => ['ID', 'Created', 'Completed', 'Age', 'Tag', 'Title'],
                'data' => [
                    ['d74c0d03', '2006-12-23', '2006-12-24', '10d', 'bar foo', 'Done task 1'],
                ],
            ],
        );

        $this->client->request('GET', '/completed', ['q' => '+foo -bar']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_completed',
            [
                'columns' => ['ID', 'Created', 'Completed', 'Age', 'Tag', 'Title'],
                'data' => [
                    ['997a2edd', '2006-12-24', '2006-12-24', '9d', 'foo', 'Done task 2'],
                ],
            ],
        );

        $this->client->request('GET', '/completed', ['q' => '-foo']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_completed',
            [
                'columns' => ['ID', 'Created', 'Completed', 'Age', 'Tag', 'Title'],
                'data' => [
                    ['da6dad30', '2007-01-01', '2007-01-01', '1d', '', 'Done task 10'],
                    ['876b7262', '2006-12-31', '2007-01-01', '2d', '', 'Done task 9'],
                    ['7ae9db23', '2006-12-30', '2006-12-30', '3d', '', 'Done task 8'],
                    ['dcc2a5e5', '2006-12-29', '2006-12-30', '4d', '', 'Done task 7'],
                    ['2a15b215', '2006-12-28', '2006-12-28', '5d', '', 'Done task 6'],
                    ['bed3c6d8', '2006-12-27', '2006-12-28', '6d', '', 'Done task 5'],
                    ['85114a9d', '2006-12-26', '2006-12-26', '7d', '', 'Done task 4'],
                    ['14936392', '2006-12-25', '2006-12-26', '8d', 'baz', 'Done task 3'],
                ],
            ],
        );

        $this->client->request('GET', '/completed', ['q' => '-foo -baz']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_completed',
            [
                'columns' => ['ID', 'Created', 'Completed', 'Age', 'Tag', 'Title'],
                'data' => [
                    ['da6dad30', '2007-01-01', '2007-01-01', '1d', '', 'Done task 10'],
                    ['876b7262', '2006-12-31', '2007-01-01', '2d', '', 'Done task 9'],
                    ['7ae9db23', '2006-12-30', '2006-12-30', '3d', '', 'Done task 8'],
                    ['dcc2a5e5', '2006-12-29', '2006-12-30', '4d', '', 'Done task 7'],
                    ['2a15b215', '2006-12-28', '2006-12-28', '5d', '', 'Done task 6'],
                    ['bed3c6d8', '2006-12-27', '2006-12-28', '6d', '', 'Done task 5'],
                    ['85114a9d', '2006-12-26', '2006-12-26', '7d', '', 'Done task 4'],
                ],
            ],
        );
    }

    public function testFilterDates(): void
    {
        self::loginUserByEmail();
        $this->client->request('GET', '/completed', ['q' => 'created>=2007-01-01 created<2007-01-02']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_completed',
            [
                'columns' => ['ID', 'Created', 'Completed', 'Age', 'Tag', 'Title'],
                'data' => [
                    ['da6dad30', '2007-01-01', '2007-01-01', '1d', '', 'Done task 10'],
                ],
            ],
        );

        $this->client->request('GET', '/completed', ['q' => 'ended>=2006-12-30 ended<2006-12-31']);
        self::assertResponseIsSuccessful();
        self::assertGridContent(
            '.grid_completed',
            [
                'columns' => ['ID', 'Created', 'Completed', 'Age', 'Tag', 'Title'],
                'data' => [
                    ['7ae9db23', '2006-12-30', '2006-12-30', '3d', '', 'Done task 8'],
                    ['dcc2a5e5', '2006-12-29', '2006-12-30', '4d', '', 'Done task 7'],
                ],
            ],
        );
    }
}
