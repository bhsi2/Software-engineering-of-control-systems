<?php

namespace App\Http\Controllers;

use App\Services\AuthServiceClient;
use App\Services\SteamStatsProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class StatsController extends Controller
{
    public function __construct(
        protected AuthServiceClient $authClient,
        protected SteamStatsProvider $statsProvider
    ) {}

    public function show(int $telegramId): JsonResponse
    {
        // 1. Запрашиваем steamId у сервиса аутентификации
        try {
            $steamId = $this->authClient->getSteamId($telegramId);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => 'Auth service unavailable'], 503);
        }

        if (!$steamId) {
            return response()->json(['error' => 'No binding found'], 404);
        }

        // 2. Получаем статистику из Steam
        try {
            $stats = $this->statsProvider->getStats($steamId);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            Log::error('Unexpected error: '.$e->getMessage());

            return response()->json(['error' => 'Internal server error'], 500);
        }

        return response()->json($stats);
    }
}
