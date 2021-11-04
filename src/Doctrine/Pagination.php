<?php

namespace App\Doctrine;

/**
 * @template T
 */
class Pagination
{
    /**
     * @param array<T> $items
     */
    public function __construct(private array $items, private bool $more)
    {
    }

    /**
     * @return array<T>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function hasMore(): bool
    {
        return $this->more;
    }
}
