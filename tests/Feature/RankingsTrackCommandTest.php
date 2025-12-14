<?php

declare(strict_types=1);

use App\Models\Application;
use App\Models\Competitor;
use App\Models\Keyword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('tracks rankings and ratings for applications and competitors', function (): void {
    // Arrange: create an app with a keyword and a competitor
    /** @var Application $app */
    $app = Application::query()->create([
        'name' => 'Terminal Madness',
        'appstore_id' => '1555737702',
    ]);

    /** @var Keyword $keyword */
    $keyword = $app->keywords()->create([
        'name' => 'lucasarts',
    ]);

    /** @var Competitor $competitor */
    $competitor = $app->competitors()->create([
        'name' => 'Competitor App',
        'appstore_id' => '6443849085',
    ]);

    // Fake App Store API response for the keyword search
    Http::fake([
        'itunes.apple.com/search*' => Http::response([
            'resultCount' => 3,
            'results' => [
                [
                    'trackId' => (int) $app->appstore_id,
                    'trackName' => $app->name,
                    'averageUserRating' => 4.6,
                    'userRatingCount' => 1234,
                ],
                [
                    'trackId' => 1111111111,
                    'trackName' => 'Other App',
                    'averageUserRating' => 3.2,
                    'userRatingCount' => 87,
                ],
                [
                    'trackId' => (int) $competitor->appstore_id,
                    'trackName' => $competitor->name,
                    'averageUserRating' => 4.9,
                    'userRatingCount' => 999,
                ],
            ],
        ], 200),
    ]);

    // Act: run the command
    $this->artisan('rankings:track-daily')
        ->assertSuccessful();

    // Assert: ranking records exist for app and competitor for today
    $this->assertDatabaseHas('rankings', [
        'keyword_id' => $keyword->id,
        'subject_type' => Application::class,
        'subject_id' => $app->id,
        'position' => 1, // zero-based 0 becomes human 1
        'average_rating' => 4.6,
        'rating_count' => 1234,
    ]);

    $this->assertDatabaseHas('rankings', [
        'keyword_id' => $keyword->id,
        'subject_type' => get_class($competitor),
        'subject_id' => $competitor->id,
        'position' => 3, // competitor is 3rd in array -> human 3
        'average_rating' => 4.9,
        'rating_count' => 999,
    ]);
});
