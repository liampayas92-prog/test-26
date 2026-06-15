<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Character;
use App\Domain\CharacterDetails;
use App\Domain\CharacterPage;
use App\Domain\Episode;
use App\Domain\SearchCriteria;
use App\Infrastructure\ApiException;
use App\Infrastructure\RickAndMortyApiClient;

final readonly class CharacterService
{
    public function __construct(private RickAndMortyApiClient $client)
    {
    }

    public function search(SearchCriteria $criteria): CharacterPage
    {
        try {
            $payload = $this->client->characters($criteria->toApiQuery());
        } catch (ApiException $exception) {
            if ($exception->statusCode() === 404) {
                return new CharacterPage([], $criteria->page, 0, 0);
            }

            throw $exception;
        }

        $results = is_array($payload['results'] ?? null) ? $payload['results'] : [];
        $characters = array_map(
            static fn (array $character): Character => Character::fromApi($character),
            array_filter($results, 'is_array'),
        );

        return new CharacterPage(
            array_values($characters),
            $criteria->page,
            (int) ($payload['info']['pages'] ?? 1),
            (int) ($payload['info']['count'] ?? count($characters)),
        );
    }

    public function details(int $id): CharacterDetails
    {
        $character = Character::fromApi($this->client->character($id));
        $episodes = array_map(
            static fn (array $episode): Episode => Episode::fromApi($episode),
            $this->client->episodes($this->episodeIds($character->episodeUrls)),
        );

        return new CharacterDetails($character, $episodes);
    }

    /**
     * @param list<string> $urls
     * @return list<int>
     */
    private function episodeIds(array $urls): array
    {
        $ids = [];

        foreach ($urls as $url) {
            $id = filter_var(basename($url), FILTER_VALIDATE_INT);

            if (is_int($id)) {
                $ids[] = $id;
            }
        }

        return $ids;
    }
}
