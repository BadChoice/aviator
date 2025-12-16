<?php

namespace App\Livewire\Applications;

use App\Models\Application;
use App\Repositories\RankingRepository;
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
        /** @var Collection<int, array{key:string, keyword_id:int, country:string, keyword_name:string, used:mixed, labels:array<int,string>, data:array<int,?int>, latest_position:?int}> $groups */
        $groups = app(RankingRepository::class)
            ->groupedSeriesForApplication($this->application);

        return view('livewire.applications.show', [
            'application' => $this->application,
            'groups' => $groups,
        ]);
    }
}
