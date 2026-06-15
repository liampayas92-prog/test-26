<?php

declare(strict_types=1);

namespace App\Domain;

final readonly class CharacterPage
{
    /**
     * @param list<Character> $characters
     */
    public function __construct(
        public array $characters,
        public int $currentPage,
        public int $totalPages,
        public int $totalCount,
    ) {
    }

    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->totalPages;
    }

    /**
     * @return list<int|string>
     */
    public function visiblePageNumbers(int $radius = 2): array
    {
        if ($this->totalPages <= 1) {
            return [];
        }

        $pages = [1];
        $start = max(2, $this->currentPage - $radius);
        $end = min($this->totalPages - 1, $this->currentPage + $radius);

        if ($start > 2) {
            $pages[] = 'ellipsis-left';
        }

        for ($page = $start; $page <= $end; $page++) {
            $pages[] = $page;
        }

        if ($end < $this->totalPages - 1) {
            $pages[] = 'ellipsis-right';
        }

        $pages[] = $this->totalPages;

        return $pages;
    }
}
