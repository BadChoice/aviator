<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider' => 'APPLE',
            'provider_country' => 'US',
            'sku' => $this->faker->slug(),
            'developer' => $this->faker->company(),
            'title' => $this->faker->sentence(3),
            'version' => '1.0',
            'product_type_identifier' => '1-B',
            'units' => 1,
            'developer_proceeds' => '4.20',
            'normalized_proceeds' => null,
            'begin_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'customer_currency' => 'USD',
            'country_code' => 'US',
            'currency_of_proceeds' => 'USD',
            'apple_identifier' => (string) $this->faker->numberBetween(1000000000, 2999999999),
            'customer_price' => '9.99',
            'promo_code' => null,
            'parent_identifier' => null,
            'subscription' => null,
            'period' => null,
            'category' => 'Games',
            'cmb' => 'CMB',
            'device' => 'iPhone',
            'supported_platforms' => 'iOS',
            'proceeds_reason' => null,
            'preserved_pricing' => null,
            'client' => null,
            'order_type' => null,
            'row_hash' => (string) str()->random(32),
            'raw' => [],
        ];
    }
}
