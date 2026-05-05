<?php

uses(Tests\TestCase::class);

use App\Services\SteamApiService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->mockHandler = new MockHandler;
    $handlerStack = HandlerStack::create($this->mockHandler);
    $this->history = [];
    $history = Middleware::history($this->history);
    $handlerStack->push($history);

    $client = new Client(['handler' => $handlerStack]);

    $this->service = new SteamApiService('test-api-key');
    $reflection = new ReflectionClass($this->service);
    $property = $reflection->getProperty('client');
    $property->setAccessible(true);
    $property->setValue($this->service, $client);
});

it('fetches player summary successfully', function () {
    $steamId = '76561197960287930';
    $responseBody = [
        'response' => [
            'players' => [
                [
                    'steamid' => $steamId,
                    'personaname' => 'Gaben',
                    'personastate' => 1,
                    'communityvisibilitystate' => 3,
                    'timecreated' => 1104537600,
                ],
            ],
        ],
    ];
    $this->mockHandler->append(new Response(200, [], json_encode($responseBody)));

    $result = $this->service->getPlayerSummary($steamId);

    expect($result)->toBe($responseBody['response']['players'][0]);
    expect($this->history)->toHaveCount(1);
    $request = $this->history[0]['request'];
    expect((string) $request->getUri())->toContain('ISteamUser/GetPlayerSummaries/v0002/');
    expect($request->getUri()->getQuery())->toContain("steamids={$steamId}");
});

it('returns null on player summary failure', function () {
    $steamId = '76561197960287930';
    $this->mockHandler->append(new Response(500, [], 'Internal Error'));

    Log::shouldReceive('error')
        ->once()
        ->with(Mockery::pattern('/Steam API error/'))
        ->andReturn(null);

    $result = $this->service->getPlayerSummary($steamId);

    expect($result)->toBeNull();
});

it('fetches owned games successfully', function () {
    $steamId = '76561197960287930';
    $responseBody = [
        'response' => [
            'game_count' => 2,
            'games' => [
                ['appid' => 440, 'name' => 'Team Fortress 2', 'playtime_forever' => 3600],
                ['appid' => 730, 'name' => 'Counter-Strike', 'playtime_forever' => 7200],
            ],
        ],
    ];
    $this->mockHandler->append(new Response(200, [], json_encode($responseBody)));

    $result = $this->service->getOwnedGames($steamId);

    expect($result)->toBe($responseBody['response']);
    expect($this->history)->toHaveCount(1);
    $request = $this->history[0]['request'];
    expect((string) $request->getUri())->toContain('IPlayerService/GetOwnedGames/v0001/');
});

it('returns null on owned games failure', function () {
    $steamId = '76561197960287930';
    $this->mockHandler->append(new Response(500, []));

    Log::shouldReceive('error')
        ->once()
        ->with(Mockery::pattern('/Steam API error/'))
        ->andReturn(null);

    $result = $this->service->getOwnedGames($steamId);

    expect($result)->toBeNull();
});

it('fetches steam level successfully', function () {
    $steamId = '76561197960287930';
    $responseBody = ['response' => ['player_level' => 42]];
    $this->mockHandler->append(new Response(200, [], json_encode($responseBody)));

    $result = $this->service->getSteamLevel($steamId);

    expect($result)->toBe(42);
});

it('returns null on steam level failure', function () {
    $steamId = '76561197960287930';
    $this->mockHandler->append(new Response(500, []));

    Log::shouldReceive('error')
        ->once()
        ->with(Mockery::pattern('/Steam API error/'))
        ->andReturn(null);

    $result = $this->service->getSteamLevel($steamId);

    expect($result)->toBeNull();
});

it('fetches friend count successfully', function () {
    $steamId = '76561197960287930';
    $responseBody = [
        'friendslist' => [
            'friends' => [
                ['steamid' => '123', 'relationship' => 'friend'],
                ['steamid' => '456', 'relationship' => 'friend'],
            ],
        ],
    ];
    $this->mockHandler->append(new Response(200, [], json_encode($responseBody)));

    $result = $this->service->getFriendCount($steamId);

    expect($result)->toBe(2);
});

it('returns 0 when friend list is empty', function () {
    $steamId = '76561197960287930';
    $responseBody = ['friendslist' => ['friends' => []]];
    $this->mockHandler->append(new Response(200, [], json_encode($responseBody)));

    $result = $this->service->getFriendCount($steamId);

    expect($result)->toBe(0);
});

it('returns 0 on friend list failure', function () {
    $steamId = '76561197960287930';
    $this->mockHandler->append(new Response(500, []));

    Log::shouldReceive('error')
        ->once()
        ->with(Mockery::pattern('/Steam API error/'))
        ->andReturn(null);

    $result = $this->service->getFriendCount($steamId);

    expect($result)->toBe(null);
});
