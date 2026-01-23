<?php

namespace App\Models;

use App\Services\AppStore\AppStoreConnect;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

class Application extends Model
{
    protected $guarded = [];

    public function competitors(): HasMany
    {
        return $this->hasMany(Competitor::class);
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(Keyword::class);
    }

    public function appleAdsCampaigns(): HasMany
    {
        return $this->hasMany(AppleAdsCampaign::class);
    }

    public function rankings(): MorphMany
    {
        return $this->morphMany(Ranking::class, 'subject');
    }

    public function lastRankings(CarbonInterface $from = null) : \Illuminate\Database\Eloquent\Collection {
        return $this->rankings()
            ->where('position', '>', 0)
            ->orderBy('created_at','desc')
            ->orderBy('position','asc')
            ->when($from, function ($query, $from) {
                $query->where('created_at', '>=', $from);
            })
            ->get();
    }

    public function appStoreReviews(?CarbonInterface $from = null) : Collection {
        return collect(AppStoreConnect::make()->reviews($this->appstore_id)['data'])->filter(function ($review) use ($from) {
            return $from == null || Carbon::parse($review['attributes']['createdDate'])->gt($from);
        });
    }
}
