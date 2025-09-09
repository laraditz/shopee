<?php

namespace Laraditz\Shopee\Models;

use Illuminate\Database\Eloquent\Model;
use Laraditz\Shopee\Models\ShopeeProduct;

class ShopeeProductModel extends Model
{
    protected $fillable = ['id', 'product_id', 'name', 'sku', 'price_info', 'stock_info', 'status', 'weight', 'dimension'];

    protected $casts = [
        'price_info' => 'json',
        'stock_info' => 'json',
        'dimension' => 'json',
    ];

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    public function product()
    {
        return $this->belongsTo(ShopeeProduct::class);
    }
}
