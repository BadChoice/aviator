<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyExchange
{
    public function __construct(public ?string $apiKey = null, public ?string $baseUrl = null) {
        $this->apiKey = $apiKey ?? config('services.fixer.key');
        $this->baseUrl = $baseUrl ?? config('services.fixer.base_url');
    }

    /**
     * Get the FX rate to convert from the given currency into USD for the given date.
     */
    public function getRateToEur(string $currency, CarbonInterface $date): float
    {
        $currency = strtoupper(trim($currency));
        if ($currency === 'EUR') {
            return 1.0;
        }

        $cacheKey = "fx:rates:{$date->toDateString()}";
        $rates = Cache::remember($cacheKey, now()->addDay(), function () use ($date) {
            $fetchedRates = $this->fetchRatesForDate($date);
            
            // Si l'API ha funcionat correctament, guardem les taxes com a backup
            if (is_array($fetchedRates) && !empty($fetchedRates)) {
                Cache::put('fx:rates:last_valid', $fetchedRates, now()->addDays(30));
            }
            
            return $fetchedRates;
        });

        // Si no hem pogut obtenir les taxes per a aquesta data, intentem utilitzar l'últim valor vàlid
        if (! is_array($rates) || empty($rates)) {
            $lastValidRates = Cache::get('fx:rates:last_valid');
            
            if (is_array($lastValidRates) && !empty($lastValidRates) && isset($lastValidRates[$currency])) {
                return (float) $lastValidRates[$currency];
            }
            
            return 1.0; // Fallback neutral rate if unavailable
        }

        return (float) ($rates[$currency] ?? 1.0);
    }

    /**
     * Convert an amount in the given currency to USD using the given date's rate.
     */
    public function convertToEur(float $amount, string $currency, CarbonInterface $date): float
    {
        if ($amount == 0) {
            return 0;
        }
        $rate = $this->getRateToEur($currency, $date);
        return round($amount / $rate, 4);
    }

    /**
     * Fetch a rates map for a specific date from Fixer and return the raw rates array keyed by currency.
     * On error, returns an empty array. Rates are typically based on EUR for free plans.
     *
     * @return array<string,float>
     */
    protected function fetchRatesForDate(CarbonInterface $date): array
    {

        if ($this->apiKey === '') {
            return [];
        }

        $endpoint = $this->baseUrl.'/'.($date->isToday() ? 'latest' : $date->toDateString());
        //https://data.fixer.io/api/latest?access_key=2bbd8854c22a8a95b03cfc44f7fa9001&base=EUR

        $response = Http::retry(2, 250)->get($endpoint, [
            'access_key' => $this->apiKey,
            'base' => 'EUR',
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
