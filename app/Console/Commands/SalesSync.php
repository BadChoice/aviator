<?php

namespace App\Console\Commands;

use App\Jobs\SyncAppStoreSales;
use Illuminate\Console\Command;

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
        $date = $dateOption ? Carbon\CarbonImmutable::parse((string) $dateOption) : null;

        dispatch(new SyncAppStoreSales($date));

        $this->components->info('Sales sync dispatched'.($date ? ' for '.$date->toDateString() : ''));

        return self::SUCCESS;
    }
}
