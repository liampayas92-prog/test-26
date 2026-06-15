<?php

declare(strict_types=1);

namespace App\Support;

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function app_path(string $path = ''): string
{
    $basePath = dirname(__DIR__, 2);

    return $path === '' ? $basePath : $basePath . DIRECTORY_SEPARATOR . trim($path, '/\\');
}
