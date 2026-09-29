<?php

namespace Laraditz\Shopee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopeeReturn extends Model
{
    protected $fillable = [
        'id',
        'shop_id',
        'order_sn',
        'status',
        'negotiation_status',
        'seller_proof_status',
        'seller_compensation_status',
        'refund_amount',
        'currency',
        'return_created_at',
        'return_updated_at',
    ];

    protected $casts = [
        'shop_id' => 'integer',
        'refund_amount' => 'decimal:2',
        'return_created_at' => 'datetime',
        'return_updated_at' => 'datetime',
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(ShopeeOrder::class, 'order_sn');
    }
}
