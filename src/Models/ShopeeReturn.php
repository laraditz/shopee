<?php

namespace Laraditz\Shopee\Models;

use Illuminate\Database\Eloquent\Model;

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
}
