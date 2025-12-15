<?php

namespace App\Livewire\Applications;

use App\Models\Application;
use Illuminate\Support\Collection;
use Livewire\Component;

class Show extends Component
{
    public Application $application;

    public function mount(Application $application)
    {
        $this->application = $application;
    }

    public function render()
    {
        // Eager load rankings with keywords ordered by date
        $this->application->load([
            'rankings' => fn ($q) => $q->with('keyword')->orderBy('date'),
        ]);

        // Group rankings by keyword and country to avoid mixing locales
        /** @var Collection<int, array{key:string, keyword_id:int, country:string, keyword_name:string, labels:array<int,string>, data:array<int,?int>, latest_position:?int}> $groups */
        $groups = collect($this->application->rankings)
            ->groupBy(fn ($r) => $r->keyword_id.'|'.$r->country)
            ->map(function ($items, $key) {
                $first = $items->first();

                // Build labels (dates) and series (positions), keeping nulls as gaps
                $labels = $items->pluck('date')->map(fn ($d) => (string) $d)->values()->all();
                $data = $items->pluck('position')->map(fn ($p) => $p === null ? null : (int) $p)->values()->all();

                // Determine latest non-null position
                $latestPosition = $items->pluck('position')->filter(fn ($p) => $p !== null)->last();

                return [
                    'key' => $key,
                    'keyword_id' => (int) $first->keyword_id,
                    'country' => (string) $first->country,
                    'keyword_name' => (string) $first->keyword->name,
                    'used' => $first->keyword->used,
                    'labels' => $labels,
                    'data' => $data,
                    'latest_position' => $latestPosition !== null ? (int) $latestPosition : null,
                ];
            })
            ->values();

        return view('livewire.applications.show', [
            'application' => $this->application,
            'groups' => $groups,
        ]);
    }
}
