<?php

namespace App\Livewire\Applications;

use App\Models\Application;
use App\Services\AppStore\AppStoreConnect;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.applications.index',[
            'applications' => Application::all()
        ]);
    }
}
