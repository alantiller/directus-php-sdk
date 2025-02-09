<?php

// tests/Unit/DirectusTest.php

use AlanTiller\DirectusSdk\Auth\ApiKeyAuth;
use AlanTiller\DirectusSdk\Directus;
use AlanTiller\DirectusSdk\Endpoints\ItemsEndpoint;
use AlanTiller\DirectusSdk\Storage\SessionStorage;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;

// Helper function to create mock responses
function createMockResponse(int $status, array $data = [], array $headers = []): Response
{
    return new Response($status, $headers, json_encode(['data' => $data], JSON_THROW_ON_ERROR));
}

// Helper function to create a Directus instance with a mock client
function createMockDirectus(array $responses): Directus
{
    $mock = new MockHandler($responses);
    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $storage = new SessionStorage('test_');
    $directus = new Directus(
        'https://example.directus.com',
        $storage,
        null,
        true
    );
    $reflection = new \ReflectionClass($directus);
    $clientProperty = $reflection->getProperty('client');
    $clientProperty->setAccessible(true);
    $clientProperty->setValue($directus, $client);

    return $directus;
}

it('can create a Directus instance', function () {
    $storage = new SessionStorage('test_');
    $directus = new Directus(
        'https://example.directus.com',
        $storage,
        null,
        true
    );
    expect($directus)->toBeInstanceOf(Directus::class);
});

it('can set and get the base URL', function () {
    $storage = new SessionStorage('test_');
    $directus = new Directus(
        'https://example.directus.com',
        $storage,
        null,
        true
    );
    expect($directus->getBaseUrl())->toBe('https://example.directus.com');
});

it('can get the Guzzle client', function () {
    $storage = new SessionStorage('test_');
    $directus = new Directus(
        'https://example.directus.com',
        $storage,
        null,
        true
    );
    expect($directus->getClient())->toBeInstanceOf(Client::class);
});

it('can create an ItemsEndpoint instance', function () {
    $storage = new SessionStorage('test_');
    $directus = new Directus(
        'https://example.directus.com',
        $storage,
        null,
        true
    );
    $items = $directus->items('test_collection');
    expect($items)->toBeInstanceOf(ItemsEndpoint::class);
});

it('can get items from a collection', function () {
    $responses = [
        createMockResponse(200, [['id' => 1, 'name' => 'Test Item']]),
    ];
    $directus = createMockDirectus($responses);

    $items = $directus->items('test_collection')->get();

    expect($items['data'])->toBeArray();
    expect($items['data'][0]['id'])->toBe(1);
    expect($items['data'][0]['name'])->toBe('Test Item');
});

it('can create items in a collection', function () {
    $responses = [
        createMockResponse(200, ['id' => 2, 'name' => 'New Item']),
    ];
    $directus = createMockDirectus($responses);

    $items = $directus->items('test_collection')->create(['name' => 'New Item']);

    expect($items['data'])->toBeArray();
    expect($items['data']['id'])->toBe(2);
    expect($items['data']['name'])->toBe('New Item');
});

it('can update items in a collection', function () {
    $responses = [
        createMockResponse(200, ['id' => 1, 'name' => 'Updated Item']),
    ];
    $directus = createMockDirectus($responses);

    $items = $directus->items('test_collection')->update(['name' => 'Updated Item'], 1);

    expect($items['data'])->toBeArray();
    expect($items['data']['id'])->toBe(1);
    expect($items['data']['name'])->toBe('Updated Item');
});

it('can delete items from a collection', function () {
    $responses = [
        new Response(204, [], null), // 204 No Content for successful deletion
    ];
    $directus = createMockDirectus($responses);

    $items = $directus->items('test_collection')->delete(1);

    expect($items['status'])->toBe(204);
});

it('can authenticate with an API key', function () {
    $requestUri = '';
    $requestHeaders = [];

    $mock = new MockHandler([
        new Response(200, [], json_encode(['data' => []])),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $storage = new SessionStorage('test_');
    $auth = new ApiKeyAuth('test_api_key');
    $directus = new Directus(
        'https://example.directus.com',
        $storage,
        $auth,
        true
    );

    $reflection = new \ReflectionClass($directus);
    $clientProperty = $reflection->getProperty('client');
    $clientProperty->setAccessible(true);
    $clientProperty->setValue($directus, $client);

    $handlerStack->push(function (callable $handler) use (&$requestUri, &$requestHeaders) {
        return function (Request $request, array $options) use ($handler, &$requestUri, &$requestHeaders) {
            $requestUri = (string) $request->getUri();
            $requestHeaders = $request->getHeaders();
            return $handler($request, $options);
        };
    });

    $directus->items('test_collection')->get();

    expect($requestHeaders['Authorization'][0])->toBe('Bearer test_api_key');
    expect($requestUri)->toBe('https://example.directus.com/items/test_collection');
});