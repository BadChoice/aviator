<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyExchange
{
    public function __construct(public ?string $apiKey = null, public ?string $baseUrl = null) {}

    /**
     * Get the FX rate to convert from the given currency into USD for the given date.
     */
    public function getRateToUsd(string $currency, CarbonInterface $date): float
    {
        $currency = strtoupper(trim($currency));
        if ($currency === 'USD') {
            return 1.0;
        }

        $cacheKey = "fx:rates:{$date->toDateString()}:{$currency}";
        $rates = Cache::remember($cacheKey, now()->addDay(), function () use ($date) {
            return $this->fetchRatesForDate($date);
        });

        // Rates may be based on EUR on free plan; compute cross-rate to USD.
        if (! is_array($rates) || empty($rates)) {
            return 1.0; // Fallback neutral rate if unavailable
        }

        $usd = (float) ($rates['USD'] ?? 0.0);
        $src = (float) ($rates[$currency] ?? 0.0);

        if ($usd <= 0.0 || $src <= 0.0) {
            return 1.0;
        }

        // If rates are quoted as base EUR: 1 EUR = rate[currency]. Then 1 currency = 1/rate[currency] EUR.
        // USD per currency = (USD per EUR) / (currency per EUR) = rates['USD'] / rates[currency]
        return $usd / $src;
    }

    /**
     * Convert an amount in the given currency to USD using the given date's rate.
     */
    public function convertToUsd(float $amount, string $currency, CarbonInterface $date): float
    {
        $rate = $this->getRateToUsd($currency, $date);
        return round($amount * $rate, 4);
    }

    /**
     * Fetch a rates map for a specific date from Fixer and return the raw rates array keyed by currency.
     * On error, returns an empty array. Rates are typically based on EUR for free plans.
     *
     * @return array<string,float>
     */
    protected function fetchRatesForDate(CarbonInterface $date): array
    {
        $apiKey = $this->apiKey ?? (string) config('services.fixer.key');
        $baseUrl = rtrim($this->baseUrl ?? (string) config('services.fixer.base_url'), '/');

        if ($apiKey === '') {
            return [];
        }

        $endpoint = $baseUrl.'/'.($date->isToday() ? 'latest' : $date->toDateString());

        $response = Http::withHeaders([
            'apikey' => $apiKey,
        ])->retry(2, 250)->get($endpoint, [
            // We request both USD and a wildcard; Fixer requires specifying symbols on free tier; however,
            // if not supported, omit symbols to get full map.
        ]);

        if (! $response->successful()) {
            return [];
        }

        $json = $response->json();
        $rates = $json['rates'] ?? [];

        if (! is_array($rates)) {
            return [];
        }

        // Normalize to float values
        $out = [];
        foreach ($rates as $code => $val) {
            $out[(string) $code] = (float) $val;
        }

        return $out;
    }
}
