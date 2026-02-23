<?php

declare(strict_types=1);

use App\Services\CurrencyExchange;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class);

it('uses month-start rate for all dates in the same month', function (): void {
    Cache::flush();

    $fx = new class extends CurrencyExchange
    {
        /** @var list<string> */
        public array $requestedDates = [];

        protected function fetchRatesForDate(CarbonInterface $date): array
        {
            $this->requestedDates[] = $date->toDateString();

            return ['USD' => 2.0];
        }
    };

    $rateA = $fx->getRateToEurForMonth('USD', Carbon::parse('2026-01-05'));
    $rateB = $fx->getRateToEurForMonth('USD', Carbon::parse('2026-01-28'));

    expect($rateA)->toBe(2.0);
    expect($rateB)->toBe(2.0);
    expect($fx->requestedDates)->toBe(['2026-01-01']);
});

it('converts using the same monthly rate within one month', function (): void {
    Cache::flush();

    $fx = new class extends CurrencyExchange
    {
        protected function fetchRatesForDate(CarbonInterface $date): array
        {
            return ['USD' => 2.0];
        }
    };

    $v1 = $fx->convertToEurUsingMonthlyRate(10, 'USD', Carbon::parse('2026-02-02'));
    $v2 = $fx->convertToEurUsingMonthlyRate(10, 'USD', Carbon::parse('2026-02-25'));

    expect($v1)->toBe(5.0);
    expect($v2)->toBe(5.0);
});

