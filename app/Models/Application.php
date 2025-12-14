<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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

    public function rankings(): MorphMany
    {
        return $this->morphMany(Ranking::class, 'subject');
    }
}
