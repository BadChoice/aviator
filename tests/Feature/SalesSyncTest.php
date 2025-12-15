<?php

declare(strict_types=1);

use App\Jobs\SyncAppStoreSales;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores sales rows into the database', function () {
    $rows = [[
        'Provider' => 'APPLE',
        'Provider Country' => 'US',
        'SKU' => 'io.codepassion.tmbundle',
        'Developer' => 'Codepassion S.L.',
        'Title' => 'Terminal Madness',
        'Version' => '1.0',
        'Product Type Identifier' => '1-B',
        'Units' => '1',
        'Developer Proceeds' => '4.20',
        'Begin Date' => '12/14/2025',
        'End Date' => '12/14/2025',
        'Customer Currency' => 'EUR',
        'Country Code' => 'ES',
        'Currency of Proceeds' => 'EUR',
        'Apple Identifier' => '1860839356',
        'Customer Price' => '14.03',
        'Promo Code' => ' ',
        'Parent Identifier' => ' ',
        'Subscription' => ' ',
        'Period' => ' ',
        'Category' => 'Games',
        'CMB' => 'CMB',
        'Device' => 'iPhone',
        'Supported Platforms' => 'iOS',
        'Proceeds Reason' => ' ',
        'Preserved Pricing' => ' ',
        'Client' => ' ',
        'Order Type' => ' ',
    ]];

    // Run the job synchronously with preset rows to avoid HTTP
    (new SyncAppStoreSales(presetRows: $rows))->handle();

    expect(Sale::count())->toBe(1);

    $sale = Sale::firstOrFail();
    expect($sale->sku)->toBe('io.codepassion.tmbundle')
        ->and($sale->title)->toBe('Terminal Madness')
        ->and($sale->developer_proceeds)->toEqual(4.20)
        ->and($sale->units)->toBe(1)
        ->and($sale->country_code)->toBe('ES')
        ->and($sale->customer_currency)->toBe('EUR')
        ->and($sale->begin_date->toDateString())->toBe('2025-12-14');
});

it('is idempotent by row_hash', function () {
    $rows = [[
        'Provider' => 'APPLE',
        'Provider Country' => 'US',
        'SKU' => 'io.codepassion.tmbundle',
        'Developer' => 'Codepassion S.L.',
        'Title' => 'Terminal Madness',
        'Version' => '1.0',
        'Product Type Identifier' => '1-B',
        'Units' => '1',
        'Developer Proceeds' => '4.20',
        'Begin Date' => '12/14/2025',
        'End Date' => '12/14/2025',
        'Customer Currency' => 'EUR',
        'Country Code' => 'ES',
        'Currency of Proceeds' => 'EUR',
        'Apple Identifier' => '1860839356',
        'Customer Price' => '14.03',
        'Promo Code' => ' ',
        'Parent Identifier' => ' ',
        'Subscription' => ' ',
        'Period' => ' ',
        'Category' => 'Games',
        'CMB' => 'CMB',
        'Device' => 'iPhone',
        'Supported Platforms' => 'iOS',
        'Proceeds Reason' => ' ',
        'Preserved Pricing' => ' ',
        'Client' => ' ',
        'Order Type' => ' ',
    ]];

    (new SyncAppStoreSales(presetRows: $rows))->handle();
    (new SyncAppStoreSales(presetRows: $rows))->handle();

    expect(Sale::count())->toBe(1);
});
