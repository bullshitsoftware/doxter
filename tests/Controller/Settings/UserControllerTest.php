<?php

namespace App\Tests\Controller\Settings;

use App\Tests\Controller\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class UserControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
    }

    public function testSettings(): void
    {
        self::loginUserByEmail();
        $this->client->request('GET', '/settings');
        $this->client->submitForm('Save', [
            'user_settings' => [
                'timezone' => 'Europe/Moscow',
                'dateFormat' => 'm.d.Y',
                'dateTimeFormat' => 'm.d.Y H:i',
            ],
        ]);
        self::assertResponseRedirects('/settings');
        $this->client->followRedirect();
        self::assertSelectorTextSame('.message_flash', 'User settings updated');
        self::assertFormValue('.form', 'user_settings[timezone]', 'Europe/Moscow');
        self::assertFormValue('.form', 'user_settings[dateFormat]', 'm.d.Y');
        self::assertFormValue('.form', 'user_settings[dateTimeFormat]', 'm.d.Y H:i');
    }
}
