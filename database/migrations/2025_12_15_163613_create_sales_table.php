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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->index();
            $table->string('provider_country', 2)->nullable();
            $table->string('sku')->index();
            $table->string('developer')->nullable();
            $table->string('title')->nullable();
            $table->string('version')->nullable();
            $table->string('product_type_identifier', 10)->nullable()->index();
            $table->integer('units')->default(0);
            $table->decimal('developer_proceeds', 12, 2)->default(0);
            $table->date('begin_date')->index();
            $table->date('end_date')->nullable();
            $table->string('customer_currency', 3)->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('currency_of_proceeds', 3)->nullable();
            $table->string('apple_identifier')->nullable()->index();
            $table->decimal('customer_price', 12, 2)->nullable();
            $table->string('promo_code')->nullable();
            $table->string('parent_identifier')->nullable();
            $table->string('subscription')->nullable();
            $table->string('period')->nullable();
            $table->string('category')->nullable();
            $table->string('cmb')->nullable();
            $table->string('device')->nullable();
            $table->string('supported_platforms')->nullable();
            $table->string('proceeds_reason')->nullable();
            $table->string('preserved_pricing')->nullable();
            $table->string('client')->nullable();
            $table->string('order_type')->nullable();
            $table->string('row_hash', 64)->unique();
            $table->json('raw')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
