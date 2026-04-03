<?php

use App\DTO\SteamStatsDto;
use App\Services\SteamApiService;
use App\Services\SteamStatsProvider;

beforeEach(function () {
    $this->steamApi = Mockery::mock(SteamApiService::class);
    $this->provider = new SteamStatsProvider($this->steamApi);
});

afterEach(function () {
    Mockery::close();
});

it('returns stats for public profile', function () {
    $steamId = '76561197960287930';

    $this->steamApi->shouldReceive('getPlayerSummary')
        ->with($steamId)
        ->once()
        ->andReturn([
            'personaname' => 'Gaben',
            'personastate' => 1,
            'communityvisibilitystate' => 3,
            'timecreated' => 1104537600,
        ]);

    $this->steamApi->shouldReceive('getOwnedGames')
        ->with($steamId)
        ->once()
        ->andReturn([
            'games' => [
                ['name' => 'Game1', 'playtime_forever' => 120],
                ['name' => 'Game2', 'playtime_forever' => 240],
            ]
        ]);

    $this->steamApi->shouldReceive('getSteamLevel')
        ->with($steamId)
        ->once()
        ->andReturn(50);

    $this->steamApi->shouldReceive('getFriendCount')
        ->with($steamId)
        ->once()
        ->andReturn(100);

    $result = $this->provider->getStats($steamId);

    expect($result)->toBeInstanceOf(SteamStatsDto::class);
    expect($result->nickname)->toBe('Gaben');
    expect($result->gamesCount)->toBe(2);
    expect($result->hoursTotal)->toBe(6); // (120+240)/60 = 6
    expect($result->topGame)->toBe('Game2');
    expect($result->steamLevel)->toBe(50);
    expect($result->friendCount)->toBe(100);
    expect($result->personState)->toBe('online');
    expect($result->communityVisibility)->toBe('public');
    expect($result->accountCreated)->toBe(1104537600);
});

it('handles no games (empty library)', function () {
    $steamId = '76561197960287930';

    $this->steamApi->shouldReceive('getPlayerSummary')
        ->once()
        ->andReturn(['personaname' => 'Gaben', 'personastate' => 0, 'communityvisibilitystate' => 3]);

    $this->steamApi->shouldReceive('getOwnedGames')
        ->once()
        ->andReturn([]); // or ['games' => []]

    $this->steamApi->shouldReceive('getSteamLevel')->once()->andReturn(10);
    $this->steamApi->shouldReceive('getFriendCount')->once()->andReturn(50);

    $result = $this->provider->getStats($steamId);

    expect($result->gamesCount)->toBe(0);
    expect($result->hoursTotal)->toBe(0);
    expect($result->topGame)->toBe('—');
});

it('handles private profile', function () {
    $steamId = '76561197960287930';

    $this->steamApi->shouldReceive('getPlayerSummary')
        ->with($steamId)
        ->once()
        ->andReturn([
            'personaname' => 'PrivateUser',
            'personastate' => 0,
            'communityvisibilitystate' => 1,
        ]);

    $this->steamApi->shouldReceive('getOwnedGames')
        ->with($steamId)
        ->once()
        ->andReturn(null);

    $this->steamApi->shouldReceive('getSteamLevel')
        ->with($steamId)
        ->once()
        ->andReturn(null);

    $this->steamApi->shouldReceive('getFriendCount')
        ->with($steamId)
        ->once()
        ->andReturn(null);

    $result = $this->provider->getStats($steamId);

    expect($result->gamesCount)->toBe(0);
    expect($result->hoursTotal)->toBe(0);
    expect($result->topGame)->toBe('—');
    expect($result->steamLevel)->toBe(0);
    expect($result->friendCount)->toBe(0);
    expect($result->personState)->toBe('offline');
    expect($result->communityVisibility)->toBe('private');
    expect($result->accountCreated)->toBe(0);
});

it('throws exception when player summary missing', function () {
    $steamId = 'invalid';

    $this->steamApi->shouldReceive('getPlayerSummary')
        ->with($steamId)
        ->once()
        ->andReturn(null);

    expect(fn() => $this->provider->getStats($steamId))
        ->toThrow(\RuntimeException::class, 'Steam profile not found');
});