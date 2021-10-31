<?php

namespace App\Service;

use App\Entity\User;
use function array_key_exists;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\Uuid;
use Throwable;

class ImportService
{
    private DateTimeZone $sourceTimezone;

    public function __construct(private Connection $connection)
    {
        $this->sourceTimezone = new DateTimeZone('UTC');
    }

    public function import(User $user, string $json): void
    {
        $items = json_decode($json, true);
        $this->connection->beginTransaction();
        try {
            $taskTagsMap = [];
            foreach ($items as $item) {
                if ('deleted' === $item['status']) {
                    continue;
                }
                $item['uuid'] = Uuid::fromString($item['uuid'])->toBinary();
                $item['tags'] = array_map(
                    fn (string $tag) => mb_strtolower($tag),
                    $item['tags'] ?? [],
                );
                usort($item['tags'], fn (string $s1, string $s2) => strcmp($s1, $s2));

                $this->importTask($user, $item);
            }
            $this->connection->commit();
        } catch (Throwable $e) {
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
                fn (string $carry, array $item) => $carry.$item['description']."\n\n",
                $description,
            );
        }

        // it is possible to move a task from one user to another, but nobody cares
        $this->connection->executeStatement(
            'INSERT OR REPLACE INTO task (id, user_id, tags, title, description, created, updated, wait, started, ended, due)
             VALUES (:id, :user_id, :tags, :title, :description, :created, :updated, :wait, :started, :ended, :due)
            ',
            [
                'id' => $item['uuid'],
                'user_id' => $user->getId()->toBinary(),
                'tags' => json_encode($item['tags']),
                'title' => $item['description'],
                'description' => $description,
                'created' => $this->prepareDate($item['entry']),
                'updated' => $this->prepareDate($item['modified']),
                'wait' => $this->prepareDate($item['wait'] ?? null),
                'started' => $this->prepareDate($item['start'] ?? null),
                'ended' => $this->prepareDate($item['end'] ?? null),
                'due' => $this->prepareDate($item['due'] ?? null),
            ],
            [
                'created' => Types::DATETIME_IMMUTABLE,
                'updated' => Types::DATETIME_IMMUTABLE,
                'wait' => Types::DATETIME_IMMUTABLE,
                'started' => Types::DATETIME_IMMUTABLE,
                'ended' => Types::DATETIME_IMMUTABLE,
                'due' => Types::DATETIME_IMMUTABLE,
            ],
        );
    }

    private function prepareDate(?string $date): ?DateTimeImmutable
    {
        if (null === $date) {
            return null;
        }

        return DateTimeImmutable::createFromFormat('Ymd\THisZ', $date, $this->sourceTimezone);
    }
}
