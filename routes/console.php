<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('sales:sync')
    ->dailyAt('08:15')
    ->timezone('America/Los_Angeles')
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Scheduled task sales:sync executed successfully');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Scheduled task sales:sync failed');
    });

Schedule::command('rankings:track-daily')
    ->dailyAt('08:25')
    ->timezone('America/Los_Angeles')
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Scheduled task rankings:track-daily executed successfully');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Scheduled task rankings:track-daily failed');
    });
