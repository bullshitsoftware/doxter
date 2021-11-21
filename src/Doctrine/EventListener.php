<?php

declare(strict_types=1);

namespace App\Doctrine;

use Doctrine\DBAL\Event\ConnectionEventArgs;

class EventListener
{
    public function postConnect(ConnectionEventArgs $args): void
    {
        $args->getConnection()->executeStatement('PRAGMA foreign_keys = ON');
    }
}
