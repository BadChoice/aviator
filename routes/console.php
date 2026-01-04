<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('sales:sync')->dailyAt('08:15')->timezone('America/Los_Angeles');
Schedule::command('rankings:track-daily')->dailyAt('08:25')->timezone('America/Los_Angeles');
Schedule::command('stats:remind')->dailyAt('08:30')->timezone('America/Los_Angeles');
