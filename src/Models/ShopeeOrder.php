<?php

namespace Laraditz\Shopee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShopeeOrder extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'shop_id', 'status'];

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

    public function returns(): HasMany
    {
        return $this->hasMany(ShopeeReturn::class, 'order_sn');
    }
}
