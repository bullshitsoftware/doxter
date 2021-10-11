<?php

namespace App\Doctrine;

use Doctrine\ORM\Query\SqlWalker;

class OrderByNullSqlWalker extends SqlWalker
{
    public const HINT = 'orderByNull';
    public const FIRST = 'NULLS FIRST';
    public const LAST = 'NULLS LAST';

    public function walkOrderByClause($orderByClause): string
    {
        $sql = parent::walkOrderByClause($orderByClause);
        $fields = $this->getQuery()->getHint(self::HINT);
        if (false === $fields) {
            return $sql;
        }
        foreach ($fields as $field => $nullOrder) {
            $sql = preg_replace("/$field (ASC|DESC)/i", "$field $1 $nullOrder", $sql);
        }

        return $sql;
    }
}
