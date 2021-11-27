<?php

declare(strict_types=1);

namespace App\Service\Task\Filter\Token;

use Doctrine\ORM\QueryBuilder;
use LogicException;

class TagToken implements Token
{
    public const MODE_INCLUDE = 'include';
    public const MODE_EXCLUDE = 'exclude';

    public function __construct(private string $mode, private string $tag)
    {
    }

    public function apply(QueryBuilder $queryBuilder, string $taskAlias): void
    {
        $id = uniqid();
        if (self::MODE_INCLUDE === $this->mode) {
            $queryBuilder->andWhere("in_array($taskAlias.tags, :tag_$id) IS NOT NULL")
                ->setParameter("tag_$id", $this->tag)
            ;
        } elseif (self::MODE_EXCLUDE === $this->mode) {
            $queryBuilder->andWhere("in_array($taskAlias.tags, :tag_$id) IS NULL")
                ->setParameter("tag_$id", $this->tag)
            ;
        } else {
            throw new LogicException(sprintf('Invalid tag token mode: %s', $this->mode));
        }
    }
}
