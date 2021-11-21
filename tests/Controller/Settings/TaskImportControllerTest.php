<?php

declare(strict_types=1);

namespace App\Tests\Controller\Settings;

use App\Entity\Task;
use App\Tests\Controller\WebTestCase;
use Doctrine\Persistence\ObjectManager;
use function in_array;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Uid\Uuid;

class TaskImportControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ObjectManager $objectManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
        $this->objectManager = self::getContainer()->get('doctrine')->getManager();
    }

    public function testImport(): void
    {
        self::loginUserByEmail();
        $this->client->request('GET', '/settings/import');
        $taskData = [
            'id' => 0,
            'uuid' => '19ce47f7-a453-42f3-a60c-845818f4a9b1',
            'description' => 'Imported',
            'annotations' => [
                ['description' => 'line1'],
                ['description' => 'line2'],
            ],
            'entry' => '20210909T220901Z',
            'start' => '20210909T220902Z',
            'modified' => '20210914T193310Z',
            'end' => '20210914T193310Z',
            'due' => '20211231T210000Z',
            'status' => 'completed',
            'tags' => ['tag1', 'tag2'],
        ];
        $this->client->submitForm('Import', ['import' => ['content' => json_encode([
            $taskData,
            ['status' => 'deleted'],
        ])]]);
        self::assertResponseRedirects('/settings/import');
        $task = $this->objectManager->find(Task::class, Uuid::fromString('19ce47f7-a453-42f3-a60c-845818f4a9b1'));
        self::assertNotNull($task);
        self::assertSame('Imported', $task->getTitle());
        self::assertSame("line1\n\nline2\n\n", $task->getDescription());
        self::assertSame('2021-09-09 22:09:01', $task->getCreated()->format('Y-m-d H:i:s'));
        self::assertSame('2021-09-09 22:09:02', $task->getStarted()->format('Y-m-d H:i:s'));
        self::assertSame('2021-09-14 19:33:10', $task->getUpdated()->format('Y-m-d H:i:s'));
        self::assertSame('2021-09-14 19:33:10', $task->getEnded()->format('Y-m-d H:i:s'));
        self::assertSame('2021-12-31 21:00:00', $task->getDue()->format('Y-m-d H:i:s'));
        $tags = $task->getTags();
        self::assertCount(2, $tags);
        self::assertTrue(in_array('tag1', $tags));
        self::assertTrue(in_array('tag2', $tags));
        $this->client->followRedirect();
        self::assertSelectorTextSame('.message_flash', 'Import succeed');

        $this->client->request('GET', '/settings/import');
        $taskData['description'] = 'Imported2';
        $taskData['tags'] = ['tag3'];
        $this->client->submitForm('Import', ['import' => ['content' => json_encode([$taskData])]]);
        self::assertResponseRedirects('/settings/import');
        $this->objectManager->refresh($task);
        self::assertNotNull($task);
        $tags = $task->getTags();
        self::assertCount(1, $tags);
        self::assertTrue(in_array('tag3', $tags));
        $this->client->followRedirect();
        self::assertSelectorTextSame('.message_flash', 'Import succeed');
    }
}
