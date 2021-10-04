<?php

namespace App\Doctrine;

class Pagination
{
    private array $items;
    private bool $more;

    public function __construct(array $items, bool $more)
    {
        $this->items = $items;
        $this->more = $more;
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
