<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\Uuid;

class ImportService
{
    private Connection $connection;
    private \DateTimeZone $sourceTimezone;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->sourceTimezone = new \DateTimeZone('UTC');
    }

    public function import(User $user, string $json): void
    {
        $items = json_decode($json, true);
        $this->connection->beginTransaction();
        try {
            $taskTagsMap = [];
            foreach ($items as $item) {
                if ($item['status'] === 'deleted') {
                    continue;
                }
                $item['uuid'] = Uuid::fromString($item['uuid'])->toBinary();
                $taskTagsMap[$item['uuid']] = array_map(
                    fn (string $tag) => mb_strtolower($tag),
                    $item['tags'] ?? [],
                );

                $this->importTask($user, $item);
            }
            $knownTags = $this->loadTags($user);
            foreach ($taskTagsMap as $taskId => $taskTags) {
                $knownTags = $this->importTags($user, $knownTags, $taskId, $taskTags);
            }
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();

            throw $e;
        }
    }

    private function importTask(User $user, array $item): void
    {
        $description = '';
        if (array_key_exists('annotations', $item)) {
            $description = array_reduce(
                $item['annotations'],
                fn (string $carry, array $item) => $carry . $item['description'] . "\n\n",
                $description,
            );
        }

        // it is possible to move a task from one user to another, but nobody cares
        $this->connection->executeStatement(
            'INSERT OR REPLACE INTO task (id, user_id, title, description, created, updated, wait, started, ended) 
             VALUES (:id, :user_id, :title, :description, :created, :updated, :wait, :started, :ended)
            ',
            [
                'id' => $item['uuid'],
                'user_id' => $user->getId()->toBinary(),
                'title' => $item['description'],
                'description' => $description,
                'created' => $this->prepareDate($item['entry']),
                'updated' => $this->prepareDate($item['modified']),
                'wait' => $this->prepareDate($item['wait'] ?? null),
                'started' => $this->prepareDate($item['start'] ?? null),
                'ended' => $this->prepareDate($item['end'] ?? null),
            ],
            [
                'created' => Types::DATETIME_IMMUTABLE,
                'updated' => Types::DATETIME_IMMUTABLE,
                'wait' => Types::DATETIME_IMMUTABLE,
                'started' => Types::DATETIME_IMMUTABLE,
                'ended' => Types::DATETIME_IMMUTABLE,
            ],
        );
    }

    private function loadTags(User $user): array
    {
        $result = $this->connection->executeQuery(
            'SELECT id, name FROM tag WHERE user_id = :user_id',
            ['user_id' => $user->getId()->toBinary()],
        );
        $tags = [];
        foreach ($result as $item) {
            $tags[$item['name']] = $item['id'];
        }

        return $tags;
    }

    private function importTags(User $user, array $knownTags, string $taskId, array $tags): array
    {
        $this->connection->executeStatement(
            'DELETE FROM task_tag WHERE task_id = :task_id',
            ['task_id' => $taskId],
        );
        foreach ($tags as $tag) {
            if (!array_key_exists($tag, $knownTags)) {
                $tagId = Uuid::v4();
                $this->connection->executeStatement(
                    'INSERT INTO tag (id, user_id, name) VALUES (:id, :user_id, :name)',
                    [
                        'id' => $tagId->toBinary(),
                        'user_id' => $user->getId()->toBinary(),
                        'name' => $tag,
                    ],
                );
                $knownTags[$tag] = $tagId->toBinary();
            }
            $this->connection->executeStatement(
                'INSERT INTO task_tag (task_id, tag_id) VALUES (:task_id, :tag_id)',
                [
                    'task_id' => $taskId,
                    'tag_id' => $knownTags[$tag],
                ],
            );
        }

        return $knownTags;
    }

    private function prepareDate(?string $date): ?\DateTimeImmutable
    {
        if ($date === null) {
            return null;
        }

        return \DateTimeImmutable::createFromFormat('Ymd\THisZ', $date, $this->sourceTimezone);
    }
}
