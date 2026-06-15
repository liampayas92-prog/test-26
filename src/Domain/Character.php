<?php

declare(strict_types=1);

namespace App\Domain;

final readonly class Character
{
    /**
     * @param list<string> $episodeUrls
     */
    private function __construct(
        public int $id,
        public string $name,
        public string $status,
        public string $species,
        public string $type,
        public string $gender,
        public string $image,
        public string $originName,
        public string $locationName,
        public array $episodeUrls,
    ) {
    }

    /**
     * Build a character from a single API record, falling back to safe
     * defaults so a partial or unexpected payload never breaks a page.
     *
     * @param array<string, mixed> $payload
     */
    public static function fromApi(array $payload): self
    {
        $origin = is_array($payload['origin'] ?? null) ? $payload['origin'] : [];
        $location = is_array($payload['location'] ?? null) ? $payload['location'] : [];
        $episodes = is_array($payload['episode'] ?? null) ? $payload['episode'] : [];

        return new self(
            (int) ($payload['id'] ?? 0),
            (string) ($payload['name'] ?? 'Unknown'),
            (string) ($payload['status'] ?? 'unknown'),
            (string) ($payload['species'] ?? 'Unknown'),
            (string) ($payload['type'] ?? ''),
            (string) ($payload['gender'] ?? 'unknown'),
            (string) ($payload['image'] ?? ''),
            (string) ($origin['name'] ?? 'Unknown'),
            (string) ($location['name'] ?? 'Unknown'),
            array_values(array_filter($episodes, 'is_string')),
        );
    }

    public function statusClass(): string
    {
        return match (strtolower($this->status)) {
            'alive' => 'status-alive',
            'dead' => 'status-dead',
            default => 'status-unknown',
        };
    }
}
