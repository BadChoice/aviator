<?php

namespace App\Livewire\Appstore;

use App\Services\AppStore\AppStoreSearch;
use Livewire\Component;

class Search extends Component
{

    protected array $json;

    public function mount()
    {
        $this->json = (new AppStoreSearch)->search(request('term'));
    }

    public function render()
    {
        return view('livewire.appstore.search',[
            'json' => $this->json
        ]);
    }
}
