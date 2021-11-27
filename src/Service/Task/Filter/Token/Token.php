<?php

declare(strict_types=1);

namespace App\Service\Task\Filter\Token;

use Doctrine\ORM\QueryBuilder;

interface Token
{
    public function apply(QueryBuilder $queryBuilder, string $taskAlias): void;
}
