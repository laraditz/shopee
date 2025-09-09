<?php

namespace Laraditz\Shopee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopeeWebhook extends Model
{
    use HasUuids;

    protected $fillable = ['id', 'shop_id', 'code', 'data', 'sent_timestamp'];

    protected $casts = [
        'data' => 'json',
        'sent_timestamp' => 'timestamp'
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(ShopeeShop::class);
    }
}
