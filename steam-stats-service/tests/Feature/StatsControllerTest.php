<?php

use Illuminate\Support\Facades\Log;
use App\DTO\SteamStatsDto;
use App\Services\AuthServiceClient;
use App\Services\SteamStatsProvider;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::get('/stats/{telegramId}', [\App\Http\Controllers\StatsController::class, 'show']);
});

it('returns stats successfully', function () {
    $telegramId = 123456;
    $steamId = '76561197960287930';
    $statsDto = new SteamStatsDto(
        nickname: 'TestUser',
        gamesCount: 10,
        hoursTotal: 100,
        topGame: 'TestGame',
        steamLevel: 5,
        friendCount: 20,
        personState: 'online',
        communityVisibility: 'public',
        accountCreated: 1234567890,
    );

    $authMock = Mockery::mock(AuthServiceClient::class);
    $authMock->shouldReceive('getSteamId')->with($telegramId)->once()->andReturn($steamId);

    $providerMock = Mockery::mock(SteamStatsProvider::class);
    $providerMock->shouldReceive('getStats')->with($steamId)->once()->andReturn($statsDto);

    $this->app->instance(AuthServiceClient::class, $authMock);
    $this->app->instance(SteamStatsProvider::class, $providerMock);

    $response = $this->getJson("/stats/{$telegramId}");
    $response->assertStatus(200)
        ->assertJsonFragment(['nickname' => 'TestUser', 'gamesCount' => 10]);
});

it('returns 404 when no binding found', function () {
    $telegramId = 123456;

    $authMock = Mockery::mock(AuthServiceClient::class);
    $authMock->shouldReceive('getSteamId')->with($telegramId)->once()->andReturn(null);

    $this->app->instance(AuthServiceClient::class, $authMock);

    $response = $this->getJson("/stats/{$telegramId}");
    $response->assertStatus(404)->assertJson(['error' => 'No binding found']);
});

it('returns 503 when auth service is unavailable', function () {
    $telegramId = 123456;

    $authMock = Mockery::mock(AuthServiceClient::class);
    $authMock->shouldReceive('getSteamId')
        ->with($telegramId)
        ->once()
        ->andThrow(new RuntimeException('Auth service unavailable'));

    $this->app->instance(AuthServiceClient::class, $authMock);

    $response = $this->getJson("/stats/{$telegramId}");
    $response->assertStatus(503)->assertJson(['error' => 'Auth service unavailable']);
});