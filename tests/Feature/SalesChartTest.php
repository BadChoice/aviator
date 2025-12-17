<?php

declare(strict_types=1);

use App\Models\User;

it('renders the Chart.js canvas on the sales index page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('sales.index'))
        ->assertOk()
        ->assertSee('Daily Sales', escape: false)
        ->assertSee('id="dailySalesChart"', escape: false);
});
