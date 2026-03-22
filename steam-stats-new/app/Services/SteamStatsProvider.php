<?php
namespace App\Services;

use App\DTO\SteamStatsDto;

class SteamStatsProvider
{
    public function __construct(
        protected SteamApiService $steamApi
    ) {}

    public function getStats(string $steamId): SteamStatsDto
    {
        $summary = $this->steamApi->getPlayerSummary($steamId);
        if (!$summary) {
            throw new \RuntimeException('Steam profile not found');
        }
        $nickname = $summary['personaname'] ?? 'Unknown';

        $gamesData = $this->steamApi->getOwnedGames($steamId);
        if (!$gamesData || !isset($gamesData['games'])) {
            $gamesCount = 0;
            $totalHours = 0;
            $topGame = '—';
        } else {
            $games = $gamesData['games'];
            $gamesCount = count($games);
            $totalMinutes = array_sum(array_column($games, 'playtime_forever'));
            $totalHours = (int) round($totalMinutes / 60);
            $topGame = $this->findMostPlayedGame($games);
        }

        return new SteamStatsDto($nickname, $gamesCount, $totalHours, $topGame);
    }

    private function findMostPlayedGame(array $games): string
    {
        $maxGame = '';
        $maxTime = -1;
        foreach ($games as $game) {
            if (($game['playtime_forever'] ?? 0) > $maxTime) {
                $maxTime = $game['playtime_forever'];
                $maxGame = $game['name'] ?? 'Unknown';
            }
        }
        return $maxGame ?: '—';
    }
}