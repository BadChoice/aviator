<?php

declare(strict_types=1);

use App\Models\Application;
use App\Models\Keyword;
use App\Models\Ranking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('groups rankings by keyword and shows latest position with a mini chart', function (): void {
    /** @var Application $app */
    $app = Application::query()->create([
        'name' => 'Test App',
        'appstore_id' => '1234567890',
    ]);

    /** @var Keyword $kw */
    $kw = $app->keywords()->create([
        'name' => 'space invaders',
    ]);

    // Create multiple daily rankings for the same keyword (US)
    Ranking::query()->create([
        'date' => now()->subDays(2)->toDateString(),
        'country' => 'US',
        'keyword_id' => $kw->id,
        'subject_type' => Application::class,
        'subject_id' => $app->id,
        'position' => 12,
    ]);

    Ranking::query()->create([
        'date' => now()->subDay()->toDateString(),
        'country' => 'US',
        'keyword_id' => $kw->id,
        'subject_type' => Application::class,
        'subject_id' => $app->id,
        'position' => 8,
    ]);

    Ranking::query()->create([
        'date' => now()->toDateString(),
        'country' => 'US',
        'keyword_id' => $kw->id,
        'subject_type' => Application::class,
        'subject_id' => $app->id,
        'position' => 5,
    ]);

    // Another country to ensure grouping by country too
    Ranking::query()->create([
        'date' => now()->toDateString(),
        'country' => 'GB',
        'keyword_id' => $kw->id,
        'subject_type' => Application::class,
        'subject_id' => $app->id,
        'position' => 7,
    ]);

    // Authenticate because the layout references auth()->user()
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('applications.show', $app));

    $response->assertSuccessful();

    // Shows keyword badge with country
    $response->assertSee('space invaders (US)', escape: false);
    $response->assertSee('space invaders (GB)', escape: false);

    // Latest position for US should be #5
    $response->assertSee('#5');

    // Canvas element for mini chart exists
    $response->assertSee('canvas', escape: false);
});
