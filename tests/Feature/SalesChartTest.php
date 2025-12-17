<?php

declare(strict_types=1);

use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Carbon;

it('shows stacked daily sales chart on the sales page', function () {
    // Create sample sales for two apps across 3 days
    $days = [2, 1, 0];

    foreach ($days as $delta) {
        $date = Carbon::now()->subDays($delta)->toDateString();

        // App A
        Sale::factory()->create([
            'title' => 'App Alpha',
            'sku' => 'alpha.sku',
            'units' => 2,
            'developer_proceeds' => '5.00',
            'begin_date' => $date,
            'end_date' => $date,
        ]);

        // App B
        Sale::factory()->create([
            'title' => 'App Beta',
            'sku' => 'beta.sku',
            'units' => 1,
            'developer_proceeds' => '10.00',
            'begin_date' => $date,
            'end_date' => $date,
        ]);
    }

    $this->actingAs(User::factory()->create());

    $response = $this->get('/sales');

    $response->assertSuccessful();

    // Chart container
    $response->assertSee('data-testid="daily-sales-stacked"', false);

    // Legend should include app titles
    $response->assertSee('App Alpha');
    $response->assertSee('App Beta');
});
