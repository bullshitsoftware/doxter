<?php

declare(strict_types=1);

namespace App\Tests\Controller\Task;

use App\Service\DateTime\DateTimeFactory;
use App\Tests\Controller\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class EditControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
    }

    public function testMinimalFields(): void
    {
        self::loginUserByEmail();
        $this->client->request('GET', '/edit/2c2bbc1d-e729-4fde-935f-2f5faca6d905');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Save', [
            'task' => [
                'title' => 'test',
                'tags' => '',
                'description' => '',
                'created' => '2006-12-01 01:02:03',
                'wait' => '',
                'started' => '',
                'ended' => '',
                'due' => '',
            ],
        ]);
        self::assertResponseRedirects('/');
        $this->client->followRedirect();
        self::assertSelectorTextSame('.message_flash', 'Task "test" updated');

        $this->client->request('GET', '/edit/2c2bbc1d-e729-4fde-935f-2f5faca6d905');
        self::assertResponseIsSuccessful();
        self::assertFormValue('.form', 'task[title]', 'test');
        self::assertFormValue('.form', 'task[tags]', '');
        self::assertFormValue('.form', 'task[description]', '');
        self::assertFormValue('.form', 'task[created]', '2006-12-01 01:02:03');
        self::assertFormValue('.form', 'task[wait]', '');
        self::assertFormValue('.form', 'task[started]', '');
        self::assertFormValue('.form', 'task[ended]', '');
        self::assertFormValue('.form', 'task[due]', '');
    }

    public function testAllFields(): void
    {
        self::loginUserByEmail();
        $this->client->request('GET', '/edit/2c2bbc1d-e729-4fde-935f-2f5faca6d905');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Save', [
            'task' => [
                'title' => 'test',
                'tags' => 'TAG1 TAG2',
                'description' => 'description',
                'created' => '2006-12-01 01:02:03',
                'wait' => '2006-12-10 03:04:05',
                'started' => '2006-12-11 05:06:07',
                'ended' => '2006-12-12 07:08:09',
                'due' => '2006-12-12 09:10:11',
            ],
        ]);
        self::assertResponseRedirects('/completed');
        $this->client->followRedirect();
        self::assertSelectorTextSame('.message_flash', 'Task "test" updated');

        $this->client->request('GET', '/edit/2c2bbc1d-e729-4fde-935f-2f5faca6d905');
        self::assertResponseIsSuccessful();
        self::assertFormValue('.form', 'task[title]', 'test');
        self::assertFormValue('.form', 'task[tags]', 'tag1 tag2');
        self::assertFormValue('.form', 'task[description]', 'description');
        self::assertFormValue('.form', 'task[created]', '2006-12-01 01:02:03');
        self::assertFormValue('.form', 'task[wait]', '2006-12-10 03:04:05');
        self::assertFormValue('.form', 'task[started]', '2006-12-11 05:06:07');
        self::assertFormValue('.form', 'task[ended]', '2006-12-12 07:08:09');
        self::assertFormValue('.form', 'task[due]', '2006-12-12 09:10:11');
    }

    public function testInvalidDate(): void
    {
        self::loginUserByEmail();
        $this->client->request('GET', '/edit/2c2bbc1d-e729-4fde-935f-2f5faca6d905');
        $this->client->submitForm('Save', [
            'task' => [
                'title' => 'test',
                'created' => 'invalid',
            ],
        ]);
        self::assertSelectorTextSame('.message', 'This value is not valid.');
    }

    public function testApplyButton(): void
    {
        self::loginUserByEmail();
        $this->client->request('GET', '/edit/2c2bbc1d-e729-4fde-935f-2f5faca6d905');
        self::assertResponseIsSuccessful();
        $this->client->submitForm('Apply');
        self::assertResponseRedirects('/view/2c2bbc1d-e729-4fde-935f-2f5faca6d905');
    }

    public function testRedirectWaiting(): void
    {
        self::loginUserByEmail();
        $this->client->request('GET', '/edit/2c2bbc1d-e729-4fde-935f-2f5faca6d905');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Save', ['task' => ['wait' => '2007-01-31 00:00:00']]);
        self::assertResponseRedirects('/waiting');
    }

    public function testRedirectCompleted(): void
    {
        self::loginUserByEmail();
        $this->client->request('GET', '/edit/2c2bbc1d-e729-4fde-935f-2f5faca6d905');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Save', [
            'task' => ['ended' => self::getContainer()->get(DateTimeFactory::class)->now()->format('Y-m-d H:i:s')],
        ]);
        self::assertResponseRedirects('/completed');
    }
}
