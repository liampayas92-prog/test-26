<?php

declare(strict_types=1);

namespace App\Domain;

final readonly class Episode
{
    private function __construct(
        public int $id,
        public string $name,
        public string $code,
        public string $airDate,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromApi(array $payload): self
    {
        return new self(
            (int) ($payload['id'] ?? 0),
            (string) ($payload['name'] ?? 'Unknown episode'),
            (string) ($payload['episode'] ?? ''),
            (string) ($payload['air_date'] ?? ''),
        );
    }
}
