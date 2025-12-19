<?php

namespace App\Livewire\Sales;

use App\Models\Application;
use App\Repositories\SalesRepository;
use Livewire\Component;

class Dailysummary extends Component
{
    public function render()
    {

        $fromDate = now()->subDays(2);

        $applications = Application::all();
        $sales = (new SalesRepository)->dailySummary($fromDate);


        return view('livewire.sales.dailysummary', [
            'fromDate' => $fromDate,
            'applications' => $applications,
            'sales' => $sales
        ]);
    }
}
