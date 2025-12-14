<?php

namespace App\Livewire\Applications;

use App\Models\Application;
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
        return view('livewire.applications.show',[
            'application' => $this->application
        ]);
    }
}
