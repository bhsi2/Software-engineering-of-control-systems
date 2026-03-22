<?php
namespace App\Http\Controllers;

use App\Repositories\UserBindingRepository;
use App\Services\SteamStatsProvider;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function __construct(
        protected UserBindingRepository $bindingRepo,
        protected SteamStatsProvider $statsProvider
    ) {}

    public function show(int $telegramId): JsonResponse
    {

        // Заглушка – всегда используем этот Steam ID (можно заменить на любой)
        $steamId = '76561197960287930';

        try {
            $stats = $this->statsProvider->getStats($steamId);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }

        return response()->json($stats);
        
        
        //return response()->json(['test' => 'ok', 'id' => $telegramId]);

        // $steamId = $this->bindingRepo->findSteamIdByTelegramId($telegramId);
        // if (!$steamId) {
        //     return response()->json(['error' => 'No binding found'], 404);
        // }

        // try {
        //     $stats = $this->statsProvider->getStats($steamId);
        // } catch (\RuntimeException $e) {
        //     return response()->json(['error' => $e->getMessage()], 404);
        // }

        // return response()->json($stats);
    }
}