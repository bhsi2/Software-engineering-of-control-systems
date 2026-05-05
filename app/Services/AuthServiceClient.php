<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class AuthServiceClient
{
    private Client $httpClient;

    private string $authServiceUrl;

    public function __construct(Client $httpClient, string $authServiceUrl)
    {
        $this->httpClient = $httpClient;
        $this->authServiceUrl = rtrim($authServiceUrl, '/');
    }

    /**
     * Получить Steam ID по Telegram ID из сервиса аутентификации.
     *
     * @throws \RuntimeException если сервис недоступен или вернул ошибку
     */
    public function getSteamId(int $telegramId): ?string
    {
        try {
            $response = $this->httpClient->get($this->authServiceUrl.'/link/'.$telegramId, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody(), true);

                return $data['steamId'] ?? null;
            }

            if ($response->getStatusCode() === 404) {
                return null; // привязка не найдена
            }

            Log::warning('Auth service returned unexpected status', [
                'status' => $response->getStatusCode(),
                'telegramId' => $telegramId,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Auth service request failed', [
                'telegramId' => $telegramId,
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Auth service unavailable');
        }
    }
}
