<?php

declare(strict_types=1);

use App\Services\AppStore\AppStoreConnect;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

it('sums proceeds for the given day', function () {
    Http::fake([
        'api.appstoreconnect.apple.com/*' => Http::response([
            'data' => [
                ['proceeds' => 10.5, 'startTime' => '2025-12-15', 'endTime' => '2025-12-15'],
                ['proceeds' => 2.25, 'startTime' => '2025-12-15', 'endTime' => '2025-12-15'],
            ],
        ], 200),
    ]);

    $svc = new AppStoreConnect(
        issuerId: 'ISS',
        keyId: 'KEY',
        privateKeyPem: 'PEM',
        tokenFactory: fn () => 'token'
    );

    $sum = $svc->revenueFor('123456789', new DateTimeImmutable('2025-12-15'));

    expect($sum)->toBe(12.75);
});

it('returns zero when there is no data', function () {
    Http::fake([
        'api.appstoreconnect.apple.com/*' => Http::response([
            'data' => [],
        ], 200),
    ]);

    $svc = new AppStoreConnect(
        issuerId: 'ISS',
        keyId: 'KEY',
        privateKeyPem: 'PEM',
        tokenFactory: fn () => 'token'
    );

    $sum = $svc->revenueFor('123456789', new DateTimeImmutable('2025-12-15'));

    expect($sum)->toBe(0.0);
});
