<?php
namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\SteamStatsProvider;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class StatsController
{
    private UserRepository $userRepo;
    private SteamStatsProvider $statsProvider;

    public function __construct(UserRepository $userRepo, SteamStatsProvider $statsProvider)
    {
        $this->userRepo = $userRepo;
        $this->statsProvider = $statsProvider;
    }

    public function getStats(Request $request, Response $response, array $args): Response
    {
        $telegramId = (int) $args['telegramId'];

        // 1. Проверяем привязку
        $steamId = $this->userRepo->findSteamIdByTelegramId($telegramId);
        if (!$steamId) {
            // Возвращаем пустой ответ с кодом 404 (или можно 200 с null, но по заданию 404)
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json')
                ->write(json_encode(['error' => 'No binding found']));
        }

        // 2. Получаем статистику
        try {
            $stats = $this->statsProvider->getStats($steamId);
        } catch (\RuntimeException $e) {
            // Steam профиль не найден или данные недоступны
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json')
                ->write(json_encode(['error' => $e->getMessage()]));
        }

        // 3. Формируем ответ
        $response->getBody()->write(json_encode($stats));
        return $response->withHeader('Content-Type', 'application/json');
    }
}