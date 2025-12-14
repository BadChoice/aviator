<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\Keyword;
use App\Models\Ranking;
use App\Services\AppStore\AppStoreSearch;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class TrackKeywordRankings extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'rankings:track-daily {--country=US}';

    /**
     * The console command description.
     */
    protected $description = 'Tracks App Store keyword ranking positions and ratings for all applications and competitors';

    public function handle(AppStoreSearch $search): int
    {
        $country = strtoupper((string) $this->option('country'));

        $today = CarbonImmutable::today();

        /** @var Collection<int, Application> $apps */
        $apps = Application::query()->with(['keywords', 'competitors'])->get();

        $apps->each(function (Application $app) use ($search, $today, $country) {
            \App\Jobs\TrackKeywordRankings::dispatchSync($app, $country);
        });

        $this->info('Ranking tracking complete.');

        return self::SUCCESS;
    }

}
