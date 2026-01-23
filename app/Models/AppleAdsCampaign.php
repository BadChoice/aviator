<?php

namespace App\Models;

use App\Services\AppleAds\AppleAds;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppleAdsCampaign extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'serving_state_reasons' => 'array',
        'raw' => 'array',
        'last_synced_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function ads(): HasMany
    {
        return $this->hasMany(AppleAdsAd::class);
    }

    public function syncFromApi(): void
    {
        $appleAds = AppleAds::make();
        $campaignData = $appleAds->getCampaign($this->apple_campaign_id);

        $this->update([
            'name' => $campaignData['data']['name'] ?? $this->name,
            'status' => $campaignData['data']['status'] ?? $this->status,
            'budget_amount' => $campaignData['data']['budgetAmount']['amount'] ?? null,
            'daily_budget_amount' => $campaignData['data']['dailyBudgetAmount']['amount'] ?? null,
            'currency' => $campaignData['data']['budgetAmount']['currency'] ?? 'USD',
            'start_date' => $campaignData['data']['startTime'] ?? null,
            'end_date' => $campaignData['data']['endTime'] ?? null,
            'country_or_region' => $campaignData['data']['countriesOrRegions'][0] ?? null,
            'serving_state_reasons' => $campaignData['data']['servingStateReasons'] ?? null,
            'raw' => $campaignData,
            'last_synced_at' => now(),
        ]);
    }

    public function isActive(): bool
    {
        return $this->status === 'ENABLED';
    }
}
