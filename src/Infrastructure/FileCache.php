<?php

declare(strict_types=1);

namespace App\Infrastructure;

final readonly class FileCache
{
    public function __construct(
        private string $directory,
        private int $ttlSeconds,
    ) {
        if (! is_dir($this->directory)) {
            mkdir($this->directory, 0775, true);
        }
    }

    /**
     * @return array<string, mixed>|list<mixed>|null
     */
    public function get(string $key): ?array
    {
        $file = $this->fileFor($key);

        if (! is_file($file)) {
            return null;
        }

        $cached = json_decode((string) file_get_contents($file), true);

        if (! is_array($cached) || ($cached['expires_at'] ?? 0) < time()) {
            @unlink($file);

            return null;
        }

        $payload = $cached['payload'] ?? null;

        return is_array($payload) ? $payload : null;
    }

    /**
     * @param array<string, mixed>|list<mixed> $payload
     */
    public function set(string $key, array $payload): void
    {
        $data = [
            'expires_at' => time() + $this->ttlSeconds,
            'payload' => $payload,
        ];

        file_put_contents($this->fileFor($key), json_encode($data, JSON_THROW_ON_ERROR), LOCK_EX);
    }

    private function fileFor(string $key): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . hash('sha256', $key) . '.json';
    }
}
