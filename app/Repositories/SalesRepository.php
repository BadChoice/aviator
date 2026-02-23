<?php

namespace App\Repositories;

use App\Models\Application;
use App\Models\Sale;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesRepository
{
    private function proceedsSqlExpression(): string
    {
        return 'COALESCE(normalized_proceeds, developer_proceeds * units)';
    }

    public function effectiveProceedsForSale(Sale $sale): float
    {
        if ($sale->normalized_proceeds !== null) {
            return round((float) $sale->normalized_proceeds, 2);
        }

        return round((float) $sale->developer_proceeds * (int) $sale->units, 2);
    }

    public function recentSales(int $limit = 500): EloquentCollection
    {
        return Sale::query()->latest('begin_date')->limit($limit)->get();
    }

    public function summaryBySku(EloquentCollection $sales): Collection
    {
        return $sales
            ->groupBy('sku')
            ->map(function (Collection $rows): float {
                return round($rows->sum(fn (Sale $sale) => $this->effectiveProceedsForSale($sale)), 2);
            });
    }

    public function dailySummary(CarbonInterface $from): EloquentCollection
    {
        $expr = $this->proceedsSqlExpression();

        return Sale::where('begin_date', '>', $from)
            ->groupBy('sku')
            ->groupBy('begin_date')
            ->whereRaw("$expr <> 0")
            ->select('sku', 'begin_date', DB::raw("SUM($expr) as normalized_proceeds"))
            ->get();
    }

    /**
     * Build a day-by-day revenue series for an application for the last N days.
     *
     * @return Collection<int, array{date:string,value:float}>
     */
    public function revenueSeriesForApplication(Application $application, int $days = 14): Collection
    {
        $days = max(1, $days);
        $expr = $this->proceedsSqlExpression();

        $end = Carbon::today();
        $start = $end->copy()->subDays($days - 1);

        /** @var Collection<string, float> $totals */
        $totals = Sale::query()
            ->where('apple_identifier', $application->appstore_id)
            ->whereBetween('begin_date', [$start, $end])
            ->selectRaw("DATE(begin_date) as day, SUM($expr) as revenue")
            ->groupBy('day')
            ->get()
            ->pluck('revenue', 'day')
            ->map(fn ($value) => round((float) $value, 2));

        $period = CarbonPeriod::create($start, $end);

        /** @var Collection<int, array{date:string,value:float}> $series */
        $series = collect($period)->map(function (Carbon $date) use ($totals) {
            $key = $date->toDateString();

            return [
                'date' => $key,
                'value' => (float) ($totals[$key] ?? 0.0),
            ];
        });

        return $series;
    }

    /**
     * @return array{
     *   dailyStacked: Collection<int, array{
     *     date: string,
     *     segments: array<int, array{app:string,value:float}>,
     *     total: float
     *   }>,
     *   topApps: Collection<int, string>,
     *   maxTotal: int
     * }
     */
    public function dailyStackedRevenueByTitle(int $days = 30, int $topAppsLimit = 6): array
    {
        $days = max(1, $days);
        $topAppsLimit = max(1, $topAppsLimit);
        $expr = $this->proceedsSqlExpression();

        $startDate = now()->subDays($days - 1)->startOfDay();
        $endDate = now()->endOfDay();

        $raw = Sale::query()
            ->whereNotNull('begin_date')
            ->whereBetween('begin_date', [$startDate, $endDate])
            ->selectRaw("DATE(begin_date) as day, title, SUM($expr) as revenue")
            ->groupBy('day', 'title')
            ->get();

        $topApps = $raw
            ->groupBy('title')
            ->map(fn (Collection $rows) => (float) $rows->sum('revenue'))
            ->sortDesc()
            ->take($topAppsLimit)
            ->keys()
            ->values();

        $dates = collect();
        for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
            $dates->push($d->toDateString());
        }

        $dailyStacked = $dates->map(function (string $date) use ($raw, $topApps) {
            $segments = $topApps->map(function ($app) use ($raw, $date): array {
                $match = $raw->first(fn ($r) => $r->day === $date && $r->title === $app);
                $value = round((float) ($match->revenue ?? 0), 2);

                return [
                    'app' => $app,
                    'value' => $value,
                ];
            })->all();

            return [
                'date' => $date,
                'segments' => $segments,
                'total' => round(collect($segments)->sum('value'), 2),
            ];
        });

        return [
            'dailyStacked' => $dailyStacked,
            'topApps' => $topApps,
            'maxTotal' => max(1, (int) ceil($dailyStacked->max('total') ?? 1)),
        ];
    }
}
