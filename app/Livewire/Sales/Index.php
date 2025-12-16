<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $salesModels = Sale::query()->latest('begin_date')->limit(500)->get();

        // Map to the same array shape used in the Blade for minimal UI change
        $sales = $salesModels->map(function (Sale $s) {
            return [
                'Begin Date' => optional($s->begin_date)->format('m/d/Y'),
                'End Date' => optional($s->end_date)->format('m/d/Y'),
                'Title' => $s->title,
                'SKU' => $s->sku,
                'Version' => $s->version,
                'Device' => $s->device,
                'Product Type Identifier' => $s->product_type_identifier,
                'Units' => $s->units,
                'Developer Proceeds' => (string) $s->developer_proceeds,
                'Currency of Proceeds' => $s->currency_of_proceeds,
                'Customer Price' => (string) $s->customer_price,
                'Customer Currency' => $s->customer_currency,
            ];
        })->all();

        $summary = collect($sales)->groupBy('SKU')->map(fn ($rows) => collect($rows)->sum(function($sale){
            return $sale['Units'] * $sale['Developer Proceeds'];
        }));

        return view('livewire.sales.index', [
            'sales' => $sales,
            'summary' => $summary,
        ]);
    }
}
