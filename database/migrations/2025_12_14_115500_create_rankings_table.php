<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rankings', function (Blueprint $table): void {
            $table->id();
            $table->date('date');
            $table->string('country', 2)->default('US');
            $table->foreignIdFor(\App\Models\Keyword::class)->constrained()->cascadeOnDelete();
            $table->morphs('subject'); // application or competitor
            $table->unsignedInteger('position')->nullable();
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->unsignedInteger('rating_count')->nullable();
            $table->timestamps();

            /*$table->unique([
                'date', 'country', 'keyword_id', 'subject_type', 'subject_id',
            ], 'rankings_unique_per_day');*/
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rankings');
    }
};
