<?php

declare(strict_types=1);

namespace App\Service\Task\Filter\Token;

use DateTimeImmutable;
use Doctrine\ORM\QueryBuilder;

class DateToken implements Token
{
    public function __construct(private string $field, private string $op, private DateTimeImmutable $date)
    {
    }

    public function apply(QueryBuilder $queryBuilder, string $taskAlias): void
    {
        $id = uniqid();

        $queryBuilder
            ->andWhere("$taskAlias.{$this->field} {$this->op} :date_$id")
            ->setParameter("date_$id", $this->date)
        ;
    }
}
