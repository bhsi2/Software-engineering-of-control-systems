<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class SteamApiService
{
    private Client $client;
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = new Client([
            'base_uri' => 'https://api.steampowered.com/',
            'timeout' => 10.0,
        ]);
    }

    public function getPlayerSummary(string $steamId): ?array
    {
        try {
            $response = $this->client->get('ISteamUser/GetPlayerSummaries/v0002/', [
                'query' => [
                    'key' => $this->apiKey,
                    'steamids' => $steamId
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            return $data['response']['players'][0] ?? null;
        } catch (\Exception $e) {
            Log::error('Steam API error (getPlayerSummary): ' . $e->getMessage());
            return null;
        }
    }

    public function getOwnedGames(string $steamId): ?array
    {
        try {
            $response = $this->client->get('IPlayerService/GetOwnedGames/v0001/', [
                'query' => [
                    'key' => $this->apiKey,
                    'steamid' => $steamId,
                    'include_appinfo' => true,
                    'include_played_free_games' => true,
                    'format' => 'json'
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            return $data['response'] ?? null;
        } catch (\Exception $e) {
            Log::error('Steam API error (getOwnedGames): ' . $e->getMessage());
            return null;
        }
    }

    public function getSteamLevel(string $steamId): ?int
    {
        try {
            $response = $this->client->get('IPlayerService/GetSteamLevel/v1/', [
                'query' => [
                    'key' => $this->apiKey,
                    'steamid' => $steamId,
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            return $data['response']['player_level'] ?? null;
        } catch (\Exception $e) {
            Log::error('Steam API error (getSteamLevel): ' . $e->getMessage());
            return null;
        }
    }

    public function getFriendCount(string $steamId): ?int
    {
        try {
            $response = $this->client->get('ISteamUser/GetFriendList/v1/', [
                'query' => [
                    'key' => $this->apiKey,
                    'steamid' => $steamId,
                    'relationship' => 'friend',
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            $friends = $data['friendslist']['friends'] ?? [];
            return count($friends);
        } catch (\Exception $e) {
            Log::error('Steam API error (getFriendCount): ' . $e->getMessage());
            return null;
        }
    }
}