<?php
namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class SteamApiService
{
    private Client $httpClient;
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->httpClient = new Client([
            'base_uri' => 'https://api.steampowered.com/',
            'timeout'  => 10.0,
        ]);
    }

    /**
     * Возвращает краткую информацию о пользователе.
     */
    public function getPlayerSummary(string $steamId): ?array
    {
        try {
            $response = $this->httpClient->get('ISteamUser/GetPlayerSummaries/v0002/', [
                'query' => [
                    'key' => $this->apiKey,
                    'steamids' => $steamId
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            return $data['response']['players'][0] ?? null;
        } catch (GuzzleException $e) {
            // Логирование ошибки (можно добавить Monolog)
            return null;
        }
    }

    /**
     * Возвращает список игр пользователя с наигранным временем.
     */
    public function getOwnedGames(string $steamId): ?array
    {
        try {
            $response = $this->httpClient->get('IPlayerService/GetOwnedGames/v0001/', [
                'query' => [
                    'key' => $this->apiKey,
                    'steamid' => $steamId,
                    'include_appinfo' => true,
                    'include_played_free_games' => true,
                    'format' => 'json'
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            return $data['response'] ?? null; // содержит game_count и games
        } catch (GuzzleException $e) {
            return null;
        }
    }
}