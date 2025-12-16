<?php

declare(strict_types=1);

use App\Models\Application;
use App\Models\Sale;
use App\Repositories\SalesRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('builds revenue series for last days for an application', function (): void {
    $app = Application::query()->create([
        'name' => 'Test App',
        'icon' => null,
        'appstore_id' => '1111111111',
    ]);

    // Create sales on three consecutive days
    Sale::factory()->create([
        'apple_identifier' => '1111111111',
        'begin_date' => Carbon::today()->subDays(2),
        'developer_proceeds' => 5.00,
    ]);
    Sale::factory()->create([
        'apple_identifier' => '1111111111',
        'begin_date' => Carbon::today()->subDay(),
        'developer_proceeds' => 12.30,
    ]);
    Sale::factory()->create([
        'apple_identifier' => '1111111111',
        'begin_date' => Carbon::today(),
        'developer_proceeds' => 0.70,
    ]);

    $repo = app(SalesRepository::class);

    $series = $repo->revenueSeriesForApplication($app, 3);

    expect($series)->toHaveCount(3);

    $dates = $series->pluck('date');
    expect($dates->first())->toBe(Carbon::today()->subDays(2)->toDateString());
    expect($dates->last())->toBe(Carbon::today()->toDateString());

    $values = $series->pluck('value');
    expect($values->all())->toBe([5.00, 12.30, 0.70]);
});

it('fills missing days with zero revenue', function (): void {
    $app = Application::query()->create([
        'name' => 'Test App',
        'icon' => null,
        'appstore_id' => '2222222222',
    ]);

    // Only one day has sales
    Sale::factory()->create([
        'apple_identifier' => '2222222222',
        'begin_date' => Carbon::today()->subDay(),
        'developer_proceeds' => 9.99,
    ]);

    $repo = app(SalesRepository::class);
    $series = $repo->revenueSeriesForApplication($app, 3);

    expect($series)->toHaveCount(3);
    $map = $series->keyBy('date')->map(fn ($r) => $r['value']);

    expect($map[Carbon::today()->subDays(2)->toDateString()])->toBe(0.0);
    expect($map[Carbon::today()->subDay()->toDateString()])->toBe(9.99);
    expect($map[Carbon::today()->toDateString()])->toBe(0.0);
});
