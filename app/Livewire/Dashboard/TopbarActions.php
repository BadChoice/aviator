<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Jobs\RunRankingsTrackDaily;
use App\Jobs\RunSalesSyncYesterday;
use Livewire\Component;

class TopbarActions extends Component
{
    public bool $runningRankings = false;

    public bool $runningSalesSync = false;

    public function runRankings(): void
    {
        $this->runningRankings = true;
        RunRankingsTrackDaily::dispatch();
        $this->dispatch('notify', type: 'success', message: __('Rankings tracking started'));
        $this->runningRankings = false;
    }

    public function runSalesSync(): void
    {
        $this->runningSalesSync = true;
        RunSalesSyncYesterday::dispatch();
        $this->dispatch('notify', type: 'success', message: __('Sales sync for yesterday started'));
        $this->runningSalesSync = false;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard.topbar-actions');
    }
}
