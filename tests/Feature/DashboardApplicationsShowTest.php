<?php

declare(strict_types=1);

use App\Livewire\Dashboard\Applications\Show as DashboardAppShow;
use App\Models\Application;
use App\Models\Keyword;
use App\Models\Ranking;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows latest ranking per keyword and revenue graph on dashboard application card', function (): void {
    $user = User::factory()->create();

    // Create an application
    $app = Application::query()->create([
        'name' => 'Test App',
        'icon' => 'https://example.com/icon.png',
        'appstore_id' => '1234567890',
    ]);

    // Create keywords
    $kw1 = Keyword::query()->create([
        'application_id' => $app->id,
        'name' => 'flight tracker',
    ]);
    $kw2 = Keyword::query()->create([
        'application_id' => $app->id,
        'name' => 'airport map',
    ]);

    // Create rankings over two days
    Ranking::query()->create([
        'date' => Carbon::today()->subDay(),
        'country' => 'US',
        'keyword_id' => $kw1->id,
        'subject_type' => Application::class,
        'subject_id' => $app->id,
        'position' => 8,
    ]);
    Ranking::query()->create([
        'date' => Carbon::today(),
        'country' => 'US',
        'keyword_id' => $kw1->id,
        'subject_type' => Application::class,
        'subject_id' => $app->id,
        'position' => 5,
    ]);
    Ranking::query()->create([
        'date' => Carbon::today(),
        'country' => 'GB',
        'keyword_id' => $kw2->id,
        'subject_type' => Application::class,
        'subject_id' => $app->id,
        'position' => 12,
    ]);

    // Create sales for three days
    Sale::factory()->create([
        'apple_identifier' => '1234567890',
        'begin_date' => Carbon::today()->subDays(2),
        'developer_proceeds' => 10.50,
    ]);
    Sale::factory()->create([
        'apple_identifier' => '1234567890',
        'begin_date' => Carbon::today()->subDay(),
        'developer_proceeds' => 20.00,
    ]);
    Sale::factory()->create([
        'apple_identifier' => '1234567890',
        'begin_date' => Carbon::today(),
        'developer_proceeds' => 7.25,
    ]);

    $component = Livewire::test(DashboardAppShow::class, ['application' => $app])
        ->assertStatus(200)
        ->assertSee('Test App')
        ->assertSee('flight tracker')
        ->assertSee('#5')
        ->assertSee('US')
        ->assertSee('airport map')
        ->assertSee('#12')
        ->assertSee('GB');

    // Assert revenue graph shows from-first to last date labels somewhere in component
    $component->assertSee(Carbon::today()->toDateString());
});
