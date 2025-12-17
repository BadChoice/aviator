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

            $query->orderBy('id')->chunkById(500, function ($chunk) use ($fx) {
                /** @var Sale $sale */
                foreach ($chunk as $sale) {
                    try {
                        $currency = (string) ($sale->currency_of_proceeds ?? 'USD');
                        $date = $sale->begin_date ?? now();

                        $amount = (float) $sale->developer_proceeds;
                        if ($currency !== 'USD') {
                            $usd = $fx->convertToUsd($amount, $currency, $date);
                        } else {
                            $usd = $amount;
                        }

                        $sale->normalized_proceeds = round($usd, 2);
                        $sale->save();
                    } catch (\Throwable $e) {
                        Log::warning('Failed to normalize proceeds for sale', [
                            'sale_id' => $sale->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
    }
}
