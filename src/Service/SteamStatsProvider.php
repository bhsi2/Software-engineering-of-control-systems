<?php
namespace App\Service;

use App\DTO\SteamStatsDto;

class SteamStatsProvider
{
    private SteamApiService $steamApi;

    public function __construct(SteamApiService $steamApi)
    {
        $this->steamApi = $steamApi;
    }

    /**
     * @throws \RuntimeException если данные не получены
     */
    public function getStats(string $steamId): SteamStatsDto
    {
        // 1. Получаем ник
        $summary = $this->steamApi->getPlayerSummary($steamId);
        if (!$summary) {
            throw new \RuntimeException('Steam profile not found');
        }
        $nickname = $summary['personaname'] ?? 'Unknown';

        // 2. Получаем игры
        $gamesData = $this->steamApi->getOwnedGames($steamId);
        if (!$gamesData || !isset($gamesData['games'])) {
            // Если игр нет (профиль скрыт или действительно нет игр) – отдаём пустую статистику
            $gamesCount = 0;
            $totalHours = 0;
            $topGame = '—';
        } else {
            $games = $gamesData['games'];
            $gamesCount = count($games);

            // Суммируем время (в минутах), переводим в часы
            $totalMinutes = array_sum(array_column($games, 'playtime_forever'));
            $totalHours = (int) round($totalMinutes / 60);

            // Определяем топ-игру по максимальному времени
            $topGame = $this->findMostPlayedGame($games);
        }

        return new SteamStatsDto($nickname, $gamesCount, $totalHours, $topGame);
    }

    /**
     * @param array $games список игр из ответа Steam
     */
    private function findMostPlayedGame(array $games): string
    {
        $maxGame = '';
        $maxTime = -1;
        foreach ($games as $game) {
            if (isset($game['playtime_forever']) && $game['playtime_forever'] > $maxTime) {
                $maxTime = $game['playtime_forever'];
                $maxGame = $game['name'] ?? 'Unknown';
            }
        }
        return $maxGame ?: '—';
    }
}