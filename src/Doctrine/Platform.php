<?php

declare(strict_types=1);

namespace App\Doctrine;

use Doctrine\DBAL\Platforms\SqlitePlatform;

class Platform extends SqlitePlatform
{
    public function supportsForeignKeyConstraints(): bool
    {
        return true;
    }
}
