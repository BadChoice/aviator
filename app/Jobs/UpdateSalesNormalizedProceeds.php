<?php

namespace App\Jobs;

use App\Models\Sale;
use App\Services\CurrencyExchange;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class UpdateSalesNormalizedProceeds implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public ?CarbonInterface $date = null, public bool $onlyMissing = true) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
            $query = Sale::query();

            if ($this->date) {
                $query->whereDate('begin_date', $this->date->toDateString());
            }

            if ($this->onlyMissing) {
                $query->whereNull('normalized_proceeds');
            }

            $fx = new CurrencyExchange();

            $query->chunkById(500, function ($chunk) use ($fx) {
                foreach ($chunk as $sale) {
                    $this->normalizeSale($sale, $fx);
                }
            });
    }

    private function normalizeSale(Sale $sale, CurrencyExchange $fx): void {
        try {
            $currency = (string) ($sale->currency_of_proceeds ?? 'USD');
            $amount = (float) $sale->developer_proceeds;
            $eur = ($currency === 'EUR') ? $amount : $fx->convertToEur(amount:$amount, currency: $currency, date:$sale->begin_date ?? now());

            $sale->update([
                'normalized_proceeds' => round($eur * $sale->units, 2)
            ]);
        } catch (\Throwable $e) {
            //echo $e->getMessage();
            Log::warning('Failed to normalize proceeds for sale', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
