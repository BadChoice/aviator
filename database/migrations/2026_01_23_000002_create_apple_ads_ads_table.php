<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apple_ads_ads', function (Blueprint $table) {
            $table->id();
            $table->string('apple_ad_id')->unique();
            $table->string('apple_adgroup_id');
            $table->foreignIdFor(\App\Models\AppleAdsCampaign::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('status');
            $table->string('ad_format');
            $table->json('creative_sets')->nullable();

            // Cached performance metrics
            $table->decimal('impressions', 15, 0)->nullable();
            $table->decimal('taps', 15, 0)->nullable();
            $table->decimal('installs', 15, 0)->nullable();
            $table->decimal('spend', 12, 2)->nullable();
            $table->decimal('avg_cpa', 12, 2)->nullable();
            $table->decimal('avg_cpt', 12, 2)->nullable();
            $table->decimal('conversion_rate', 5, 4)->nullable();

            $table->json('raw')->nullable();
            $table->timestamp('metrics_updated_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['apple_ads_campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apple_ads_ads');
    }
};
