<?php

declare(strict_types=1);

namespace App\Core;

final readonly class Response
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        private string $body,
        private int $statusCode = 200,
        private array $headers = ['Content-Type' => 'text/html; charset=UTF-8'],
    ) {
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $this->body;
    }
}
