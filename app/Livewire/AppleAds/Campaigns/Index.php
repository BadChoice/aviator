<?php

namespace App\Livewire\AppleAds\Campaigns;

use App\Models\AppleAdsCampaign;
use App\Models\Application;
use App\Services\AppleAds\AppleAds;
use Livewire\Component;

class Index extends Component
{
    public $applicationFilter = null;
    public $statusFilter = 'all';

    public function syncAll()
    {
        try {
            $appleAds = AppleAds::make();
            $campaignsResponse = $appleAds->getCampaigns();

            foreach ($campaignsResponse['data'] as $campaignData) {
                AppleAdsCampaign::updateOrCreate(
                    ['apple_campaign_id' => $campaignData['id']],
                    [
                        'name' => $campaignData['name'],
                        'status' => $campaignData['status'],
                        'budget_amount' => $campaignData['budgetAmount']['amount'] ?? null,
                        'daily_budget_amount' => $campaignData['dailyBudgetAmount']['amount'] ?? null,
                        'currency' => $campaignData['budgetAmount']['currency'] ?? 'USD',
                        'start_date' => $campaignData['startTime'] ?? null,
                        'end_date' => $campaignData['endTime'] ?? null,
                        'country_or_region' => $campaignData['countriesOrRegions'][0] ?? null,
                        'serving_state_reasons' => $campaignData['servingStateReasons'] ?? null,
                        'raw' => $campaignData,
                        'last_synced_at' => now(),
                    ]
                );
            }

            session()->flash('message', 'Campaigns synced successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to sync campaigns: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = AppleAdsCampaign::with('application');

        if ($this->applicationFilter) {
            $query->where('application_id', $this->applicationFilter);
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.apple-ads.campaigns.index', [
            'campaigns' => $query->latest('last_synced_at')->get(),
            'applications' => Application::all(),
        ]);
    }
}
