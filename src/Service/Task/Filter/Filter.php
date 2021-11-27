<?php

declare(strict_types=1);

namespace App\Service\Task\Filter;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use LogicException;
use Symfony\Component\Security\Core\Security;

class Filter
{
    public function __construct(private Security $security, private Parser $parser)
    {
    }

    public function apply(QueryBuilder $queryBuilder, string $taskAlias, string $query): void
    {
        /** @var User|null */
        $user = $this->security->getUser();
        if (null === $user) {
            throw new LogicException('The filter should only be used for authorized users');
        }

        foreach ($this->parser->parse($user, $query) as $token) {
            $token->apply($queryBuilder, $taskAlias);
        }
    }
}
