<?php

namespace App\Http\Controllers;

use App\Services\SteamStatsProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class StatsController extends Controller
{
    public function __construct(
        protected SteamStatsProvider $statsProvider
    ) {}

    public function show(int $telegramId): JsonResponse
    {
        // Фиксированный Steam ID (можно вынести в конфиг или .env)
        $steamId = config('services.steam.default_steam_id', '76561197960287930');

        try {
            $stats = $this->statsProvider->getStats($steamId);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            Log::error('Unexpected error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }

        return response()->json($stats);
    }
}