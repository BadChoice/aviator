<?php

namespace App\Livewire\Dashboard\Applications;

use App\Models\Application;
use App\Repositories\RankingRepository;
use App\Repositories\SalesRepository;
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
        /** @var Collection<int, array{keyword:string,country:?string,position:?int,date:?string}> $latestRankings */
        $latestRankings = app(RankingRepository::class)
            ->latestPerKeywordForApplication($this->application);

        // Revenue series for the last 14 days
        $revenueSeries = app(SalesRepository::class)
            ->revenueSeriesForApplication($this->application, 14);

        return view('livewire.dashboard.applications.show', [
            'application' => $this->application,
            'latestRankings' => $latestRankings,
            'revenueSeries' => $revenueSeries,
        ]);
    }
}
