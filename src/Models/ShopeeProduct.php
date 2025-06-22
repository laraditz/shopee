<?php

namespace Laraditz\Shopee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopeeProduct extends Model
{
    protected $fillable = ['id', 'shop_id', 'status', 'category_id', 'name', 'sku', 'has_model'];

    protected $casts = [
        'has_model' => 'boolean',
    ];

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(ShopeeShop::class);
    }

    public function models(): HasMany
    {
        return $this->hasMany(ShopeeProductModel::class, 'product_id');
    }
}
