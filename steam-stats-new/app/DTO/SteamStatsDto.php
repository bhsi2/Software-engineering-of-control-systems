<?php
namespace App\DTO;

class SteamStatsDto
{
    public function __construct(
        public readonly string $nickname,
        public readonly int $gamesCount,
        public readonly int $hoursTotal,
        public readonly string $topGame,
        public readonly int $steamLevel,
        public readonly int $friendCount,
        public readonly string $personState,
        public readonly string $communityVisibility,
        public readonly int $accountCreated, // unix timestamp
    ) {}
}