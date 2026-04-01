<?php

use App\DTO\SteamStatsDto;
use App\Services\SteamStatsProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    // Регистрируем маршрут, если его нет
    if (!Route::has('stats.show')) {
        Route::get('/stats/{telegramId}', [\App\Http\Controllers\StatsController::class, 'show']);
    }
});

it('returns stats for any telegramId', function () {
    $telegramId = 123456;
    $expectedDto = new SteamStatsDto(
        nickname: 'Gaben',
        gamesCount: 542,
        hoursTotal: 14837,
        topGame: 'TF2',
        steamLevel: 50,
        friendCount: 100,
        personState: 'online',
        communityVisibility: 'public',
        accountCreated: 1104537600,
    );

    // Мок провайдера
    $provider = Mockery::mock(SteamStatsProvider::class);
    $provider->shouldReceive('getStats')
        ->once()
        ->with('76561197960287930') // фиксированный Steam ID из контроллера
        ->andReturn($expectedDto);

    $this->app->instance(SteamStatsProvider::class, $provider);

    $response = $this->getJson("/stats/{$telegramId}");

    $response->assertStatus(200)
        ->assertJson([
            'nickname' => 'Gaben',
            'gamesCount' => 542,
            'hoursTotal' => 14837,
            'topGame' => 'TF2',
            'steamLevel' => 50,
            'friendCount' => 100,
            'personState' => 'online',
            'communityVisibility' => 'public',
            'accountCreated' => 1104537600,
        ]);
});

it('returns 404 when steam profile not found', function () {
    $telegramId = 123456;

    $provider = Mockery::mock(SteamStatsProvider::class);
    $provider->shouldReceive('getStats')
        ->once()
        ->andThrow(new \RuntimeException('Steam profile not found'));

    $this->app->instance(SteamStatsProvider::class, $provider);

    $response = $this->getJson("/stats/{$telegramId}");

    $response->assertStatus(404)
        ->assertJson(['error' => 'Steam profile not found']);
});

it('returns 500 on unexpected exception', function () {
    $telegramId = 123456;

    $provider = Mockery::mock(SteamStatsProvider::class);
    $provider->shouldReceive('getStats')
        ->once()
        ->andThrow(new \Exception('Unexpected error'));

    $this->app->instance(SteamStatsProvider::class, $provider);

    $response = $this->getJson("/stats/{$telegramId}");

    $response->assertStatus(500)
        ->assertJson(['error' => 'Internal server error']);
});