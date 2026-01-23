<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apple_ads_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('apple_campaign_id')->unique();
            $table->foreignIdFor(\App\Models\Application::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('status');
            $table->string('budget_amount')->nullable();
            $table->string('daily_budget_amount')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('country_or_region', 2)->nullable();
            $table->json('serving_state_reasons')->nullable();
            $table->json('raw')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['application_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apple_ads_campaigns');
    }
};
