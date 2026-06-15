<?php

declare(strict_types=1);

namespace App\Domain;

final readonly class CharacterDetails
{
    /**
     * @param list<Episode> $episodes
     */
    public function __construct(
        public Character $character,
        public array $episodes,
    ) {
    }
}
