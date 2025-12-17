<?php

namespace App\Livewire\Applications;

use App\Models\Application;
use App\Models\Keyword;
use App\Repositories\RankingRepository;
use App\Services\AppStore\AppStoreConnect;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

class Show extends Component
{
    public Application $application;
    public string $newKeywords = '';

    public function mount(Application $application): void
    {
        $this->application = $application;
    }

    public function addKeywords(): void {
        Str::of($this->newKeywords)->explode(",")->map(fn ($word) => trim($word))->filter()->each(function (string $word) {
            $this->application->keywords()->firstOrCreate([
                'name' => $word,
            ]);
        });
        $this->newKeywords = '';
    }

    public function removeKeyword(int $keywordId): void {
        Keyword::find($keywordId)->delete();
    }

    public function render(): \Illuminate\View\View
    {

        $reviews = AppStoreConnect::make()->reviews($this->application->appstore_id)['data'];

        /** @var Collection<int, array{key:string, keyword_id:int, country:string, keyword_name:string, used:mixed, labels:array<int,string>, data:array<int,?int>, latest_position:?int}> $groups */
        $groups = app(RankingRepository::class)
            ->groupedSeriesForApplication($this->application);

        return view('livewire.applications.show', [
            'application' => $this->application,
            'groups' => $groups,
            'reviews' => $reviews
        ]);
    }
}
