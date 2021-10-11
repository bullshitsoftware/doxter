<?php

namespace App\Service;

use function count;
use Doctrine\ORM\QueryBuilder;

class TaskSearchFilter
{
    public function apply(QueryBuilder $queryBuilder, string $taskAlias, string $query): void
    {
        $components = $this->parseQuery($query);
        if (count($components['includeTags']) > 0) {
            $this->applyIncludeTags($queryBuilder, $taskAlias, $components['includeTags']);
        }

        if (count($components['excludeTags']) > 0) {
            $this->applyExcludeTags($queryBuilder, $taskAlias, $components['excludeTags']);
        }

        if (count($components['terms']) > 0) {
            $this->applyTerms($queryBuilder, $taskAlias, $components['terms']);
        }
    }

    private function parseQuery(string $query): array
    {
        $components = ['includeTags' => [], 'excludeTags' => [], 'terms' => []];
        foreach (explode(' ', $query) as $token) {
            if (str_starts_with($token, '+')) {
                $components['includeTags'][] = mb_substr($token, 1);
            } elseif (str_starts_with($token, '-')) {
                $components['excludeTags'][] = mb_substr($token, 1);
            } else {
                $components['terms'][] = $token;
            }
        }

        return $components;
    }

    private function applyIncludeTags(QueryBuilder $queryBuilder, string $taskAlias, array $tags): void
    {
        $queryBuilder->andWhere(
            $queryBuilder->expr()->exists("
                SELECT gi FROM App\Entity\Task ti JOIN ti.tags gi
                WHERE ti = $taskAlias AND gi.name IN (:includeTags)
                GROUP BY ti
                HAVING COUNT(ti) = :includeTagsCount
            ")
        )
            ->setParameter('includeTags', $tags)
            ->setParameter('includeTagsCount', count($tags));
    }

    private function applyExcludeTags(QueryBuilder $queryBuilder, string $taskAlias, array $tags): void
    {
        $queryBuilder->andWhere(
            $queryBuilder->expr()->not(
                $queryBuilder->expr()->exists("
                    SELECT te FROM App\Entity\Task te JOIN te.tags ge
                    WHERE te = $taskAlias AND ge.name IN (:excludeTags)
                ")
            )
        )->setParameter('excludeTags', $tags);
    }

    private function applyTerms(QueryBuilder $queryBuilder, string $taskAlias, array $terms): void
    {
        foreach ($terms as $i => $term) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->orX(
                    "mb_strtolower($taskAlias.title) LIKE :term_$i",
                    "mb_strtolower($taskAlias.description) LIKE :term_$i",
                ))
                ->setParameter("term_$i", '%'.mb_strtolower($term).'%');
        }
    }
}
