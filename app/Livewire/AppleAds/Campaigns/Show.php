<?php

namespace App\Livewire\AppleAds\Campaigns;

use App\Models\AppleAdsCampaign;
use App\Services\AppleAds\AppleAds;
use App\Services\AppleAds\AppleAdsRecommendation;
use Carbon\Carbon;
use Livewire\Component;

class Show extends Component
{
    public AppleAdsCampaign $campaign;
    public int $dateRange = 7;
    public array $recommendations = [];
    public bool $loadingRecommendations = false;

    public function mount(AppleAdsCampaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    public function syncCampaign()
    {
        try {
            $this->campaign->syncFromApi();
            session()->flash('message', 'Campaign synced successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to sync campaign: ' . $e->getMessage());
        }
    }

    public function runRecommendations()
    {
        $this->loadingRecommendations = true;

        try {
            $recommendationService = new AppleAdsRecommendation();
            $this->recommendations = $recommendationService->analyze($this->campaign);
            session()->flash('message', count($this->recommendations) . ' recommendations generated.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to generate recommendations: ' . $e->getMessage());
        }

        $this->loadingRecommendations = false;
    }

    public function applyRecommendation($id)
    {
        try {
            $recommendation = collect($this->recommendations)->firstWhere('id', $id);

            if (!$recommendation) {
                throw new \Exception('Recommendation not found.');
            }

            $recommendationService = new AppleAdsRecommendation();
            $recommendationService->apply($this->campaign, $recommendation);

            $this->recommendations = array_filter($this->recommendations, fn($r) => $r['id'] !== $id);

            session()->flash('message', 'Recommendation applied successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to apply recommendation: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $appleAds = AppleAds::make();

        $adGroups = [];
        $keywords = [];
        $metrics = null;

        try {
            $adGroupsResponse = $appleAds->getAdGroups($this->campaign->apple_campaign_id);
            $adGroups = $adGroupsResponse['data'] ?? [];

            foreach ($adGroups as &$adGroup) {
                $adsResponse = $appleAds->getAds($this->campaign->apple_campaign_id, $adGroup['id']);
                $adGroup['ads'] = $adsResponse['data'] ?? [];
            }

            if (!empty($adGroups)) {
                $firstAdGroup = $adGroups[0];
                $keywordsResponse = $appleAds->getKeywords($this->campaign->apple_campaign_id, $firstAdGroup['id']);
                $keywords = $keywordsResponse['data'] ?? [];
            }

            $endDate = Carbon::now();
            $startDate = Carbon::now()->subDays($this->dateRange);

            $metricsResponse = $appleAds->getCampaignMetrics($this->campaign->apple_campaign_id, [
                'startTime' => $startDate->toIso8601String(),
                'endTime' => $endDate->toIso8601String(),
                'selector' => [
                    'orderBy' => [
                        ['field' => 'localSpend', 'sortOrder' => 'DESCENDING']
                    ]
                ],
                'groupBy' => ['countryOrRegion'],
                'returnRowTotals' => true,
                'returnRecordsWithNoMetrics' => false,
            ]);

            $metrics = $metricsResponse['data']['reportingDataResponse']['row'][0]['total'] ?? null;
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to fetch campaign data: ' . $e->getMessage());
        }

        return view('livewire.apple-ads.campaigns.show', [
            'adGroups' => $adGroups,
            'keywords' => $keywords,
            'metrics' => $metrics,
        ]);
    }
}
