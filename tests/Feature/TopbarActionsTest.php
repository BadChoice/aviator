<?php

declare(strict_types=1);

use App\Jobs\RunRankingsTrackDaily;
use App\Jobs\RunSalesSyncYesterday;
use App\Livewire\Dashboard\TopbarActions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows action buttons on the dashboard top bar', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertSuccessful();
    $response->assertSee('Track rankings');
    $response->assertSee('Sync yesterday sales');
});

it('dispatches jobs when actions are triggered', function (): void {
    Bus::fake();

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(TopbarActions::class)
        ->call('runRankings')
        ->call('runSalesSync')
        ->assertStatus(200);

    Bus::assertDispatched(RunRankingsTrackDaily::class);
    Bus::assertDispatched(RunSalesSyncYesterday::class);
});
