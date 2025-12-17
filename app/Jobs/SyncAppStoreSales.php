<?php

namespace App\Jobs;

use App\Models\Sale;
use App\Services\AppStore\AppStoreConnect;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SyncAppStoreSales implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public ?CarbonInterface $date = null, public ?array $presetRows = null) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (is_array($this->presetRows)) {
            $rows = $this->presetRows;
        } else {
            $connect = AppStoreConnect::make();

            $vendorId = (string) config('services.app_store_connect.vendor_id');
            $date = $this->date ? CarbonImmutable::parse($this->date) : now()->subDay(2)->startOfDay();

            $rows = $connect->salesReports(vendorId: $vendorId, date: $date);
        }

        // Upsert rows
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $mapped = $this->mapRow($row);

                Sale::updateOrCreate(
                    ['row_hash' => $mapped['row_hash']],
                    $mapped
                );
            }
        });
    }

    /**
     * @param  array<string,mixed>  $row
     * @return array<string,mixed>
     */
    protected function mapRow(array $row): array
    {
        // Normalize dates from m/d/Y to Y-m-d
        $begin = $this->normalizeDate(Arr::get($row, 'Begin Date'));
        $end = $this->normalizeDate(Arr::get($row, 'End Date'));

        $hashSource = implode('|', [
            Arr::get($row, 'Begin Date'),
            Arr::get($row, 'End Date'),
            Arr::get($row, 'SKU'),
            Arr::get($row, 'Product Type Identifier'),
            Arr::get($row, 'Country Code'),
            Arr::get($row, 'Customer Currency'),
            Arr::get($row, 'Developer Proceeds'),
            Arr::get($row, 'Units'),
        ]);

        return [
            'provider' => (string) Arr::get($row, 'Provider'),
            'provider_country' => Arr::get($row, 'Provider Country'),
            'sku' => (string) Arr::get($row, 'SKU'),
            'developer' => Arr::get($row, 'Developer'),
            'title' => Arr::get($row, 'Title'),
            'version' => Arr::get($row, 'Version'),
            'product_type_identifier' => Arr::get($row, 'Product Type Identifier'),
            'units' => (int) Arr::get($row, 'Units', 0),
            'developer_proceeds' => (float) Arr::get($row, 'Developer Proceeds', 0),
            'begin_date' => $begin,
            'end_date' => $end,
            'customer_currency' => Arr::get($row, 'Customer Currency'),
            'country_code' => Arr::get($row, 'Country Code'),
            'currency_of_proceeds' => Arr::get($row, 'Currency of Proceeds'),
            'apple_identifier' => Arr::get($row, 'Apple Identifier'),
            'customer_price' => (float) Arr::get($row, 'Customer Price'),
            'promo_code' => Arr::get($row, 'Promo Code') ?: null,
            'parent_identifier' => Arr::get($row, 'Parent Identifier') ?: null,
            'subscription' => Arr::get($row, 'Subscription') ?: null,
            'period' => Arr::get($row, 'Period') ?: null,
            'category' => Arr::get($row, 'Category') ?: null,
            'cmb' => Arr::get($row, 'CMB') ?: null,
            'device' => Arr::get($row, 'Device') ?: null,
            'supported_platforms' => Arr::get($row, 'Supported Platforms') ?: null,
            'proceeds_reason' => Arr::get($row, 'Proceeds Reason') ?: null,
            'preserved_pricing' => Arr::get($row, 'Preserved Pricing') ?: null,
            'client' => Arr::get($row, 'Client') ?: null,
            'order_type' => Arr::get($row, 'Order Type') ?: null,
            'row_hash' => hash('sha256', $hashSource),
            'raw' => $row,
        ];
    }

    protected function normalizeDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        // Common App Store format is m/d/Y
        try {
            return CarbonImmutable::createFromFormat('m/d/Y', $value)->toDateString();
        } catch (\Throwable) {
            try {
                return CarbonImmutable::parse($value)->toDateString();
            } catch (\Throwable) {
                return null;
            }
        }
    }
}
