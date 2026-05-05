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
        // 1. Получаем сводку (ник, статус, дата регистрации, видимость)
        $summary = $this->steamApi->getPlayerSummary($steamId);
        if (!$summary) {
            throw new \RuntimeException('Steam profile not found');
        }
        $nickname = $summary['personaname'] ?? 'Unknown';

        // Определяем статус пользователя
        $personState = $this->mapPersonState($summary['personastate'] ?? 0);
        $communityVisibility = $this->mapCommunityVisibility($summary['communityvisibilitystate'] ?? 1);
        $accountCreated = $summary['timecreated'] ?? null; // unix timestamp

        // 2. Получаем игры
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

        // 3. Получаем уровень Steam
        $steamLevel = $this->steamApi->getSteamLevel($steamId) ?? 0;

        // 4. Получаем количество друзей (может быть недоступно для приватных профилей)
        $friendCount = $this->steamApi->getFriendCount($steamId) ?? 0;

        return new SteamStatsDto(
            nickname: $nickname,
            gamesCount: $gamesCount,
            hoursTotal: $totalHours,
            topGame: $topGame,
            steamLevel: $steamLevel,
            friendCount: $friendCount,
            personState: $personState,
            communityVisibility: $communityVisibility,
            accountCreated: $accountCreated ?? 0,
        );
    }

    private function mapPersonState(int $state): string
    {
        return match ($state) {
            0 => 'offline',
            1 => 'online',
            2 => 'busy',
            3 => 'away',
            4 => 'snooze',
            5 => 'in-game',
            6 => 'looking-to-trade',
            7 => 'looking-to-play',
            default => 'unknown',
        };
    }

    private function mapCommunityVisibility(int $visibility): string
    {
        return match ($visibility) {
            1 => 'private',
            2 => 'friends-only',
            3 => 'public',
            default => 'unknown',
        };
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
