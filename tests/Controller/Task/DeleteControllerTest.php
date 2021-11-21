<?php

declare(strict_types=1);

namespace App\Tests\Controller\Task;

use App\Tests\Controller\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeleteControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
    }

    public function testSuccess(): void
    {
        self::loginUserByEmail();

        $this->client->request('GET', '/view/2c2bbc1d-e729-4fde-935f-2f5faca6d905');
        self::assertResponseIsSuccessful();
        $this->client->submitForm('Delete');
        self::assertResponseRedirects('/');
        $this->client->followRedirect();
        self::assertSelectorTextSame('.message_flash', 'Task "Current task 1" deleted');

        $this->client->catchExceptions(false);
        $this->expectException(NotFoundHttpException::class);
        $this->client->request('GET', '/view/2c2bbc1d-e729-4fde-935f-2f5faca6d905');
    }

    public function testInvalidToken(): void
    {
        self::loginUserByEmail();

        $this->client->request('POST', '/delete/2c2bbc1d-e729-4fde-935f-2f5faca6d905', ['_token' => 'wrong one']);
        self::assertResponseRedirects('/view/2c2bbc1d-e729-4fde-935f-2f5faca6d905');
        $this->client->followRedirect();
        self::assertSelectorTextSame('.message_flash', 'Failed to delete "Current task 1" task. Please try again');
    }
}
