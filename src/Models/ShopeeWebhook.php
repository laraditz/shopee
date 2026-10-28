<?php

namespace Laraditz\Shopee\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopeeWebhook extends Model
{
    use MassPrunable, HasUuids;

    protected $fillable = ['id', 'shop_id', 'code', 'data', 'sent_timestamp'];

    protected $casts = [
        'data' => 'json',
        'sent_timestamp' => 'timestamp'
    ];

    public function prunable(): Builder
    {
        $days = config('shopee.logging.prune_after_days');

        if (is_numeric($days) && (int) $days > 0) {
            return static::where('created_at', '<=', now()->subDays((int) $days));
        }

        return static::query()->whereRaw('1 = 0');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(ShopeeShop::class);
    }
}
