<?php

namespace App\Doctrine;

class Pagination
{
    public function __construct(private array $items, private bool $more)
    {
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function hasMore(): bool
    {
        return $this->more;
    }
}
