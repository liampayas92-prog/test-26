<?php

declare(strict_types=1);

namespace App\Infrastructure;

final readonly class RickAndMortyApiClient
{
    public function __construct(
        private string $baseUri,
        private FileCache $cache,
    ) {
    }

    /**
     * @param array<string, string|int> $query
     * @return array<string, mixed>
     */
    public function characters(array $query): array
    {
        return $this->request('/character', $query);
    }

    /**
     * @return array<string, mixed>
     */
    public function character(int $id): array
    {
        return $this->request('/character/' . $id);
    }

    /**
     * Fetch several episodes in one call so a character page does not trigger
     * one request per episode.
     *
     * The API returns a single object when asked for one id and a list when
     * asked for several, so we normalise both shapes to a list here.
     *
     * @param list<int> $ids
     * @return list<array<string, mixed>>
     */
    public function episodes(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $payload = $this->request('/episode/' . implode(',', $ids));

        return array_is_list($payload) ? $payload : [$payload];
    }

    /**
     * @param array<string, string|int> $query
     * @return array<string, mixed>
     */
    private function request(string $path, array $query = []): array
    {
        $url = rtrim($this->baseUri, '/') . $path;

        if ($query !== []) {
            $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        // Serve from cache first. The full URL is a stable key and keeps us
        // well within the public API's rate limit when users page around.
        $cacheKey = 'GET ' . $url;
        $cached = $this->cache->get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        [$statusCode, $body] = $this->fetch($url);

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new ApiException('Rick and Morty API returned HTTP ' . $statusCode . '.', $statusCode);
        }

        $payload = json_decode($body, true);

        if (! is_array($payload)) {
            throw new ApiException('Rick and Morty API returned an invalid response.');
        }

        $this->cache->set($cacheKey, $payload);

        return $payload;
    }

    /**
     * Prefer cURL when the extension is available and fall back to the stream
     * wrapper otherwise, so the app does not hard-depend on a single transport.
     *
     * @return array{0: int, 1: string}
     */
    private function fetch(string $url): array
    {
        if (function_exists('curl_init')) {
            return $this->fetchWithCurl($url);
        }

        return $this->fetchWithStreams($url);
    }

    /**
     * @return array{0: int, 1: string}
     */
    private function fetchWithCurl(string $url): array
    {
        $handle = curl_init($url);

        if ($handle === false) {
            throw new ApiException('Unable to initialise an HTTP request.');
        }

        curl_setopt_array($handle, [
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_USERAGENT => 'RickAndMortyEncyclopedia/1.0',
        ]);

        $body = curl_exec($handle);
        $statusCode = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $error = curl_error($handle);

        if ($body === false) {
            throw new ApiException('Unable to contact the Rick and Morty API: ' . $error);
        }

        return [$statusCode, (string) $body];
    }

    /**
     * @return array{0: int, 1: string}
     */
    private function fetchWithStreams(string $url): array
    {
        $context = stream_context_create([
            'http' => [
                'header' => "Accept: application/json\r\nUser-Agent: RickAndMortyEncyclopedia/1.0\r\n",
                'ignore_errors' => true,
                'timeout' => 6,
            ],
        ]);

        $body = @file_get_contents($url, false, $context);

        if ($body === false) {
            throw new ApiException('Unable to contact the Rick and Morty API.');
        }

        $statusCode = 0;

        foreach ($http_response_header ?? [] as $header) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $header, $matches) === 1) {
                $statusCode = (int) $matches[1];
                break;
            }
        }

        return [$statusCode, $body];
    }
}
