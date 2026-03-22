<?php
namespace App\DTO;

class SteamStatsDto
{
    public function __construct(
        public readonly string $nickname,
        public readonly int $gamesCount,
        public readonly int $hoursTotal,
        public readonly string $topGame
    ) {}
}