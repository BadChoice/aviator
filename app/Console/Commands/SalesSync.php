<?php

namespace App\Console\Commands;

use App\Jobs\SyncAppStoreSales;
use App\Jobs\UpdateSalesNormalizedProceeds;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class SalesSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:sync {--date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync App Store Connect daily sales report into the local database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dateOption = $this->option('date');
        $date = $dateOption ? Carbon::parse($dateOption) : null;

        Bus::chain([
            new SyncAppStoreSales($date),
            new UpdateSalesNormalizedProceeds($date, onlyMissing: false),
        ])->dispatch();

        $this->components->info('Sales sync dispatched'.($date ? ' for '.$date->toDateString() : ''));

        return self::SUCCESS;
    }
}
