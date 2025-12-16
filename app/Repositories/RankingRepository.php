<?php

namespace App\Repositories;

use App\Models\Application;
use App\Models\Ranking;
use Illuminate\Support\Collection;

class RankingRepository
{
    /**
     * Fetch the latest ranking per keyword for a given application.
     *
     * @return Collection<int, array{keyword:string,country:?string,position:?int,date:?string}>
     */
    public function latestPerKeywordForApplication(Application $application): Collection
    {
        /** @var Collection<int, array{keyword:string,country:?string,position:?int,date:?string}> $result */
        $result = Ranking::query()
            ->whereMorphedTo('subject', $application)
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

        return $result;
    }

    /**
     * Group rankings by keyword and country for a given application, returning chart-ready series.
     *
     * Each item contains labels (dates) and data (positions) preserving nulls as gaps,
     * and the latest non-null position.
     *
     * @return Collection<int, array{
     *     key:string,
     *     keyword_id:int,
     *     country:string,
     *     keyword_name:string,
     *     used:mixed,
     *     labels:array<int,string>,
     *     data:array<int,?int>,
     *     latest_position:?int,
     * }>
     */
    public function groupedSeriesForApplication(Application $application): Collection
    {
        $application->load([
            'rankings' => fn ($q) => $q->with('keyword')->orderBy('date'),
        ]);

        /** @var Collection<int, array{key:string, keyword_id:int, country:string, keyword_name:string, used:mixed, labels:array<int,string>, data:array<int,?int>, latest_position:?int}> $groups */
        $groups = collect($application->rankings)
            ->groupBy(fn ($r) => $r->keyword_id.'|'.$r->country)
            ->map(function ($items, $key) {
                $first = $items->first();

                $labels = $items->pluck('date')->map(fn ($d) => (string) $d)->values()->all();
                $data = $items->pluck('position')->map(fn ($p) => $p === null ? null : (int) $p)->values()->all();
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

        return $groups;
    }
}
