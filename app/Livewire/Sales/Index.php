<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use Livewire\Component;

class Index extends Component
{
    /**
     * Number of days to include in the chart window.
     */
    public int $days = 30;

    public function render()
    {
        $salesModels = Sale::query()->latest('begin_date')->limit(500)->get();

        // Map to the same array shape used in the Blade for minimal UI change
        $sales = $salesModels->map(function (Sale $s) {
            $usd = $s->normalized_proceeds ?? $s->developer_proceeds;
            return [
                'Begin Date' => optional($s->begin_date)->format('m/d/Y'),
                'End Date' => optional($s->end_date)->format('m/d/Y'),
                'Title' => $s->title,
                'SKU' => $s->sku,
                'Version' => $s->version,
                'Device' => $s->device,
                'Product Type Identifier' => $s->product_type_identifier,
                'Units' => $s->units,
                // Show normalized USD proceeds on UI
                'Developer Proceeds' => (string) $usd,
                'Currency of Proceeds' => 'USD',
                'Customer Price' => (string) $s->customer_price,
                'Customer Currency' => $s->customer_currency,
            ];
        })->all();

        $summary = collect($sales)
            ->groupBy('SKU')
            ->map(fn ($rows) => collect($rows)->sum(function ($sale) {
                return (float) $sale['Units'] * (float) $sale['Developer Proceeds'];
            }));

        // Build daily stacked data per app (by Title) for the last N days
        $startDate = now()->subDays($this->days - 1)->startOfDay();
        $endDate = now()->endOfDay();

        $raw = Sale::query()
            ->whereNotNull('begin_date')
            ->whereBetween('begin_date', [$startDate, $endDate])
            ->selectRaw('DATE(begin_date) as day, title, SUM(units * COALESCE(normalized_proceeds, developer_proceeds)) as revenue')
            ->groupBy('day', 'title')
            ->get();

        // Determine the top apps by total revenue in the period (limit to 6 for readability)
        $topApps = $raw
            ->groupBy('title')
            ->map(fn ($rows) => $rows->sum('revenue'))
            ->sortDesc()
            ->take(6)
            ->keys()
            ->values();

        // Build a continuous series of dates
        $dates = collect();
        for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
            $dates->push($d->toDateString());
        }

        // Map data per date with segments for each top app
        $dailyStacked = $dates->map(function (string $date) use ($raw, $topApps) {
            $segments = $topApps->map(function ($app) use ($raw, $date) {
                $match = $raw->first(fn ($r) => $r->day === $date && $r->title === $app);
                $value = (float) ($match->revenue ?? 0);

                return [
                    'app' => $app,
                    'value' => round($value, 2),
                ];
            })->all();

            $total = collect($segments)->sum('value');

            return [
                'date' => $date,
                'segments' => $segments,
                'total' => round($total, 2),
            ];
        });

        $maxTotal = max(1, (int) ceil($dailyStacked->max('total') ?? 1));

        return view('livewire.sales.index', [
            'sales' => $sales,
            'summary' => $summary,
            'dailyStacked' => $dailyStacked,
            'maxTotal' => $maxTotal,
            'topApps' => $topApps,
            'daysWindow' => $this->days,
        ]);
    }
}
