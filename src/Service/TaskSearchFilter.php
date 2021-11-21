<?php

declare(strict_types=1);

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

    /**
     * @return array{includeTags:array<string>,excludeTags:array<string>,terms:array<string>}
     */
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

    /**
     * @param array<string> $tags
     */
    private function applyIncludeTags(QueryBuilder $queryBuilder, string $taskAlias, array $tags): void
    {
        foreach ($tags as $i => $tag) {
            $queryBuilder->andWhere("in_array($taskAlias.tags, :includeTag$i) IS NOT NULL")
                ->setParameter("includeTag$i", $tag)
            ;
        }
    }

    /**
     * @param array<string> $tags
     */
    private function applyExcludeTags(QueryBuilder $queryBuilder, string $taskAlias, array $tags): void
    {
        foreach ($tags as $i => $tag) {
            $queryBuilder->andWhere("in_array($taskAlias.tags, :excludeTag$i) IS NULL")
                ->setParameter("excludeTag$i", $tag)
            ;
        }
    }

    /**
     * @param array<string> $terms
     */
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
