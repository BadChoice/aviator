<?php

namespace App\Livewire\Dashboard\Applications;

use App\Models\Application;
use App\Models\Ranking;
use App\Models\Sale;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Livewire\Component;

class Show extends Component
{
    public Application $application;

    public function mount(Application $application): void
    {
        $this->application = $application;
    }

    public function render(): \Illuminate\View\View
    {
        // Latest ranking per keyword (for this application), keeping country context and date
        /** @var Collection<int, array{keyword:string,country:string|null,position:int|null,date:?string}> $latestRankings */
        $latestRankings = Ranking::query()
            ->whereMorphedTo('subject', $this->application)
            ->with('keyword')
            ->orderBy('date')
            ->get()
            ->groupBy('keyword_id')
            ->map(function (Collection $items) {
                $last = $items->last();

                return [
                    'keyword' => (string) $last->keyword->name,
                    'country' => $last->country ?? null,
                    'position' => $last->position !== null ? (int) $last->position : null,
                    'date' => $last->date ? (string) $last->date : null,
                ];
            })
            ->values();

        // Revenue series for the last 14 days (sum of developer_proceeds per day)
        $days = 14;
        $end = Carbon::today();
        $start = $end->copy()->subDays($days - 1);

        // Sales are identified by Apple Identifier which should match application's appstore_id
        $sales = Sale::query()
            ->where('apple_identifier', $this->application->appstore_id)
            ->whereBetween('begin_date', [$start, $end])
            ->get()
            ->groupBy(fn (Sale $s) => Carbon::parse($s->begin_date)->toDateString())
            ->map(fn (Collection $rows) => (float) $rows->sum('developer_proceeds'));

        $period = CarbonPeriod::create($start, $end);
        $revenueSeries = collect($period)
            ->map(fn (Carbon $d) => [
                'date' => $d->toDateString(),
                'value' => (float) ($sales[$d->toDateString()] ?? 0.0),
            ]);

        return view('livewire.dashboard.applications.show', [
            'application' => $this->application,
            'latestRankings' => $latestRankings,
            'revenueSeries' => $revenueSeries,
        ]);
    }
}
