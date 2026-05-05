<?php

uses(Tests\TestCase::class);

use App\Services\AuthServiceClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    $mock = new MockHandler;
    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $this->service = new AuthServiceClient($client, 'http://auth.local');
    $this->mockHandler = $mock;
});

it('returns steamId on successful response', function () {
    $this->mockHandler->append(new Response(200, [], json_encode(['steamId' => '123456789'])));

    $result = $this->service->getSteamId(111);
    expect($result)->toBe('123456789');
});

it('throws exception on unavailable service (500 or connection error)', function () {
    $this->mockHandler->append(new Response(500, [], 'Internal Error'));

    expect(fn () => $this->service->getSteamId(111))
        ->toThrow(RuntimeException::class, 'Auth service unavailable');
});
