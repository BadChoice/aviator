<?php

namespace App\Jobs;

use App\Models\Application;
use App\Models\Keyword;
use App\Models\Ranking;
use App\Services\AppStore\AppStoreSearch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class TrackKeywordRankings implements ShouldQueue
{
    use Queueable;

    public AppStoreSearch $search;

    public function __construct(public Application $application, public $country = 'US')
    {
        $this->search = new AppStoreSearch();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->application->keywords as $keyword) {
            // Track for the application itself
            $this->trackSubject(
                subjectType: Application::class,
                subjectId: $this->application->id,
                appStoreId: (string) $this->application->appstore_id,
                keyword: $keyword,
            );

            // Track for each competitor
            /*foreach ($app->competitors as $competitor) {
                $this->trackSubject(
                    subjectType: get_class($competitor),
                    subjectId: $competitor->id,
                    appStoreId: (string) $competitor->appstore_id,
                    keyword: $keyword,
                );
            }*/
        }
    }

    protected function trackSubject(string $subjectType, int $subjectId, string $appStoreId, Keyword $keyword): void
    {
        $info = $this->search->trackInfoFor(application_id: $appStoreId, keyword: $keyword->name, country: $this->country);

        $position = $info['position'];
        // Convert zero-based index to human position (1..n)
        $humanPosition = $position === null ? null : ($position + 1);

        Ranking::updateOrCreate(
            [
                'date' => now()->toDateString(),
                'country' => $this->country,
                'keyword_id' => $keyword->id,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
            ],
            [
                'position' => $humanPosition,
                'average_rating' => $info['average_rating'],
                'rating_count' => $info['rating_count'],
            ]
        );
    }

}
