<?php

declare(strict_types=1);

namespace App\Core;

final readonly class Request
{
    /**
     * @param array<string, mixed> $query
     */
    private function __construct(
        private string $method,
        private string $path,
        private array $query,
    ) {
    }

    public static function fromGlobals(): self
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);

        return new self(
            strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')),
            $path !== false && $path !== null ? $path : '/',
            $_GET,
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function string(string $key, int $maxLength = 100): string
    {
        $value = $this->query[$key] ?? '';

        if (! is_scalar($value)) {
            return '';
        }

        return substr(trim((string) $value), 0, $maxLength);
    }

    /**
     * Read a bounded integer from the query string. The min/max act as a guard
     * against out-of-range or non-numeric input (e.g. ?page=-1 or ?page=abc).
     */
    public function integer(string $key, int $default = 1, int $min = 1, int $max = 500): int
    {
        $value = $this->query[$key] ?? null;

        if ($value === null || is_array($value)) {
            return $default;
        }

        $validated = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => $min, 'max_range' => $max],
        ]);

        return is_int($validated) ? $validated : $default;
    }
}
