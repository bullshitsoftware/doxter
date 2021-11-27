<?php

declare(strict_types=1);

namespace App\Service\Task\Filter\Token;

use Doctrine\ORM\QueryBuilder;

class StringToken implements Token
{
    public function __construct(private string $term)
    {
    }

    public function apply(QueryBuilder $queryBuilder, string $taskAlias): void
    {
        $id = uniqid();
        $queryBuilder
            ->andWhere($queryBuilder->expr()->orX(
                "mb_strtolower($taskAlias.title) LIKE :term_$id",
                "mb_strtolower($taskAlias.description) LIKE :term_$id",
            ))
            ->setParameter("term_$id", '%'.mb_strtolower($this->term).'%')
        ;
    }
}
