<?php

namespace App\Models;

use App\Services\AppleAds\AppleAds;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppleAdsAd extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'creative_sets' => 'array',
        'raw' => 'array',
        'impressions' => 'integer',
        'taps' => 'integer',
        'installs' => 'integer',
        'spend' => 'decimal:2',
        'avg_cpa' => 'decimal:2',
        'avg_cpt' => 'decimal:2',
        'conversion_rate' => 'decimal:4',
        'metrics_updated_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AppleAdsCampaign::class, 'apple_ads_campaign_id');
    }

    public function syncMetrics(Carbon $startDate, Carbon $endDate): void
    {
        $appleAds = AppleAds::make();

        $metricsData = $appleAds->getCampaignMetrics($this->campaign->apple_campaign_id, [
            'startTime' => $startDate->toIso8601String(),
            'endTime' => $endDate->toIso8601String(),
            'selector' => [
                'orderBy' => [
                    ['field' => 'countryOrRegion', 'sortOrder' => 'ASCENDING']
                ],
                'conditions' => [
                    [
                        'field' => 'adGroupId',
                        'operator' => 'EQUALS',
                        'values' => [$this->apple_adgroup_id]
                    ]
                ]
            ],
            'groupBy' => ['countryOrRegion'],
            'returnRowTotals' => true,
            'returnRecordsWithNoMetrics' => false,
        ]);

        $totals = $metricsData['data']['reportingDataResponse']['row'][0]['total'] ?? null;

        if ($totals) {
            $this->update([
                'impressions' => $totals['impressions'] ?? 0,
                'taps' => $totals['taps'] ?? 0,
                'installs' => $totals['installs'] ?? 0,
                'spend' => $totals['localSpend']['amount'] ?? 0,
                'avg_cpa' => $totals['avgCPA']['amount'] ?? 0,
                'avg_cpt' => $totals['avgCPT']['amount'] ?? 0,
                'conversion_rate' => $totals['conversionRate'] ?? 0,
                'metrics_updated_at' => now(),
            ]);
        }
    }
}
