<?php

namespace App\Livewire\Sales;

use App\Repositories\SalesRepository;
use Livewire\Component;

class Index extends Component
{
    /**
     * Number of days to include in the chart window.
     */
    public int $days = 30;

    public bool $showAll = false;


    public function toggleAll(){
        $this->showAll = !$this->showAll;
    }

    public function render()
    {
        $repository = app(SalesRepository::class);
        $salesModels = $repository->recentSales();

        // Map to the same array shape used in the Blade for minimal UI change
        $sales = $salesModels->map(function ($sale) use ($repository) {
            $proceeds = $repository->effectiveProceedsForSale($sale);

            return [
                'Begin Date' => optional($sale->begin_date)->format('m/d/Y'),
                'End Date' => optional($sale->end_date)->format('m/d/Y'),
                'Title' => $sale->title,
                'SKU' => $sale->sku,
                'Version' => $sale->version,
                'Device' => $sale->device,
                'Product Type Identifier' => $sale->product_type_identifier,
                'Units' => $sale->units,
                'Developer Proceeds' => (string) $proceeds,
                'Currency of Proceeds' => 'USD',
                'Customer Price' => (string) $sale->customer_price,
                'Customer Currency' => $sale->customer_currency,
            ];
        })->all();

        $summary = $repository->summaryBySku($salesModels);
        $standardProceedsTotal = round($salesModels->sum(function ($sale) {
            return (float) $sale->developer_proceeds * (int) $sale->units;
        }), 2);

        $sales = collect($sales);
        if (!$this->showAll){
            $sales = $sales->filter(fn($sale) => $sale['Developer Proceeds'] != 0);
        }

        $stackedRevenue = $repository->dailyStackedRevenueByTitle($this->days, 6);

        return view('livewire.sales.index', [
            'sales' => $sales,
            'summary' => $summary,
            'dailyStacked' => $stackedRevenue['dailyStacked'],
            'maxTotal' => $stackedRevenue['maxTotal'],
            'topApps' => $stackedRevenue['topApps'],
            'daysWindow' => $this->days,
            'standardProceedsTotal' => $standardProceedsTotal,
        ]);
    }
}
