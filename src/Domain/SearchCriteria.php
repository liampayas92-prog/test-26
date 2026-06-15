<?php

declare(strict_types=1);

namespace App\Domain;

final readonly class SearchCriteria
{
    public function __construct(
        public int $page,
        public string $name = '',
        public string $status = '',
        public string $species = '',
        public string $gender = '',
    ) {
    }

    /**
     * @return array<string, string|int>
     */
    public function toApiQuery(): array
    {
        return array_filter([
            'page' => $this->page,
            'name' => $this->name,
            'status' => $this->status,
            'species' => $this->species,
            'gender' => $this->gender,
        ], static fn (string|int $value): bool => $value !== '');
    }

    /**
     * @return array<string, string|int>
     */
    public function toUrlQuery(int $page): array
    {
        return array_filter([
            'page' => $page,
            'q' => $this->name,
            'status' => $this->status,
            'species' => $this->species,
            'gender' => $this->gender,
        ], static fn (string|int $value): bool => $value !== '');
    }
}
