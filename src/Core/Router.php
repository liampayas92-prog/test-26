<?php

declare(strict_types=1);

namespace App\Core;

use Closure;

final class Router
{
    /**
     * @var list<array{method: string, pattern: string, handler: Closure}>
     */
    private array $routes = [];

    public function get(string $pattern, Closure $handler): void
    {
        $this->routes[] = [
            'method' => 'GET',
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }

            $parameters = $this->match($route['pattern'], $request->path());

            if ($parameters !== null) {
                return ($route['handler'])($request, $parameters);
            }
        }

        return new Response('Page not found.', 404);
    }

    /**
     * @return array<string, string>|null
     */
    private function match(string $pattern, string $path): ?array
    {
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);

        if ($regex === null || preg_match('#^' . $regex . '$#', $path, $matches) !== 1) {
            return null;
        }

        return array_filter(
            $matches,
            static fn (string|int $key): bool => is_string($key),
            ARRAY_FILTER_USE_KEY,
        );
    }
}
