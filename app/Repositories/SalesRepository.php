<?php

namespace App\Repositories;

use App\Models\Application;
use App\Models\Sale;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesRepository
{

    public function dailySummary(CarbonInterface $from) : \Illuminate\Database\Eloquent\Collection{
        return Sale::where('begin_date', '>', $from)
            ->groupBy('sku')
            ->groupBy('begin_date')
            ->where('normalized_proceeds', '<>', 0)
            ->select('sku', 'begin_date', DB::raw('sum(normalized_proceeds) as normalized_proceeds'))
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

        $end = Carbon::today();
        $start = $end->copy()->subDays($days - 1);

        /** @var Collection<string, float> $totals */
        $totals = Sale::query()
            ->where('apple_identifier', $application->appstore_id)
            ->whereBetween('begin_date', [$start, $end])
            ->get()
            ->groupBy(fn (Sale $s) => Carbon::parse($s->begin_date)->toDateString())
            ->map(fn (Collection $rows) => (float) $rows->sum('developer_proceeds'));

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
}
