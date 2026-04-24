<?php

namespace App\Http\Controllers;

use App\Services\AuthServiceClient;
use App\Services\SteamStatsProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\DTO\SteamStatsDto;
class AiStatsController extends Controller
{
    public function __construct(
        protected AuthServiceClient $authClient,
        protected SteamStatsProvider $statsProvider
    ) {}

    public function getSummary(Request $request): JsonResponse
    {
        $telegramId = $request->input('telegramId');

        if (!$telegramId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing telegramId'
            ], 400);
        }

        // 1. Получаем Steam ID через сервис аутентификации
        try {
            $steamId = $this->authClient->getSteamId($telegramId);
        } catch (\RuntimeException $e) {
            Log::error('Auth service unavailable', ['telegramId' => $telegramId]);
            return response()->json([
                'status' => 'error',
                'message' => 'Auth service unavailable'
            ], 503);
        }

        if (!$steamId) {
            return response()->json([
                'status' => 'error',
                'message' => 'No binding found'
            ], 404);
        }

        // 2. Получаем статистику Steam
        try {
            $stats = $this->statsProvider->getStats($steamId);
        } catch (\RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            Log::error('Unexpected error', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }

        // 3. Формируем текстовую сводку (summary) на основе статистики
        $summary = $this->generateSummary($stats);

        return response()->json([
            'status' => 'success',
            'summary' => $summary
        ]);
    }

    private function generateSummary(SteamStatsDto $stats): string
    {
        // Здесь можно улучшить генерацию текста, добавив больше деталей
        $hours = $stats->hoursTotal;
        $games = $stats->gamesCount;
        $topGame = $stats->topGame;
        $level = $stats->steamLevel;
        $friends = $stats->friendCount;

        $text = "Игрок {$stats->nickname} имеет {$games} игр в библиотеке, ";
        $text .= "общее наигранное время: {$hours} часов. ";
        $text .= "Самый популярная игра: {$topGame}. ";
        $text .= "Уровень Steam: {$level}. ";
        $text .= "Количество друзей: {$friends}. ";

        if ($stats->communityVisibility === 'public') {
            $text .= "Профиль публичный. ";
        } else {
            $text .= "Профиль приватный. ";
        }

        if ($stats->personState === 'online') {
            $text .= "Пользователь сейчас в сети. ";
        } elseif ($stats->personState === 'in-game') {
            $text .= "Пользователь сейчас в игре. ";
        }

        return $text;
    }
}