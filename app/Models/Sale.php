<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'provider_country',
        'sku',
        'developer',
        'title',
        'version',
        'product_type_identifier',
        'units',
        'developer_proceeds',
        'begin_date',
        'end_date',
        'customer_currency',
        'country_code',
        'currency_of_proceeds',
        'apple_identifier',
        'customer_price',
        'promo_code',
        'parent_identifier',
        'subscription',
        'period',
        'category',
        'cmb',
        'device',
        'supported_platforms',
        'proceeds_reason',
        'preserved_pricing',
        'client',
        'order_type',
        'row_hash',
        'raw',
    ];

    protected function casts(): array
    {
        return [
            'begin_date' => 'date',
            'end_date' => 'date',
            'units' => 'integer',
            'developer_proceeds' => 'decimal:2',
            'customer_price' => 'decimal:2',
            'raw' => 'array',
        ];
    }
}
