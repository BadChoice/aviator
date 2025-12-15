<?php

namespace App\Livewire\Appstore;

use App\Services\AppStore\AppStoreSearch;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Search extends Component
{
    public ?array $results = null;

    public string $search = '';
    public string $country = 'US';

    public function onSearchPressed(){
        $this->results = Cache::remember("search-$this->search", now()->addMinutes(10), function () {
            return ($this->search == "") ? null : (new AppStoreSearch)->search($this->search);
        });
    }
    public function render()
    {
        $this->results = ($this->search == "") ? ['results' => []] : (new AppStoreSearch)->search($this->search);
        return view('livewire.appstore.search',[
            'results' => $this->results
        ]);
    }
}
